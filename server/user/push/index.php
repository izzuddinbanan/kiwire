<?php


require_once dirname(__FILE__, 2) . "/includes/include_session.php";

require_once dirname(__FILE__, 3) . "/admin/includes/include_connection.php";


$kiw_subscribe_data  = file_get_contents('php://input');

$kiw_subscribe_data = json_decode($kiw_subscribe_data, true);


if (!isset($kiw_subscription_array['endpoint'])) die("You are not allowed to access this module");

$kiw_subscription_hash = hash("md5", $kiw_subscribe_data);



if (!empty($_SESSION['user']['mac'])) {


    $kiw_existed = $kiw_db->query_first("SELECT * FROM kiwire_push_subscription WHERE mac_address = '{$_SESSION['user']['mac']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");


    if (empty($kiw_existed)) {


        $kiw_data = array();

        $kiw_data['id']            = "NULL";
        $kiw_data['tenant_id']     = $_SESSION['controller']['tenant_id'];
        $kiw_data['updated_date']  = "NOW()";
        $kiw_data['mac_address']   = $_SESSION['user']['mac'];
        $kiw_data['push_key']      = base64_encode(json_encode($kiw_subscribe_data));
        $kiw_data['push_hash']     = $kiw_subscription_hash;

        $kiw_db->insert("kiwire_push_subscription", $kiw_data);


    } else if ($kiw_existed['push_hash'] != $kiw_subscription_hash) {


        $kiw_data['updated_date']  = "NOW()";
        $kiw_data['push_key']      = base64_encode(json_encode($kiw_subscribe_data));
        $kiw_data['push_hash']     = $kiw_subscription_hash;

        $kiw_db->update("kiwire_push_subscription", $kiw_data, "mac_address = '{$_SESSION['user']['mac']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}'");


    }


}


