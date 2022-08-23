<?php

$kiw['module'] = "Campaign -> Coupon Creation";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_general.php";
require_once "../includes/include_connection.php";

require_once "../../libs/class.sql.helper.php";


if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}


$action = $_REQUEST['action'];

switch ($action) {

    case "create": create(); break;
    case "delete": delete(); break;
    case "get_all": get_data(); break;
    case "get_update": get_update(); break;
    case "edit_single_data": edit_single_data(); break;
    case "generateCouponCode": generateCouponCode(6, 1); break;
    default: echo "ERROR: Wrong implementation";

}

function create()
{
    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));


        $data['title']           = $_GET['title'];
        $data['img_name']        = $_GET['img_name'];
        $data['img_path']        = $_GET['img_path'];
        $data['details']         = $_GET['details'];

        $data['price']           = $_GET['price'];
        $data['additional_info'] = $_GET['additional_info'];
        $data['date_expired']    = date("Y-m-d H:i:s", strtotime($_GET['date_expired']));
        $data['code_method']     = $_GET['code_method'];

        $data['code']            = $_GET['code'];
        $data['updated_date']    = date('Y-m-d H:i:s');
        $data['tenant_id']       = $tenant_id;

        if($kiw_db->insert("kiwire_coupon_generator", $data)){
            
            sync_logger("{$_SESSION['user_name']} create coupon {$_GET['title']}", $_SESSION['tenant_id']);

            echo json_encode(array("status" => "success", "message" => "SUCCESS: New Coupon : {$_GET['title']} added", "data" => null));
        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }  


        


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}


function delete()
{
    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $id = $kiw_db->escape($_POST['id']);


        if (!empty($id)) {


            $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_coupon_generator WHERE id = '{$id}' AND tenant_id = '{$tenant_id}'");

            $kiw_db->query("DELETE FROM kiwire_coupon_generator WHERE id = '{$id}' AND tenant_id = '{$tenant_id}'");

        }


        sync_logger("{$_SESSION['user_name']} deleted coupon {$kiw_temp['title']}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Coupon {$kiw_temp['title']} has been deleted", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}


function get_data()
{
    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->fetch_array("SELECT  * FROM kiwire_coupon_generator  WHERE tenant_id = '{$tenant_id}' LIMIT 1000");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }

}


function get_update()
{

    global $kiw_db, $tenant_id;

    $id = $kiw_db->escape($_REQUEST['id']);


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_coupon_generator WHERE tenant_id = '$tenant_id' AND id = '{$id}' LIMIT 1");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));

    }


}


function edit_single_data()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));


        $id = $kiw_db->escape($_REQUEST['reference']);

        $kiw_data['title']            = $kiw_db->escape($_REQUEST['title']);
        $kiw_data['img_name']         = $kiw_db->escape($_REQUEST['img_name']);
        $kiw_data['img_path']         = $kiw_db->escape($_REQUEST['img_path']);
        $kiw_data['details']          = $kiw_db->escape($_REQUEST['details']);

        $kiw_data['price']            = $kiw_db->escape($_REQUEST['price']);
        $kiw_data['additional_info']  = $kiw_db->escape($_REQUEST['additional_info']);
        $kiw_data['date_expired']     = $kiw_db->escape($_REQUEST['date_expired']);
        $kiw_data['code_method']      = $kiw_db->escape($_REQUEST['code_method']);
        $kiw_data['code']             = $kiw_db->escape($_REQUEST['code']);

        $kiw_db->query(sql_update($kiw_db, "", $kiw_data, "id = '{$id}' AND tenant_id = '{$tenant_id}' LIMIT 1"));


        sync_logger("{$_SESSION['user_name']} updated coupon {$kiw_data['title']}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Coupon {$kiw_data['title']} has been updated", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}


function generateCouponCode($length = 10, $echo = 0)
{

    global $kiw_db;

    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    $charactersLength = strlen($characters);


    $randomString = '';

    while ($randomString == "") {

        for ($i = 0; $i < $length; $i++) {

            $randomString .= $characters[rand(0, $charactersLength - 1)];

        }


        $kiw_test = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_coupon_generator WHERE tenant_id = '{$_SESSION['tenant_id']}' AND code = '{$randomString}'");

        if ($kiw_test['kcount'] == 0){

            echo json_encode(array("status" => "success", "message" => null, "data" => $randomString));

        } else $randomString = "";


    }


}
