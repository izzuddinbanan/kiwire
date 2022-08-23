<?php

$kiw['module'] = "Integration -> SMS";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_connection.php";
require_once "../includes/include_general.php";
require_once "../../user/includes/include_general.php";

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

        csrf($kiw_db->escape($_REQUEST['token']));

        $prefix = $kiw_db->sanitize($_GET['prefix']);

        $data['prefix']          = $prefix;
        $data['country']         = $kiw_db->sanitize($_GET['country']);
        $data['tenant_id']       = $tenant_id;
        $data['updated_date']    = date('Y-m-d H-i-s');

        if($kiw_db->insert("kiwire_int_sms_prefix", $data)){

            sync_logger("{$_SESSION['user_name']} create Prefix {$prefix} ", $_SESSION['tenant_id']);

            echo json_encode(array("status" => "success", "message" => "SUCCESS: New Prefix {$prefix} Added", "data" => null));

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

        csrf($kiw_db->escape($_REQUEST['token']));
        
        $data['twilio_sid']                = $kiw_db->escape($_POST['twilio_sid']);
        $data['twilio_token']              = $kiw_db->escape($_POST['twilio_token']);
        $data['enabled']                   = (isset($_POST['enabled']) ? "y" : "n");
        $data['twilio_no']                 = $kiw_db->escape($_POST['twilio_no']);

        $data['profile']                   = $kiw_db->escape($_POST['profile']);
        $data['allowed_zone']              = $kiw_db->escape($_POST['allowed_zone']);
        $data['validity']                  = $kiw_db->escape($_POST['validity']);
        $data['template_id']               = $kiw_db->escape($_POST['template_id']);

        $data['sms_text']                  = $kiw_db->escape($_POST['sms_text']);
        $data['mode']                      = $kiw_db->escape($_POST['mode']);
        $data['template']                  = $kiw_db->escape($_POST['template']);
        $data['syn_key']                   = $kiw_db->escape($_POST['syn_key']);

        $data['operator']                  = $kiw_db->escape($_POST['operator']);
        $data['u_uri']                     = $kiw_db->escape($_POST['u_uri']);
        $data['u_phoneno']                 = $kiw_db->sanitize($_POST['u_phoneno']);

        $data['u_message']                 = $kiw_db->sanitize($_POST['u_message']);
        $data['u_method']                  = $kiw_db->escape($_POST['u_method']);
        $data['u_header']                  = $kiw_db->escape($_POST['u_header']);
        $data['updated_date']              = date('Y-m-d H-i-s');

        $data['g_url']                     = $kiw_db->escape($_POST['g_url']);
        $data['g_clientid']                = $kiw_db->escape($_POST['g_clientid']);
        $data['g_username']                = $kiw_db->escape($_POST['g_username']);
        $data['g_key']                     = $kiw_db->escape($_POST['g_key']);
        
        $data['prefix_phoneno']            = (isset($_POST['prefix_phoneno']) ? "y" : "n");
        $data['twilio_use_whatsapp']       = (isset($_POST['twilio_use_whatsapp']) ? "y" : "n");
        $data['data']                      = $kiw_db->escape(implode(",", $_POST['data']));
        $data['after_register']            = (isset($_POST['after_register'])) ? $_POST['after_register'] : "internet";


        // $data['is_24_hour']     = isset($_REQUEST['is_24']) && $_REQUEST['is_24'] ? 1 : 0;
        // $data['start_time']     = $kiw_db->escape($_REQUEST['start_time']);

        // if(!$data['is_24_hour']){
            
        //     if($_REQUEST['start_time'] == $_REQUEST['stop_time'])
        //         die(json_encode(array("status" => "failed", "message" => "ERROR: Stop Time cannot same as Start Time", "data" => null)));
        //     else
        //         $data['stop_time'] =   $kiw_db->escape($_REQUEST['stop_time']);

        // }

        
        if($kiw_db->update("kiwire_int_sms", $data, "tenant_id = '$tenant_id'")){

            sync_logger("{$_SESSION['user_name']} updated SMS setting", $_SESSION['tenant_id']);
    
            echo json_encode(array("status" => "success", "message" => "SUCCESS: Integration SMS updated", "data" => null));
        
        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }



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

    $kiw_sms_content = "[ {$kiw_sms_id} ] If you received this SMS, then the setting is correct.";


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


    } elseif ($kiw_config['operator'] == "genusis"){


        if (substr($kiw_sms_content, 0, 2) != "RM"){

            $kiw_sms_content = "RM0 {$kiw_sms_content}";

        }

        if (substr($phone_number, 0, 1) == "+"){

            $phone_number = substr($phone_number, 1);

        }


        $kiw_message = array(
            "DigitalMedia" => array(
                "ClientID" => $kiw_config['g_clientid'],
                "Username" => $kiw_config['g_username'],
                "SEND" => array(
                    array(
                        "Media" => "SMS",
                        "MessageType" => "S",
                        "Message" => $kiw_sms_content,
                        "Destination" => array(
                            array(
                                "MSISDN" => $phone_number,
                                "MessageType" => "S"
                            )
                        )
                    )
                )
            )
        );


        $kiw_message = json_encode($kiw_message);

        $kiw_signature = md5($kiw_message.$kiw_config['g_key']);

        $kiw_config['g_url'] = "{$kiw_config['g_url']}?Key={$kiw_signature}";


        $kiw_curl = curl_init();

        curl_setopt($kiw_curl, CURLOPT_URL, $kiw_config['g_url']);
        curl_setopt($kiw_curl, CURLOPT_POST, true);
        curl_setopt($kiw_curl, CURLOPT_POSTFIELDS, $kiw_message);
        curl_setopt($kiw_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($kiw_curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($kiw_curl, CURLOPT_CONNECTTIMEOUT, 20);

        $kiw_test = curl_exec($kiw_curl);

        $kiw_test = json_decode($kiw_test, true);

        $kiw_status = $kiw_test['DigitalMedia'][0]['Result'];


    } else {

        echo json_encode(array("status" => "error", "message" => "Unknown operator provided", "data" => null));

        die();

    }


    echo json_encode(array("status" => "success", "message" => "SMS sent to [ {$phone_number} ] id: [ {$kiw_sms_id} ] with status: {$kiw_status}", "data" => null));


}