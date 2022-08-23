<?php

$kiw['module'] = "Device -> Monitoring -> MIB";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_general.php";
require_once "../includes/include_connection.php";
require_once "../../libs/class.sql.helper.php";

if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}


$action = $_REQUEST['action'];

switch ($action) {

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

        $mib_name = $kiw_db->sanitize($_REQUEST['mib_name']);

        $kiw_temp = "SELECT COUNT(`mib_name`) AS `ccount` FROM `kiwire_nms_mib` WHERE `mib_name` = '{$mib_name}' AND `tenant_id` = '{$tenant_id}' LIMIT 1";
        $row = $kiw_db->query_first($kiw_temp);

        if ($row['ccount'] < 1) {

            $data['tenant_id']      = $tenant_id;
            $data['mib_name']       = $mib_name;
            $data['description']    = $kiw_db->sanitize($_REQUEST['description']);
            $data['system_name']    = $kiw_db->escape($_REQUEST['system_name']);

            $data['cpu_load']       = $kiw_db->escape($_REQUEST['cpu_load']);
            $data['memory_used']    = $kiw_db->escape($_REQUEST['memory_used']);
            $data['disk_used']      = $kiw_db->escape($_REQUEST['disk_used']);
            $data['input_vol']      = $kiw_db->escape($_REQUEST['input_vol']);

            $data['output_vol']     = $kiw_db->escape($_REQUEST['output_vol']);
            $data['uptime']         = $kiw_db->escape($_REQUEST['uptime']);
            $data['if_total']       = $kiw_db->escape($_REQUEST['if_total']);
            $data['if_status']      = $kiw_db->escape($_REQUEST['if_status']);

            $data['if_desc']        = $kiw_db->escape($_REQUEST['if_desc']);
            $data['dev_loc']        = $kiw_db->escape($_REQUEST['dev_loc']);
            $data['device_count']   = $kiw_db->escape($_REQUEST['device_count']);
            $data['memory_total']   = $kiw_db->escape($_REQUEST['memory_total']);

            $data['if_speed']       = $kiw_db->escape($_REQUEST['if_speed']);
            $data['disk_total']     = $kiw_db->escape($_REQUEST['disk_total']);
            $data['updated_date']   = "NOW()";

            if($kiw_db->insert("kiwire_nms_mib", $data)){

                sync_logger("{$_SESSION['user_name']} create MIB {$mib_name}", $_SESSION['tenant_id']);

                echo json_encode(array("status" => "success", "message" => "SUCCESS: New MIB [{$mib_name}]  added", "data" => null));

            }
            else {

                echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));
    
            }

        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: MIB Name already existed", "data" => null));

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


            $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_nms_mib WHERE id = '{$id}' AND tenant_id = '{$tenant_id}'");

            $kiw_db->query("DELETE FROM kiwire_nms_mib WHERE id = '{$id}' AND tenant_id = '{$tenant_id}'");


        }


        sync_logger("{$_SESSION['user_name']} deleted MIB {$kiw_temp['mib_name']}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: MIB [{$kiw_temp['mib_name']}] has been deleted", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}


function get_data()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->fetch_array("SELECT  * FROM kiwire_nms_mib  WHERE tenant_id = '{$tenant_id}' LIMIT 1000");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }

}


function get_single_data()
{

    global $kiw_db, $tenant_id;

    $id = $kiw_db->escape($_REQUEST['id']);

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_nms_mib WHERE tenant_id = '$tenant_id' AND id = '{$id}' LIMIT 1");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }

}


function edit_single_data()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));
        
        $id = $kiw_db->escape($_REQUEST['reference']);

        $kiw_data['mib_name']       = $kiw_db->sanitize($_REQUEST['mib_name']);
        $kiw_data['description']    = $kiw_db->sanitize($_REQUEST['description']);
        $kiw_data['updated_date']   = "NOW()";
        $kiw_data['system_name']    = $kiw_db->escape($_REQUEST['system_name']);

        $kiw_data['cpu_load']       = $kiw_db->escape($_REQUEST['cpu_load']);
        $kiw_data['memory_used']    = $kiw_db->escape($_REQUEST['memory_used']);
        $kiw_data['disk_used']      = $kiw_db->escape($_REQUEST['disk_used']);
        $kiw_data['input_vol']      = $kiw_db->escape($_REQUEST['input_vol']);

        $kiw_data['output_vol']     = $kiw_db->escape($_REQUEST['output_vol']);
        $kiw_data['uptime']         = $kiw_db->escape($_REQUEST['uptime']);
        $kiw_data['if_total']       = $kiw_db->escape($_REQUEST['if_total']);
        $kiw_data['if_status']      = $kiw_db->escape($_REQUEST['if_status']);

        $kiw_data['if_desc']        = $kiw_db->escape($_REQUEST['if_desc']);
        $kiw_data['disk_total']     = $kiw_db->escape($_REQUEST['disk_total']);
        $kiw_data['memory_total']   = $kiw_db->escape($_REQUEST['memory_total']);
        $kiw_data['dev_loc']        = $kiw_db->escape($_REQUEST['dev_loc']);

        $kiw_data['if_speed']       = $kiw_db->escape($_REQUEST['if_speed']);
        $kiw_data['device_count']   = $kiw_db->escape($_REQUEST['device_count']);


        $kiw_db->query(sql_update($kiw_db, "kiwire_nms_mib", $kiw_data, "id = {$id} AND tenant_id = '{$_SESSION['tenant_id']}'"));

        sync_logger("{$_SESSION['user_name']} updated MIB {$kiw_data['mib_name']}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: MIB {$kiw_data['mib_name']} has been updated", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}
