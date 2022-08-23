<?php

$kiw['module'] = "Help -> System Quick Fix";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_connection.php";

if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}

$kiw_db = Database::obtain();


if ($_REQUEST['action'] == "clear_cache"){


    $kiw_lists = $kiw_cache->keys("*:{$_SESSION['tenant_id']}*");

    if (count($kiw_lists) > 0) {

        $kiw_cache->multi();

        foreach ($kiw_lists as $kiw_list) {

            // dont remove cache required for report and wifidog auth

            if (!in_array(substr($kiw_list, 0, 6), array("REPORT", "WD:ACT", "QUERY_", "WD:BL:", "WD:DC:", "WD:SES", "WD:LOG", "WD:POR"))) {

                $kiw_cache->del($kiw_list);

            }

        }

        $kiw_cache->exec();

    }


    $kiw_lists = $kiw_cache->keys("NAS_TENANT:*");

    if (count($kiw_lists) > 0) {

        $kiw_cache->multi();

        foreach ($kiw_lists as $kiw_list) {

            $kiw_cache->del($kiw_list);

        }

        $kiw_cache->exec();

    }


    $kiw_lists = $kiw_cache->keys("CONTROLLER_DATA:*");

    if (count($kiw_lists) > 0) {

        $kiw_cache->multi();

        foreach ($kiw_lists as $kiw_list) {

            $kiw_cache->del($kiw_list);

        }

        $kiw_cache->exec();

    }


    $kiw_lists = $kiw_cache->keys("RUCKUS_DATA:*");

    if (count($kiw_lists) > 0) {

        $kiw_cache->multi();

        foreach ($kiw_lists as $kiw_list) {

            $kiw_cache->del($kiw_list);

        }

        $kiw_cache->exec();

    }


    echo json_encode(array("status" => "success", "message" => "SUCCESS: Server cache has cleared for this tenant.", "data" => null));


} elseif ($_REQUEST['action'] == "set_timezone"){


    echo json_encode(array("status" => "success", "message" => "SUCCESS: This system server has been set.", "data" => null));


} elseif ($_REQUEST['action'] == "reset"){


    $kiw_db->query("DELETE FROM kiwire_admin_group WHERE tenant_id = '{$_SESSION['tenant_id']}' AND groupname = 'operator'");

    $kiw_db->query("INSERT INTO kiwire_admin_group (SELECT NULL, 'operator', moduleid, '{$_SESSION['tenant_id']}', NOW() FROM kiwire_moduleid ORDER BY moduleid ASC)");


    echo json_encode(array("status" => "success", "message" => "SUCCESS: Role operator for this tenant has been reset.", "data" => null));


} elseif ($_REQUEST['action'] == "set_custom_permission"){


    system("chmod 755 -R " . dirname(__FILE__, 2) . "/custom/");

    echo json_encode(array("status" => "success", "message" => "SUCCESS: Permission has been updated.", "data" => null));


}

