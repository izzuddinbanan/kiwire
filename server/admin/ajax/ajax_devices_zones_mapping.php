<?php

$kiw['module'] = "Device -> Zone";
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

        // case "update": update(); break;
    case "save_rules":
        save_rules($kiw_db);
        break;
    case "get_rules":
        get_rules($kiw_db);
        break;
    case "delete_rule":
        delete_rule($kiw_db);
        break;
    case "create":
        create();
        break;
    case "delete":
        delete();
        break;
    case "get_all":
        get_data();
        break;
    case "get_update":
        get_single_data();
        break;
    case "edit_single_data":
        edit_single_data();
        break;

    default:
        echo "ERROR: Wrong implementation";
}


function create()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));
        $kiw_row = $kiw_db->query_first("SELECT COUNT(*) AS ccount FROM kiwire_zone WHERE name = '" . $_GET['name'] . "' AND tenant_id = '$tenant_id'");

        if ($kiw_row['ccount'] < 1) {

            $kiw_data['name']               = $kiw_db->sanitize($_REQUEST['name']);
            $kiw_data['auto_login']         = $kiw_db->escape($_REQUEST['auto_login']);
            $kiw_data['simultaneous']       = $kiw_db->escape($_REQUEST['simultaneous']);
            $kiw_data['priority']           = $kiw_db->escape($_REQUEST['priority']);
            $kiw_data['journey']            = $kiw_db->escape($_REQUEST['journey']);
            $kiw_data['force_profile']      = $kiw_db->escape($_REQUEST['force_profile']);
            $kiw_data['force_allowed_zone'] = $kiw_db->escape($_REQUEST['force_zone']);

            if (!empty($kiw_db->escape($_REQUEST['status']))) {
                $kiw_data['status'] = $kiw_db->escape($_REQUEST['status']);
            }

            $kiw_data['tenant_id']       = $tenant_id;
            $kiw_data['updated_date']    = date('Y-m-d H:i:s');


            if ($kiw_db->insert("kiwire_zone", $kiw_data)) {

                sync_logger("{$_SESSION['user_name']} create zone mapping {$_REQUEST['name']}", $_SESSION['tenant_id']);

                echo json_encode(array("status" => "success", "message" => "SUCCESS: New Zone {$_REQUEST['name']} added", "data" => null));
            } else {

                echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));
            }
        } else {

            echo json_encode(array("status" => "Error", "message" => "ERROR: Zone name already exists!", "data" => null));
        }
    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
    }
}



function save_rules($kiw_db)
{

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $kiw_temp['tenant_id']  = $_SESSION['tenant_id'];
        $kiw_temp['master_id']  = $kiw_db->escape($_REQUEST['zone']);
        $kiw_temp['ipaddr']     = $kiw_db->escape($_REQUEST['ipaddress']);
        $kiw_temp['ipv6addr']   = $kiw_db->escape($_REQUEST['ipv6addr']);
        $kiw_temp['vlan']       = $kiw_db->escape($_REQUEST['vlan']);
        $kiw_temp['ssid']       = $kiw_db->escape($_REQUEST['ssid']);
        $kiw_temp['nasid']      = $kiw_db->escape($_REQUEST['controller_id']);
        $kiw_temp['dzone']      = $kiw_db->escape($_REQUEST['controller_zone']);

        $kiw_temp['hash']           = md5(implode(",", $kiw_temp));
        $kiw_temp['updated_date']   = date("Y-m-d H:i:s");

        $kiw_id = $kiw_db->query_first("SELECT * FROM kiwire_zone WHERE name = '" . $kiw_db->escape($_REQUEST['zone']) . "' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");

        if (count($kiw_id)) {


            $kiw_temp['priority'] = $kiw_id['priority'];


            $kiw_db->insert("kiwire_zone_child", $kiw_temp);

            echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp['hash']));
        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
        }
    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
    }
}



function get_rules($kiw_db)
{


    $kiw_zone = $kiw_db->escape($_REQUEST['zone']);


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->fetch_array("SELECT * FROM kiwire_zone_child WHERE tenant_id = '{$_SESSION['tenant_id']}' AND master_id = '{$kiw_zone}'");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));
    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
    }
}


function delete_rule($kiw_db)
{


    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $kiw_temp = $kiw_db->escape($_REQUEST['rules_id']);


        if (strlen($kiw_temp) > 0) {


            $kiw_db->query("DELETE FROM kiwire_zone_child WHERE hash = '{$kiw_temp}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");

            echo json_encode(array("status" => "failed", "message" => "ERROR: Rules has been deleted", "data" => null));
        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Rules has been deleted", "data" => null));
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


            $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_zone WHERE id = '{$id}' AND tenant_id = '{$_SESSION['tenant_id']}'");


            $kiw_db->query("DELETE FROM kiwire_zone WHERE name = '{$kiw_temp['name']}' AND tenant_id = '{$_SESSION['tenant_id']}'");

            $kiw_db->query("DELETE FROM kiwire_zone_child WHERE master_id = '{$kiw_temp['name']}' AND tenant_id = '{$_SESSION['tenant_id']}'");
        }


        sync_logger("{$_SESSION['user_name']} deleted zone mapping {$kiw_temp['name']}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Zone {$kiw_temp['name']} has been deleted", "data" => null));
    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
    }
}


function get_data()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        $kiw_temp = $kiw_db->fetch_array("SELECT  * FROM kiwire_zone  WHERE tenant_id = '{$tenant_id}' LIMIT 1000");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));
    }
}


function get_single_data()
{

    global $kiw_db, $tenant_id;

    $id = $kiw_db->escape($_REQUEST['id']);

    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_zone WHERE tenant_id = '$tenant_id' AND id = '{$id}' LIMIT 1");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));
    }
}


function edit_single_data()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $id = $kiw_db->escape($_REQUEST['reference']);

        $old_zone = $kiw_db->query_first("SELECT name FROM kiwire_zone WHERE id = '{$id}' and tenant_id = '{$tenant_id}' LIMIT 1");
        $old_zone = $old_zone['name'];

        $new_name              = $kiw_db->sanitize($_REQUEST['name']);
        $new_auto_login        = $kiw_db->escape($_REQUEST['auto_login']);
        $new_simultaneous      = $kiw_db->escape($_REQUEST['simultaneous']);
        $new_priority          = $kiw_db->escape($_REQUEST['priority']);
        $new_journey           = $kiw_db->escape($_REQUEST['journey']);
        $new_force_profile     = $kiw_db->escape($_REQUEST['force_profile']);
        $new_force_zone        = $kiw_db->escape($_REQUEST['force_zone']);

        $new_status = 'n';
        if (isset($_REQUEST['status'])) $new_status = "y";
        $new_updated_date      = "NOW()";

        $update_zone = $kiw_db->query("UPDATE kiwire_zone SET name = '{$new_name}', auto_login = '{$new_auto_login}' , simultaneous = '{$new_simultaneous}', priority = '{$new_priority}', status = '{$new_status}', journey = '{$new_journey}', force_profile = '{$new_force_profile}', force_allowed_zone = '{$new_force_zone}', updated_date = NOW() WHERE id = '{$id}' AND tenant_id = '{$tenant_id}'");


        if ($update_zone) {

            sync_logger("{$_SESSION['user_name']} updated zone mapping {$new_name}", $_SESSION['tenant_id']);

            $kiw_db->query("UPDATE kiwire_zone_child
                SET master_id = '{$new_name}'
                WHERE master_id = '{$old_zone}' AND tenant_id = '{$tenant_id}'");


            echo json_encode(array("status" => "success", "message" => "SUCCESS: Zone {$new_name} has been updated", "data" => null));
        } else {

            echo json_encode(array("status" => "failed", "message" => "Error: Please check your input", "data" => null));
        }
    } else 

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
}
