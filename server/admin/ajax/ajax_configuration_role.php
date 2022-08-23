<?php

$kiw['module'] = "Configuration -> Access Level";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once '../includes/include_general.php';
require_once "../includes/include_connection.php";

if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}

$action = $_REQUEST['action'];

switch ($action) {

    case "get_all": get_all(); break;
    case "create": create(); break;
    case "delete": delete(); break;
    case "get_update": get_update(); break;
    default: echo "ERROR: Wrong implementation";

}


function get_all()
{


    global $kiw_db;


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->fetch_array("SELECT DISTINCT(groupname) AS groupname FROM kiwire_admin_group WHERE tenant_id = '{$_SESSION['tenant_id']}' ORDER BY groupname ASC");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }

}


function create(){


    global $kiw_db;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $kiw_groupname = $kiw_db->sanitize($_POST['groupname']);


        $kiw_temp = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_admin_group WHERE groupname = '{$kiw_groupname}' AND tenant_id = '{$_SESSION['tenant_id']}'");


        $kiw_update = $kiw_db->escape($_POST['reference']);


        if (empty($kiw_update)) {


            if (!empty($kiw_groupname)) {


                if ($kiw_temp['kcount'] == 0) {


                    $kiw_role_list = $_POST['modules'];


                    if (count($kiw_role_list) > 0) {

                        foreach ($kiw_role_list as $kiw_role) {

                            $kiw_db->query("INSERT INTO kiwire_admin_group VALUE (NULL, '{$kiw_groupname}', '" . $kiw_db->escape($kiw_role) . "', '{$_SESSION['tenant_id']}', NOW())");

                        }


                        sync_logger("{$_SESSION['user_name']} create role {$kiw_groupname}", $_SESSION['tenant_id']);

                        echo json_encode(array("status" => "success", "message" => "SUCCESS: New role [{$kiw_groupname}] has been added", "data" => null));


                    } else {

                        echo json_encode(array("status" => "failed", "message" => "Please add at least one role", "data" => null));

                    }


                } else {

                    echo json_encode(array("status" => "failed", "message" => "[{$kiw_groupname}] is a duplicated role name", "data" => null));

                }


            } else {

                echo json_encode(array("status" => "failed", "message" => "Please provide a name for this role", "data" => null));

            }


            } else {


                $kiw_role_list = $_POST['modules'];


                if (!empty($kiw_update)) {

                    if (count($kiw_role_list) > 0) {


                        $kiw_db->query("DELETE FROM kiwire_admin_group WHERE groupname = '{$kiw_groupname}' AND tenant_id = '{$_SESSION['tenant_id']}'");


                        foreach ($kiw_role_list as $kiw_role) {

                            $kiw_db->query("INSERT INTO kiwire_admin_group VALUE (NULL, '{$kiw_groupname}', '" . $kiw_db->escape($kiw_role) . "', '{$_SESSION['tenant_id']}', NOW())");

                        }


                        sync_logger("{$_SESSION['user_name']} create role {$kiw_groupname}", $_SESSION['tenant_id']);

                        echo json_encode(array("status" => "success", "message" => "SUCCESS: Role [{$kiw_groupname}] has been updated", "data" => null));


                


                } else {

                    echo json_encode(array("status" => "failed", "message" => "Please try again.", "data" => null));

                }


            }


        }


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}


function delete()
{


    global $kiw_db;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {
        csrf($kiw_db->escape($_REQUEST['token']));

        $kiw_group_name = $kiw_db->escape($_POST['groupname']);


        if (!empty($kiw_group_name)) {


            $kiw_db->query("DELETE FROM kiwire_admin_group WHERE groupname = '{$kiw_group_name}' AND tenant_id = '{$_SESSION['tenant_id']}'");


        }

        sync_logger("{$_SESSION['user_name']} deleted role {$kiw_group_name}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Role [{$kiw_group_name}] has been deleted", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}


function get_update(){


    global $kiw_db;


    $kiw_group_name = $kiw_db->escape($_REQUEST['groupname']);


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->fetch_array("SELECT * FROM kiwire_admin_group WHERE tenant_id = '{$_SESSION['tenant_id']}' AND groupname = '{$kiw_group_name}'");


        if (count($kiw_temp) > 0) {


            for ($i = 0; $i < count($kiw_temp); $i++){

                $kiw_temp[$i]['moduleid'] = preg_replace("/[^a-zA-Z0-9]+/", "", $kiw_temp[$i]['moduleid']);

            }


            echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


        } else {

            echo json_encode(array("status" => "failed", "message" => "No roles with this name", "data" => null));

        }


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}