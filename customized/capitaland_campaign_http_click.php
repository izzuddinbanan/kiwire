<?php


$kiw_start_time = strtotime("17-02-2020 16:00:00");

$kiw_stop_time = strtotime("17-03-2020 15:59:59");


$kiw_tenants = file_get_contents("capitaland_campaign_list.json");

$kiw_tenants = json_decode($kiw_tenants, true);


$kiw_datas = fopen(dirname(__FILE__) . "/click.log", "r");


$kiw_db = new mysqli("127.0.0.1", "root", "", "capitaland");


while (!feof($kiw_datas)){


    $kiw_current = fgets($kiw_datas);

    $kiw_current = explode(" ", $kiw_current);


    $kiw_campaign[] = ltrim($kiw_current[3], "[");

    $kiw_campaign[] = $kiw_current[6];


    unset($kiw_current);


    $kiw_campaign[0] = substr($kiw_campaign[0], 0, 11) . " " . substr($kiw_campaign[0], 12);

    $kiw_campaign[0] = date("Y-m-d H:00:00", strtotime(str_replace("/", "-", $kiw_campaign[0])));

    $kiw_campaign[2] = strtotime($kiw_campaign[0]);


    if ($kiw_campaign[2] > $kiw_start_time && $kiw_campaign[2] < $kiw_stop_time) {


        parse_str($kiw_campaign[1], $kiw_campaign[1]);


        $kiw_campaign[1] = urldecode(base64_decode($kiw_campaign[1]['name']));

        if (strpos($kiw_campaign[1], " || ") == false){

            $kiw_campaign[1] = urldecode(base64_decode($kiw_campaign[1]['name']));

        }


        $kiw_campaign[3] = $kiw_tenants[urlencode($kiw_campaign[1])];


        if (!empty($kiw_campaign[3])) {


            $kiw_db->query("UPDATE kiwire_report_campaign_general SET updated_date = NOW(), click = click + 1 WHERE name = '{$kiw_campaign[1]}' AND tenant_id = '{$kiw_campaign[3]}' AND report_date = '{$kiw_campaign[0]}' LIMIT 1");

            if ($kiw_db->affected_rows == 0) {

                echo "Invalid click: {$kiw_campaign[0]} | {$kiw_campaign[1]}\n";

            }


        } else {

            echo "Missing tenant id for campaign {$kiw_campaign[1]}\n";

        }


    }


    unset($kiw_campaign);


}










