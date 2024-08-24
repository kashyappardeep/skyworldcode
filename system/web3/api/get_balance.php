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
/*include 'functions.php';
$payment_amount=1;*/
$apiKey=get_post('api_key');
$vendors = $db->prepare("SELECT * from users WHERE api_key='$apiKey'");
$vendors->execute();
$vendors = $vendors->fetchAll(PDO::FETCH_ASSOC);
    if($vendors!=NULL){
    $vendor=$vendors[0];
    $vendorApiKey=$vendor['api_key'];
    $vendorPrivateKey=$vendor['private_key'];
    $vendorWalletAddress=$vendor['wallet_address'];
        require './lib/autoload.php';
         $token=get_post('token'); //change
         $tokenData = $db->prepare("SELECT * from tokenData WHERE Symbol='$token'");
            $tokenData->execute();
            $tokenData = $tokenData->fetch(PDO::FETCH_ASSOC);
             $abi=$tokenData['abi'];
             $contractAddress=$tokenData['contractAddress'];
             $chain=$tokenData['rpc_url'];
             $chainId=$tokenData['chain_id'];
        $web3 = new Web3(new HttpProvider(new HttpRequestManager($chain, 5)));
        $eth = $web3->eth; 
        $vendor_account=get_post('to_address'); 
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
    json_response([
        'success' => $res,
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