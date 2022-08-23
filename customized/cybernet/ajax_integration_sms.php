<?php

$kiw['module'] = "Integration -> SMS";
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
    case "test": test($kiw_db, $_SESSION['tenant_id']); break;
    case "create": create(); break;
    case "delete": delete(); break;
    case "get_all": get_data(); break;
    case "update": update(); break;
    default: echo "ERROR: Wrong implementation";
}

function create()
{
    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        $prefix = $_GET['prefix'];

        $data['prefix']          = $prefix;
        $data['country']         = $_GET['country'];
        $data['tenant_id']       = $tenant_id;
        $data['updated_date']    = date('Y-m-d H-i-s');

        $kiw_db->insert("kiwire_int_sms_prefix", $data);


        sync_logger("{$_SESSION['user_name']} create Prefix {$prefix} ", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: New Prefix {$prefix} Added", "data" => null));

    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}


function delete()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        $id = $kiw_db->escape($_POST['id']);

        if (!empty($id)) {


            $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_int_sms_prefix WHERE id = '{$id}' AND tenant_id = '{$tenant_id}'");
            $del_prefix = $kiw_temp['prefix'];

            $kiw_db->query("DELETE FROM kiwire_int_sms_prefix WHERE id = '{$id}' AND tenant_id = '{$tenant_id}'");


        }


        sync_logger("{$_SESSION['user_name']} deleted Prefix {$del_prefix} ", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Prefix : $del_prefix has been deleted", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}


function get_data()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->fetch_array("SELECT  * FROM kiwire_int_sms_prefix WHERE tenant_id = '{$tenant_id}' LIMIT 1000");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }

}


function update()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        $data['twilio_sid']                = $_POST['twilio_sid'];
        $data['twilio_token']              = $_POST['twilio_token'];
        $data['enabled']                   = (isset($_POST['enabled']) ? "y" : "n");
        $data['twilio_no']                 = $_POST['twilio_no'];

        $data['profile']                   = $_POST['profile'];
        $data['allowed_zone']              = $_POST['allowed_zone'];
        $data['validity']                  = $_POST['validity'];
        $data['template_id']               = $_POST['template_id'];

        $data['sms_text']                  = $_POST['sms_text'];
        $data['mode']                      = $_POST['mode'];
        $data['template']                  = $_POST['template'];
        $data['syn_key']                   = $_POST['syn_key'];

        $data['operator']                  = $_POST['operator'];
        $data['u_uri']                     = $_POST['u_uri'];
        $data['u_phoneno']                 = $_POST['u_phoneno'];

        $data['u_message']                 = $_POST['u_message'];
        $data['u_method']                  = $_POST['u_method'];
        $data['u_header']                  = $_POST['u_header'];
        $data['updated_date']              = date('Y-m-d H-i-s');
        
        $data['prefix_phoneno']            = (isset($_POST['prefix_phoneno']) ? "y" : "n");
        $data['twilio_use_whatsapp']       = (isset($_POST['twilio_use_whatsapp']) ? "y" : "n");
        $data['data']                      = $kiw_db->escape(implode(",", $_POST['data']));
        $data['after_register']            = (isset($_POST['after_register'])) ? $_POST['after_register'] : "internet";


        $kiw_db->update("kiwire_int_sms", $data, "tenant_id = '$tenant_id'");


        sync_logger("{$_SESSION['user_name']} updated SMS setting", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Integration SMS updated", "data" => null));

    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}


function test($kiw_db, $kiw_tenant){


    require_once dirname(__FILE__, 3) . "/libs/twilio/Twilio/autoload.php";


    $kiw_config = [];

    foreach ($_REQUEST as $kiw_key => $kiw_value){

        $kiw_config[$kiw_db->escape($kiw_key)] = $kiw_db->escape($kiw_value);

    }


    $phone_number = preg_replace('/\D/', '', $kiw_config['smsto']);

    if ($kiw_config['prefix_phoneno'] == "y") $phone_number = "+" . $phone_number;

    $kiw_status = false;


    $kiw_sms_id = strtoupper(substr(md5(time() . rand(1000, 9000)), rand(1, 8), 8));

    $kiw_sms_content = "[{$kiw_sms_id}] If you received this SMS, then the setting is correct.";


    if ($kiw_config['operator'] == "twilio"){

        try {


            if ($kiw_config['twilio_use_whatsapp'] == "y"){

                $phone_number = "whatsapp:" . $phone_number;

                $kiw_config['twilio_no'] = "whatsapp:" . $kiw_config['twilio_no'];

            }


            $kiw_client = new Twilio\Rest\Client($kiw_config['twilio_sid'], $kiw_config['twilio_token']);

            $kiw_message = $kiw_client->messages->create($phone_number, array("from" => $kiw_config['twilio_no'], "body" => trim(strip_tags($kiw_sms_content))));


            $kiw_status = $kiw_message->status;


        } catch (Exception $e){

            echo json_encode(array("status" => "error", "message" => "Error: Unable to send out SMS to {$phone_number}: " . $e->getMessage(), "data" => null));

            die();

        }


    } elseif ($kiw_config['operator'] == "synchroweb"){


        $data_json = json_encode(array('api_key' => $kiw_config['key'], "phone_number_to" => $phone_number, "message" => trim(strip_tags($kiw_sms_content))));

        $kiw_client = send_http_request("https://sms.synchroweb.com/agent/index_sms.php", array("data" => $data_json),"post");

        if ($kiw_client['status'] == 200) $kiw_status = "succeed";


    } elseif ($kiw_config['operator'] == "generic"){


        $kiw_client['phone_var'] = $kiw_config['u_phoneno'];

        $kiw_client['message_var'] = $kiw_config['u_message'];


        try {

            if ($kiw_config['u_method'] == "get") {


                $kiw_client['url'] = $kiw_config['u_uri'];

                $kiw_client['status'] = send_http_request($kiw_client['url'], array($kiw_client['phone_var'] => $phone_number, $kiw_client['message_var'] => trim(strip_tags($kiw_sms_content))), "get");


            } else {

                $kiw_client['status'] = send_http_request($kiw_config['u_uri'], array($kiw_client['phone_var'] => $phone_number, $kiw_client['message_var'] => trim(strip_tags($kiw_sms_content))), "post", $kiw_config['u_header']);

            }


            if ($kiw_client['status'] == 200) $kiw_status = $kiw_client['message'];


        } catch (Exception $e){

            echo json_encode(array("status" => "error", "message" => "Error: Unable to send out SMS to {$phone_number}: " . $e->getMessage(), "data" => null));

            die();

        }


    } elseif ($kiw_config['operator'] == "cybernet"){


        $kiw_data['urns'] = ["tel:{$phone_number}"];
        $kiw_data['text'] = $kiw_sms_content;


        $kiw_connection = curl_init("http://175.107.240.143/api/v2/broadcasts.json");

        curl_setopt($kiw_connection, CURLOPT_POST, 1);
        curl_setopt($kiw_connection, CURLOPT_POSTFIELDS, json_encode($kiw_data));
        curl_setopt($kiw_connection, CURLOPT_FOLLOWLOCATION, 1);

        curl_setopt($kiw_connection, CURLOPT_HTTPHEADER, array(
            "Authorization: Token de1f8562762ea37970122c83b3c8d1fdc092382e",
            "Content-Type: application/json")
        );

        curl_setopt($kiw_connection, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($kiw_connection, CURLOPT_TIMEOUT, 10);
        curl_setopt($kiw_connection, CURLOPT_CONNECTTIMEOUT, 10);


        $kiw_status = curl_exec($kiw_connection);
        $kiw_error = curl_errno($kiw_connection);

        curl_close($kiw_connection);


        if ($kiw_error != 0){

            $kiw_status = "Error code: {$kiw_error}";

        }


    } else {

        echo json_encode(array("status" => "error", "message" => "Unknown operator provided", "data" => null));

        die();

    }


    echo json_encode(array("status" => "success", "message" => "SMS sent to [{$phone_number}] id: [{$kiw_sms_id}] with status: {$kiw_status}", "data" => null));


}