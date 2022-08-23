<?php

$kiw['module'] = "Device -> Project";
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

switch ($kiw_action) {

    case "create":
        create($kiw_db);
        break;
    case "remove":
        delete($kiw_db);
        break;
    case "get_all":
        get_data($kiw_db);
        break;
    case "get_data":
        get_update($kiw_db);
        break;
    case "update":
        update($kiw_db);
        break;
    default:
        echo "ERROR: Wrong implementation";

}



// create function for adding new data
function create($kiw_db){

    // strip out unwanted data like malformed html or possible sql injections.
    $kiw_name = $kiw_db->sanitize($_REQUEST['name']);

    // join elements in the zones array with ","
    $kiw_zones = implode(",", $_REQUEST['zones']);

    // strip out unwanted data again
    $kiw_zones = $kiw_db->escape($kiw_zones);

    // if name is empty,
    if (empty($kiw_name)){

        // show a fail message
        die(json_encode(array("status" => "failed", "message" => "Please provide a name for this project", "data" => null)));

    }

    // if permission w (write) or rw (read/write) is in the array,
    if (in_array($_SESSION['permission'], array("w", "rw"))) {
        csrf($kiw_db->escape($_REQUEST['token']));

        // find out total number of elements that exists for the specified tenant.
        // Used query_first because there should only be 1 element per ID, so just return the 1st result
        $kiw_existed = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_project WHERE tenant_id = '{$_SESSION['tenant_id']}' AND name = '{$kiw_name}'");

        // if it doesn't exist yet in the database,
        if ($kiw_existed['kcount'] == 0){

            // insert the data into the database
            $kiw_db->query("INSERT INTO kiwire_project(id, tenant_id, updated_date, name, zone_list) VALUE (NULL, '{$_SESSION['tenant_id']}', NOW(), '{$kiw_name}', '{$kiw_zones}')");

            // return a success message
            echo json_encode(array("status" => "success", "message" => "Project name [ {$kiw_name} ] has been created.", "data" => null));


        } else {
            // if the name already exist in database, show error message
            echo json_encode(array("status" => "failed", "message" => "Project name [ {$kiw_name} ] already existed.", "data" => null));

        }



    }


}

// function to get a specific data for 1 ID
function get_data($kiw_db){

    // if permission w (write) or rw (read/write) is in the array,
    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        // fetch the array of data (fetch everything in the database)
        $kiw_data = $kiw_db->fetch_array("SELECT * FROM kiwire_project WHERE tenant_id = '{$_SESSION['tenant_id']}'");

        // show success message
        echo json_encode(array("status" => "success", "message" => "", "data" => $kiw_data));


    } 
    // if no permission,
    else {
        // show error message
        echo json_encode(array("status" => "failed", "message" => "You are not allowed to access this module", "data" => null));

    }

}

// function to delete a specified row of data
function delete($kiw_db){

    csrf($kiw_db->escape($_REQUEST['token']));
    // strip out unwanted data like malformed html or possible sql injections.
    $kiw_name = $kiw_db->escape($_REQUEST['name']);

    // if permission w (write) or rw (read/write) is in the array,
    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        // delete the specified row from the database
        $kiw_db->query("DELETE FROM kiwire_project WHERE name = '{$kiw_name}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");

        // show success message
        echo json_encode(array("status" => "success", "message" => "SUCCESS: Project [ {$kiw_name} ] has been deleted.", "data" => null));


    } else {
        // if no permission to write or read/write, show error message
        echo json_encode(array("status" => "failed", "message" => "You are not allowed to access this module", "data" => null));

    }


}

// function that is used to retrieve specified data from the database
function get_update($kiw_db){

    // if permission w (write) or rw (read/write) is in the array,
    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        // strip out unwanted data like malformed html or possible sql injections.
        $kiw_name = $kiw_db->escape($_REQUEST['name']);

        // get the first result after querying specified data based on name and ID
        $kiw_data = $kiw_db->query_first("SELECT * FROM kiwire_project WHERE name = '{$kiw_name}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");

        // return success message and data
        echo json_encode(array("status" => "success", "message" => "", "data" => $kiw_data));


    } 
    // else if no permission,
    else {
        // return error message
        echo json_encode(array("status" => "failed", "message" => "You are not allowed to access this module", "data" => null));

    }



}

// function used to actually change the values for a specified row in database and save it
function update($kiw_db){

    // if permission w (write) or rw (read/write) is in the array,
    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));
        // strip out unwanted data like malformed html or possible sql injections.
        $kiw_id = $kiw_db->escape($_REQUEST['reference']);
        
        // strip out unwanted data like malformed html or possible sql injections.
        $kiw_name = $kiw_db->sanitize($_REQUEST['name']);

        // join elements in the zones array with ","
        $kiw_zones = implode(",", $_REQUEST['zones']);

        // strip out unwanted data like malformed html or possible sql injections.
        $kiw_zones = $kiw_db->escape($kiw_zones);

        // update the data with updated info that is passed from the form
        $kiw_db->query("UPDATE kiwire_project SET updated_date = NOW(), name = '{$kiw_name}', zone_list = '{$kiw_zones}' WHERE tenant_id = '{$_SESSION['tenant_id']}' AND id = '{$kiw_id}' LIMIT 1");

        // show success message
        echo json_encode(array("status" => "success", "message" => "SUCCESS: Your project has been updated.", "data" => null));


    } 
    // else if no permission,
    else {
        // show fail message
        echo json_encode(array("status" => "failed", "message" => "You are not allowed to access this module", "data" => null));

    }


}