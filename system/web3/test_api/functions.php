<?php


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






?>