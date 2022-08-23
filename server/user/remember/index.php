<?php


require_once dirname(__FILE__, 2) . "/includes/include_session.php";

require_once dirname(__FILE__, 3) . "/admin/includes/include_connection.php";
require_once dirname(__FILE__, 3) . "/admin/includes/include_general.php";


if ($_SESSION['system']['checked'] == "true") {


    header("Content-Type: application/json");


    $kiw_credential = $_COOKIE['smart-wifi-login'];

    $kiw_credential = base64_decode(sync_decrypt($kiw_credential));


    if ($kiw_credential){


        $kiw_policies = $kiw_cache->get("POLICIES_DATA:{$_SESSION['controller']['tenant_id']}");

        if (empty($kiw_policies)) {


            $kiw_policies = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_policies WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

            if (empty($kiw_policies)) $kiw_policies = array("dummy" => true);

            $kiw_cache->set("POLICIES_DATA:{$_SESSION['controller']['tenant_id']}", $kiw_policies, 1800);


        }


        if ($kiw_policies['remember_me'] == "y") {


            $kiw_credential = explode("||", $kiw_credential);


            if (is_array($kiw_credential) && !empty($kiw_credential)) {


                echo json_encode(array("status" => "success", "message" => "", "data" => array("username" => trim($kiw_credential[0]), "password" => trim($kiw_credential[1]))));


            } else json_encode(array("status" => "error", "message" => "", "data" => ""));


        } else json_encode(array("status" => "error", "message" => "", "data" => ""));


    } else json_encode(array("status" => "error", "message" => "", "data" => ""));


}