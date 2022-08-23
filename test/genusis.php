<?php

$kiw_genusis['url']        = "https://gensuite11.genusis.com/api/dm/";
$kiw_genusis['key']        = "A912EA9262A46B8";
$kiw_genusis['id']         = "synchroweb";
$kiw_genusis['username']   = "system";


$kiw_message = array(
    "DigitalMedia" => array(
        "ClientID" => $kiw_genusis['id'],
        "Username" => $kiw_genusis['username'],
        "SEND" => array(
            array(
                "Media" => "SMS",
                "MessageType" => "S",
                "Message" => "RM0 Hello there",
                "Destination" => array(
                    array(
                        "MSISDN" => "60136449583",
                        "MessageType" => "S"
                    )
                )
            )
        )
    )
);


$kiw_message = json_encode($kiw_message);

$kiw_signature = md5($kiw_message.$kiw_genusis['key']);

$kiw_genusis['url'] = "{$kiw_genusis['url']}?Key={$kiw_signature}";


$kiw_curl = curl_init();

curl_setopt($kiw_curl, CURLOPT_URL, $kiw_genusis['url']);
curl_setopt($kiw_curl, CURLOPT_POST, true);
curl_setopt($kiw_curl, CURLOPT_POSTFIELDS, $kiw_message);
curl_setopt($kiw_curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($kiw_curl, CURLOPT_TIMEOUT, 20);
curl_setopt($kiw_curl, CURLOPT_CONNECTTIMEOUT, 20);

$kiw_test = curl_exec($kiw_curl);


echo json_encode(json_decode($kiw_test, true), JSON_PRETTY_PRINT) . "\n\n";