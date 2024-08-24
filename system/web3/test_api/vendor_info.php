<?php
use Sop\CryptoTypes\Asymmetric\EC\ECPublicKey;
use Sop\CryptoTypes\Asymmetric\EC\ECPrivateKey;
use Sop\CryptoEncoding\PEM;
use kornrunner\Keccak;
use Web3\Web3;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use Web3p\EthereumTx\Transaction;
use Web3\Utils;
use Web3\Contract;

isset($_CONFIG) or die;
//include 'functions.php';

//require 'lib/autoload.php';
//require 'inc/database.php';
$apiKey=get_post('api_key');
//$apiKey='eracomtron';

$vendors = $db->prepare("SELECT * from users WHERE api_key='$apiKey'");
$vendors->execute();
$vendors = $vendors->fetchAll(PDO::FETCH_ASSOC);
    if($vendors!=NULL){
    $vendor=$vendors[0];
    $vendorApiKey=$vendor['api_key'];
    $vendorPrivateKey=$vendor['private_key'];
    $vendor_account=$vendor['wallet_address'];
        
         
         $network=$vendor['network'];
         
        if($network=="BSC"){
            $token='BUSD';
            $tokenData = $db->prepare("SELECT * from tokenData WHERE Symbol='$token'");
                $tokenData->execute();
                $tokenData = $tokenData->fetch(PDO::FETCH_ASSOC);                   
                 $abi=$tokenData['abi'];                                
                 $contractAddress=$tokenData['contractAddress'];                                          
                 $chain=$tokenData['rpc_url'];                                                                     
                 $chainId=$tokenData['chain_id']; 
            $web3 = new Web3(new HttpProvider(new HttpRequestManager($chain, 5)));
            $eth = $web3->eth;
            $coin_balance=NULL;
            $eth->getBalance($vendor_account, function ($err, $balance) use (&$coin_balance) {
            if ($err == null) {
                $coin_balance=$wallet_balance = wei_to_eth($balance);
            }else{
                $coin_balance=null;
            }
            
            });
            
            $contract=new Contract($chain,$abi);
            $token_balance=NULL;
            $contract->at($contractAddress)->call('balanceOf', $vendor_account, [
                    'from' => $vendor_account
                ], function ($err, $results) use (&$token_balance,&$res) {
                    if ($err == null) {
                        if (isset($results)) {
                            $res=true;
                        foreach ($results as &$result) {
                            $bn = Utils::toBn($result);
                           $token_balance=wei_to_eth($bn->toString());
                        }
                    }else
                        {
                            $res=false;
                        }
                    }else{
                            $token_balance=$err;
                            $res=false;
                        
                    }
                    
                });
            
        }elseif($network=="tron"){
            
            $token='USDT-TRC20';
            require 'api/vendor/autoload.php';  //change
           $tokenData = $db->prepare("SELECT * from tokenData WHERE Symbol='$token'");
                $tokenData->execute();
                $tokenData = $tokenData->fetch(PDO::FETCH_ASSOC);                   
                 $abi=$tokenData['abi'];                                
                 $contractAddress=$tokenData['contractAddress'];                                          
                 $chain=$tokenData['rpc_url'];                                                                     
                 $chainId=$tokenData['chain_id']; 
         $fullNode = new \IEXBase\TronAPI\Provider\HttpProvider($chain);
            $solidityNode = new \IEXBase\TronAPI\Provider\HttpProvider($chain);   //https://api.shasta.trongrid.io
            $eventServer = new \IEXBase\TronAPI\Provider\HttpProvider($chain);   //https://api.trongrid.io
           
            try {
                $tron = new \IEXBase\TronAPI\Tron($fullNode, $solidityNode, $eventServer);
                $trx = $tron->contract($contractAddress); 
                
            } catch (\IEXBase\TronAPI\Exception\TronException $e) {
                exit($e->getMessage());
            }
            
            if(!empty($vendor['wallet_address'])){
                $token_balance= $trx->balanceOf($vendor['wallet_address'],true);
                $coin_balance=$tron->getBalance($vendor['wallet_address'], true); 
            }
        }
    json_response([
        'success' => $res,
        'network' => $network,
        'account_id' => $vendor['id'],
        'payout_wallet' => $vendor['wallet_address'],
        'receiving_wallet' => $vendor['receiving_address'],
        'fee_wallet' => $vendor['fee_wallet'],
        'coin_balance' => $coin_balance,
        'token_balance' => $token_balance,
        'message' => 'Balance of '.$vendor_account
        ]);    
        
    }else{
        json_response([
        'success' => false,
        'coin_balance' => null,
        'token_balance' => null,
        'message' => 'Invalid API key'
        ]);  
    }
?>