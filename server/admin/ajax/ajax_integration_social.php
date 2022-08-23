<?php

$kiw['module'] = "Integration -> Social";
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

    case "update": update(); break;
    default: echo "ERROR: Wrong implementation";

}

function update()
{
    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));
        
        $data['facebook_en']      = (isset($_POST['facebook_en']) ? "y" : "n");
        $data['facebook_page']    = $_POST['facebook_page'];

        $data['twitter_en']       = (isset($_POST['twitter_en']) ? "y" : "n");
        $data['twitter_page']     = $_POST['twitter_page'];

        $data['instagram_en']     = (isset($_POST['instagram_en']) ? "y" : "n");
        $data['kakao_en']         = (isset($_POST['kakao_en']) ? "y" : "n");
        $data['vk_en']            = (isset($_POST['vk_en']) ? "y" : "n");
        $data['line_en']          = (isset($_POST['line_en']) ? "y" : "n");
        $data['zalo_en']          = (isset($_POST['zalo_en']) ? "y" : "n");

        $data['wechat_en']        = (isset($_POST['wechat_en']) ? "y" : "n");
        $data['linkedin_en']      = (isset($_POST['linkedin_en']) ? "y" : "n");
        $data['profile']          = $_POST['profile'];
        $data['allowed_zone']     = $_POST['allowed_zone'];

        $data['updated_date']     = date('Y-m-d H-i-s');
        $data['data']             = $kiw_db->escape(implode(",", $_POST['data']));


        if($kiw_db->update("kiwire_int_social", $data, "tenant_id = '$tenant_id'")){

            sync_logger("{$_SESSION['user_name']} updated Social Networks setting ", $_SESSION['tenant_id']);
    
            echo json_encode(array("status" => "success", "message" => "SUCCESS: General Setting has been updated", "data" => null));
        
        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }



    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
    }
}
