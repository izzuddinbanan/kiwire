<?php

$kiw['module'] = "Finance -> Manual Posting";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_connection.php";
require_once "../includes/include_general.php";

if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}

$action = $_REQUEST['action'];

switch ($action) {

    case "update": update($kiw_db, $tenant_id); break;
    default: echo "ERROR: Wrong implementation";

}


function update($kiw_db, $tenant_id)
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $kiw_room = $kiw_db->escape($_REQUEST['room_no']);
        $kiw_amount = $kiw_db->escape($_REQUEST['charge_amount']);
        $kiw_remark = $kiw_db->escape($_REQUEST['remark']);


        if (!empty($kiw_room) && !empty($kiw_amount) && !empty($kiw_remark)) {


            $kiw_room = $kiw_db->query_first("SELECT username,fullname FROM kiwire_account_auth WHERE username = '{$kiw_room}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");


            $kiw_data['id']             = "NULL";
            $kiw_data['updated_date']   = "NOW()";
            $kiw_data['tenant_id']      = $_SESSION['tenant_id'];
            $kiw_data['login_date']     = "NOW()";
            $kiw_data['post_date']      = "NULL";
            $kiw_data['room']           = $kiw_room['username'];
            $kiw_data['status']         = "new";
            $kiw_data['amount']         = $kiw_amount;
            $kiw_data['profile']        = $kiw_remark;
            $kiw_data['name']           = $kiw_room['fullname'];;

            if($kiw_db->insert("kiwire_int_pms_payment", $kiw_data)){

                sync_logger("{$_SESSION['user_name']} updated PMS manual posting {$kiw_room}", $_SESSION['tenant_id']);

                echo json_encode(array("status" => "success", "message" => "SUCCESS: We have scheduled the posting. It may take up to 30 seconds.", "data" => null));

            }
            else {

                echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));
    
            }

        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please provide all information", "data" => null));

        }


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}
