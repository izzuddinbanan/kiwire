<?php

$kiw['module'] = "Integration -> Radius SSO";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_connection.php";

if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));
}

$action = $_REQUEST['action'];

switch ($action) {

    case "update": update(); break;
    default: echo "ERROR: Wrong implementation";
}

function update()

{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));
        
        $data['enabled']     = (isset($_REQUEST['enabled']) ? "y" : "n");
        $data['sso_server']  = $_REQUEST['sso_server'];
        $data['sso_port']    = $_REQUEST['sso_port'];
        $data['sso_secret']  = $_REQUEST['sso_secret'];

        $data['sso_simul']   = $_REQUEST['sso_simul'];
        $data['sso_timeout'] = $_REQUEST['sso_timeout'];
        $data['sso_retry']   = $_REQUEST['sso_retry'];
        $data['sso_info']    = json_encode($_REQUEST['sso_data']);

        $data['acctsessionid']     = (isset($_REQUEST['acctsessionid']) ? "y" : "n");
        $data['username']     = (isset($_REQUEST['username']) ? "y" : "n");
        $data['nasipaddress']     = (isset($_REQUEST['nasipaddress']) ? "y" : "n");
        $data['nasportid']     = (isset($_REQUEST['nasportid']) ? "y" : "n");

        $data['nasporttype']     = (isset($_REQUEST['nasporttype']) ? "y" : "n");
        $data['acctsessiontime']     = (isset($_REQUEST['acctsessiontime']) ? "y" : "n");
        $data['acctoutputoctets']     = (isset($_REQUEST['acctoutputoctets']) ? "y" : "n");
        $data['acctinputoctets']     = (isset($_REQUEST['acctinputoctets']) ? "y" : "n");

        $data['calledstationid']     = (isset($_REQUEST['calledstationid']) ? "y" : "n");
        $data['callingstationid']     = (isset($_REQUEST['callingstationid']) ? "y" : "n");
        $data['acctterminatecause']     = (isset($_REQUEST['acctterminatecause']) ? "y" : "n");
        $data['framedipaddress']     = (isset($_REQUEST['framedipaddress']) ? "y" : "n");


        if($kiw_db->update("kiwire_sso", $data, "tenant_id = '{$tenant_id}'")){

            echo json_encode(array("status" => "success", "message" => "SUCCESS: Radius SSO Integration saved", "data" => null));
    
        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}
