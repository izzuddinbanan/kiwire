<?php

require_once dirname(__FILE__, 2) . "/includes/include_redirect_from_login.php";
require_once dirname(__FILE__, 2) . "/includes/include_session.php";
require_once dirname(__FILE__, 2) . "/includes/include_general.php";

require_once dirname(__FILE__, 3) . "/admin/includes/include_general.php";
require_once dirname(__FILE__, 3) . "/admin/includes/include_connection.php";
require_once dirname(__FILE__, 3) . "/libs/phpqrcode/phpqrcode.php";


header("Content-Type: application/json");


if ($_REQUEST['action'] == "request") {


    $kiw_random = md5(time() . rand(1000, 9999));

    $kiw_filename = dirname(__FILE__, 3) . "/temp/{$kiw_random}.png";


    $kiw_secret = json_encode(array(
        "mac_address" => $_SESSION['user']['mac'],
        "ip_address" => $_SESSION['user']['ip'],
        "device_type" => $_SESSION['user']['name'],
        "device_brand" => $_SESSION['user']['brand'],
        "random_string" => $kiw_random,
        "zone" => $_SESSION['user']['zone']
    ));


    $kiw_secret = base64_encode(sync_encrypt($kiw_secret));

    $kiw_secret = $_SESSION['system']['domain'] . "/admin/agents/verification/?data=" . $kiw_secret;


    QRcode::png($kiw_secret, $kiw_filename);

    $kiw_filename = substr($kiw_filename, strpos($kiw_filename, "/temp/"));


    echo json_encode(array("status" => "success", "message" => "", "data" => array("path" => $kiw_filename, "unique-id" => $kiw_random)));


} elseif ($_REQUEST['action'] == "check"){


    $kiw_auth = $kiw_cache->get("QR_LOGIN_AUTH:{$_SESSION['controller']['tenant_id']}:{$_REQUEST['random']}");

    if (is_array($kiw_auth)){


        echo json_encode(array("status" => "success", "message" => "", "data" => $kiw_auth));

        $kiw_cache->del("QR_LOGIN_AUTH:{$_SESSION['controller']['tenant_id']}:{$_REQUEST['random']}");


    } else echo json_encode(array("status" => "failed", "message" => null, "data" => null));


}