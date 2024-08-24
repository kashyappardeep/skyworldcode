<?php
use Sop\CryptoTypes\Asymmetric\EC\ECPublicKey;
use Sop\CryptoTypes\Asymmetric\EC\ECPrivateKey;
use Sop\CryptoEncoding\PEM;
use kornrunner\Keccak;

include_once 'api/vendor/autoload.php';  //change

isset($_CONFIG) or die;   //change
//include 'functions.php';
//include '../inc/database.php';  //change
$payment_amount = floatval(get_post('payment_amount'));  //change
//$payment_amount = floatval(2);
$network=get_post('network');
//$network='tron';
$payment_amount_str = bc_number_format($payment_amount, $_CONFIG['precision']);

$expires_in_minutes = 60;

if($payment_amount > 0) {
  // require './lib/autoload.php';   //change
    $token=get_post('token');
    function create_new_wallet(){
        $res = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'secp256k1'
        ]);
        
        if (!$res) {
            json_response([
                'success' => false,
                'message' => 'Private key could not be generated!'
            ]);
        }
        
        openssl_pkey_export($res, $priv_key);
        
        $key_detail = openssl_pkey_get_details($res);
        $pub_key = $key_detail['key'];
        
        $priv_pem = PEM::fromString($priv_key);
        
        $ec_priv_key = ECPrivateKey::fromPEM($priv_pem);
        
        $ec_priv_seq = $ec_priv_key->toASN1();
        
        $priv_key_hex = bin2hex($ec_priv_seq->at(1)->asOctetString()->string());
        $priv_key_len = strlen($priv_key_hex) / 2;
        $pub_key_hex = bin2hex($ec_priv_seq->at(3)->asTagged()->asExplicit()->asBitString()->string());
        $pub_key_len = strlen($pub_key_hex) / 2;
        
        $pub_key_hex_2 = substr($pub_key_hex, 2);
        $pub_key_len_2 = strlen($pub_key_hex_2) / 2;
        
        $hash = Keccak::hash(hex2bin($pub_key_hex_2), 256);
        
        $wallet_address = '0x' . substr($hash, -40);
        $wallet_private_key = '0x' . $priv_key_hex;
        return [$wallet_address,$wallet_private_key];
    }
    
    function create_tron_address(){
        $tron = new \IEXBase\TronAPI\Tron();

        $generateAddress = $tron->generateAddress(); // or createAddress()
        $isValid = $tron->isAddress($generateAddress->getAddress());
       
            $wallet_address=$generateAddress->getAddress(true);
            $wallet_private_key=$generateAddress->getPrivateKey();
            return [$wallet_address,$wallet_private_key];
    }
    
    
    $currentTime= date('Y-m-d H:i:s');
    function get_empty_address($db,$currentTime,$network){
        $addresses=$db->prepare('select * from addresses where use_upto < ? and use_status = ? and network= ?');
        $addresses->execute([$currentTime,0,$network]);
        $addresses = $addresses->fetch(PDO::FETCH_ASSOC);
        return $addresses;
    }
    $expired_at = date('Y-m-d H:i:s', time() + $expires_in_minutes * 60);
    $getPaymentWallet=get_empty_address($db,$currentTime,$network);
    if(!empty($getPaymentWallet) && $token!='trx'){
        $wallet_address=$getPaymentWallet['payment_wallet'];
        $wallet_private_key=$getPaymentWallet['private_key'];
        $update = $db->prepare('update addresses SET use_at = ?, use_upto = ?, use_status = ? WHERE payment_wallet = ?');
        $issued=$update->execute([$currentTime,$expired_at,1,$wallet_address]);
    }else{
        
        if($network=="BSC"){
            require 'lib/autoload.php';  //change
        $newPaymentWallet=create_new_wallet();
        }elseif($network=="tron"){
            $newPaymentWallet= create_tron_address();
        }
        $wallet_address=$newPaymentWallet[0];
        $wallet_private_key=$newPaymentWallet[1];
        $payment = $db->prepare('INSERT into addresses SET payment_wallet = ?, private_key = ?, use_at = ?, use_upto = ?, network= ?');
        $issued=$payment->execute([
            $wallet_address,
            $wallet_private_key,
            $currentTime,
            $expired_at,
            $network
        ]);
    }
    if($issued==true){
        $payment = $db->prepare('INSERT into payments SET user = ?, payment_wallet = ?, amount = ?, expired_at = ?, created_at = ?, Symbol = ?');
        $payment->execute([
            $user['id'],
            $wallet_address,
            $payment_amount_str,
            $expired_at,
            $currentTime,
            $token
        ]);
        $paymentId = $db->lastInsertId();
        json_response([
            'success' => true,
            'paymentId' => $paymentId,
            'paymentAmount' => $payment_amount_str,
            'paymentWallet' => $wallet_address,
            'expiryDate' => $expired_at
        ]);
    }else{
        json_response([
        'success' => false,
        'message' => 'Something went wrong!'
    ]);
    }
}
else {
    json_response([
        'success' => false,
        'message' => 'Payment amount must be greater than 0!'
    ]);
}