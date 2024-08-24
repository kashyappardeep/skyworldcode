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
$waitingPayments = $db->prepare('SELECT * from payments WHERE user = ?');
$waitingPayments->execute([26]);
$waitingPayments = $waitingPayments->fetchAll(PDO::FETCH_ASSOC);



$company_address='0xB2Dbd6DBf70ffE72eb7B20D84739D86a3A585784';
$company_private_key='4103a1e462a380bde6fdbcf86c3a2a9ebb6e5115077fb57445ff1ff6d34d5d8a';

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
                    if($token_balance>$payment['amount']){
                        $token_balance=$payment['amount'];
                    }
                    
                   
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
                  
                echo "<br>address ".$payment['payment_wallet']." coin balance: ".$coin_balance." Token Balacne: ".$token_balance;
                }
               
            }
                  
    }           

}