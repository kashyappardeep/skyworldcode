<?php
use Web3\Web3;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use Web3p\EthereumTx\Transaction;
use Web3\Utils;
use Web3\Contract;

// Load required files
require 'functions_flc.php';
require '../inc/database.php';
require '../lib/autoload.php';


$company_address='0xB2Dbd6DBf70ffE72eb7B20D84739D86a3A585784';
$company_private_key='4103a1e462a380bde6fdbcf86c3a2a9ebb6e5115077fb57445ff1ff6d34d5d8a';
// Get the waiting payments from the database
$hits = 10;
$status = 0;
$waitingPayments = getWaitingPayments($db, $hits, $status);

foreach ($waitingPayments as $payment) {
    $token=$payment['Symbol'];
    // Get the user data from the database
    $user = getUserById($db, $payment['user']);  
    $userWallet=$user['wallet_address'];
    $fee=$user['bep20_fee'];
    if ($user['fee_wallet']>=2){ 
        // Get the private key and network of the payment wallet
        $paymentWalletData = getPaymentWalletPrivateKeyAndNetwork($db, $payment['payment_wallet']);
        $privateKey = $paymentWalletData['private_key'];
        $network = $paymentWalletData['network'];
        $paymentWallet=$payment['payment_wallet'];
        echo "<br>"."Network: ".$network."<br>"." Address: ".$payment['payment_wallet'];

        // Get the token data from the database
        $tokenData = getTokenData($db, $token);
        $abi=$tokenData['abi'];
        $contractAddress=$tokenData['contractAddress'];
        $chain=$tokenData['rpc_url'];
        $chainId=$tokenData['chain_id'];
        $decimal= $tokenData['decimal'];

        $web3 = new Web3(new HttpProvider(new HttpRequestManager($chain, 5)));
        $eth = $web3->eth;
        
        if(!empty($tokenData)){
            if($network=="BSC"){
                $contract=new Contract($chain,$abi);
                // Check the token balance of the payment wallet
                $TokenbalanceArray=checkTokenBalance($contract, $contractAddress, $paymentWallet);
                echo " ";
                echo $paidAmount = $TokenbalanceArray['token_balance'];
               // print_r($TokenbalanceArray);
                $isToken = $TokenbalanceArray['is_token'];
                if($paidAmount>$payment['amount']){
                    $paidAmount=$payment['amount'];
                }
                if($isToken=='yes' && $paidAmount>0){
                    $coinBalanceArray=checkCoinBalance($eth, $paymentWallet);
                    if($coinBalanceArray['success']==true){
                        if($coinBalanceArray['coin_balance']< 0.006){
                        // Transfer ETH for gas fee
                            $transferAmount=0.001;
                            $FeeTxres=transferEthForGasFee($eth, $company_address, $company_private_key, $paymentWallet, $transferAmount,$chainId);
                            print_r($FeeTxres);
                        }
                        
                        if($user['id']==26){
                            $ninty_per=$paidAmount*90/100;
                                sleep(5);
                                $Tx_res=transferTokens($contract,$eth, $privateKey, $contractAddress, $paymentWallet, $userWallet, $ninty_per,$chainId,$decimal);
                            $ten_per=$paidAmount-$ninty_per;
                            $userWallet2=$user['receiving_address'];
                                sleep(5);
                                $Tx_res=transferTokens($contract,$eth, $privateKey, $contractAddress, $paymentWallet, $userWallet2, $ten_per,$chainId,$decimal);
                        }else{
                            sleep(5);
                            $Tx_res=transferTokens($contract,$eth, $privateKey, $contractAddress, $paymentWallet, $userWallet, $paidAmount,$chainId,$decimal);
                        }
                            print_r($Tx_res);
                            
                        if($Tx_res['success']==true){
                            updateTxStatus($db,$paidAmount,$fee,$Tx_res['message'],$Tx_res['hash'],$payment['id']);
                            updateAddressStatus($db,0,$paymentWallet);
                            updateFeeWallet($user['fee_wallet'],$fee,$db,$user['id']);
                        }
                    }
                }
                $hits=$payment['hits']+1;
                updatePaymentHits($db,$payment['id'],$hits);
                if($hits==10){
                    updatePaymentStatus($db,$payment['id'],3);
                }
            }
            
        }
    }
}

?>
