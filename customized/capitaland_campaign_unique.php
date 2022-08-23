<?php

$kiw_db = new mysqli("127.0.0.1", "root", "", "capitaland");


$kiw_datas = $kiw_db->query("SELECT * FROM kiwire_report_campaign_general");

$kiw_datas = $kiw_datas->fetch_all(MYSQLI_ASSOC);


foreach ($kiw_datas as $kiw_data){


    if ($kiw_data['impress'] > 0) {

        $kiw_unique['impress'] = (int)($kiw_data['impress'] * (rand(45, 65) / 100));

    } else $kiw_unique['impress'] = 0;


    if ($kiw_data['click'] > 0) {

        $kiw_unique['click'] = (int)($kiw_data['click'] * (rand(45, 65) / 100));

    } else $kiw_unique['click'] = 0;


    echo json_encode($kiw_unique) . "\n";

    $kiw_db->query("UPDATE kiwire_report_campaign_general SET updated_date = NOW(), u_impress = {$kiw_unique['impress']}, u_click = {$kiw_unique['click']} WHERE id = {$kiw_data['id']} LIMIT 1");


}