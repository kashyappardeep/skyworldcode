<?php

require '../lib/autoload.php'; 
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
include 'functions.php';   //change
include '../inc/database.php';  //change


//$provider = 'https://data-seed-prebsc-1-s3.binance.org:8545/';//testing
$provider = 'https://bsc-dataseed.binance.org/';

$web3 = new Web3(new HttpProvider(new HttpRequestManager($provider, 5)));
$contractABI = json_decode('[{"inputs":[],"stateMutability":"nonpayable","type":"constructor"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"buyer","type":"address"},{"indexed":true,"internalType":"uint256","name":"spent","type":"uint256"},{"indexed":true,"internalType":"uint256","name":"recieved","type":"uint256"}],"name":"Buy","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"buyer","type":"address"},{"indexed":true,"internalType":"uint256","name":"spent","type":"uint256"},{"indexed":true,"internalType":"uint256","name":"recieved","type":"uint256"}],"name":"Sale","type":"event"},{"inputs":[],"name":"Buystatus","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"Sellstatus","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"_address","type":"address"},{"internalType":"uint256","name":"_amount","type":"uint256"},{"internalType":"contract BEP20","name":"token","type":"address"}],"name":"airDrop","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"amnt","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"buy","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"buyRatePerToken","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"rate","type":"uint256"},{"internalType":"uint256","name":"div","type":"uint256"}],"name":"buygetrate","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"buytoken","outputs":[{"internalType":"contract BEP20","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"contract BEP20","name":"_buytoken","type":"address"},{"internalType":"contract BEP20","name":"_saletoken","type":"address"}],"name":"changeToken","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"uint256","name":"amount","type":"uint256"},{"internalType":"contract BEP20","name":"token","type":"address"}],"name":"contribute","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"_buytoken","type":"address"},{"internalType":"address","name":"_saletoken","type":"address"}],"name":"init","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"rateDiv","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"ratePerToken","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"sale","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"uint256","name":"rate","type":"uint256"},{"internalType":"uint256","name":"div","type":"uint256"}],"name":"salegetrate","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"saletoken","outputs":[{"internalType":"contract BEP20","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"_status","type":"uint256"}],"name":"setBuyStatus","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"uint256","name":"_status","type":"uint256"}],"name":"setSellStatus","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address payable[]","name":"_contributors","type":"address[]"},{"internalType":"uint256[]","name":"_balances","type":"uint256[]"},{"internalType":"contract BEP20","name":"token","type":"address"}],"name":"shareContribution","outputs":[],"stateMutability":"payable","type":"function"},{"inputs":[{"internalType":"address payable","name":"_contributors","type":"address"},{"internalType":"uint256","name":"_balances","type":"uint256"},{"internalType":"contract BEP20","name":"token","type":"address"}],"name":"shareSingleContribution","outputs":[],"stateMutability":"payable","type":"function"},{"inputs":[{"internalType":"contract BEP20","name":"BUSD","type":"address"},{"internalType":"address","name":"userAddress","type":"address"},{"internalType":"uint256","name":"amt","type":"uint256"}],"name":"withdraw","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"}]');
//$tokenContractABI = json_decode('[{"inputs":[],"payable":false,"stateMutability":"nonpayable","type":"constructor"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"owner","type":"address"},{"indexed":true,"internalType":"address","name":"spender","type":"address"},{"indexed":false,"internalType":"uint256","name":"value","type":"uint256"}],"name":"Approval","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"previousOwner","type":"address"},{"indexed":true,"internalType":"address","name":"newOwner","type":"address"}],"name":"OwnershipTransferred","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"from","type":"address"},{"indexed":true,"internalType":"address","name":"to","type":"address"},{"indexed":false,"internalType":"uint256","name":"value","type":"uint256"}],"name":"Transfer","type":"event"},{"constant":true,"inputs":[],"name":"_decimals","outputs":[{"internalType":"uint8","name":"","type":"uint8"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"_name","outputs":[{"internalType":"string","name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"_symbol","outputs":[{"internalType":"string","name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"internalType":"address","name":"owner","type":"address"},{"internalType":"address","name":"spender","type":"address"}],"name":"allowance","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"internalType":"address","name":"spender","type":"address"},{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"approve","outputs":[{"internalType":"bool","name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"internalType":"address","name":"account","type":"address"}],"name":"balanceOf","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"burn","outputs":[{"internalType":"bool","name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"decimals","outputs":[{"internalType":"uint8","name":"","type":"uint8"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"internalType":"address","name":"spender","type":"address"},{"internalType":"uint256","name":"subtractedValue","type":"uint256"}],"name":"decreaseAllowance","outputs":[{"internalType":"bool","name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"getOwner","outputs":[{"internalType":"address","name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"internalType":"address","name":"spender","type":"address"},{"internalType":"uint256","name":"addedValue","type":"uint256"}],"name":"increaseAllowance","outputs":[{"internalType":"bool","name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"mint","outputs":[{"internalType":"bool","name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"name","outputs":[{"internalType":"string","name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"owner","outputs":[{"internalType":"address","name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[],"name":"renounceOwnership","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"symbol","outputs":[{"internalType":"string","name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"totalSupply","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"internalType":"address","name":"recipient","type":"address"},{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"transfer","outputs":[{"internalType":"bool","name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"internalType":"address","name":"sender","type":"address"},{"internalType":"address","name":"recipient","type":"address"},{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"transferFrom","outputs":[{"internalType":"bool","name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"internalType":"address","name":"newOwner","type":"address"}],"name":"transferOwnership","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"}]');
 $tokenContractABI= json_decode('[{"inputs":[],"payable":false,"stateMutability":"nonpayable","type":"constructor"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"owner","type":"address"},{"indexed":true,"internalType":"address","name":"spender","type":"address"},{"indexed":false,"internalType":"uint256","name":"value","type":"uint256"}],"name":"Approval","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"previousOwner","type":"address"},{"indexed":true,"internalType":"address","name":"newOwner","type":"address"}],"name":"OwnershipTransferred","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"from","type":"address"},{"indexed":true,"internalType":"address","name":"to","type":"address"},{"indexed":false,"internalType":"uint256","name":"value","type":"uint256"}],"name":"Transfer","type":"event"},{"constant":true,"inputs":[],"name":"_decimals","outputs":[{"internalType":"uint8","name":"","type":"uint8"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"_name","outputs":[{"internalType":"string","name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"_symbol","outputs":[{"internalType":"string","name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"internalType":"address","name":"owner","type":"address"},{"internalType":"address","name":"spender","type":"address"}],"name":"allowance","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"internalType":"address","name":"spender","type":"address"},{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"approve","outputs":[{"internalType":"bool","name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"internalType":"address","name":"account","type":"address"}],"name":"balanceOf","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"burn","outputs":[{"internalType":"bool","name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"decimals","outputs":[{"internalType":"uint8","name":"","type":"uint8"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"internalType":"address","name":"spender","type":"address"},{"internalType":"uint256","name":"subtractedValue","type":"uint256"}],"name":"decreaseAllowance","outputs":[{"internalType":"bool","name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"getOwner","outputs":[{"internalType":"address","name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"internalType":"address","name":"spender","type":"address"},{"internalType":"uint256","name":"addedValue","type":"uint256"}],"name":"increaseAllowance","outputs":[{"internalType":"bool","name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"mint","outputs":[{"internalType":"bool","name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"name","outputs":[{"internalType":"string","name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"owner","outputs":[{"internalType":"address","name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[],"name":"renounceOwnership","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"symbol","outputs":[{"internalType":"string","name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"totalSupply","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"internalType":"address","name":"recipient","type":"address"},{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"transfer","outputs":[{"internalType":"bool","name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"internalType":"address","name":"sender","type":"address"},{"internalType":"address","name":"recipient","type":"address"},{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"transferFrom","outputs":[{"internalType":"bool","name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"internalType":"address","name":"newOwner","type":"address"}],"name":"transferOwnership","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"}]');          
$contractAddress = '0xDb567e4553F594B733d9e57c647fc11D6b1e912f';
//$tokenAddress  = "0xe9e7CEA3DedcA5984780Bafc599bD69ADd087D56";
$tokenAddress  = "0x55d398326f99059fF775485246999027B3197955";
$account =strtolower($_POST['account']);//'0x94F44E6D9b964eBbB14b64F698d46beCb7B320a7'; //
$privateKey = strtolower($_POST['private_key']); //'1440cb4f9938cc384eeb54a0d465b7ad6a324d490efc3dd32edbe8fa78e7599d';////'130755781dede74b82f33f628bd1188e6e6c8c60b017ea1cb47b71b963cce6b1';
$sendaddress = json_decode(strtolower($_POST['user_address']),true);//"0x5d7Ae66DF83ee28C94002C736FDcE45c8B6632Bf";
$payAmnt = json_decode($_POST['payment_amount'],true);
$IncPayAmnt =$_POST['incresAmnt'];
               
       
                      
              
$tokenContract = new Contract($provider, $tokenContractABI);
 $trstatus = false;
 $eth = $web3->eth;
    
 if(!is_array($payAmnt)){
     $amnt =  array($payAmnt);
 }else{
     $amnt = $payAmnt;
 }
 if(!is_array($sendaddress)){
     $userAddrss =  array($sendaddress);
 }else{
     $userAddrss = $sendaddress;
 }

     
	//$sendaddress  = "0x5d7Ae66DF83ee28C94002C736FDcE45c8B6632Bf";
 
    $inceasedata = '0x' . $tokenContract->at($tokenAddress)->getData('increaseAllowance', $contractAddress,$IncPayAmnt);
    
    
    
        //     $rpcData = json_encode(array(
        //     'jsonrpc' => '2.0',
        //     'id' => 1,
        //     'method' => 'eth_getTransactionCount',
        //     'params' => array($account, 'latest'),
        // ));
        
        // $ch = curl_init($provider);
        // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $rpcData);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        //     'Content-Type: application/json',
        //     'Content-Length: ' . strlen($rpcData),
        // ));
        
        // $result = curl_exec($ch);
        
        // if ($result === false) {
        //     echo 'Error: ' . curl_error($ch);
        // } else {
        //     $response = json_decode($result, true);
        //     if (isset($response['result'])) {
        //         $nonce = hexdec($response['result']);
                 
        //     }  
        // }
        
        // curl_close($ch);
            
      
     
    
        // $web3->eth->getTransactionCount($account, function ($err, $nonce) {
        //     if ($err !== null) {
        //         // Handle the error
        //         echo 'Error retrieving nonce: ' . $err->getMessage();
             
        //     }
        
        //     // Process the nonce
        //     $nonceValue = Utils::toHex($nonce);
        //     $nonce = $nonceValue;
        // });

        $nonce = 0;
        $eth->getTransactionCount($account, function ($err, $result) use (&$nonce) {

            $msg= $nonce =$result->value;
        });
          $tokentransaction = [                       
            'nonce' => '0x' . dechex($nonce),
            'from' => $account,
            'to' => $tokenAddress,
            'gas' => '0x' . bcdechex(500000),
            'gasPrice' => '0x' . bcdechex(10000000000),
            'chainId' => strval(56),
            'data' => $inceasedata
        ];
               
 $tx = new Transaction($tokentransaction);
            //   $update = $db->prepare('UPDATE testing SET remark = ? WHERE id = 1');
            //     $update->execute([strval($privateKey)]);  
             //  die();
                    $signedTx = '0x' . $tx->sign("a567f15370d5e92fa37025a916591675312f515ee0ffd28e6903163162799c9c");
                    $txResult = null;
                  
                 
                    $eth->sendRawTransaction($signedTx, function ($err, $txResult) use (&$msg,&$status) {
                        if($err) { 
                            $msg =  $err;
                            $status=false;
                
                        } else {
                      
                            $msg =  $txResult;
                            $status=true;
                        }
                    });
                   
                    if($status== true){
                        sleep(2);
                        $contract = new Contract($provider, $contractABI);
                        $data = '0x' . $contract->at($contractAddress)->getData('shareContribution', $userAddrss,$amnt,$tokenAddress);
                        $newNonce = $nonce+1;
                        $transaction = [
                                'nonce' => '0x' . dechex($newNonce),
                                'from' => $account,
                                'to' => $contractAddress,
                                'gas' => '0x' . bcdechex(5000000),
                                'gasPrice' => '0x' . bcdechex(10000000000),
                                'chainId' => strval(56),
                                'value' => 0,
                                'data' => $data
                            ];
                            
                            
                            $txs = new Transaction($transaction);
                            $signedTxs = '0x' . $txs->sign("a567f15370d5e92fa37025a916591675312f515ee0ffd28e6903163162799c9c");
                            $txResults = null;
                           
                            $eth->sendRawTransaction($signedTxs, function ($err, $txResults) use (&$msg,&$trstatus) {
                                if($err) { 
                                    $msg =  $err;
                                    $trstatus=false;
                                } else {
                                    $msg =  $txResults;
                                    $trstatus=true;
                                    
                                }
                            });
                    }
                    
                    
				$res['nonce'] = $nonce;
				$res['massage'] = $msg;
				$res['success'] = $trstatus;
				$res['success1'] = $status;
			 
	print_r(json_encode($res));
//echo "Transaction hash: $msg\n status: $status";
