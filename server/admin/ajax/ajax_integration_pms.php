<?php

$kiw['module'] = "Integration -> PMS";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_general.php";
require_once "../includes/include_connection.php";

if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}

$kiw_action = $_REQUEST['action'];


if ($kiw_action == "update_pms") {


    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $kiw_data['enabled']        = $kiw_db->escape($_REQUEST['enabled']);
        $kiw_data['pms_type']       = $kiw_db->escape($_REQUEST['pms_type']);
        $kiw_data['pms_host']       = $kiw_db->escape($_REQUEST['pms_host']);

        $kiw_data['pms_port']       = $kiw_db->escape($_REQUEST['pms_port']);
        $kiw_data['pms_project']    = $kiw_db->escape($_REQUEST['pms_project']);
        $kiw_data['pms_token']      = $kiw_db->escape($_REQUEST['pms_token']);
        $kiw_data['vip_match']      = $kiw_db->escape($_REQUEST['vip_match']);
        $kiw_data['updated_date']   = "NOW()";

        $kiw_data['pass_mode']             = $kiw_db->escape($_REQUEST['pass_mode']); 
        $kiw_data['use_first_login_only']  = (isset($_POST['use_first_login']) ? "y" : "n");
        $kiw_data['pass_predefined']       = $kiw_db->escape($_REQUEST['pass_predefined']);
        $kiw_data['pass_percentage']       = $kiw_db->escape($_REQUEST['pass_percentage']);
        $kiw_data['zone_allowed']          = $kiw_db->escape($_REQUEST['allowed_zone']);
        $kiw_data['credential_string']     = $kiw_db->escape($_REQUEST['credential_string']);


        if($kiw_data['pass_mode'] == "0" || $kiw_data['pass_mode'] == "1" || $kiw_data['pass_mode'] == "5") {

            $kiw_data['use_first_login_only'] = "n";

        }

        
        $kiw_data['is_24_hour']     = isset($_REQUEST['is_24']) && $_REQUEST['is_24'] ? 1 : 0;
        $kiw_data['start_time']     = $kiw_db->escape($_REQUEST['start_time']);

        if(!$kiw_data['is_24_hour']){
            
            if($_REQUEST['start_time'] == $_REQUEST['stop_time'])
                die(json_encode(array("status" => "failed", "message" => "ERROR: Stop Time cannot same as Start Time", "data" => null)));
            else
                $kiw_data['stop_time'] =   $kiw_db->escape($_REQUEST['stop_time']);

        }

        if($kiw_db->update("kiwire_int_pms", $kiw_data, "tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1")){

            echo json_encode(array("status" => "success", "message" => "Success: PMS Setting has been saved.", "data" => null));
        
        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }



    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


} elseif ($kiw_action == "get_vip_list") {


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_data = $kiw_db->fetch_array("SELECT * FROM kiwire_int_pms_vipcode WHERE tenant_id = '{$_SESSION['tenant_id']}'");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_data));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


} elseif ($kiw_action == "delete_vip_list") {


    $kiw_reference = $_REQUEST['reference'];

    if (!empty($kiw_reference)){


        if (in_array($_SESSION['permission'], array("w", "rw"))) {

            csrf($kiw_db->escape($_REQUEST['token']));

            $kiw_db->query("DELETE FROM kiwire_int_pms_vipcode WHERE tenant_id = '{$_SESSION['tenant_id']}' AND id = '{$kiw_reference}' LIMIT 1");

            echo json_encode(array("status" => "success", "message" => "SUCCESS: VIP Code has been deleted", "data" => $kiw_data));


        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

        }


    }


} elseif ($kiw_action == "update_data_vip") {


    $kiw_reference = $kiw_db->escape($_REQUEST['reference']);

    if (!empty($kiw_reference)){


        if (in_array($_SESSION['permission'], array("r", "rw"))) {

            $kiw_data = $kiw_db->query_first("SELECT * FROM kiwire_int_pms_vipcode WHERE tenant_id = '{$_SESSION['tenant_id']}' AND id = '{$kiw_reference}' LIMIT 1");

            echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_data));


        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

        }


    }


} elseif ($kiw_action == "save_data_vip") {


    $kiw_reference = $kiw_db->escape($_REQUEST['reference']);

    if (!empty($kiw_reference)){


        if (in_array($_SESSION['permission'], array("w", "rw"))) {
            csrf($kiw_db->escape($_REQUEST['token']));

            $kiw_data['updated_date'] = "NOW()";
            $kiw_data['code']         = $kiw_db->escape($_REQUEST['code']);
            $kiw_data['profile']      = $kiw_db->escape($_REQUEST['profile']);
            $kiw_data['price']        = $kiw_db->escape($_REQUEST['price']);

    
            if($kiw_db->update("kiwire_int_pms_vipcode", $kiw_data, "id = '{$kiw_reference}' AND tenant_id = '{$_SESSION['tenant_id']}'")){

                echo json_encode(array("status" => "success", "message" => "SUCCESS: VIP code has been updated", "data" => null));
            }
            else {

                echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));
    
            }
    


        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

        }


    }


} elseif ($kiw_action == "create_data_vip") {


    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $kiw_data['id']             = "NULL";
        $kiw_data['updated_date']   = "NOW()";
        $kiw_data['tenant_id']      = $_SESSION['tenant_id'];
        $kiw_data['code']           = $kiw_db->escape($_REQUEST['code']);
        $kiw_data['price']          = $kiw_db->escape($_REQUEST['price']);
        $kiw_data['profile']        = $kiw_db->escape($_REQUEST['profile']);

        if($kiw_db->insert("kiwire_int_pms_vipcode", $kiw_data)){

            echo json_encode(array("status" => "success", "message" => "SUCCESS: VIP Code [ {$kiw_data['code']} ] has been created.", "data" => $kiw_data));

        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }
        

    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


} elseif ($kiw_action == "dbswap") {


    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        if ($kiw_cache->exists("PMS_ACTION:{$_SESSION['tenant_id']}") == false) {


            $kiw_cache->set("PMS_ACTION:{$_SESSION['tenant_id']}", "sync");

            echo json_encode(array("status" => "success", "message" => "SUCCESS: Database synchronization has been scheduled. It may take up to 30 seconds to start.", "data" => $kiw_data));


        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: There is pending action.", "data" => null));

        }


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


} elseif ($kiw_action == "shutdown") {


    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        if ($kiw_cache->exists("PMS_ACTION:{$_SESSION['tenant_id']}") == false) {


            $kiw_cache->set("PMS_ACTION:{$_SESSION['tenant_id']}", "shutdown");

            echo json_encode(array("status" => "success", "message" => "SUCCESS: Link to PMS has been schedule to shutdown. It may take up to 60 seconds to complete.", "data" => $kiw_data));


        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: There is pending action.", "data" => null));

        }


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}