<?php
require_once('vendor/autoload.php');

$client = new \GuzzleHttp\Client();

$response = $client->request('GET', 'https://api.shasta.trongrid.io/v1/accounts/TBzQXw5H2kmPTDbt7mbiE69sw1i2sfUc17?only_confirmed=true', [
  'headers' => [
    'accept' => 'application/json',
  ],
]);

echo $response->getBody();