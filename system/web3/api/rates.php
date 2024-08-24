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

// $provider = 'https://data-seed-prebsc-1-s3.binance.org:8545/';
$provider = 'https://bsc-dataseed.binance.org/';
$contractABI = json_decode('[{"inputs":[{"internalType":"address","name":"_tokenAAddress","type":"address"},{"internalType":"address","name":"_tokenBAddress","type":"address"}],"stateMutability":"nonpayable","type":"constructor"},{"inputs":[],"name":"buyRate","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"_tokenBAmount","type":"uint256"}],"name":"buyTokens","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"uint256","name":"_rateDecimal","type":"uint256"}],"name":"changeRateDecimal","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"owner","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"rateDecimals","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"sellRate","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"_tokenAAmount","type":"uint256"}],"name":"sellTokens","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"uint256","name":"_buyRate","type":"uint256"},{"internalType":"uint256","name":"_sellRate","type":"uint256"}],"name":"setRates","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"tokenA","outputs":[{"internalType":"contract IERC20","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"tokenB","outputs":[{"internalType":"contract IERC20","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"_amount","type":"uint256"}],"name":"withdrawTokenA","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"uint256","name":"_amount","type":"uint256"}],"name":"withdrawTokenB","outputs":[],"stateMutability":"nonpayable","type":"function"}]');
$contractAddress = '0x141aA796a97314286C1090BF20027E5b678A7dC8';

$res = array();
               
$web3 = new Web3(new HttpProvider(new HttpRequestManager($provider, 5)));
$contract = new Contract($provider, $contractABI);                    
$contract->at($contractAddress);

//  buyRate.
$contract->call('buyRate', function ($err, $result) use (&$res) {
    if ($err !== null) {
        $res['message'] = 'Error: ' . $err->getMessage();
        return;
    }
    $buy = $result[0] . PHP_EOL;
    $buy_rate=$buy / 1e18;
    $res['buyrate'] =$buy_rate;
});

//  sellRate.
$contract->call('sellRate', function ($err, $result) use (&$res) {
    if ($err !== null) {
        $res['message'] = 'Error: ' . $err->getMessage();
        return;
    }
    $sell = $result[0] . PHP_EOL;
    $sell_rate=$sell / 1e18;
    $res['sellrate'] =$sell_rate;
});

$res['message'] = "success";
print_r($res);

?>
