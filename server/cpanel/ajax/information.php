<?php


header("Content-Type: application/json");


require_once "../includes/include_general.php";
require_once "../includes/include_session.php";


$action = $_REQUEST['action'];

switch ($action) {

    case "update_data":
        update_data();
        break;
    case "get_data":
        get_data();
        break;

    default:
        echo "ERROR: Wrong implementation";
}


function update_data()
{


    global $kiw_db, $kiw_tenant, $kiw_username;


    $kiw_fullname  = $_REQUEST['fullname'];
    $kiw_email     = $_REQUEST['email_address'];
    $kiw_phone     = $_REQUEST['phone_no'];


    // check if password is not hash then means need to update

    $kiw_password = $_REQUEST['password'];
    if (sync_decrypt($_REQUEST['password']) == false) {

        $kiw_password = sync_encrypt($_REQUEST['password']);
    }


    $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), fullname = '{$kiw_fullname}', email_address = '{$kiw_email}', phone_number = '{$kiw_phone}', password = '{$kiw_password}' WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant}' LIMIT 1");


    sync_logger("{$_SESSION['cpanel']['username']} updated users {$kiw_username}", $kiw_tenant);

    echo json_encode(array("status" => "success", "message" => "SUCCESS: User has been updated", "data" => null));



}


function get_data()
{


    global $kiw_db, $kiw_tenant, $kiw_username;


    if (strlen($kiw_username) > 0) {


        $kiw_user_data = array();

        $kiw_user_data = $kiw_db->query_first("SELECT * FROM kiwire_account_auth WHERE tenant_id = '{$kiw_tenant}' AND username = '{$kiw_username}' LIMIT 1");


        $kiw_user_data['status']      = ucfirst($kiw_user_data['status']);

        $kiw_user_data['date_create'] = sync_tolocaltime($kiw_user_data['date_create']);
        $kiw_user_data['date_expiry'] = sync_tolocaltime($kiw_user_data['date_expiry']);


        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_user_data));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: Missing account details", "data" => null));

    }


}
