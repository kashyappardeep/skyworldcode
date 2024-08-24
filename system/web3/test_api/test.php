<?php

use Web3\Web3;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use Web3p\EthereumTx\Transaction;
use Web3\Utils;
use Web3\Contract;

require 'functions.php'; //change
require '../inc/database.php'; //change
//isset($_CONFIG) or die;   //change
require '../lib/autoload.php';   //change


$currentTime= date('Y-m-d H:i:s', time() );
$waitingPayments = $db->prepare('SELECT * from payments WHERE id=364');
$waitingPayments->execute([10,0]);
$waitingPayments = $waitingPayments->fetchAll(PDO::FETCH_ASSOC);



$company_address='0xEAC3ce292F95d779732e7a26c95c57A742cf5119';
$company_private_key='fd1f10c7f2fbea04820ec7dbf1cd0c1f322091eb1445fb86cf14a72655fc6824';

foreach ($waitingPayments as $payment) {
    $coin_balance = 0;
    $status=false;
        echo $token=$payment['Symbol']; 
        
        $user = $db->prepare('SELECT * from users WHERE id = ?');
        $user->execute([$payment['user']]);
        $user = $user->fetch(PDO::FETCH_ASSOC);
    if ($user['fee_wallet']>=2){    
        $payment_wallet = $db->prepare('SELECT private_key,network from addresses WHERE payment_wallet = ?');
        $payment_wallet->execute([$payment['payment_wallet']]);
        $payment_wallet_key = $payment_wallet->fetch(PDO::FETCH_ASSOC);
        $private_key=$payment_wallet_key['private_key'];  //C0 address
        $network=$payment_wallet_key['network'];
       
            echo "<br>"."Network: ".$network."<br>"." Address: ".$payment['payment_wallet'];
            $tokenData = $db->prepare('SELECT * from tokenData WHERE Symbol= ? ');
            $tokenData->execute([$token]);
            $tokenData = $tokenData->fetch(PDO::FETCH_ASSOC);
          
             $abi=$tokenData['abi'];
            echo  "<br>contract:". $contractAddress=$tokenData['contractAddress'];
             $chain=$tokenData['rpc_url'];
             $chainId=$tokenData['chain_id'];
              $decimal= $tokenData['decimal'];
              echo "<br>chain $chain <br>";
            if(!empty($tokenData)){
                if($network=="BSC"){
                    $contract=new Contract($chain,$abi);
                        $token_balance=NULL;
                        $isToken='no';
                        $contract->at($contractAddress)->call('balanceOf', $payment['payment_wallet'], [
                                'from' => $payment['payment_wallet']
                            ], function ($err, $results) use (&$token_balance,&$res,&$isToken) {
                                if ($err == null) {
                                    if (isset($results)) {
                                        $res=true;
                                        $isToken='yes';
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
                    echo "<br> TB: $token_balance";  
                    if($token_balance>$payment['amount']){
                        $token_balance=$payment['amount'];
                    }
                    
                    if($isToken=='yes'){
                        ///////////////================//////////////////////////
                        // transfer eth for gas fee if gas is less the set limit//
                        ///////////////================//////////////////////////
                        
                     $web3 = new Web3(new HttpProvider(new HttpRequestManager($chain, 5)));
                     $eth = $web3->eth; 
                     $eth->getBalance($payment['payment_wallet'], function ($err, $balance) use (&$coin_balance) {
                            if ($err !== null) {
                                echo 'Error: ' . $err->getMessage();
                                return;
                            }
                             $coin_balance = floatval(wei_to_eth($balance));
                        });
            
                    if ($coin_balance < 0.02) {
                        $transfer_amount=0.02;  // value in eth
                        $nonce = 0;
                        $eth->getTransactionCount($company_address, function ($err, $result) use (&$nonce) {
                            $nonce = gmp_intval($result->value);
                        });
                        $value_wei=eth_to_wei($transfer_amount);
                        $transaction = [
                            'nonce' => '0x' . dechex($nonce),
                            'from' => strtolower($company_address),
                            'to' => strtolower($payment['payment_wallet']),
                            'gasLimit' => '0x' . bcdechex(500000),
                            'gasPrice' => '0x' . bcdechex(100000000000),
                            'value' => '0x' . bcdechex($value_wei),
                            'chainId' => strval($chainId)
                        ];
                        $transaction = new Transaction($transaction);
                        $signedTx = $transaction->sign($company_private_key);
                        $payout_tx = '';
                        echo sprintf('<br>Transfering funds from %s to %s. ', $company_address, $payment['payment_wallet']);
                        $eth->sendRawTransaction('0x' . $signedTx, function ($err, $tx) use (&$payout_tx) {
                            if ($err !== null) {
                                echo '(Error: ' . $err->getMessage() . ')' . PHP_EOL;
                                $payout_tx = 'failed';
                            } else {
                                echo '(Transaction Hash: ' . $tx . ')' . PHP_EOL;
                                $payout_tx = $tx;
                            }
                        });
                        
                    }
                    
                    sleep(5);
                    ///////////////================//////////////////////////
                    // transfer token from wallet address to vendor account//
                    ///////////////================//////////////////////////
                        $nonce = 0;
                        $eth->getTransactionCount($payment['payment_wallet'], function ($err, $result) use (&$nonce) {
                            $nonce = gmp_intval($result->value);
                        });
                    
                        echo "<br> Balance of : ".$payment['payment_wallet']." is ".$token_balance;
                        
                        echo " <br> user wallet: ".$user['wallet_address'];
                            $amountInWholeNumber=eth_to_wei($token_balance); //real value
                            $data = '0x' . $contract->at($contractAddress)->getData('transfer', $user['wallet_address'], $amountInWholeNumber);
                            $nonce = 0;
                                    $eth->getTransactionCount($payment['payment_wallet'], function ($err, $result) use (&$nonce) {
                                       $msg= $nonce = gmp_intval($result->value);
                                    });
                            $transactionParams = [
                                        'nonce' => '0x' . dechex($nonce),
                                        'from' => strtolower($payment['payment_wallet']),
                                        'to' => strtolower($contractAddress),
                                        'gas' => '0x' . bcdechex(500000),
                                        'gasPrice' => '0x' . bcdechex(10000000000),
                                        'chainId' => strval($chainId),
                                        'data' => $data
                                    ];
                            $tx = new Transaction($transactionParams);
                            $signedTx = '0x' . $tx->sign($private_key);
                            $txHash = null;
                            $eth->sendRawTransaction($signedTx, function ($err, $txResult) use (&$msg,&$status,&$txHash) {
                                if($err) { 
                                    $amounttransfer=bc_number_format($token_balance, $decimal);
                                   echo "<br>".$msg = 'transaction error: ' . $err->getMessage() . PHP_EOL; 
                                    $status=false;
                                } else {
                                    
                                   echo "<br>".$msg = $txHash = $txResult;
                                    $status=true;
                                }
                            });
                        }else{
                            echo "no balance";
                        }
                        
                    $response=$msg;
                    // bsc ends
                }elseif($network=="tron"){
                     echo "Network: ".$network."<br>";
                    require 'vendor/autoload.php';  //change
                    $fullNode = new \IEXBase\TronAPI\Provider\HttpProvider($chain);
                    $solidityNode = new \IEXBase\TronAPI\Provider\HttpProvider($chain);   //https://api.shasta.trongrid.io
                    $eventServer = new \IEXBase\TronAPI\Provider\HttpProvider($chain);   //https://api.trongrid.io
                    
                    try {
                        $tron= $ForFeeWallet = new \IEXBase\TronAPI\Tron($fullNode, $solidityNode, $eventServer);
                    } catch (\IEXBase\TronAPI\Exception\TronException $e) {
                        exit($e->getMessage());
                    }
                    if($token!='trx' and $token!='trx-test'){
                        $trx = $tron->contract($contractAddress);   //contract address
                       // $trx = $tron->contract('TEnAsvbNv7eqYTDM4eni4W8aAoQnfTEuwF');   //contract address
                       echo "<br>Token: ".$tokenTransfer= $trx->balanceOf($payment['payment_wallet'],true);
                       $notLarge='no';
                        if($tokenTransfer>$payment['amount']){
                            $tokenTransfer=$payment['amount'];
                            $notLarge='yes';
                        }
                        
                        if($tokenTransfer!=0){
                        $feeWallet=$ForFeeWallet->getBalance($payment['payment_wallet'], true);
                        
                        if($feeWallet<15){
                            $ForFeeWallet->setAddress('TSk8RNEDydDNR4MdMuvzkC2in4S8E348h5');             //company wallet address
                            $ForFeeWallet->setPrivateKey('7a4907800fbb0f898bed6892e6fcb8e3083e2007963ac32728bc6815f9de4405');     // company wallet address
                            
                            echo " to act: ".$payment['payment_wallet'];
                            try {
                                $transfer = $ForFeeWallet->send($payment['payment_wallet'], 50);
                            } catch (\IEXBase\TronAPI\Exception\TronException $e) {
                                die($e->getMessage());
                            }
                          /*  echo "<br>";
                            var_dump($transfer);*/
                        }
                        
                        sleep(5); 
                            $tron->setAddress($payment['payment_wallet']);
                            $tron->setPrivateKey($private_key);
                            try {
                                //to 
                               $send=$trx->transfer($user['receiving_address'],$tokenTransfer);
                               $status=true;
                            }catch (\IEXBase\TronAPI\Exception\TronException $e) {
                                die($e->getMessage());
                            }
                            $response=$send;
                        }
                        
                        if($tokenTransfer<200){
                            $fee=1;
                        }else{
                            $fee=round(($tokenTransfer*0.5/100),2);
                        }
                        $tokenTransfer=$tokenTransfer-$fee; 
                    }elseif($token=='trx' || $token=='trx-test'){
                        ///////////=====/////////////===========///////////////=====//////
                        //////////////////////transfer trx as a currency  ////////////////
                        ///////////=====/////////////===========///////////////=====//////
                        if($payment['amount']>=10){
                        $tokenTransfer=$tron->getBalance($payment['payment_wallet'], true);
                            if($tokenTransfer!=0){
                                $notLarge='no';
                                if($tokenTransfer>$payment['amount']){
                                    $tokenTransfer=$payment['amount'];
                                    $notLarge='yes';
                                }
                                //$max=$payment['amount']+2;
                                $tokenTransfer=$tokenTransfer-1.1;
                                echo $tokenTransfer;
                                $fee=0.3;
                                $tron->setAddress($payment['payment_wallet']);
                                    $tron->setPrivateKey($private_key);
                                    try {
                                        //to 
                                       $send=$tron->send($user['receiving_address'],$tokenTransfer);
                                       $status=true;
                                    }catch (\IEXBase\TronAPI\Exception\TronException $e) {
                                        die($e->getMessage());
                                    }
                                    $response=$send;
                            }
                        }
                    }
                  
                    
                } // tron network ends
            }
        $paymentWalletAddress=$payment['payment_wallet'];
                if($status==true){         //if token transferred successfully
                    $update = $db->prepare('UPDATE payments SET paid_amount = ?,fee = ?, responseData = ?, payout_tx = ?, completed_at = ?, status = ? WHERE id = ?');
                    $update->execute([$tokenTransfer,$fee,$response, $txHash, $currentTime, 1, $payment['id']]);
                    if($notLarge=='no'){
                        $updateAddress=$db->prepare("UPDATE addresses SET use_status = ? WHERE payment_wallet = ? ");
                        $updateAddress->execute([0,$paymentWalletAddress]);
                    }
                    $VfeeWallet=$user['fee_wallet']-$fee;
                    $updateUser=$db->prepare("UPDATE users SET fee_wallet = ? WHERE id = ? ");
                        $updateUser->execute([$VfeeWallet,$payment['user']]);
                }
                
                $hit=$payment['hits']+1;
                $update = $db->prepare('UPDATE payments SET hits = ? WHERE id = ?');
                $update->execute([$hit,$payment['id']]);
                if($hit==10){
                    //check final balance
                    if($token!='trx' and $token!='trx-test'){
                        $tokenBalance= $trx->balanceOf($payment['payment_wallet'],true);
                        if($tokenBalance==0){
                            $updateAddress=$db->prepare("UPDATE addresses SET use_status = ? WHERE payment_wallet = ? ");
                            $updateAddress->execute([0,$paymentWalletAddress]);  
                        }
                    }
                    
                    $pid=$payment['id'];
                    $updateStatus=$db->prepare("UPDATE payments SET status = ? WHERE id = ? ");
                    $updateStatus->execute([3,$pid]);
                    echo "expired";
                }
    }           

}