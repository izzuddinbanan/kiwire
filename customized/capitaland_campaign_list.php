<?php


$kiw_tenant_list = [];


$kiw_datas = fopen(dirname(__FILE__) . "/capitaland_campaign_list.log", "r");


while (!feof($kiw_datas)){


    $kiw_current = fgets($kiw_datas);


    if (strlen($kiw_current) > 5) {


        $kiw_current = explode("-", trim($kiw_current));


        if (count($kiw_current) > 4) {


            $kiw_current[5] = urldecode(base64_decode($kiw_current[5]));


            if (strpos($kiw_current[5], " || ") == false) {

                $kiw_current[5] = urldecode(base64_decode($kiw_current[5]));

            }


            if (strlen($kiw_current[5]) > 0) {

                $kiw_tenant_list[urlencode($kiw_current[5])] = "{$kiw_current[3]}-{$kiw_current[4]}";

            }


        }


    }


    unset($kiw_current);


}


echo json_encode($kiw_tenant_list);


file_put_contents("capitaland_campaign_list.json", json_encode($kiw_tenant_list));
