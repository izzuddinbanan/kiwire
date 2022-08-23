<?php


require_once dirname(__FILE__, 2) . "/includes/include_session.php";

require_once dirname(__FILE__, 3) . "/admin/includes/include_connection.php";


if ($_SESSION['system']['checked'] == "true") {


    $kiw_action = $_REQUEST['action'];


    if ($kiw_action == "available") {


        $kiw_username = $kiw_db->escape($_REQUEST['value']);

        if (!empty($kiw_username)) {


            header("Content-Type: application/json");


            $kiw_notification = $kiw_cache->get("NOTIFICATION_DATA:{$_SESSION['controller']['tenant_id']}");

            if (empty($kiw_notification)) {


                $kiw_notification = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_notification WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

                if (empty($kiw_notification)) $kiw_notification = array("dummy" => true);

                $kiw_cache->set("NOTIFICATION_DATA:{$_SESSION['controller']['tenant_id']}", $kiw_notification, 1800);


            }


            $kiw_result = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_account_auth WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' AND username = '{$kiw_username}'");


            if ($kiw_result['kcount'] == 0) echo json_encode(array("status" => "success"));
            else echo json_encode(array("status" => "failed", "message" => $kiw_notification['error_username_existed']));


        }


    } elseif ($kiw_action == "password") {


        require_once "../includes/include_registration.php";


        $kiw_notification = $kiw_cache->get("NOTIFICATION_DATA:{$_SESSION['controller']['tenant_id']}");

        if (empty($kiw_notification)) {


            $kiw_notification = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_notification WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

            if (empty($kiw_notification)) $kiw_notification = array("dummy" => true);

            $kiw_cache->set("NOTIFICATION_DATA:{$_SESSION['controller']['tenant_id']}", $kiw_notification, 1800);


        }


        $kiw_username = $kiw_db->escape($_REQUEST['username']);

        $kiw_password = $kiw_db->escape($_REQUEST['password']);


        $kiw_password_test = check_password_policy($kiw_db, $kiw_cache, $_SESSION['controller']['tenant_id'], $kiw_notification, $kiw_username, $kiw_password);

        if ($kiw_password_test !== true){

            echo json_encode(array("status" => "failed", "message" => $kiw_password_test));

        } else {

            echo json_encode(array("status" => "success"));

        }


    }


}