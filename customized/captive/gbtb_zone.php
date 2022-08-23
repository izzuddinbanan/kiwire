<?php

$omaya_mac = $_SESSION['user']['mac'];

$omaya_url = "http://10.148.0.7/api/omaya_location.php?company_id=2";
$omaya_secret = "OmYAA_KIWIre";

$omaya_time = time();

$omaya_url .= "&mac_address={$omaya_mac}";
$omaya_url .= "&time={$omaya_time}";

$omaya_signature = md5("{$omaya_time}|{$omaya_mac}|{$omaya_secret}");

$omaya_url .= "&signature={$omaya_signature}";

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $omaya_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);

$omaya_zone = curl_exec($ch);

curl_close($ch);


if (strtoupper(substr($omaya_zone, 0, 5)) != "ERROR") {

    if (!empty($omaya_zone)) {

        $omaya_zone = json_decode($omaya_zone, true);

        if ($omaya_zone) {


            $zone = str_replace(array(" ", "'"), array("-", ""), $omaya_zone['location']);

            $_SESSION['user']['zone'] =  $zone;

            $kiw_cache->set("GBTB_ZONE_DATA:" . str_replace(array(":", "-"), "", strtoupper($omaya_mac)), $zone, 600);


        }

    }

}


unset($omaya_url);
unset($omaya_secret);
unset($omaya_mac);
unset($omaya_time);
