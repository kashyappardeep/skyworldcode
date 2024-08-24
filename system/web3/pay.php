<?php

$url = "https://test.eracom.in/sendcryp/";

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$headers = [
  "Content-Type: application/x-www-form-urlencoded"
];

curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);


$data = http_build_query([
  "api_key" => "81c7413f682a2079701bd19f3c99a107",
  "action" => "transfer",
  "to_address" => "TJxfSsE6NbN1B2WK2GMBGaKVQCHAL7m3ni",
  "token" => "trx",
  "payment_amount" => 1,
  "network" => "tron"
]); 


/*$data = http_build_query([
  "api_key" => "61df57dd96f5d3310b1c77fd882b5e51c1dd9a57",
  "action" => "review_transaction",
  "txId" => 18743
  ]);*/
  
/*$data = http_build_query([
  "api_key" => "61df57dd96f5d3310b1c77fd882b5e51c1dd9a57",
  "action" => "get_balance",
  "to_address" => "0x4CaD5908F3799c3Ce412fe129c0D3B14cf4e27C0",
  "token" => "tBUSD",
]); */

//create payment link
/*$data = http_build_query([
  "api_key" => "85e36ab036744dd301c3c8007274b3fd",
  "action" => "create_payment",
  "payment_amount" => "9",
  "token" => "USDT-TRC20",
  "network" => "tron"
]);*/

//get payment status

/*$data = http_build_query([
  "api_key" => "e7c8045bd5373dfe39d37b69e8182e22",
  "action" => "get_payment",
  "payment_id" => "56"
]);*/

//vendor Info
/*$data = http_build_query([
  "api_key" => "9a919f2d503a24524ec702e02764c0e4",
  "action" => "vendor_info",
]);*/

curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

$result = json_decode(curl_exec($curl), true);
curl_close($curl);
var_dump($result);
?>