<?php

require_once dirname(__FILE__, 2) . "/includes/include_session.php";
require_once dirname(__FILE__, 2) . "/includes/include_connection.php";

header("Content-Type: application/json");


$kiw_survey = $kiw_db->escape($_REQUEST['survey_name']);

if (!empty($kiw_survey)){


    $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_survey_list WHERE id = '{$kiw_survey}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");

    if (!empty($kiw_temp)){


        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    } else {

        echo json_encode(array("status" => "error", "message" => "Survey no longer in database.", "data" => null));

    }



} else {

    echo json_encode(array("status" => "error", "message" => "Please provide a valid survey id", "data" => null));

}



