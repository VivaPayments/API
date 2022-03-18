<?php

// Bearer must be generated with OAuth 2 and scope: urn:viva:payments:core:api:redirectcheckout
// https://developer.vivawallet.com/tutorials-for-payments/enable-oauth2-authentication/
$accessToken   = 'xxx';
$transactionId = 'xxx';

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL            => "https://demo-api.vivapayments.com/checkout/v2/transactions/$transactionId",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING       => '',
    CURLOPT_MAXREDIRS      => 10,
    CURLOPT_TIMEOUT        => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST  => 'GET',
    CURLOPT_HTTPHEADER     => array(
        "Authorization: Bearer $accessToken"
    ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;

/*
{
    "email": "someone@vivawallet.com",
    "amount": 1,
    "orderCode": 6810910170372602,
    "statusId": "F",
    "fullName": "George Seferis",
    "insDate": "2021-12-13T11:16:32.541Z",
    "cardNumber": "523929XXXXXX0168",
    "currencyCode": 978,
    "customerTrns": "This is a description displayed to the customer",
    "merchantTrns": "This is a short description that helps you uniquely identify the transaction",
    "transactionTypeId": 5,
    "recurringSupport": true,
    "totalInstallments": 0,
    "cardCountryCode": null,
    "cardIssuingBank": null,
    "currentInstallment": 0,
    "cardTypeId": 1
}
*/

?>
