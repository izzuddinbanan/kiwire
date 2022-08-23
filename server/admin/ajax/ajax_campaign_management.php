<?php
 
$kiw['module'] = "Campaign -> Campaign Management";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_general.php";
require_once "../includes/include_connection.php";

require_once dirname(__FILE__, 3) . "/libs/class.sql.helper.php";

if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}


$action = $_REQUEST['action'];

switch ($action) {

    case "verify": verify($kiw_db, $_SESSION['tenant_id']); break;
    case "create": create($kiw_db, $_SESSION['tenant_id']); break;
    case "update": update($kiw_db, $_SESSION['tenant_id']); break;
    case "delete": delete(); break;
    case "get_all": get_data(); break;
    case "get_update": get_single_data($kiw_db, $_SESSION['tenant_id']); break;
    case "edit_single_data": edit_single_data(); break;
    default: echo "ERROR: Wrong implementation";
}


function create($kiw_db, $tenant_id)
{


    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));


        $kiw_timezone = $_SESSION['timezone'];

        if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


        $kiw_data = array();

        $kiw_data['tenant_id']      = $tenant_id;
        $kiw_data['updated_date']   = "NOW()";
        $kiw_data['creator']        = $_SESSION['user_name'];
        $kiw_data['name']           = $kiw_db->sanitize($_REQUEST['name']);
        $kiw_data['c_order']        = $_REQUEST['c_order'];
        $kiw_data['remark']         = $kiw_db->sanitize($_REQUEST['remark']);

        $kiw_data['date_start']      = date("Y-m-d H:i:s", strtotime($_REQUEST['date_start']));
        $kiw_data['date_end']        = date("Y-m-d H:i:s", strtotime($_REQUEST['date_end']));

        $kiw_data['expired_click']   = $_REQUEST['expire_click'];
        $kiw_data['expired_impress'] = $_REQUEST['expire_impression'];
        $kiw_data['target']          = $_REQUEST['target'];
        $kiw_data['target_value']    = $_REQUEST['target_value_' . $_REQUEST['target']];
        $kiw_data['target_option']   = $_REQUEST['c_zone'];

        $kiw_data['c_interval']             = $_REQUEST['c_interval'];
        $kiw_data['c_interval_time_start']  = $_REQUEST['shour'];
        $kiw_data['c_interval_time_stop']   = $_REQUEST['thour'];


        // collect the trigger data

        $kiw_data['c_trigger'] = $_REQUEST['c_trigger'];


        if ($kiw_data['c_trigger'] == "dwell") {

            $kiw_data['c_trigger_value'] = $_REQUEST['dwell'];


        } elseif ( in_array($kiw_data['c_trigger'], array("recurring", "milestone"))){

            $kiw_data['c_trigger_value'] = $_REQUEST['recurring'];


        } elseif ($kiw_data['c_trigger'] == "lastvisit"){

            $kiw_data['c_trigger_value'] = $_REQUEST['lastvisit'];


        }


        // check if verification required. if yes then status should be inactive

        $kiw_temp = $kiw_db->query_first("SELECT campaign_require_verification FROM kiwire_clouds WHERE tenant_id = '{$tenant_id}' LIMIT 1");

        if ($kiw_temp['campaign_require_verification'] != "y") {

            $kiw_data['status'] = "active";

        } else {

            $kiw_data['status'] = "inactive";

        }


        // collect data for specific action

        $kiw_data['action'] = $_REQUEST['c_action'];


        if ($kiw_data['action'] == "ads"){


            $kiw_data['action_value'] = $_REQUEST['ads_id'];
            $kiw_data['c_space'] = $_REQUEST['c_space'];


        } elseif ($kiw_data['action'] == "notification"){

            $kiw_data['action_method'] = $_REQUEST['notification_type'];


            if (in_array($kiw_data['action_method'], array("api", "push"))){

                $kiw_data['action_value'] = $_REQUEST['notification_url'];


            } else {

                $kiw_data['action_value'] = $_REQUEST['notification_template'];


            }


        } elseif ($kiw_data['action'] == "redirect"){

            $kiw_data['action_value'] = $_REQUEST['redirection'];


        }


        if($kiw_db->query(sql_insert($kiw_db, "kiwire_campaign_manager", $kiw_data))){
            sync_logger("{$_SESSION['user_name']} create campaign {$kiw_data['name']}", $_SESSION['tenant_id']);
    
            echo json_encode(array("status" => "success", "message" => "SUCCESS: New Campaign :  {$kiw_data['name']}  added", "data" => NULL));
        }
        else{
            echo json_encode(array("status" => "failed", "message" => "ERROR: Something Problem", "data" => null));
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


            $kiw_temp = $kiw_db->query_first("SELECT name FROM kiwire_campaign_manager WHERE id = '{$id}' AND tenant_id = '{$tenant_id}' LIMIT 1");

            $kiw_db->query("DELETE FROM kiwire_campaign_manager WHERE id = '{$id}' AND tenant_id = '{$tenant_id}' LIMIT 1");


            sync_logger("{$_SESSION['user_name']} deleted campaign {$kiw_temp['name']}", $_SESSION['tenant_id']);

            echo json_encode(array("status" => "success", "message" => "SUCCESS: Campaign : {$kiw_temp['name']} has been deleted", "data" => null));


        } else {

            echo json_encode(array("status" => "error", "message" => "ERROR: Campaign has been already been deleted", "data" => null));

        }


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}


function get_data()
{


    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->fetch_array("SELECT * FROM kiwire_campaign_manager WHERE tenant_id = '{$tenant_id}' LIMIT 1000");


        for ($i = 0; $i < count($kiw_temp); $i++) {

            $kiw_temp[$i]['status'] = ucfirst($kiw_temp[$i]['status']);
            $kiw_temp[$i]['target'] = ucfirst($kiw_temp[$i]['target']);
            $kiw_temp[$i]['c_trigger'] = ucfirst($kiw_temp[$i]['c_trigger']);
            $kiw_temp[$i]['c_interval'] = ucfirst($kiw_temp[$i]['c_interval']);
            $kiw_temp[$i]['action'] = ucfirst($kiw_temp[$i]['action']);

            $kiw_temp[$i]['remark'] = substr($kiw_temp[$i]['remark'], 0, 30);

            $kiw_temp[$i]['date_start'] = date("Y-m-d", strtotime($kiw_temp[$i]['date_start']));
            $kiw_temp[$i]['date_end'] = date("Y-m-d", strtotime($kiw_temp[$i]['date_end']));

        }


        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));

    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}


function get_single_data($kiw_db, $tenant_id)
{


    $id = $kiw_db->escape($_REQUEST['id']);


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_timezone = $_SESSION['timezone'];

        if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_campaign_manager WHERE tenant_id = '{$tenant_id}' AND id = '{$id}' LIMIT 1");

        $kiw_temp['date_start'] = sync_tolocaltime($kiw_temp['date_start'], $kiw_timezone);
        $kiw_temp['date_end'] = sync_tolocaltime($kiw_temp['date_end'], $kiw_timezone);

        $kiw_temp['date_start'] = date("m/d/Y", strtotime($kiw_temp['date_start']));
        $kiw_temp['date_end'] = date("m/d/Y", strtotime($kiw_temp['date_end']));

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }


}

function update($kiw_db, $tenant_id){

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $kiw_id = (int)$_REQUEST['id'];

        $kiw_data = array();

        $kiw_data['updated_date']   = "NOW()";
        $kiw_data['name']           = $kiw_db->sanitize($_REQUEST['name']);
        $kiw_data['creator']        = $_SESSION['user_name'];
        $kiw_data['c_order']        = $_REQUEST['c_order'];
        $kiw_data['remark']         = $kiw_db->sanitize($_REQUEST['remark']);

        $kiw_data['date_start']      = date("Y-m-d H:i:s", strtotime($_REQUEST['date_start']));
        $kiw_data['date_end']        = date("Y-m-d H:i:s", strtotime($_REQUEST['date_end']));

        $kiw_data['expired_click']   = $_REQUEST['expire_click'];
        $kiw_data['expired_impress'] = $_REQUEST['expire_impression'];
        $kiw_data['target']          = $_REQUEST['target'];
        $kiw_data['target_value']    = $_REQUEST['target_value_' . $_REQUEST['target']];
        $kiw_data['target_option']   = $_REQUEST['c_zone'];

        $kiw_data['c_interval']             = $_REQUEST['c_interval'];
        $kiw_data['c_interval_time_start']  = $_REQUEST['shour'];
        $kiw_data['c_interval_time_stop']   = $_REQUEST['thour'];


        // collect the trigger data

        $kiw_data['c_trigger'] = $_REQUEST['c_trigger'];

        if ($kiw_data['c_trigger'] == "dwell") {

            $kiw_data['c_trigger_value'] = $_REQUEST['dwell'];


        } elseif ( in_array($kiw_data['c_trigger'], array("recurring", "milestone"))){

            $kiw_data['c_trigger_value'] = $_REQUEST['recurring'];


        } elseif ($kiw_data['c_trigger'] == "lastvisit"){

            $kiw_data['c_trigger_value'] = $_REQUEST['lastvisit'];


        }


        // check if verification required. if yes then status should be inactive

        $kiw_temp = $kiw_db->query_first("SELECT campaign_require_verification FROM kiwire_clouds WHERE tenant_id = '{$tenant_id}' LIMIT 1");

        if ($kiw_temp['campaign_require_verification'] != "y") {

            $kiw_data['status'] = "active";

        } else {

            $kiw_data['status'] = "inactive";

        }


        // collect data for specific action

        $kiw_data['action'] = $_REQUEST['c_action'];


        if ($kiw_data['action'] == "ads"){


            $kiw_data['action_value'] = $_REQUEST['ads_id'];
            $kiw_data['c_space'] = $_REQUEST['c_space'];


        } elseif ($kiw_data['action'] == "notification"){

            $kiw_data['action_method'] = $_REQUEST['notification_type'];


            if (in_array($kiw_data['action_method'], array("api", "push"))){

                $kiw_data['action_value'] = $_REQUEST['notification_url'];


            } else {

                $kiw_data['action_value'] = $_REQUEST['notification_template'];


            }


        } elseif ($kiw_data['action'] == "redirect"){

            $kiw_data['action_value'] = $_REQUEST['redirection'];


        }


        $kiw_db->query(sql_update($kiw_db, "kiwire_campaign_manager", $kiw_data, "tenant_id = '{$tenant_id}' AND id = '{$kiw_id}'"));


        sync_logger("{$_SESSION['user_name']} updated campaign {$kiw_data['name']}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Campaign [{$kiw_data['name']}]  has been updated", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}


function verify($kiw_db, $tenant_id){


    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        if (in_array("Campaign -> Verify", $_SESSION['access_list'])) {


            $kiw_id = (int)$_REQUEST['id'];


            $kiw_campaign = $kiw_db->query_first("SELECT * FROM kiwire_campaign_manager WHERE tenant_id = '{$tenant_id}' AND id = '{$kiw_id}' LIMIT 1");


            if ($kiw_campaign) {


                if ($kiw_campaign['creator'] == $_SESSION['user_name'] || $_SESSION['access_level'] == "superuser") {


                    if ($kiw_campaign['status'] == "active"){

                        $kiw_status = "inactive";
                        $kiw_respond = "unverified";

                    } else {

                        $kiw_status = "active";
                        $kiw_respond = "verified";

                    }


                    $kiw_db->query("UPDATE kiwire_campaign_manager SET updated_date = NOW(),  status = '{$kiw_status}' WHERE tenant_id = '{$tenant_id}' AND id = '{$kiw_id}' LIMIT 1");


                    sync_logger("{$_SESSION['user_name']} updated campaign {$kiw_respond}", $_SESSION['tenant_id']);
                    
                    echo json_encode(array("status" => "success", "message" => "SUCCESS: Campaign has been {$kiw_respond}", "data" => null));


                } else {

                    echo json_encode(array("status" => "error", "message" => "ERROR: You are not allowed to update self-created campaign", "data" => null));

                }


            }


        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to verify campaign", "data" => null));

        }


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}