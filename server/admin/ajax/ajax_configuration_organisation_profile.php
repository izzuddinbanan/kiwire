<?php

$kiw['module'] = "Configuration -> Site Branding";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_connection.php";
require_once '../includes/include_general.php';

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

        $data['name']       = $kiw_db->sanitize($_POST['name']);
        $data['industry']   = $kiw_db->escape($_POST['industry']);
        $data['website']    = $kiw_db->escape($_POST['website']);
        $data['phone']      = $kiw_db->sanitize($_POST['phone']);
        $data['address']    = $kiw_db->escape($_POST['address']);

        if($kiw_db->update("kiwire_clouds", $data, "tenant_id = '{$tenant_id}' LIMIT 1")){

            $logo1 = $_FILES["logo"];
            
            if (!empty($logo1)) {

                $kinfo = finfo_open(FILEINFO_MIME_TYPE);
                $ktype = finfo_file($kinfo, $logo1['tmp_name']);


                if (file_exists( dirname(__FILE__, 3) . "/custom/{$tenant_id}") == false) {

                    mkdir( dirname(__FILE__, 3) . "/custom/{$tenant_id}/", 0755,true);

                }


                if (in_array($ktype, array("image/png", "image/jpeg", "image/gif"))) {

                    system("rm -f ". dirname(__FILE__, 3) . "/custom/{$tenant_id}/logo-{$tenant_id}*");

                    $extension = strtolower(pathinfo($logo1['name'], PATHINFO_EXTENSION));

                    $destination = dirname(__FILE__, 3) . "/custom/{$tenant_id}/logo-{$tenant_id}.{$extension}";

                    copy($logo1['tmp_name'], $destination);


                } else {

                    unlink($logo1['tmp_name']);

                }

            }


            sync_logger("{$_SESSION['user_name']} updated Organisation profile setting", $_SESSION['tenant_id']);

            echo json_encode(array("status" => "success", "message" => "SUCCESS: Organisation profile setting has been updated", "data" => str_replace("/var/www/kiwire/server/", "/", $destination)));
        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }

    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

    
}