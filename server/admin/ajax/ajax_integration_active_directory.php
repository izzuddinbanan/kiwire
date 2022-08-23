<?php

$kiw['module'] = "Integration -> Active Directory";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_general.php";
require_once "../includes/include_connection.php";

require_once dirname(__FILE__, 3) . "/libs/adldap/adLDAP.php";


if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}


$action = $_REQUEST['action'];

switch ($action) {
    case "test": test(); break;
    case "update": update(); break;
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
        $data['type']               = 'ad';
        $data['tenant_id']          = $tenant_id;
        $data['updated_date']       = date('Y-m-d H-i-s');

        if($kiw_db->insert("kiwire_group_mapping", $data)){
            
            unset($data);

            sync_logger("{$_SESSION['user_name']} create group mapping {$_GET['group_name']}", $_SESSION['tenant_id']);

            echo json_encode(array("status" => "success", "message" => "SUCCESS: New Group Mapping  " . $_GET['group_name'] . "  added", "data" => null));

        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }

    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}


function get_data()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->fetch_array("SELECT * FROM kiwire_group_mapping WHERE type = 'ad' AND  tenant_id = '{$tenant_id}' LIMIT 1000");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }

}



function get_single_data()
{


    global $kiw_db, $tenant_id;


    $id = $kiw_db->escape($_REQUEST['id']);

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_group_mapping WHERE tenant_id = '$tenant_id' AND id = '{$id}' LIMIT 1");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }


}


function update()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $data['host']               = $kiw_db->escape($_POST['host']);
        $data['basedn']             = $kiw_db->escape($_POST['basedn']);
        $data['accsuffix']          = $kiw_db->escape($_POST['accsuffix']);
        $data['adminuser']          = $kiw_db->escape($_POST['adminuser']);

        $data['adminpw']            = $kiw_db->escape($_POST['adminpw']);
        $data['profile']            = $kiw_db->escape($_POST['profile_master']);
        $data['enabled']            = $kiw_db->escape($_POST['enabled']);
        $data['allowed_zone']       = $kiw_db->escape($_POST['allowed_zone_master']);

        $data['updated_date']       = date('Y-m-d H-i-s');


        // $data['is_24_hour']     = isset($_REQUEST['is_24']) && $_REQUEST['is_24'] ? 1 : 0;
        // $data['start_time']     = $kiw_db->escape($_REQUEST['start_time']);

        // if(!$data['is_24_hour']){
            
        //     if($_REQUEST['start_time'] == $_REQUEST['stop_time'])
        //         die(json_encode(array("status" => "failed", "message" => "ERROR: Stop Time cannot same as Start Time", "data" => null)));
        //     else
        //         $data['stop_time'] =   $kiw_db->escape($_REQUEST['stop_time']);

        // }


        if($kiw_db->update("kiwire_int_msad", $data, "tenant_id = '$tenant_id'")){

            sync_logger("{$_SESSION['user_name']} updated Active Directory setting ", $_SESSION['tenant_id']);
            
            echo json_encode(array("status" => "success", "message" => "SUCCESS: Active Directory setting saved", "data" => null));
               
        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }


    } else

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
}




function delete()
{
    
    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $id = $kiw_db->escape($_POST['id']);

        if (!empty($id)) {

            $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_group_mapping WHERE id ='{$id}' AND tenant_id = '{$tenant_id}'");
            $del_groupname = $kiw_temp['group_name'];

            $kiw_db->query("DELETE FROM kiwire_group_mapping WHERE id ='{$id}' AND tenant_id = '{$tenant_id}'");

        }


        sync_logger("{$_SESSION['user_name']} deleted Group Mapping {$del_groupname}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Group Mapping : {$del_groupname} deleted", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}


function edit_single_data()
{
    
    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $id = $kiw_db->escape($_REQUEST['reference']);

        $new_group_name          = $kiw_db->escape($_REQUEST['group_name']);
        $new_profile             = $kiw_db->escape($_REQUEST['profile_group']);
        $new_allowed_zone        = $kiw_db->escape($_REQUEST['allowed_zone_group']);

        $new_priority            = $kiw_db->escape($_REQUEST['priority']);
        $new_status              = $kiw_db->escape($_REQUEST['status']);
        $new_updated_date        = date('Y-m-d H-i-s');

        if($kiw_db->query("UPDATE kiwire_group_mapping SET updated_date = NOW(), group_name = '{$new_group_name}', profile = '{$new_profile}', status = '{$new_status}' , priority = '{$new_priority}', allowed_zone = '{$new_allowed_zone}', updated_date = '{$new_updated_date}' WHERE id = '{$id}' AND tenant_id = '{$tenant_id}' LIMIT 1")){

            sync_logger("{$_SESSION['user_name']} updated Group Mapping {$new_group_name}", $_SESSION['tenant_id']);

            echo json_encode(array("status" => "success", "message" => "SUCCESS: Group Mapping has been updated", "data" => null));
            
        }
        
        echo json_encode(array("status" => "failed", "message" => "Error: Failed to update data", "data" => null));
        

    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}



function test(){


    foreach ($_REQUEST as $item => $value){

        $kiw_config[$item] = $value;

    }


    if (!empty($kiw_config['host']) && !empty($kiw_config['adminuser']) && !empty($kiw_config['adminpw']) && !empty($kiw_config['accsuffix'])){


        try {


            $kiw_connection = new adLDAP(array(
                'account_suffix'     => $kiw_config['accsuffix'],
                'domain_controllers' => explode(",", $kiw_config['host']),
                'base_dn'            => $kiw_config['basedn'],
                'admin_username'     => $kiw_config['adminuser'],
                'admin_password'     => $kiw_config['adminpw'],
            ));


            $kiw_auth = $kiw_connection->user()->authenticate($kiw_config['adminuser'], $kiw_config['adminpw']);


            if ($kiw_auth) {

                echo json_encode(array("status" => "success", "message" => "SUCCESS: Connection to the server succeed", "data" => null));

            } else {

                echo json_encode(array("status" => "error", "message" => "ERROR: Connection to the server failed", "data" => null));

            }


        } catch (Exception $e){

            echo json_encode(array("status" => "error", "message" => "ERROR: " . $e->getMessage(), "data" => null));

        }



    }


}