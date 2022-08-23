<?php

$kiw['module'] = "Integration -> E-Payment";
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
    
    case "update": update(); break;
    default: echo "ERROR: Wrong implementation";

}


function update()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));
        
        $data['enabled']                 = (isset($_POST['enabled']) ? "y" : "n");
        $data['allowed_profile']         = $kiw_db->escape($_POST['allowed_profile']);
        $data['validity']                = $kiw_db->escape($_POST['validity']);
        $data['notification_send']       = (isset($_POST['notification_send']) ? "y" : "n");

        $data['on_success']              = $kiw_db->escape($_POST['on_success']);
        $data['on_after_success']        = $kiw_db->escape($_POST['on_after_success']);
        $data['page_success']            = $kiw_db->escape($_POST['page_success']);
        $data['page_failed']             = $kiw_db->escape($_POST['page_failed']);

        $data['paymenttype']             = $_POST['paymenttype'];

        if ($_POST['paymenttype'] == 'payfast') {

            $data['merchant_id']             = $_POST['merchant_id1'];
            $data['merchant_key']            = $_POST['merchant_key1'];
            $data['passphrase']              = $_POST['passphrase1'];
            $data['reference']               = $_POST['reference'];
            $data['description']             = $_POST['description'];
            $data['confirmation_email']      = $_POST['confirmation_email'];

        } elseif ($_POST['paymenttype'] == 'paypal') {

            $data['confirmation_email']      = $_POST['confirmation_email'];

        } elseif ($_POST['paymenttype'] == 'wirecard') {

            $data['merchant_id']             = $_POST['merchant_id1'];
            $data['security_sequence']       = $_POST['security_sequence'];
            $data['merchant_key']            = $_POST['secret_key'];
 
        } elseif ($_POST['paymenttype'] == 'alipay') {

            $data['merchant_id']             = $_POST['merchant_id1'];
            $data['merchant_key']            = $_POST['MD5_signature_key'];

        } elseif ($_POST['paymenttype'] == 'stripe') {

            $data['merchant_id']             = $_POST['publishable_key'];
            $data['merchant_key']            = $_POST['secret_key'];

        } elseif ($_POST['paymenttype'] == 'senangpay') {

            $data['merchant_id']             = $_POST['merchant_id1'];
            $data['merchant_key']            = $_POST['secret_key'];

        } elseif ($_POST['paymenttype'] == 'adyen') {
            
            $data['merchant_id']             = $_POST['merchant_id1'];
            $data['merchant_key']            = $_POST['skin_code'];
            $data['passphrase']              = $_POST['HMAC_key'];

        } elseif ($_POST['paymenttype'] == 'ipay88') {

            $data['merchant_id']             = $_POST['merchant_code'];
            $data['merchant_key']            = $_POST['merchant_key1'];

        } else {

            $data['merchant_id']             = $_POST['merchant_id1'];
            $data['merchant_key']            = $_POST['merchant_name'];
            $data['reference']               = $_POST['username'];
            $data['passphrase']              = $_POST['password'];


        }

        
        // $data['is_24_hour']     = isset($_REQUEST['is_24']) && $_REQUEST['is_24'] ? 1 : 0;
        // $data['start_time']     = $kiw_db->escape($_REQUEST['start_time']);

        // if(!$data['is_24_hour']){
            
        //     if($_REQUEST['start_time'] == $_REQUEST['stop_time'])
        //         die(json_encode(array("status" => "failed", "message" => "ERROR: Stop Time cannot same as Start Time", "data" => null)));
        //     else
        //         $data['stop_time'] =   $kiw_db->escape($_REQUEST['stop_time']);

        // }
      
        $data['updated_date']            = date('Y-m-d H-i-s');

        if($kiw_db->update("kiwire_int_payment_gateways", $data, "tenant_id = '$tenant_id'")){
            
            sync_logger("{$_SESSION['user_name']} updated Payment setting", $_SESSION['tenant_id']);
    
            echo json_encode(array("status" => "success", "message" => "SUCCESS: Payment setting has been updated", "data" => null));
        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }



    } else
    
        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
}