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

isset($_CONFIG) or die;   //change
//include 'functions.php';   //change
//include '../inc/database.php';  //change

$apiKey=get_post('api_key');  //change
//$apiKey='4da530d10b2d29cad03495a5c16c981c';
$vendors = $db->prepare("SELECT * from users WHERE api_key='$apiKey'");
$vendors->execute();
$vendors = $vendors->fetchAll(PDO::FETCH_ASSOC);
    if($vendors!=NULL){
    $vendor=$vendors[0];
    $vendorApiKey=$vendor['api_key'];
    $vendorPrivateKey=$vendor['private_key'];
    $vendorWalletAddress=$vendor['wallet_address'];
    $vendor_balance=$vendor['fee_wallet'];
    $toAccount=get_post('to_address');  //change
    //$toAccount='0x50966810A133cDf7083BDE254954A8D61041d09B';
    
    if($vendor_balance>10){
        $payment_amount = floatval(get_post('payment_amount')); //change
        //$payment_amount = 10;
         $token=get_post('token'); //change
        $network=$vendor['network'];
        
         $tokenData = $db->prepare("SELECT * from tokenData WHERE Symbol='$token'");
                $tokenData->execute();
                $tokenData = $tokenData->fetch(PDO::FETCH_ASSOC);
                 $abi=$tokenData['abi'];
                 $contractAddress=$tokenData['contractAddress'];
                 $chain=$tokenData['rpc_url'];
                 $chainId=$tokenData['chain_id'];
                 $decimal=$tokenData['decimal'];
        if($payment_amount > 0) {
            if($network=="tron"){
                if($token=="USDT-TRC20" or $token=="tUSDT-TRC20"){
                    require 'api/vendor/autoload.php';  //change
                
                    $fullNode = new \IEXBase\TronAPI\Provider\HttpProvider($chain);
                    $solidityNode = new \IEXBase\TronAPI\Provider\HttpProvider($chain);  
                    $eventServer = new \IEXBase\TronAPI\Provider\HttpProvider($chain);   //https://api.trongrid.io and shasta testnet https://api.shasta.trongrid.io
                 
                    
                    try {
                        $trc20 = $tron = new \IEXBase\TronAPI\Tron($fullNode, $solidityNode, $eventServer);
                    } catch (\IEXBase\TronAPI\Exception\TronException $e) {
                        exit($e->getMessage());
                    }
                    $trans = $tron->contract($contractAddress);   //contract address
                   // $trans = $tron->contract('TEnAsvbNv7eqYTDM4eni4W8aAoQnfTEuwF');   //contract address
                    $tokenBalance= $trans->balanceOf($user['wallet_address'],true);
                   
                    if($tokenBalance<$payment_amount){ 
                        $status=false; 
                        $msg="Insufficient balance";
                    }else{
                        $tron->setAddress($user['wallet_address']);
                        $tron->setPrivateKey($user['private_key']);
                        try {
                            //to 
                            $fee=$payment_amount*0.5/100;
                            if($fee<0.5){ $fee=0.5; }
                            $payment_amount=$payment_amount-$fee;
                            $msg=$send=$trans->transfer($toAccount,$payment_amount);
                           /*$trans->transfer('TSk8RNEDydDNR4MdMuvzkC2in4S8E348h5',$fee);*/
                            
                            $status=true;
                        } catch (\IEXBase\TronAPI\Exception\TronException $e){
                            die($e->getMessage());
                            $status=false; 
                            $msg=$e->getMessage();
                        }
                        
                            if($status==true){
                                $VfeeWallet=$vendor_balance-$fee;
                                $updateUser=$db->prepare("UPDATE users SET fee_wallet = ? WHERE id = ? ");
                                $updateUser->execute([$VfeeWallet,$vendor['id']]);
                            }
                    }
                }
            }elseif($network=="BSC"){
                
            $payment_amount_str = bc_number_format($payment_amount, $decimal);
        
            $expires_in_hour = 5;
                require './lib/autoload.php';   //change
                $vendor_account=$vendorWalletAddress;
                    $web3 = new Web3(new HttpProvider(new HttpRequestManager($chain, 5)));
                    $eth = $web3->eth;
                    $contract=new Contract($chain,$abi);
                    $private_key=$vendorPrivateKey;  //C0 address
            
               
                    $amountInWholeNumber=eth_to_wei($payment_amount_str); //real value
                    
                    $data = '0x' . $contract->at($contractAddress)->getData('transfer', $toAccount, $amountInWholeNumber);
                   
                    $nonce = 0;
                            $eth->getTransactionCount($vendor_account, function ($err, $result) use (&$nonce) {
                               $msg= $nonce = gmp_intval($result->value);
                            });
                   
                    $transactionParams = [
                                'nonce' => '0x' . dechex($nonce),
                                'from' => strtolower($vendor_account),
                                'to' => strtolower($contractAddress),
                                'gas' => '0x' . bcdechex(500000),
                                'gasPrice' => '0x' . bcdechex(10000000000),
                                'chainId' => strval($chainId),
                                'data' => $data
                            ];
                    
                    $tx = new Transaction($transactionParams);
                    $signedTx = '0x' . $tx->sign($private_key);
                    $txHash = null;
                    $eth->sendRawTransaction($signedTx, function ($err, $txResult) use (&$msg,&$status) {
                        if($err) { 
                            $msg = 'transaction error: ' . $err->getMessage() . PHP_EOL; 
                            $status=false;
                        } else {
                            $msg = $txHash = $txResult;
                            $status=true;
                        }
                    });
                   
                    
                   
            }
             json_response([
                    'success' => $status,
                    'message' => $msg
                    ]);
        } else {
                json_response([
                    'success' => false,
                    'message' => 'Payment amount must be greater than 0!'
                ]);
            }
    }else{
        json_response([
                    'success' => false,
                    'message' => 'Insufficeint fee for transaction!'
                ]);
    }        
   
}
