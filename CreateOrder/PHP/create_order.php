<?php

// Bearer must be generated with OAuth 2 and scope: urn:viva:payments:core:api:redirectcheckout
// https://developer.vivawallet.com/tutorials-for-payments/enable-oauth2-authentication/
$accessToken = 'xxx';
$postFields  = [
    'amount'              => 100,
    'customerTrns'        => 'This is a description displayed to the customer',
    'customer'            => [
        'email'       => 'someone@vivawallet.com',
        'fullName'    => 'George Seferis',
        'phone'       => '69xxxxxxxxx',
        'countryCode' => 'GR',
        'requestLang' => 'el-GR'
    ],
    'paymentTimeout'      => 1800,
    'preauth'             => true,
    'allowRecurring'      => true,
    'maxInstallments'     => 0,
    'paymentNotification' => true,
    'tipAmount'           => 1,
    'disableExactAmount'  => true,
    'disableCash'         => false,
    'disableWallet'       => false,
    'sourceCode'          => 'Default',
    'merchantTrns'        => 'This is a short description that helps you uniquely identify the transaction',
    'tags'                => ['tag1', 'tag2']
];

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL            => 'https://demo-api.vivapayments.com/checkout/v2/orders',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING       => '',
    CURLOPT_MAXREDIRS      => 10,
    CURLOPT_TIMEOUT        => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST  => 'POST',
    CURLOPT_POSTFIELDS     => json_encode($postFields),
    CURLOPT_HTTPHEADER     => array(
        "Authorization: Bearer $accessToken",
        'Content-Type: application/json'
    ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response; // {"orderCode":4201736414972602}

?>
