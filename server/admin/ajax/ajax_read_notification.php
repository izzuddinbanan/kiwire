<?php


header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_general.php";
require_once "../includes/include_connection.php";


$kiw_action = $_REQUEST['action'];

if ($_SESSION['access_level'] == "superuser"){

    $kiw_tenant_id = "superuser";

} else $kiw_tenant_id = $_SESSION['tenant_id'];



if ($kiw_action == "read") {


    $kiw_messages = $kiw_cache->hGetAll("KIWIRE_MESSAGE:{$kiw_tenant_id}:{$_SESSION['user_name']}");

    if (empty($kiw_messages)) {


        $kiw_messages = $kiw_db->fetch_array("SELECT * FROM kiwire_message WHERE recipient = '{$_SESSION['user_name']}' AND date_read = '0000-00-00 00:00:00' AND tenant_id = '{$kiw_tenant_id}' ORDER BY date_sent DESC LIMIT 10");

        if (empty($kiw_messages)) $kiw_messages = array("dummy" => true);


        $kiw_cache->hMSet("KIWIRE_MESSAGE:{$kiw_tenant_id}:{$_SESSION['user_name']}", $kiw_messages);
        $kiw_cache->expire("KIWIRE_MESSAGE:{$kiw_tenant_id}:{$_SESSION['user_name']}", 60);


    }


    if ($kiw_messages['dummy'] != true) {


        foreach ($kiw_messages as $kiw_message) {


            $kiw_request_id = (int)$_REQUEST['message_id'];


            if ($kiw_message['id'] == $kiw_request_id) {


                $kiw_response = array("status" => "success", "message" => "", "data" => $kiw_message);

                $kiw_db->query("UPDATE kiwire_message SET updated_date = NOW(), date_read = NOW() WHERE id = '{$kiw_request_id}' AND tenant_id = '{$kiw_tenant_id}'");


            } else {

                $kiw_remaining[] = $kiw_message;

            }


        }


        if (empty($kiw_remaining)) {

            $kiw_remaining = array("dummy" => true);

        }


        $kiw_cache->del("KIWIRE_MESSAGE:{$kiw_tenant_id}:{$_SESSION['user_name']}");
        $kiw_cache->hMSet("KIWIRE_MESSAGE:{$kiw_tenant_id}:{$_SESSION['user_name']}", $kiw_remaining);
        $kiw_cache->expire("KIWIRE_MESSAGE:{$kiw_tenant_id}:{$_SESSION['user_name']}", 60);


        echo json_encode($kiw_response);


    }


} elseif ($kiw_action == "list"){


    $kiw_messages = $kiw_cache->hGetAll("KIWIRE_MESSAGE:{$kiw_tenant_id}:{$_SESSION['user_name']}");

    if (empty($kiw_messages)) {


        $kiw_messages = $kiw_db->fetch_array("SELECT * FROM kiwire_message WHERE recipient = '{$_SESSION['user_name']}' AND date_read = '0000-00-00 00:00:00' AND tenant_id = '{$kiw_tenant_id}' ORDER BY date_sent DESC LIMIT 20");

        if (empty($kiw_messages)) $kiw_messages = array("dummy" => true);


        $kiw_cache->hMSet("KIWIRE_MESSAGE:{$kiw_tenant_id}:{$_SESSION['user_name']}", $kiw_messages);

        $kiw_cache->expire("KIWIRE_MESSAGE:{$kiw_tenant_id}:{$_SESSION['user_name']}", 60);


    }


    if ($kiw_messages['dummy'] != true && count($kiw_messages) > 0){

        echo json_encode(array("status" => "success", "message" => "", "data" => $kiw_messages), JSON_OBJECT_AS_ARRAY);

    } else {

        echo json_encode(array("status" => "failed", "message" => "", "data" => null));

    }


} elseif ($kiw_action == "mark-all-read"){


    $kiw_messages = array("dummy" => true);

    $kiw_cache->del("KIWIRE_MESSAGE:{$kiw_tenant_id}:{$_SESSION['user_name']}");
    $kiw_cache->hMSet("KIWIRE_MESSAGE:{$kiw_tenant_id}:{$_SESSION['user_name']}", $kiw_messages);
    $kiw_cache->expire("KIWIRE_MESSAGE:{$kiw_tenant_id}:{$_SESSION['user_name']}", 60);


    $kiw_db->query("UPDATE kiwire_message SET updated_date = NOW(), date_read = NOW() WHERE recipient = '{$_SESSION['user_name']}' AND date_read = '0000-00-00 00:00:00' AND tenant_id = '{$kiw_tenant_id}'");


    echo json_encode(array("status" => "success", "message" => "SUCCESS: All notification has been set as read!", "data" => null));


}