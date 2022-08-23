<?php

$kiw['module'] = "Account -> Auto Reset";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_general.php";
require_once "../includes/include_connection.php";

if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}

$action = $_REQUEST['action'];

switch ($action) {

    case "get_all": get_data(); break;
    case "delete": delete(); break;
    default: echo "ERROR: Wrong implementation";

}


function get_data()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        $kiw_temp = $kiw_db->fetch_array("SELECT * FROM  kiwire_facebook_reputation WHERE tenant_id = '{$tenant_id}' LIMIT 1000");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));

    }
}


function delete()
{
    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));
        
        $id = $kiw_db->escape($_POST['id']);

        if (!empty($id)) {

            $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_facebook_reputation WHERE id = '" . $id . "' AND tenant_id = '$tenant_id'");
            $del_pagename = $kiw_temp['pagename'];

            $sql = $kiw_db->query("DELETE FROM kiwire_facebook_reputation WHERE id = '" . $id . "' AND tenant_id = '$tenant_id'");

        }


        sync_logger("{$_SESSION['user_name']} deleted Facebook {$del_pagename}", $_SESSION['tenant_id']);
        
        echo json_encode(array("status" => "success", "message" => "SUCCESS: Facebook : $del_pagename has been deleted", "data" => null));

    } else

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
}