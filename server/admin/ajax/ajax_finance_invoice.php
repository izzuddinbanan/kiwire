
<?php

$kiw['module'] = "Finance -> Report";
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

    case "get_all": get_data(); break;
    case "pay": pay(); break;
    case "get_update": get_update(); break;

    default: echo "ERROR: Wrong implementation";

}


function get_data()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        $kiw_temp = $kiw_db->fetch_array("SELECT * FROM kiwire_invoice WHERE tenant_id = '$tenant_id' ");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));

    }

}


function get_update()
{
    global $kiw_db, $tenant_id;


    $id = $kiw_db->escape($_REQUEST['id']);

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_invoice WHERE tenant_id = '$tenant_id' AND id = '{$id}' LIMIT 1");
        
        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}


function pay()
{

    global $kiw_db, $tenant_id;
    
    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        $id = $kiw_db->escape($_REQUEST['id']);


        $new_pay = (float)$_REQUEST['totalpay'];
        
        $kiw_db->query("UPDATE kiwire_invoice SET updated_date = NOW(), total_paid = total_paid + {$new_pay}, balance = balance - {$new_pay} WHERE id = '{$id}' AND tenant_id = '{$tenant_id}' LIMIT 1");
        

        sync_logger("{$_SESSION['user_name']} updated total pay {$new_pay} for {$_REQUEST['username']}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Payment submitted successfully", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}