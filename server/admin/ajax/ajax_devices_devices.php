<?php

$kiw['module'] = "Device -> Device";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_general.php";
require_once "../includes/include_connection.php";

require_once "../../libs/class.sql.helper.php";

require_once "../../user/includes/include_radius.php";


if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}


$action = $_REQUEST['action'];


// check the tenant id to be used

if ($_SESSION['access_level'] == "superuser") {

    if (!empty($_SESSION['tenant_allowed'])){

        $kiw_tenants = explode(",", $_SESSION['tenant_allowed']);

        if (in_array($_REQUEST['tenant_id'], $kiw_tenants)){

            $kiw_tenant_id = $kiw_db->escape($_REQUEST['tenant_id']);

        } else $kiw_tenant_id = $_SESSION['tenant_id'];

    } else {


        if (isset($_REQUEST['tenant_id']) && !empty($_REQUEST['tenant_id'])){


            $kiw_tenant_id = $kiw_db->escape($_REQUEST['tenant_id']);


        } else $kiw_tenant_id = $_SESSION['tenant_id'];


    }

} else $kiw_tenant_id = $_SESSION['tenant_id'];


switch ($action) {

    case "create": create(); break;
    case "delete": delete(); break;
    case "change_tenant": change_tenant(); break;
    case "get_all": get_data(); break;
    case "get_update": get_single_data(); break;
    case "edit_single_data": edit_single_data(); break;
    case "import_account": import_account($kiw_db); break;
    default: echo "ERROR: Wrong implementation";

}


function create()
{

    global $kiw_db, $kiw_tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {
        csrf($kiw_db->escape($_REQUEST['token']));

        $data['device_type']    = $kiw_db->escape($_REQUEST['device_type']);
        $data['unique_id']      = $kiw_db->escape($_REQUEST['unique_id']);
        $data['device_ip']      = $kiw_db->escape($_REQUEST['device_ip']);
        $data['vendor']         = $kiw_db->escape($_REQUEST['vendor']);


        if ($data['unique_id'] != "Ruckus_Controller") {

            $kiw_test = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_controller WHERE unique_id = '{$data['unique_id']}'");

        } else $kiw_test = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_controller WHERE unique_id = '{$data['unique_id']}' AND tenant_id = '{$kiw_tenant_id}'");


        if ($kiw_test['kcount'] > 0){

            die(json_encode(array("status" => "error", "message" => "ERROR: Identity [ {$data['unique_id']} ] already registered", "data" => null)));

        }


        // check license if still valid


        if ($data['device_type'] == "controller") {


            $kiw_license = @file_get_contents(dirname(__FILE__, 3) . "/custom/{$kiw_tenant_id}/tenant.license");


            if (!empty($kiw_license)) {


                $kiw_license = sync_license_decode($kiw_license);

                if (is_array($kiw_license)) {


                    $kiw_count = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_controller WHERE tenant_id = '{$kiw_tenant_id}' AND device_type = 'controller'");


                    if ($kiw_count['kcount'] >= $kiw_license['device_limit']) {

                        die(json_encode(array("status" => "failed", "message" => "ERROR: Reached maximum number of device allowed", "data" => "")));

                    }


                } else die(json_encode(array("status" => "failed", "message" => "ERROR: Invalid license has been provided", "data" => "")));


            } else {


                $kiw_license = @file_get_contents(dirname(__FILE__, 3) . "/custom/cloud.license");


                if (!empty($kiw_license)) {


                    $kiw_license = sync_license_decode($kiw_license);


                    if (is_array($kiw_license)) {


                        $kiw_count = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_controller WHERE device_type = 'controller'");


                        if ($kiw_count['kcount'] >= $kiw_license['device_limit']) {

                            die(json_encode(array("status" => "failed", "message" => "ERROR: Reached maximum number of device allowed", "data" => "")));

                        }


                    } else die(json_encode(array("status" => "failed", "message" => "ERROR: Invalid license has been provided", "data" => "")));


                } else {


                    // check for temporary license

                    $kiw_license = @file_get_contents(dirname(__FILE__, 3) . "/custom/cloud.data");

                    $kiw_license = sync_brand_decrypt($kiw_license);


                    if ((time() - $kiw_license) >= (86400 * sync_brand_decrypt(SYNC_MAX_TRIAL_DAYS))) {

                        die(json_encode(array("status" => "failed", "message" => "ERROR: Trial license already expired", "data" => "")));

                    }


                    $kiw_count = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_controller WHERE device_type = 'controller'");

                    if (sync_brand_decrypt(SYNC_MAX_TRIAL_DEVICES) >= $kiw_count['kcount']) {

                        die(json_encode(array("status" => "failed", "message" => "ERROR: Reached maximum number of device for trial license", "data" => "")));

                    }


                }


            }


        }


        if (strlen($data['unique_id']) > 4){


            if (empty($data['unique_id'])) {

                die(json_encode(array("status" => "failed", "message" => "ERROR: Please provide an identity", "data" => null)));

            }

            if (empty($data['device_ip'])) {

                die(json_encode(array("status" => "failed", "message" => "ERROR: Please provide the device IP address", "data" => null)));

            }


            if (empty($data['vendor'])) {

                die(json_encode(array("status" => "failed", "message" => "ERROR: Please provide the device vendor.", "data" => null)));

            }

            $data['location']       = $kiw_db->sanitize($_REQUEST['location']);
            $data['username']       = $kiw_db->sanitize($_REQUEST['username']);
            $data['password']       = $kiw_db->escape($_REQUEST['password']);
            $data['shared_secret']  = $kiw_db->escape($_REQUEST['shared_secret']);

            $data['coa_port']       = $kiw_db->escape($_REQUEST['coa_port']);
            $data['vendor']         = $kiw_db->escape($_REQUEST['vendor']);
            $data['description']    = $kiw_db->sanitize($_REQUEST['description']);
            $data['seamless_type']  = $kiw_db->escape($_REQUEST['seamless_type']);

            $data['community']      = $kiw_db->escape($_REQUEST['community']);
            $data['monitor_method'] = $kiw_db->escape($_REQUEST['monitor_method']);
            $data['snmpv']          = $kiw_db->escape($_REQUEST['snmpv']);
            $data['mib']            = $kiw_db->escape($_REQUEST['mib']);
           
            // $data['is_virtual']     = isset($_REQUEST['is_virtual']) && $_REQUEST['is_virtual'] ? 1 : 0;
            // $data['is_24_hour']     = isset($_REQUEST['enabled']) && $_REQUEST['enabled'] ? 1 : 0;
            // $data['start_time']     = $kiw_db->escape($_REQUEST['start_time']);

            // if(!$data['is_24_hour']){

            //     if($_REQUEST['start_time'] == $_REQUEST['stop_time'])
            //         die(json_encode(array("status" => "failed", "message" => "ERROR: Stop Time cannot same as Start Time", "data" => null)));
            //     else
            //         $data['stop_time'] =   $kiw_db->escape($_REQUEST['stop_time']);
            // }

                
                
            $data['tenant_id']      = $kiw_tenant_id;

            if($results = $kiw_db->insert("kiwire_controller", $data)) {

                
                sync_logger("{$_SESSION['user_name']} create device {$_REQUEST['unique_id']}", $kiw_tenant_id);
                
                die(json_encode(array("status" => "success", "message" => "SUCCESS: New device " . $_REQUEST['unique_id'] . "  added", "data" => null)));
            }

            die(json_encode(array("status" => "failed", "message" => "ERROR: Please check you input data.", "data" => $results)));


        } else {


            echo json_encode(array("status" => "failed", "message" => "ERROR: Identity need to be at least 5 characters", "data" => null));


        }

    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}


function delete()
{

    global $kiw_db, $kiw_tenant_id;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {
        csrf($kiw_db->escape($_REQUEST['token']));

        $id = $kiw_db->escape($_POST['id']);

        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_controller WHERE id = '{$id}' AND tenant_id = '{$kiw_tenant_id}'");


        if (!empty($kiw_temp)) {


            $kiw_db->query("DELETE FROM kiwire_controller WHERE id = '{$id}' AND tenant_id = '{$kiw_tenant_id}' LIMIT 1");

            sync_logger("{$_SESSION['user_name']} deleted device {$kiw_temp['unique_id']}", $kiw_tenant_id);

            echo json_encode(array("status" => "success", "message" => "SUCCESS: Device [ {$kiw_temp['unique_id']} ] has been deleted", "data" => null));


        } else {

            echo json_encode(array("status" => "error", "message" => "Error: Device not found or has been deleted", "data" => null));

        }


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}


function get_data()
{

    global $kiw_db;


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


    $kiw_where = "WHERE tenant_id = '{$_SESSION['tenant_id']}'";


    ############ Previous Code ##############################

        // if ($_SESSION['access_level'] == "superuser"){


        //     if (!empty($_SESSION['tenant_allowed'])){


        //         $kiw_where = explode(",", $_SESSION['tenant_allowed']);

        //         $kiw_where = "WHERE tenant_id IN ('" . implode("','", $kiw_where) . "')";


        //     } else $kiw_where = "";


        // } else $kiw_where = "WHERE tenant_id = '{$_SESSION['tenant_id']}'";

    ##############################################################


        $kiw_temp = $kiw_db->fetch_array("SELECT * FROM kiwire_controller {$kiw_where}");


        if (is_array($kiw_temp)) {

            for ($i = 0; $i < count($kiw_temp); $i++) {

                $kiw_temp[$i]['device_type'] = ucfirst($kiw_temp[$i]['device_type']);
                $kiw_temp[$i]['vendor'] = ucfirst($kiw_temp[$i]['vendor']);

                $kiw_temp[$i]['location'] = empty($kiw_temp[$i]['location']) ? "" : $kiw_temp[$i]['location'];


            }

        }


        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }


}


function get_single_data()
{

    global $kiw_db, $kiw_tenant_id;

    $id = $kiw_db->escape($_REQUEST['id']);


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_controller WHERE tenant_id = '{$kiw_tenant_id}' AND id = '{$id}' LIMIT 1");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }


}


function edit_single_data()
{

    global $kiw_db, $kiw_tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {
        csrf($kiw_db->escape($_REQUEST['token']));
        
        $id = $kiw_db->escape($_REQUEST['reference']);

        $data['device_type']    = $kiw_db->escape($_REQUEST['device_type']);
        $data['vendor']         = $kiw_db->escape($_REQUEST['vendor']);
        $data['unique_id']      = $kiw_db->escape($_REQUEST['unique_id']);
        $data['device_ip']      = $kiw_db->escape($_REQUEST['device_ip']);

        if (empty($data['unique_id'])) {

            die(json_encode(array("status" => "failed", "message" => "ERROR: Please provide an identity", "data" => null)));

        }

        if (empty($data['device_ip'])) {

            die(json_encode(array("status" => "failed", "message" => "ERROR: Please provide the device IP address", "data" => null)));

        }

        if (empty($data['vendor'])) {

            die(json_encode(array("status" => "failed", "message" => "ERROR: Please provide the device vendor.", "data" => null)));

        }

        $data['location']       = $kiw_db->sanitize($_REQUEST['location']);
        $data['username']       = $kiw_db->sanitize($_REQUEST['username']);
        $data['password']       = $kiw_db->escape($_REQUEST['password']);
        $data['shared_secret']  = $kiw_db->escape($_REQUEST['shared_secret']);

        $data['coa_port']       = $kiw_db->escape($_REQUEST['coa_port']);
        $data['vendor']         = $kiw_db->escape($_REQUEST['vendor']);
        $data['description']    = $kiw_db->sanitize($_REQUEST['description']);
        $data['seamless_type']  = $kiw_db->escape($_REQUEST['seamless_type']);

        $data['community']      = $kiw_db->escape($_REQUEST['community']);
        $data['snmpv']          = $kiw_db->escape($_REQUEST['snmpv']);
        $data['mib']            = $kiw_db->escape($_REQUEST['mib']);
        $data['monitor_method'] = $kiw_db->escape($_REQUEST['monitor_method']);

        // $data['is_24_hour']     = isset($_REQUEST['enabled']) && $_REQUEST['enabled'] ? 1 : 0;
        // $data['is_virtual']     = isset($_REQUEST['is_virtual']) && $_REQUEST['is_virtual'] ? 1 : 0;
        // $data['start_time']     = $kiw_db->escape($_REQUEST['start_time']);

        // if(!$data['is_24_hour']){

        //     if($_REQUEST['start_time'] == $_REQUEST['stop_time'])
        //         die(json_encode(array("status" => "failed", "message" => "ERROR: Stop Time cannot same as Start Time", "data" => null)));
        //     else
        //         $data['stop_time'] =   $kiw_db->escape($_REQUEST['stop_time']);
        // }
        


        if($kiw_db->update("kiwire_controller", $data, "id = '{$id}' AND tenant_id = '{$kiw_tenant_id}' LIMIT 1")){
   
            sync_logger("{$_SESSION['user_name']} updated device {$data['unique_id']}", $kiw_tenant_id);
            
            echo json_encode(array("status" => "success", "message" => "SUCCESS: Device {$data['unique_id']} has been updated", "data" => null));

        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}


function change_tenant(){


    global $kiw_db, $kiw_cache;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        $kiw_tenant_original = $kiw_db->escape($_REQUEST['tenant']);
        $kiw_tenant_new = $kiw_db->escape($_REQUEST['tenant_new']);
        $kiw_controller = $kiw_db->escape($_REQUEST['id']);


        if (!empty($kiw_tenant_original) && !empty($kiw_tenant_new) && !empty($kiw_controller)){


            $kiw_controller = $kiw_db->query_first("SELECT * FROM kiwire_controller WHERE id = '{$kiw_controller}' AND tenant_id = '{$kiw_tenant_original}' LIMIT 1");

            $kiw_stales = $kiw_db->fetch_array("SELECT * FROM kiwire_active_session WHERE controller = '{$kiw_controller['unique_id']}' AND tenant_id = '{$kiw_controller['tenant_id']}'");


            foreach ($kiw_stales as $kiw_stale) {


                if ($kiw_controller['vendor'] == "wifidog") {


                    $kiw_user['action']         = "accounting";
                    $kiw_user['nasid']          = $kiw_stale['controller'];
                    $kiw_user['username']       = $kiw_stale['username'];
                    $kiw_user['macaddress']     = $kiw_stale['macaddress'];
                    $kiw_user['unique_id']      = $kiw_stale['unique_id'];
                    $kiw_user['station_id']     = $kiw_stale['mac_address'];
                    $kiw_user['ipaddress']      = $kiw_stale['ip_address'];
                    $kiw_user['controller_ip']  = $kiw_stale['controller_ip'];
                    $kiw_user['session_id']     = $kiw_stale['session_id'];
                    $kiw_user['session_time']   = time() - strtotime($kiw_stale['start_time']);
                    $kiw_user['quota_in']       = $kiw_stale['quota_in'];
                    $kiw_user['quota_out']      = $kiw_stale['quota_out'];
                    $kiw_user['event-time']     = date("Y-m-d H:i:s");
                    $kiw_user['type']           = "Stop";
                    $kiw_user['terminate']      = "Stale-Session";
                    $kiw_user['quota_in_gw']    = 0;
                    $kiw_user['quota_out_gw']   = 0;

                    $kiw_auth = curl_init();

                    curl_setopt($kiw_auth, CURLOPT_URL, "http://127.0.0.1:9955");
                    curl_setopt($kiw_auth, CURLOPT_POST, true);
                    curl_setopt($kiw_auth, CURLOPT_POSTFIELDS, http_build_query($kiw_user));
                    curl_setopt($kiw_auth, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($kiw_auth, CURLOPT_TIMEOUT, 5);
                    curl_setopt($kiw_auth, CURLOPT_CONNECTTIMEOUT, 5);

                    curl_exec($kiw_auth);
                    curl_close($kiw_auth);

                    unset($kiw_auth);
                    unset($kiw_user);


                    $kiw_cache->set("WD:DC:{$kiw_stale['mac_address']}", array("disconnected" => true), 600);


                } else {

                    disconnect_device($kiw_db, $kiw_cache, $kiw_stale['tenant_id'], $kiw_stale['mac_address']);

                }


            }

            unset($kiw_stale);
            unset($kiw_stales);


            $kiw_db->query("UPDATE kiwire_controller SET updated_date = NOW(), tenant_id = '{$kiw_tenant_new}' WHERE tenant_id = '{$kiw_controller['tenant_id']}' AND id = '{$kiw_controller['id']}' LIMIT 1");

            echo json_encode(array("status" => "success", "message" => "SUCCESS: Device {$kiw_controller['unique_id']} has been moved to tenant [ {$kiw_tenant_new} ]", "data" => null));


        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Missing information to update database", "data" => null));

        }


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}



function import_account($kiw_db){

    global  $kiw_tenant_id;

    $kiw_file = $_FILES['accounts_file'];

    $ext = end((explode(".", $kiw_file['name'])));

    if($ext == 'csv'){

        if ($kiw_file['size'] > 0){
    
            $kiw_this_session_path = dirname(__FILE__, 3) . "/temp/import_device_contoller_status_" . substr(md5(time()), 0, 4) . ".csv";
    
    
            // get the account setting
    
            $kiw_config['device_type']   = $kiw_db->escape($_REQUEST['device_type']);
            $kiw_config['vendor']       = $kiw_db->escape($_REQUEST['vendor']);
    
            $kiw_file_reader = fopen($kiw_file['tmp_name'], "r");
    
            file_put_contents($kiw_this_session_path, implode(",", ["NasID", "Status", "Reason"]) . "\n", FILE_APPEND);
    
            // echo json_encode(array("status" => "success", "message" => "SUCCESS: ", "data" => fgetcsv($kiw_file_reader)));
    
            while (!feof($kiw_file_reader)){
    
                $kiw_device = fgetcsv($kiw_file_reader);
    
                if (!empty($kiw_device)){
                    
                    //remove bom utf8
                    if(substr($kiw_device[0],0,3)==chr(hexdec('EF')).chr(hexdec('BB')).chr(hexdec('BF'))){
                        $kiw_device[0] = substr($kiw_device[0],3);
                    }                    
                    
                    // if (count($kiw_device) == 8){
    
                        if ($kiw_device[0] != "Ruckus_Controller") {

                            $kiw_test = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_controller WHERE unique_id = '{$kiw_device[0]}'");
                
                        } else $kiw_test = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_controller WHERE unique_id = '{$kiw_device[0]}' AND tenant_id = '{$kiw_tenant_id}'");
                
                
                        if ($kiw_test['kcount'] > 0){
                
                            file_put_contents($kiw_this_session_path, implode(",", [$kiw_device[0], "Failed", "ERROR: Identity already registered"]) . "\n", FILE_APPEND);
                
                            continue;
                        }


                        $kiw_existed = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_controller WHERE unique_id = '{$kiw_device[0]}' AND tenant_id = '{$kiw_tenant_id}'");
    
                        if ($kiw_existed['kcount'] == 0){
    
    
                            $data = array();
    
                            $data['tenant_id']      = $kiw_tenant_id;

                            $data['unique_id']      = $kiw_device[0];
                            $data['device_ip']      = $kiw_device[1];
                            $data['shared_secret']  = $kiw_device[2];
                            $data['coa_port']       = $kiw_device[3];
                            $data['username']       = $kiw_device[4];
                            $data['password']       = $kiw_device[5];
                            $data['location']       = $kiw_device[6];
                            $data['description']    = isset($kiw_device[7]) ? $kiw_device[7] : '';

                            $data['device_type']    = $kiw_config['device_type'];
                            $data['vendor']         = $kiw_config['vendor'];
                            $data['snmpv']          = 1;
                            $data['seamless_type']  = 'disabled';

                            //insert
                            if($kiw_db->insert("kiwire_controller", $data)) {

                
                                sync_logger("{$_SESSION['user_name']} create device {$data['unique_id']}", $kiw_tenant_id);

                                file_put_contents($kiw_this_session_path, implode(",", [$kiw_device[0], "Succeed", ""]) . "\n", FILE_APPEND);
                            }else{

                                file_put_contents($kiw_this_session_path, implode(",", [$kiw_device[0], "Failed", "failed"]) . "\n", FILE_APPEND);
                            }
                
    
                        } else {
    
    
                            file_put_contents($kiw_this_session_path, implode(",", [$kiw_device[0], "Failed", "Duplicate"]) . "\n", FILE_APPEND);
    
    
                        }
    
    
                    // } else {
    
    
                    //     file_put_contents($kiw_this_session_path, implode(",", [$kiw_device[0], "Failed", "Invalid"]) . "\n", FILE_APPEND);
    
    
                    // }
    
    
                }
    
    
            }
    
    
            echo json_encode(array("status" => "success", "message" => "SUCCESS: Users has been imported. Please check log file for details.", "data" => basename($kiw_this_session_path)));
    
    
        } else {
    
            echo json_encode(array("status" => "failed", "message" => "ERROR: Missing accounts file.", "data" => null));
            
        }
        
    }
    else echo json_encode(array("status" => "failed", "message" => "ERROR: Invalid file format [ {$ext} ]. Only .csv allowed", "data" => $kiw_file));
    
    
    

}

