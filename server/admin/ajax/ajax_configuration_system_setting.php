<?php

$kiw['module'] = "Configuration -> Global";
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

    case "update": update(); break;
    default: echo "ERROR: Wrong implementation";

}

function update(){


    global $kiw_db;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {
        csrf($kiw_db->escape($_REQUEST['token']));

        $kiw_interim_free = $_REQUEST['freeprofile_interim'];
        $kiw_interim_paid = $_REQUEST['paidprofile_interim'];

        if($kiw_interim_free < 300) $kiw_interim_free = 300;
        if($kiw_interim_paid < 300) $kiw_interim_paid = 300;


        $kiw_profiles = $kiw_db->fetch_array("SELECT * FROM kiwire_profiles");

        foreach ($kiw_profiles as $kiw_profile){


            $kiw_temp = json_decode($kiw_profile['attribute'], true);

            $kiw_temp['reply:Acct-Interim-Interval'] = $kiw_profile['type'] == "free" ? $kiw_interim_free : $kiw_interim_paid;

            $kiw_db->query("UPDATE kiwire_profiles SET updated_date = NOW(), attribute = '" . json_encode($kiw_temp) . "' WHERE id = '{$kiw_profile['id']}' LIMIT 1");

            unset($kiw_temp);


        }

        unset($kiw_profile);
        unset($kiw_profiles);


        $kiw_config['freeprofile_interim']      = $kiw_interim_free;
        $kiw_config['paidprofile_interim']      = $kiw_interim_paid;
        $kiw_config['keep_log_data']            = $kiw_db->escape($_REQUEST['keep_log_data']);
        $kiw_config['system_url']               = $kiw_db->escape($_REQUEST['system_url']);
        $kiw_config['timezone']                 = $kiw_db->escape($_REQUEST['timezone']);
        $kiw_config['service_worker']           = $kiw_db->escape($_REQUEST['service_worker']);
        $kiw_config['integration_worker']       = $kiw_db->escape($_REQUEST['integration_worker']);
        $kiw_config['update_check']             = $kiw_db->escape($_REQUEST['update_check']);
        $kiw_config['campaign_check']           = $kiw_db->escape($_REQUEST['campaign_check']);
        $kiw_config['backup_db']                = $kiw_db->escape($_REQUEST['backup_db']);
        $kiw_config['device_monitor']           = $kiw_db->escape($_REQUEST['device_monitor']);
        $kiw_config['notification_interval']    = $kiw_db->escape($_REQUEST['notification_interval']);
        $kiw_config['statistics_admin']         = $kiw_db->escape($_REQUEST['statistics_admin']);
        $kiw_config['statistics_user']          = $kiw_db->escape($_REQUEST['statistics_user']);
        $kiw_config['reset_daily']              = $kiw_db->escape($_REQUEST['reset_daily']);
        $kiw_config['reset_weekly']             = $kiw_db->escape($_REQUEST['reset_weekly']);
        $kiw_config['reset_monthly']            = $kiw_db->escape($_REQUEST['reset_monthly']);
        $kiw_config['reset_yearly']             = $kiw_db->escape($_REQUEST['reset_yearly']);
        $kiw_config['tenant_via_ip']            = $kiw_db->escape($_REQUEST['tenant_via_ip']);


        @file_put_contents(dirname(__FILE__, 3) . "/custom/system_setting.json", json_encode($kiw_config));


        unset($kiw_config);


        $kiw_config['host']             = $_REQUEST['host'];
        $kiw_config['port']             = $_REQUEST['port'];
        $kiw_config['auth']             = $_REQUEST['auth'];
        $kiw_config['user']             = $_REQUEST['user'];
        $kiw_config['password']         = $_REQUEST['password'];
        $kiw_config['from_email']       = $_REQUEST['from_email'];
        $kiw_config['from_name']        = $_REQUEST['from_name'];
        $kiw_config['notification']     = $_REQUEST['notification'];


        @file_put_contents(dirname(__FILE__, 3) . "/custom/system_smtp.json", json_encode($kiw_config));

        
        sync_logger("{$_SESSION['user_name']} updated System setting", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: System setting has been updated", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}
