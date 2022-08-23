<?php

$kiw['module'] = "Account -> Profile";
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

$action = $_REQUEST['action'];

switch ($action) {

    case "get_all": get_all(); break;
    case "get_update": get_update(); break;
    case "create": create(); break;
    case "delete": delete(); break;
    case "edit_single_data": edit_single_data(); break;

    default: echo "ERROR: Wrong implementation";

}


function get_all(){

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_profiles = $kiw_db->fetch_array("SELECT id,name,price,type,attribute FROM kiwire_profiles WHERE tenant_id = '{$tenant_id}'");


        foreach ($kiw_profiles as $kiw_profile){

            $kiw_attribute = json_decode($kiw_profile['attribute'], true);

            $kiw_temp = array();

            $kiw_temp['id']         = $kiw_profile['id'];
            $kiw_temp['name']       = $kiw_profile['name'];
            $kiw_temp['price']      = $kiw_profile['price'];
            $kiw_temp['type']       = ucfirst($kiw_profile['type']);


            if ($kiw_attribute['control:Max-All-Session']) {

                $kiw_temp['minute'] = $kiw_attribute['control:Max-All-Session'] / 60;

            } elseif ($kiw_attribute['control:Access-Period']){

                $kiw_temp['minute'] = $kiw_attribute['control:Access-Period'] / 60;

            } else {

                $kiw_temp['minute'] = 0;

            }


            $kiw_temp['speed_up']   = ($kiw_attribute['reply:WISPr-Bandwidth-Max-Down'] / 1024);
            $kiw_temp['speed_down'] = ($kiw_attribute['reply:WISPr-Bandwidth-Max-Up'] / 1024);

            $kiw_result[] = $kiw_temp;

            unset($kiw_temp);
            unset($kiw_attribute);

        }


        unset($kiw_profile);
        unset($kiw_profiles);


        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_result));


    }

}

function get_update()
{
    global $kiw_db, $tenant_id;

    $id = $kiw_db->escape($_REQUEST['id']);


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_profile = $kiw_db->query_first("SELECT * FROM kiwire_profiles WHERE id = '{$id}' AND tenant_id = '{$tenant_id}' LIMIT 1");


        $kiw_attribute = json_decode($kiw_profile['attribute'], true);


        $kiw_temp = array();

        $kiw_temp['id']         = $kiw_profile['id'];
        $kiw_temp['name']       = $kiw_profile['name'];
        $kiw_temp['price']      = $kiw_profile['price'];
        $kiw_temp['type']       = $kiw_profile['type'];
        $kiw_temp['grace']      = $kiw_profile['grace'];

        $kiw_temp['advance']            = $kiw_profile['advance'];
        $kiw_temp['quota_trigger']      = $kiw_profile['a_limit'];


        if ($kiw_attribute['control:Max-All-Session']) {

            $kiw_temp['minutes'] = $kiw_attribute['control:Max-All-Session'] / 60;

        } elseif ($kiw_attribute['control:Access-Period']){

            $kiw_temp['minutes'] = $kiw_attribute['control:Access-Period'] / 60;

        } else {

            $kiw_temp['minutes'] = 0;

        }


        $kiw_temp['simultaneous']       = $kiw_attribute['control:Simultaneous-Use'];
        $kiw_temp['vol_limit']          = $kiw_attribute['control:Kiwire-Total-Quota'];
        $kiw_temp['iddle']              = $kiw_attribute['reply:Idle-Timeout'] / 60;
        $kiw_temp['bwdown']             = ($kiw_attribute['reply:WISPr-Bandwidth-Max-Down'] / 1024);
        $kiw_temp['bwup']               = ($kiw_attribute['reply:WISPr-Bandwidth-Max-Up'] / 1024);
        $kiw_temp['min_down']           = ($kiw_attribute['reply:WISPr-Bandwidth-Min-Up'] / 1024);
        $kiw_temp['min_up']             = ($kiw_attribute['reply:WISPr-Bandwidth-Min-Down'] / 1024);


        $kiw_temp['attribute_custom']   = $kiw_profile['attribute_custom'];

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}


function create()
{
    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {
        
        csrf($kiw_db->escape($_REQUEST['token']));

        $data['tenant_id']  = $tenant_id;

        $data['name']       = $kiw_db->sanitize($_REQUEST['name']);
        $data['type']       = $kiw_db->escape($_REQUEST['type']);
        $data['price']      = $kiw_db->sanitize($_REQUEST['price']);
        $data['advance']    = $kiw_db->escape($_REQUEST['advance']);

        $data['grace']      = $kiw_db->escape($_REQUEST['grace']);
        $data['a_limit']    = $kiw_db->escape($_REQUEST['quota_trigger']);


        $kiw_test = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_profiles WHERE tenant_id = '{$_SESSION['tenant_id']}' AND name = '{$data['name']}'");

        if ($kiw_test['kcount'] > 0){

            die(json_encode(array("status" => "failed", "message" => "ERROR: Profile name [ {$data['name']} ] already existed!", "data" => null)));

        }



        $kiw_attribute = array();

        if (empty($_REQUEST['minutes'])) $_REQUEST['minutes'] = 60;

        if ($data['type'] == "countdown") $kiw_attribute['control:Max-All-Session']    = ((int)$_REQUEST['minutes']) * 60;
        elseif ($data['type'] == "expiration") $kiw_attribute['control:Access-Period'] = ((int)$_REQUEST['minutes']) * 60;

        $kiw_attribute['control:Simultaneous-Use']   = (int)$_REQUEST['simultaneous'];
        $kiw_attribute['control:Kiwire-Total-Quota'] = (int)$_REQUEST['vol_limit'];


        $kiw_interim = @file_get_contents( dirname(__FILE__, 3) . "/custom/system_setting.json");
        $kiw_interim = json_decode($kiw_interim, true);


        if ($_REQUEST['type'] == "free") {

            $kiw_attribute['reply:Acct-Interim-Interval'] = ($kiw_interim['freeprofile_interim'] > 0) ? $kiw_interim['freeprofile_interim'] : 1800;

        } else {

            $kiw_attribute['reply:Acct-Interim-Interval'] = ($kiw_interim['paidprofile_interim'] > 0) ? $kiw_interim['paidprofile_interim'] : 1800;

        }


        $kiw_attribute['reply:Idle-Timeout'] = (int)$_REQUEST['iddle'] * 60;
        $kiw_attribute['reply:Idle-Timeout'] = $kiw_attribute['reply:Idle-Timeout'] > 0 ? $kiw_attribute['reply:Idle-Timeout'] : 1800;


        $kiw_attribute['reply:WISPr-Bandwidth-Max-Down'] = (int)$_REQUEST['bwdown'] * 1024;
        $kiw_attribute['reply:WISPr-Bandwidth-Max-Up']   = (int)$_REQUEST['bwup'] * 1024;
        $kiw_attribute['reply:WISPr-Bandwidth-Min-Up']   = (int)$_REQUEST['min_down'] * 1024;
        $kiw_attribute['reply:WISPr-Bandwidth-Min-Down'] = (int)$_REQUEST['min_up'] * 1024;

        $data['attribute'] = json_encode($kiw_attribute);

        unset($kiw_attribute);


        $kiw_attribute = json_decode($_REQUEST['attribute_custom'], true);

        if ($kiw_attribute) {

            $data['attribute_custom'] = json_encode($kiw_attribute);

        }

        unset($kiw_attribute);

        $profile = $kiw_db->query("INSERT INTO kiwire_profiles(tenant_id, name, type, price, advance, grace, a_limit, attribute, attribute_custom) VALUE ('{$data['tenant_id']}', '{$data['name']}', '{$data['type']}', '{$data['price']}', '{$data['advance']}', '{$data['grace']}', '{$data['a_limit']}', '{$data['attribute']}', '{$data['attribute_custom']}')");


        if($profile){

            sync_logger("{$_SESSION['user_name']} create profile {$_REQUEST['name']}", $_SESSION['tenant_id']);

            echo json_encode(array("status" => "success", "message" => "SUCCESS: New profile [{$data['name']}] added", "data" => null));

        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

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


            $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_profiles WHERE id ='{$id}' AND tenant_id = '{$tenant_id}'");


            if ($kiw_temp['name'] != "Temp_Access") {


                $kiw_action = $kiw_db->escape($_REQUEST['account']);

                $kiw_action = explode("*", $kiw_action);


                if ($kiw_action[0] == "delete"){

                    $kiw_db->query("DELETE FROM kiwire_account_auth WHERE profile_subs = '{$kiw_temp['name']}' AND tenant_id = '{$tenant_id}'");

                } else {


                    if ($kiw_action[0] == "actionto" && !empty($kiw_action[1])) {

                        $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), profile_subs = '{$kiw_action[1]}' WHERE profile_subs = '{$kiw_temp['name']}' AND tenant_id = '{$tenant_id}'");

                    }


                }


                $kiw_db->query("DELETE FROM kiwire_profiles WHERE id ='{$id}' AND tenant_id = '{$tenant_id}'");

                sync_logger("{$_SESSION['user_name']} deleted profile {$kiw_temp['name']}", $_SESSION['tenant_id']);

                echo json_encode(array("status" => "success", "message" => "SUCCESS: Profile {$kiw_temp['name']} deleted", "data" => null));


            } else {


                echo json_encode(array("status" => "error", "message" => "ERROR: Your are not allowed to delete Temp_Access", "data" => null));


            }


        }


    } else {


        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));


    }


}


function edit_single_data()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));
        
        $kiw_id = $kiw_db->escape($_REQUEST['id']);

        $data['tenant_id']         = $tenant_id;

        $data['name']              = $kiw_db->sanitize($_REQUEST['name']);
        $data['price']             = $kiw_db->sanitize($_REQUEST['price']);
        $data['advance']           = $kiw_db->escape($_REQUEST['advance']);

        $data['grace']             = $kiw_db->escape($_REQUEST['grace']);
        $data['a_limit']           = $kiw_db->escape($_REQUEST['quota_trigger']);

        $data_auth['profile_subs'] = $kiw_db->sanitize($_REQUEST['name']);
        $data_auth['profile_curr'] = $kiw_db->sanitize($_REQUEST['name']);


        $kiw_attribute = array();

        if (empty($_REQUEST['minutes'])) $_REQUEST['minutes'] = 60;


        if ($data['name'] !== "Temp_Access") {


            $data['type'] = $_REQUEST['type'];


            if ($data['type'] == "countdown") {

                $kiw_attribute['control:Max-All-Session'] = ((int)$_REQUEST['minutes']) * 60;

            } elseif ($data['type'] == "expiration") {

                $kiw_attribute['control:Access-Period'] = ((int)$_REQUEST['minutes']) * 60;

            }


        } else {


            $data['type'] = "countdown";

            if (empty($_REQUEST['minutes'])) $_REQUEST['minutes'] = 1;

            $kiw_attribute['control:Max-All-Session'] = ((int)$_REQUEST['minutes']) * 60;


        }

        $kiw_attribute['control:Simultaneous-Use']   = (int)$_REQUEST['simultaneous'];
        $kiw_attribute['control:Kiwire-Total-Quota'] = (int)$_REQUEST['vol_limit'];


        $kiw_interim = @file_get_contents( dirname(__FILE__, 3) . "/custom/system_setting.json");
        $kiw_interim = json_decode($kiw_interim, true);


        if ($_REQUEST['type'] == "free") {

            $kiw_attribute['reply:Acct-Interim-Interval'] = ($kiw_interim['freeprofile_interim'] > 0) ? $kiw_interim['freeprofile_interim'] : 1800;

        } else {

            $kiw_attribute['reply:Acct-Interim-Interval'] = ($kiw_interim['paidprofile_interim'] > 0) ? $kiw_interim['paidprofile_interim'] : 1800;

        }


        $kiw_attribute['reply:Idle-Timeout'] = (int)$_REQUEST['iddle'] * 60;
        $kiw_attribute['reply:Idle-Timeout'] = $kiw_attribute['reply:Idle-Timeout'] > 0 ? $kiw_attribute['reply:Idle-Timeout'] : 1800;


        $kiw_attribute['reply:WISPr-Bandwidth-Max-Down'] = (int)$_REQUEST['bwdown'] * 1024;
        $kiw_attribute['reply:WISPr-Bandwidth-Max-Up']   = (int)$_REQUEST['bwup'] * 1024;
        $kiw_attribute['reply:WISPr-Bandwidth-Min-Up']   = (int)$_REQUEST['min_down'] * 1024;
        $kiw_attribute['reply:WISPr-Bandwidth-Min-Down'] = (int)$_REQUEST['min_up'] * 1024;

        $data['attribute'] = json_encode($kiw_attribute);

        unset($kiw_attribute);


        $kiw_attribute = json_decode($_REQUEST['attribute_custom'], true);

        if ($kiw_attribute) {

            $data['attribute_custom'] = json_encode($kiw_attribute);

        }

        unset($kiw_attribute);

        $kiw_profile = $kiw_db->query_first("SELECT * FROM kiwire_profiles WHERE id = '{$kiw_id}' AND tenant_id = '{$tenant_id}' LIMIT 1");

        $kiw_update = $kiw_db->query("UPDATE kiwire_profiles SET tenant_id = '{$data['tenant_id']}', updated_date = NOW(), name = '{$data['name']}', price = '{$data['price']}', type = '{$data['type']}', advance = '{$data['advance']}', grace = '{$data['grace']}', a_limit = '{$data['a_limit']}', attribute = '{$data['attribute']}', attribute_custom = '{$data['attribute_custom']}' WHERE id = '{$kiw_id}' AND tenant_id = '{$tenant_id}'");

        // if($kiw_db->update("kiwire_profiles", $data, "id = '{$kiw_id}' AND tenant_id = '{$tenant_id}'")){
        if($kiw_update) {
            
            // update profile name to user table as well
            // $kiw_db->update("kiwire_account_auth", $data_auth, "profile_subs = '{$kiw_profile['name']}' AND tenant_id = '{$tenant_id}'");

            $kiw_db->query("UPDATE kiwire_account_auth SET profile_subs = '{$data_auth['profile_subs']}', profile_curr = '{$data_auth['profile_curr']}' WHERE profile_subs = '{$kiw_profile['name']}' AND tenant_id = '{$tenant_id}'");
            
            sync_logger("{$_SESSION['user_name']} updated profile {$_REQUEST['name']}", $_SESSION['tenant_id']);
    
            echo json_encode(array("status" => "success", "message" => "SUCCESS: Profile has been updated", "data" => null));
        
        }else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }

        

    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}
