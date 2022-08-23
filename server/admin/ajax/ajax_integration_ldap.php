<?php

$kiw['module'] = "Integration -> LDAP";
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

    case "test": test(); break;
    case "update": update();break;
    case "get_all": get_data(); break;
    case "create": create(); break;
    case "delete": delete(); break;
    case "get_update": get_single_data(); break;
    case "edit_single_data": edit_single_data(); break;
    default: echo "ERROR: Wrong implementation";

}

function create()
{
    global $kiw_db, $tenant_id;
 
     if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $data['group_name']         = $kiw_db->escape($_GET['group_name']);
        $data['profile']            = $kiw_db->escape($_GET['profile_group']);
        $data['status']             = ($_GET['status'] == "" ? "n" : "y");
        $data['priority']           = $kiw_db->escape($_GET['priority']);

        $data['allowed_zone']       = $kiw_db->escape($_GET['allowed_zone_group']);
        $data['type']               = 'ldap';
        $data['tenant_id']          = $tenant_id;
        $data['updated_date']       = date('Y-m-d H-i-s');


        if($kiw_db->insert("kiwire_group_mapping", $data)){

            unset($data);
            
            sync_logger("{$_SESSION['user_name']} updated LDAP mapping {$_GET['group_name']} ", $_SESSION['tenant_id']);
            
            echo json_encode(array("status" => "success", "message" => "SUCCESS: Groupname " . $_GET['group_name'] . " has been created.", "data" => null));
        
        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }

    } else {

         echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

     }


}


function update()
{
    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $data['enabled']          = $kiw_db->escape($_POST['enabled']);
        $data['host']             = $kiw_db->escape($_POST['host']);
        $data['port']             = $kiw_db->escape($_POST['port']);
        $data['rdn']              = $kiw_db->escape($_POST['rdn']);

        $data['profile']          = $kiw_db->escape($_POST['profile_master']);
        $data['allowed_zone']     = $kiw_db->escape($_POST['allowed_zone_master']);
        $data['updated_date']     = date('Y-m-d H-i-s');

        // $data['is_24_hour']     = isset($_REQUEST['is_24']) && $_REQUEST['is_24'] ? 1 : 0;
        // $data['start_time']     = $kiw_db->escape($_REQUEST['start_time']);

        // if(!$data['is_24_hour']){
            
        //     if($_REQUEST['start_time'] == $_REQUEST['stop_time'])
        //         die(json_encode(array("status" => "failed", "message" => "ERROR: Stop Time cannot same as Start Time", "data" => null)));
        //     else
        //         $data['stop_time'] =   $kiw_db->escape($_REQUEST['stop_time']);

        // }
        

        if($kiw_db->update("kiwire_int_ldap", $data, "tenant_id = '$tenant_id'")){

            sync_logger("{$_SESSION['user_name']} updated LDAP setting", $_SESSION['tenant_id']);
    
            echo json_encode(array("status" => "success", "message" => "SUCCESS: LDAP setting has been saved", "data" => null));
        
        }
        else {

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


            $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_group_mapping WHERE id = '{$id}' AND tenant_id = '{$tenant_id}'");

            $del_groupname = $kiw_temp['group_name'];

            $kiw_db->query("DELETE FROM kiwire_group_mapping WHERE id = '{$id}' AND tenant_id = '{$tenant_id}'");


        }


        sync_logger("{$_SESSION['user_name']} deleted LDAP mapping {$del_groupname}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Group Mapping : {$del_groupname} has been deleted", "data" => null));


    } else {


        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));


    }


}



function get_data()
{


    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->fetch_array("SELECT  * FROM kiwire_group_mapping WHERE type = 'ldap' AND tenant_id = '{$tenant_id}' LIMIT 1000");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }


}



function get_single_data()
{


    global $kiw_db, $tenant_id;


    $id = $kiw_db->escape($_REQUEST['id']);


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_group_mapping WHERE tenant_id = '{$tenant_id}' AND id = '{$id}' LIMIT 1");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }


}


function edit_single_data()
{
    
    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $id = $kiw_db->escape($_REQUEST['reference']);

        $kiw_data['group_name']     = $kiw_db->escape($_REQUEST['group_name']);
        $kiw_data['profile']        = $kiw_db->escape($_REQUEST['profile_group']);
        $kiw_data['allowed_zone']   = $kiw_db->escape($_REQUEST['allowed_zone_group']);

        $kiw_data['priority']       = $kiw_db->escape($_REQUEST['priority']);
        $kiw_data['status']         = $kiw_db->escape($_REQUEST['status']);
        $kiw_data['updated_date']   = date('Y-m-d H-i-s');

        // $kiw_db->query(sql_update($kiw_db, "kiwire_group_mapping", $kiw_data, "id = '{$id}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1"));
        if($kiw_db->update("kiwire_group_mapping", $kiw_data, "id = '{$id}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1")){
            
            sync_logger("{$_SESSION['user_name']} updated LDAP mapping {$kiw_data['group_name']}", $_SESSION['tenant_id']);
    
            echo json_encode(array("status" => "success", "message" => "SUCCESS: Group Mapping has been updated", "data" => null));
        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }
      
    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}



function test()
{

    foreach ($_REQUEST as $item => $value){

        $kiw_config[$item] = $value;

    }


    if (!empty($kiw_config['host']) && !empty($kiw_config['port']) && !empty($kiw_config['rdn'])) {

        
        try {


            // create connection to ldap

            $kiw_connection = ldap_connect($kiw_config['host'], $kiw_config['port']);


            ldap_set_option($kiw_connection, LDAP_OPT_PROTOCOL_VERSION, 3);

            ldap_set_option($kiw_connection, LDAP_OPT_REFERRALS, 0);


            $kiw_rdns = array_filter(explode(";", $kiw_config['rdn']));


            foreach ($kiw_rdns as $kiw_rdn) {


                $kiw_username = str_replace("{{username}}", $kiw_config['username'], $kiw_rdn);

                $kiw_result = ldap_bind($kiw_connection, $kiw_username, $kiw_config['password']);

                if ($kiw_result == true) break;


            }


            if ($kiw_result == true){

                echo json_encode(array("status" => "success", "message" => "SUCCESS: Connection to LDAP server succeed", "data" => null));

            } else {

                echo json_encode(array("status" => "error", "message" => "ERROR: Possible wrong credential provided", "data" => null));

            }


        } catch (Exception $e){

            echo json_encode(array("status" => "error", "message" => "ERROR: " . $e->getMessage(), "data" => null));
            
        }

    }

}