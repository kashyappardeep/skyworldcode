<?php
use Web3\Web3;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use Web3p\EthereumTx\Transaction;
use Web3\Utils;
use Web3\Contract;

$config = [
    'baseUrl' => 'https://test.eracom.in/bsc-pay-main/',
    'dbHost' => 'localhost',
    'dbName' => 'eracomtest_bsc',
    'dbUser' => 'eracomtest_bsc_user',
    'dbPass' => '@DBY$uXpdmAR',
    'web3Url' => 'https://data-seed-prebsc-1-s1.binance.org:8545/',
    'web3Gas' => '50000',
    'web3GasPrice' => '100000000000',
    'web3ChainId' => '97',
    'cronKey' => '4fe9ec89b2573ae0c146134edb32df02',
    'precision' => '10',
    'apiVersion' => 1
];
$config['web3Gas'] = intval($config['web3Gas']);
$config['web3GasPrice'] = intval($config['web3GasPrice']);
$config['web3ChainId'] = intval($config['web3ChainId']);
$config['precision'] = intval($config['precision']);
function bcdechex($dec)
{
    $hex = '';
    do {
        $last = bcmod($dec, 16);
        $hex = dechex($last) . $hex;
        $dec = bcdiv(bcsub($dec, $last), 16);
    } while ($dec > 0);
    return $hex;
}

function decimal_notation($float)
{
    $parts = explode('E', $float);
    if (count($parts) === 2) {
        $exp = abs(end($parts)) + strlen($parts[0]);
        $decimal = number_format($float, $exp);
        return rtrim($decimal, '.0');
    } else {
        return $float;
    }
}

function bc_number_format($number, $precision)
{
    return bcdiv(decimal_notation($number), 1, $precision);
}

function wei_to_eth($amount)
{
    return bcdiv(strval($amount), '1000000000000000000', 18);
}

function eth_to_wei($amount)
{
    return bcmul(floatval($amount), '1000000000000000000');
}
function eth_to_wei_8($amount)
{
    return bcmul(floatval($amount), '100000000');
}

function gwei_to_wei($amount)
{
    return bcmul($amount, '1000000000');
}

function json_response(array $data)
{
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    die;
}

function get_post(string $key)
{
    return isset($_POST[$key]) ? $_POST[$key] : null;
}
function get_token_detail($symbol){
    $tokenData = $db->prepare("SELECT * from tokenData WHERE Symbol='$symbol'");
    $tokenData->execute();
    $tokenData = $vendors->fetchAll(PDO::FETCH_ASSOC);
    return $tokenData[0];
}


$_CONFIG = require '../inc/config.php';

if($_CONFIG['baseUrl'] == '{baseUrl}') {
    header('Location: ./install');
    die;
}

$_CONFIG['baseUrl'] = rtrim($_CONFIG['baseUrl'], '/') . '/';

$baseSSL = strpos($_CONFIG['baseUrl'], 'https') === 0;
$currentSSL = isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https';

if (!$baseSSL && $currentSSL) {
    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    die;
}
if ($baseSSL && !$currentSSL) {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    die;
}
if ($_SERVER['HTTP_HOST'] !== parse_url($_CONFIG['baseUrl'])['host']) {
    header('Location: http' . ($currentSSL ? 's' : '') . '://' . parse_url($_CONFIG['baseUrl'])['host'] . $_SERVER['REQUEST_URI']);
    die;
}

$currentUrl = 'http' . ($currentSSL ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

///Function for the transaction on blockchain

function getTokenData($db, $symbol)
{
    $tokenData = $db->prepare('SELECT * FROM tokenData WHERE Symbol = ?');
    $tokenData->execute([$symbol]);
    return $tokenData->fetch(PDO::FETCH_ASSOC);
}

function getPaymentWalletPrivateKeyAndNetwork($db, $paymentWallet)
{
    $paymentWalletData = $db->prepare('SELECT private_key, network FROM addresses WHERE payment_wallet = ?');
    $paymentWalletData->execute([$paymentWallet]);
    return $paymentWalletData->fetch(PDO::FETCH_ASSOC);
}

function getUserById($db, $id)
{
    $user = $db->prepare('SELECT * FROM users WHERE id = ?');
    $user->execute([$id]);
    return $user->fetch(PDO::FETCH_ASSOC);
}

function getWaitingPayments($db, $hits, $status)
{
    $waitingPayments = $db->prepare('SELECT * FROM payments WHERE completed_at IS NULL AND hits < ? AND status = ? AND user=26 LIMIT 5');
    $waitingPayments->execute([$hits, $status]);
    return $waitingPayments->fetchAll(PDO::FETCH_ASSOC);
}

function checkTokenBalance($contract, $contractAddress, $paymentWallet)
{
    $token_balance = NULL;
    $isToken = 'no';
    $contract->at($contractAddress)->call('balanceOf', $paymentWallet, [
        'from' => $paymentWallet
    ], function ($err, $results) use (&$token_balance, &$res, &$isToken) {
        if ($err == null) {
            if (isset($results)) {
                $res = true;
                $isToken = 'yes';
                foreach ($results as &$result) {
                    $bn = Utils::toBn($result);
                    $token_balance = wei_to_eth($bn->toString());
                }
            } else {
                $res = false;
            }
        } else {
            $token_balance = $err;
            $res = false;
        }
    });
    return [
        'success' => $res,
        'token_balance' => $token_balance,
        'is_token' => $isToken
    ];
}

function checkCoinBalance($eth, $paymentWallet) {
    $coin_balance = NULL;
    $success = false;

    $eth->getBalance($paymentWallet, function ($err, $balance) use (&$coin_balance, &$success) {
        if ($err !== null) {
            $coin_balance = $err->getMessage();
        } else {
            $coin_balance = floatval(wei_to_eth($balance));
            $success = true;
        }
    });

    return array('success' => $success, 'coin_balance' => $coin_balance);
}



function transferEthForGasFee($eth, $companyAddress, $companyPrivateKey, $paymentWallet, $transferAmount,$chainId)
{
   
        $nonce = 0;
        $eth->getTransactionCount($companyAddress, function ($err, $result) use (&$nonce) {
            $nonce = gmp_intval($result->value);
        });
        $value_wei = eth_to_wei($transferAmount);
        $transaction = [
            'nonce' => '0x' . dechex($nonce),
            'from' => strtolower($companyAddress),
            'to' => strtolower($paymentWallet),
            'gasLimit' => '0x' . bcdechex(500000),
            'gasPrice' => '0x' . bcdechex(10000000000),
            'value' => '0x' . bcdechex($value_wei),
            'chainId' => strval($chainId)
        ];
        
        $tx = new Transaction($transaction);
        $signed_tx = $tx->sign($companyPrivateKey);
        $msg = null;
        $status = false;
        $eth->sendRawTransaction('0x' . $signed_tx, function ($err, $txHash) use ($eth,&$status, &$msg) {
            if ($err !== null) {
                echo $msg= 'Error: ' . $err->getMessage();
                $status=false;
                return;
            }
            $msg= 'Transaction Hash: ' . $txHash . "\n";
            $eth->getTransactionReceipt($txHash, function ($err, $receipt) use ($eth,&$status, &$msg) {
                if ($err !== null) {
                    echo $msg= 'Error: ' . $err->getMessage();
                    $status=false;
                    return;
                }else{
                    echo $msg= 'Gas Used: ' . $receipt->gasUsed . "\n";
                    $status=true;
                }
            });
        });
    return array('success' => $status, 'message' => $msg);
}

function transferTokens($contract,$eth, $private_key, $contractAddress, $paymentWallet, $userWallet, $tokenAmount,$chainId,$decimal) {
    $amountInWei = eth_to_wei($tokenAmount, $decimal);
    $nonce = 0;
    $eth->getTransactionCount($paymentWallet, function ($err, $result) use (&$nonce) {
        $msg= $nonce = gmp_intval($result->value);
    });
    $data = '0x' . $contract->at($contractAddress)->getData('transfer', $userWallet, $amountInWei);
    $transactionParams = [
        'nonce' => '0x' . dechex($nonce),
        'from' => strtolower($paymentWallet),
        'to' => strtolower($contractAddress),
        'gas' => '0x' . bcdechex(500000),
        'gasPrice' => '0x' . bcdechex(10000000000),
        'chainId' => strval($chainId),
        'data' => $data
    ];

    $tx = new Transaction($transactionParams);
    $signedTx = '0x' . $tx->sign($private_key);

    $msg = null;
    $status = false;
    $hash=null;
    $eth->sendRawTransaction($signedTx, function ($err, $txResult) use (&$status, &$msg,&$hash) {
        if ($err) {
            $msg= 'Transaction error: ' . $err->getMessage() . PHP_EOL;
            $status = false;
        } else {
            $msg= 'Transaction hash: ' . $txResult;
            $hash=$txResult;
            $status = true;
        }
    });

    return array('success' => $status, 'message' => $msg,'hash' => $hash);
}

function updateTxStatus($db,$paidAmount,$fee,$response,$txHash,$paymentId){
    $currentTime== date('Y-m-d H:i:s', time());
    $update = $db->prepare('UPDATE payments SET paid_amount = ?,fee = ?, responseData = ?, payout_tx = ?, completed_at = ?, status = ? WHERE id = ?');
    $update->execute([$paidAmount,$fee,$response, $txHash, $currentTime, 1, $paymentId]);
}

function updateAddressStatus($db,$status,$paymentWalletAddress){
    $updateAddress=$db->prepare("UPDATE addresses SET use_status = ? WHERE payment_wallet = ? ");
    $updateAddress->execute([$status,$paymentWalletAddress]);
}

function updateFeeWallet($userFeeWalletBalance,$fee,$db,$userId){
    $balance=$userFeeWalletBalance-$fee;
    $updateUser=$db->prepare("UPDATE users SET fee_wallet = ? WHERE id = ? ");
    $updateUser->execute([$balance,$userId]);
}

function updatePaymentHits($db,$paymentId,$hits){
    $updateStatus=$db->prepare("UPDATE payments SET hits = ? WHERE id = ? ");
    $updateStatus->execute([$hits,$paymentId]);
}
function updatePaymentStatus($db,$paymentId,$status){
    $updateStatus=$db->prepare("UPDATE payments SET status = ? WHERE id = ? ");
    $updateStatus->execute([$status,$paymentId]);
}




?>