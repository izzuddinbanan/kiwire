<?php

$kiw['module'] = "Campaign -> Company Apps";
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

    case "update": update();break;
    default: echo "ERROR: Wrong implementation";

}

//main
function update()
{
    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));


        $data['app_title']         = $kiw_db->sanitize($_POST['app_title']);
        $data['app_author']        = $kiw_db->sanitize($_POST['app_author']);
        $data['app_price']         = $kiw_db->sanitize($_POST['app_price']);
        $data['app_playstore_url'] = $kiw_db->escape($_POST['app_playstore_url']);
        $data['app_appstore_url']  = $kiw_db->escape($_POST['app_appstore_url']);
        $data['status']            = (isset($_POST['status']) ? "y" : "n");
        $data['updated_at']        = "NOW()";
        $data['updated_date']      = date('Y-m-d H-i-s');


        if (empty($data['app_price'])) $data['app_price'] = "FREE";
       
        $logo = $_FILES["logo"];

        if (file_exists(dirname(__FILE__, 3) . "/custom/{$tenant_id}/images") == false) {

            mkdir(dirname(__FILE__, 3) . "/custom/{$tenant_id}/images", 0755, true);

        }

        if (!empty($logo['name']) && is_image($logo['tmp_name'])) {

            system("rm -f ". dirname(__FILE__, 3) . "/custom/{$tenant_id}/images/banner-logo-{$tenant_id}*");

            $kiw_logo_path = $_FILES['logo']['name'];
            $kiw_logo_ext = strtolower(pathinfo($kiw_logo_path, PATHINFO_EXTENSION));

            $filename_logo = "banner-logo-{$tenant_id}.{$kiw_logo_ext}";

            $destination_logo = dirname(__FILE__, 3) .  "/custom/{$tenant_id}/images/$filename_logo";

            move_uploaded_file($logo['tmp_name'], $destination_logo);

            $data['app_logopath'] = $filename_logo;


        } else {

            $data['app_logopath'] = "";

        }

        if($kiw_db->update("kiwire_campaign_apps", $data, "tenant_id = '$tenant_id'")){

            sync_logger("{$_SESSION['user_name']} updated smart banner {$_POST['app_title']}", $_SESSION['tenant_id']);
    
            echo json_encode(array("status" => "success", "message" => "SUCCESS: Campaign Apps Configuration saved", "data" => null));
        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }



    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
    }
}

function is_image($kiw_path){

    $kiw_temp = mime_content_type($kiw_path);

    $kiw_allow_list = array(
        'image/png',
        'image/jpeg',
        'image/gif'
    );

    return in_array($kiw_temp, $kiw_allow_list);

}
