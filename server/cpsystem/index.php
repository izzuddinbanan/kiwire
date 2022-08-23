<?php


session_start();


if ($_SERVER['HTTPS'] != "on"){

    die(json_encode(array("status" => "error", "message" => "Please use secure connection")));

}


$kiw_request = $_REQUEST['request'];

$kiw_request = explode("/", $kiw_request);


$kiw_tenant_id =  $kiw_request[0];

$kiw_device_id =  $kiw_request[1];


header("Content-Type: application/captive+json");

header("Cache-Control: private");


$kiw_domain = $_SERVER['HTTP_HOST'];

$kiw_port = $_SERVER['SERVER_PORT'];


if ($kiw_port == 443) {

    $kiw_url = "https://{$kiw_domain}/login/";

} else $kiw_url = "https://{$kiw_domain}:{$kiw_port}/login/";


file_put_contents("request.log", date("Y-m-d H:i:s") . " :: " . json_encode($_REQUEST) . "\n", FILE_APPEND);


echo json_encode(
    array(
        "captive" => true,
        "user-portal-url" => $kiw_url,
        "venue-info-url" => $kiw_url
    )
);