<?php

$kiw['module'] = "Account -> Persona";
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

    case "get_all":
        get_data();
        break;
    case "get_update":
        get_single_data();
        break;
    case "create":
        create();
        break;
    case "delete":
        delete();
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


        $kiw_name = $kiw_db->sanitize($_REQUEST['name']);

        if (strlen($kiw_name) > 0) {

            $kiw_row = $kiw_db->query_first("SELECT COUNT(*) AS ccount FROM kiwire_persona WHERE name = '{$kiw_name}' AND tenant_id = '{$tenant_id}'");


            if ($kiw_row['ccount'] == 0) {


                $data['name'] = $kiw_db->sanitize($_REQUEST['name']);
                $data['updated_date'] = date('Y-m-d H-i-s');
                $data['tenant_id'] = $tenant_id;




                for ($x = 0; $x < count($_REQUEST['fields']); $x++) {

                    if (strlen($_REQUEST['values'][$x]) > 0) {

                        $data['rule'][] = array(
                            "field" => $kiw_db->escape($_REQUEST['fields'][$x]),
                            "operator" => $kiw_db->escape($_REQUEST['operators'][$x]),
                            "value" => $kiw_db->escape($_REQUEST['values'][$x])
                        );
                    }
                }


                if (count($data['rule']) > 0) {


                    $data['rule'] = json_encode($data['rule']);

                    $persona = $kiw_db->query("INSERT INTO kiwire_persona(name, rule, tenant_id, updated_date) VALUE ('{$data['name']}', '{$data['rule']}', '{$data['tenant_id']}', now())");

                    if ($persona) {

                        sync_logger("{$_SESSION['user_name']} create persona {$kiw_name}", $_SESSION['tenant_id']);

                        echo json_encode(array("status" => "success", "message" => "SUCCESS: New Persona added", "data" => $data['rule']));
                   
                    
                    } else {

                        echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));
                    }
               
                
                } else {

                    echo json_encode(array("status" => "Error", "message" => "ERROR: No rules with valid value!", "data" => null));
                }
            
            
            } else {

                echo json_encode(array("status" => "Error", "message" => "ERROR: Persona name already exists!", "data" => null));
            }
        
        
        } else {

            echo json_encode(array("status" => "Error", "message" => "ERROR: Please provide a name for this persona rule!", "data" => null));
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

            $kiw_db->query("DELETE FROM kiwire_persona WHERE name = '{$id}' AND tenant_id = '{$tenant_id}' LIMIT 1");
        }


        sync_logger("{$_SESSION['user_name']} deleted persona {$id}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Persona [{$id}] has been deleted", "data" => null));
    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
    }
}


function get_data()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->fetch_array("SELECT * FROM kiwire_persona WHERE tenant_id = '{$tenant_id}' LIMIT 1000");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));
    }
}


function get_single_data()
{

    global $kiw_db, $tenant_id;


    $id = $kiw_db->escape($_REQUEST['id']);


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_persona WHERE tenant_id = '{$tenant_id}' AND name = '{$id}' LIMIT 1");

        $kiw_temp['rule'] = json_decode($kiw_temp['rule'], true);

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));
    }
}



function edit_single_data()
{

    global $kiw_db, $tenant_id;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        csrf($kiw_db->escape($_REQUEST['token']));

        $id = $kiw_db->escape($_REQUEST['reference']);

        $kiw_name = $kiw_db->sanitize($_REQUEST['name']);


        for ($x = 0; $x < count($_REQUEST['fields']); $x++) {

            if (strlen($_REQUEST['values'][$x]) > 0) {

                $data['rule'][] = array(
                    "field" => $kiw_db->escape($_REQUEST['fields'][$x]),
                    "operator" => $kiw_db->escape($_REQUEST['operators'][$x]),
                    "value" => $kiw_db->escape($_REQUEST['values'][$x])
                );
            }
        }

        if (strlen($kiw_name) > 0) {

            if (count($data['rule']) > 0) {


                $kiw_rule_list = $data['rule'] = json_encode($data['rule']);


                $kiw_db->query("UPDATE kiwire_persona SET updated_date = NOW(), name = '{$kiw_name}', rule = '{$kiw_rule_list}' WHERE tenant_id = '{$tenant_id}' AND id = '{$id}' LIMIT 1");


                sync_logger("{$_SESSION['user_name']} updated persona {$kiw_name}", $_SESSION['tenant_id']);

                echo json_encode(array("status" => "success", "message" => "SUCCESS: Persona has been saved.", "data" => null));
            } else {

                echo json_encode(array("status" => "Error", "message" => "ERROR: No rules with valid value!", "data" => null));
            }
        } else {
            echo json_encode(array("status" => "Error", "message" => "ERROR: Please provide a name for this persona rule!", "data" => null));
        }
    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
    }
}
