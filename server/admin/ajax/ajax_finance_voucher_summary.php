<?php

$kiw['module'] = "Integration -> Realm";
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

    case "delete": delete(); break;
    case "get_all": get_data(); break;
    case "view_voucherSummary"  : view_voucherSummary(); break;
    default: echo "ERROR: Wrong implementation";

}


function get_data()
{

    global $kiw_db, $tenant_id;


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_profiles_db = $kiw_db->fetch_array("SELECT name,price FROM kiwire_profiles WHERE tenant_id = '{$_SESSION['tenant_id']}'");

        foreach ($kiw_profiles_db as $kiw_value){

            $kiw_profile[$kiw_value['name']] = $kiw_value['price'];

        }



        $kiw_temps = $kiw_db->fetch_array("SELECT creator, remark, bulk_id, count(bulk_id) as qty, SUM(price) AS price, profile_subs FROM kiwire_account_auth WHERE bulk_id <> '' AND ktype = 'voucher' AND tenant_id = '{$tenant_id}' GROUP BY bulk_id");

        for ($x = 0; $x < count($kiw_temps); $x++){


            if (isset($kiw_profile[$kiw_temps[$x]['profile_subs']])){

                $kiw_temps[$x]['price'] = $kiw_profile[$kiw_temps[$x]['profile_subs']];

            } else $kiw_temps[$x]['price'] = 0;


            $kiw_temps[$x]['total'] = $kiw_temps[$x]['qty'] * $kiw_profile[$kiw_temps[$x]['profile_subs']];


        }

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temps));


    }


}


function delete()
{


    global $kiw_db, $tenant_id;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {
        csrf($kiw_db->escape($_REQUEST['token']));

        $id = $kiw_db->escape($_POST['id']);

        if (!empty($id)) {

            $kiw_db->query("DELETE FROM kiwire_account_auth WHERE bulk_id = '{$id}' AND tenant_id = '{$tenant_id}'");

        }


        sync_logger("{$_SESSION['user_name']} deleted voucher {$id}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Voucher : [ {$id} ] has been deleted", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}



function view_voucherSummary()
{


    global $kiw_db, $tenant_id;


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $id = $kiw_db->escape($_POST['bulk_id']);


        $kiw_temp = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_account_auth WHERE bulk_id = '{$id}' AND tenant_id = '{$tenant_id}'");


        if ($kiw_temp['kcount'] > 5000){


            echo json_encode(array("status" => "failed", "message" => "System is not allowed to display voucher more than 5,000 units", "data" => null));


        } else {


            $kiw_temp = $kiw_db->fetch_array("SELECT username,date_create,date_expiry,price FROM kiwire_account_auth WHERE bulk_id = '{$id}' AND tenant_id = '{$tenant_id}'");

            echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


        }


    }


}
