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
 $provider = 'https://data-seed-prebsc-1-s3.binance.org:8545/';//testing
//$provider = 'https://bsc-dataseed.binance.org/';
 
            $web3 = new Web3(new HttpProvider(new HttpRequestManager($provider, 5)));
            $contractABI = json_decode('[{"inputs":[],"stateMutability":"nonpayable","type":"constructor"},{"anonymous":false,"inputs":[{"indexed":false,"internalType":"uint256","name":"value","type":"uint256"},{"indexed":true,"internalType":"address","name":"sender","type":"address"}],"name":"Contribute","type":"event"},{"anonymous":false,"inputs":[{"indexed":false,"internalType":"uint256","name":"value","type":"uint256"},{"indexed":true,"internalType":"address","name":"sender","type":"address"}],"name":"ShareContribution","type":"event"},{"inputs":[{"internalType":"address","name":"_address","type":"address"},{"internalType":"uint256","name":"_amount","type":"uint256"},{"internalType":"contract BEP20","name":"token","type":"address"}],"name":"airDrop","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"uint256","name":"amount","type":"uint256"},{"internalType":"contract BEP20","name":"token","type":"address"}],"name":"contribute","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address payable[]","name":"_contributors","type":"address[]"},{"internalType":"uint256[]","name":"_balances","type":"uint256[]"},{"internalType":"contract BEP20","name":"token","type":"address"}],"name":"shareContribution","outputs":[],"stateMutability":"payable","type":"function"},{"inputs":[{"internalType":"address payable","name":"_contributors","type":"address"},{"internalType":"uint256","name":"_balances","type":"uint256"},{"internalType":"contract BEP20","name":"token","type":"address"}],"name":"shareSingleContribution","outputs":[],"stateMutability":"payable","type":"function"}]');
            $tokenContractABI = json_decode('[{"inputs":[],"payable":false,"stateMutability":"nonpayable","type":"constructor"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"owner","type":"address"},{"indexed":true,"internalType":"address","name":"spender","type":"address"},{"indexed":false,"internalType":"uint256","name":"value","type":"uint256"}],"name":"Approval","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"from","type":"address"},{"indexed":true,"internalType":"address","name":"to","type":"address"},{"indexed":false,"internalType":"uint256","name":"value","type":"uint256"}],"name":"Transfer","type":"event"},{"constant":true,"inputs":[{"internalType":"address","name":"owner","type":"address"},{"internalType":"address","name":"spender","type":"address"}],"name":"allowance","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"internalType":"address","name":"spender","type":"address"},{"internalType":"uint256","name":"value","type":"uint256"}],"name":"approve","outputs":[{"internalType":"bool","name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"internalType":"address","name":"account","type":"address"}],"name":"balanceOf","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"decimals","outputs":[{"internalType":"uint8","name":"","type":"uint8"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"internalType":"address","name":"spender","type":"address"},{"internalType":"uint256","name":"subtractedValue","type":"uint256"}],"name":"decreaseAllowance","outputs":[{"internalType":"bool","name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"internalType":"address","name":"spender","type":"address"},{"internalType":"uint256","name":"addedValue","type":"uint256"}],"name":"increaseAllowance","outputs":[{"internalType":"bool","name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"name","outputs":[{"internalType":"string","name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"symbol","outputs":[{"internalType":"string","name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"totalSupply","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"internalType":"address","name":"recipient","type":"address"},{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"transfer","outputs":[{"internalType":"bool","name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"internalType":"address","name":"sender","type":"address"},{"internalType":"address","name":"recipient","type":"address"},{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"transferFrom","outputs":[{"internalType":"bool","name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"}]');
            
            $contractAddress = '0xAa8643C711D4bDBEC4Eb8146c4adDe36925Ecb11';
            $tokenAddress  = "0x325a4deFFd64C92CF627Dd72d118f1b8361c5691";
            	 
            $account =$_POST['account'];//'0x94F44E6D9b964eBbB14b64F698d46beCb7B320a7'; //
            $privateKey = '1440cb4f9938cc384eeb54a0d465b7ad6a324d490efc3dd32edbe8fa78e7599d';////'130755781dede74b82f33f628bd1188e6e6c8c60b017ea1cb47b71b963cce6b1';
            $sendaddress = $_POST['user_address'];//"0x5d7Ae66DF83ee28C94002C736FDcE45c8B6632Bf";
            $payAmnt = $_POST['payment_amount'];
            $IncPayAmnt = $_POST['incresAmnt'];
            
            $contract = new Contract($provider, $contractABI);
            $tokenContract = new Contract($provider, $tokenContractABI);
             
             $eth = $web3->eth;
  
     
	            $increaseAllowanceData = '0x' . $tokenContract->at($tokenAddress)->getData('increaseAllowance', $contractAddress, $IncPayAmnt);
    
                  
                    $rpcUrl = 'https://data-seed-prebsc-1-s3.binance.org:8545/';
                    $ch = curl_init($rpcUrl);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    
                    // Prepare the RPC payload
                    $payload = json_encode([
                        'jsonrpc' => '2.0',
                        'method' => 'eth_estimateGas',
                        'params' => [
                            // Your transaction object here
                            [
                                'from' => $account,
                                'to' => $tokenAddress,
                                'data' => $increaseAllowanceData,
                                // Add other parameters as needed
                            ]
                        ],
                        'id' => 1
                    ]);
                    
                    // Send the RPC request
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                    $response = curl_exec($ch);
                    curl_close($ch);
                    
                    // Process the response
                    $result = json_decode($response, true);
                    $gasLimit = hexdec($result['result']);
                    //  print_r($gasLimit);
                    //  die();
                //  $nonce = 0;
                    // $eth->getTransactionCount($account, function ($err, $result) use (&$nonce) {
                    //                               $nonce = gmp_intval($result->value);
                    //                          });
                                

                    $rpcUrl = 'https://data-seed-prebsc-1-s3.binance.org:8545/';
                    
                    
                    $payload = json_encode([
                        'jsonrpc' => '2.0',
                        'method' => 'eth_getTransactionCount',
                        'params' => [$account, 'latest'],
                        'id' => 1
                    ]);
                    
                    $ch = curl_init($rpcUrl);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                    
                    $response = curl_exec($ch);
                    curl_close($ch);
                    
                    $result = json_decode($response, true);
                    
                    if (isset($result['result'])) {
                        $nonce = hexdec($result['result']);
                         
                    } else {
                        echo 'Error: ' . $result['error']['message'];
                    }
            
            
                    $increaseAllowanceTransaction = [
                    'nonce' => '0x' . $nonce,
                    'from' => $account,
                    'to' => $tokenAddress,
                    'gas' => '0x' . bcdechex($gasLimit),
                    'gasPrice' => '0x' . bcdechex(10000000000),
                    'chainId' => strval(97),
                    'data' => $increaseAllowanceData
                    ];
                    
                    $increaseAllowanceTx = new Transaction($increaseAllowanceTransaction);
                    
                    $signedIncreaseAllowanceTx = '0x' . $increaseAllowanceTx->sign($privateKey);
                    $eth->sendRawTransaction($signedIncreaseAllowanceTx, function ($err, $txResult) use (&$msg, &$status) {
                        if ($err) {
                            print_r($err);
                            die();
                            $msg = $err;
                            $status = false;
                        } else {
                            print_r($txResult);
                            die();
                            $msg = $txResult;
                            $status = true;
                   
                        }
                    });
                    sleep(5);
                    $data = '0x' . $contract->at($contractAddress)->getData('shareContribution', $sendaddress,$payAmnt,$tokenAddress);
                     $newNonce = $nonce+1;
                    $transaction = [
                        'nonce' => '0x' .$newNonce,
                        'from' => $account,
                        'to' => $contractAddress,
                        'gas' => '0x' . bcdechex(10000000),
                        'gasPrice' => '0x' . bcdechex(10000000000),
                        'chainId' => strval(97),
                        'data' => $data
                    ];
                   
                    $tx = new Transaction($transaction);
                    
                    $signedTx = '0x' . $tx->sign($privateKey);
                    $txResults = null;
                   
                    $eth->sendRawTransaction($signedTx, function ($err, $txResults) use (&$msg,&$status) {
                        if($err) { 
                             print_r($err);
                            die();
                            $msg =  $err;
                            $status=false;
                        } else {
                           
                            $msg =  $txResults;
                            $status=true;
                        }
                    });
            		$res['massage'] = $msg;
            		$res['success'] = $status;
            		$res['res'] = json_encode($_POST);
			 
	print_r(json_encode($res));
//echo "Transaction hash: $msg\n status: $status";
