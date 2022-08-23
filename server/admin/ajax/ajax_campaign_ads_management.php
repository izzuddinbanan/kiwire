<?php

$kiw['module'] = "Campaign -> Ads Management";
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

    case "delete_image": delete_image($kiw_db); break;
    case "create": create(); break;
    case "delete": delete(); break;
    case "get_all": get_data(); break;
    case "get_update": get_update(); break;
    case "edit_single_data": edit_single_data(); break;
    default: echo "ERROR: Wrong implementation";

}


function create()
{
    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $data['name'] = $kiw_db->sanitize($_REQUEST['adsname']);


        $kiw_existed = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_campaign_ads WHERE name = '{$data['name']}' AND tenant_id = '{$_SESSION['tenant_id']}'");


        if ($kiw_existed['kcount'] == 0) {

            $data['link'] = $_REQUEST['link'];
            $data['updated_date'] = date('Y-m-d H-i-s');
            $data['type'] = $_REQUEST['type'];

            $data['status'] = 'n';
            $data['viewport'] = $_REQUEST['viewport'];
            $data['remark'] = $kiw_db->sanitize($_REQUEST['remark']);
            $data['msg'] = $_REQUEST['msg'];

            $data['captcha_txt'] = $_REQUEST['captcha_txt'];
            $data['verified_by'] = $_SESSION['username'];
            $data['verified_on'] = date('Y-m-d H-i-s');

            $data['json_url'] = $_REQUEST['json_url'];
            $data['json_path'] = $_REQUEST['json_path'];
            $data['ads_max_no'] = $_REQUEST['ads_max_no'];

            $data['random'] = (isset($_REQUEST['random']) ? "y" : "n");


            $kiw_mapping = json_decode($_REQUEST['mapping'], true);

            if ($kiw_mapping) {

                $data['mapping'] = $kiw_db->escape(base64_encode(json_encode($kiw_mapping)));

            }

            $fn_desktop = $_FILES["fn_desktop"];
            $fn_tablet = $_FILES["fn_tablet"];
            $fn_phone = $_FILES["fn_phone"];
            $adsname = $_REQUEST['name'];


            if (file_exists(dirname(__FILE__, 3) . "/custom/{$tenant_id}/images") == false) {

                mkdir(dirname(__FILE__, 3) . "/custom/{$tenant_id}/images", 0755, true);

            }


            if (!empty($fn_desktop['name']) && is_image($fn_desktop['tmp_name'])) {


                $filename_desktop = md5(basename($fn_desktop['name'])) . "." . substr($fn_desktop['name'], -3);

                $destination_desktop = dirname(__FILE__, 3) . "/custom/{$tenant_id}/images/$filename_desktop";


                move_uploaded_file($fn_desktop['tmp_name'], $destination_desktop);

                $data['fn_desktop'] = $filename_desktop;


            }


            if (!empty($fn_tablet['name']) && is_image($fn_tablet['tmp_name'])) {


                $filename_tablet = md5(basename($fn_tablet['name'])) . "." . substr($fn_tablet['name'], -3);

                $destination_tablet = dirname(__FILE__, 3) . "/custom/{$tenant_id}/images/{$filename_tablet}";


                move_uploaded_file($fn_tablet['tmp_name'], $destination_tablet);

                $data['fn_tablet'] = $filename_tablet;


            }


            if (!empty($fn_phone['name']) && is_image($fn_phone['tmp_name'])) {


                $filename_phone = md5(basename($fn_phone['name'])) . "." . substr($fn_phone['name'], -3);

                $destination_phone = dirname(__FILE__, 3) . "/custom/{$tenant_id}/images/{$filename_phone}";


                move_uploaded_file($fn_phone['tmp_name'], $destination_phone);

                $data['fn_phone'] = $filename_phone;


            }


            $data['fn_desktop'] = $filename_desktop;
            $data['fn_tablet'] = $filename_tablet;
            $data['fn_phone'] = $filename_phone;
            $data['created_by'] = $_SESSION['username'];
            $data['tenant_id'] = $tenant_id;

            if($results = $kiw_db->insert("kiwire_campaign_ads", $data)) {

                
                sync_logger("{$_SESSION['user_name']} create campaign {$adsname}", $_SESSION['tenant_id']);
                
                echo json_encode(array("status" => "success", "message" => "SUCCESS: New Campaign Ads {$adsname}  added", "data" => null));
                
            }else{

               echo json_encode(array("status" => "failed", "message" => "ERROR: Please check you input data.", "data" => $results));

            }

        } else {


            echo json_encode(array("status" => "failed", "message" => "ERROR: Ad name [ {$data['name']} ] already existed.", "data" => null));


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


            $kiw_temp = $kiw_db->query_first("SELECT name,fn_desktop,fn_tablet,fn_phone FROM kiwire_campaign_ads WHERE id = '" . $id . "' AND tenant_id = '$tenant_id'");

            $adsname           = $kiw_temp['name'];
            $filename_desktop  = $kiw_temp['fn_desktop'];
            $filename_tablet   = $kiw_temp['fn_tablet'];
            $filename_phone    = $kiw_temp['fn_phone'];

            if (!empty($filename_desktop)) unlink(dirname(__FILE__, 3) . "/custom/{$tenant_id}/images/{$filename_desktop}");
            if (!empty($filename_tablet)) unlink(dirname(__FILE__, 3) . "/custom/{$tenant_id}/images/{$filename_tablet}");
            if (!empty($filename_phone)) unlink(dirname(__FILE__, 3) . "/custom/{$tenant_id}/images/{$filename_phone}");


            $kiw_db->query("DELETE FROM kiwire_campaign_ads WHERE id = '{$id}' AND tenant_id = '{$tenant_id}' LIMIT 1");


        }


        sync_logger("{$_SESSION['user_name']} deleted campaign {$adsname}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Campaign Ads {$adsname} has been deleted", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}


function get_data()
{

    global $kiw_db, $tenant_id;


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_timezone = $_SESSION['timezone'];

        if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


        $kiw_temp = $kiw_db->fetch_array("SELECT * FROM kiwire_campaign_ads  WHERE tenant_id = '{$tenant_id}' LIMIT 1000");


        for ($kiw_x = 0; $kiw_x < count($kiw_temp); $kiw_x++){

            $kiw_temp[$kiw_x]['updated_date'] = sync_tolocaltime($kiw_temp[$kiw_x]['updated_date'], $kiw_timezone);

        }


        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }


}


function get_update()
{


    global $kiw_db, $tenant_id;

    $id = $kiw_db->escape($_REQUEST['id']);


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_campaign_ads WHERE tenant_id = '{$tenant_id}' AND id = '{$id}' LIMIT 1");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }

}


function edit_single_data()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $id = $kiw_db->escape($_REQUEST['reference']);


        $data['name']  = $kiw_db->sanitize($_POST['adsname']);
        $data['link']  = $kiw_db->escape($_POST['link']);
        $data['type']  = $kiw_db->escape($_POST['type']);

        $data['viewport']     = $kiw_db->escape($_REQUEST['viewport']);
        $data['captcha_txt']  = $kiw_db->escape($_REQUEST['captcha_txt']);

        $data['updated_date']  = "NOW()";

        $data['remark'] = $kiw_db->sanitize($_REQUEST['remark']);
        $data['msg']    = $kiw_db->escape($_REQUEST['msg']);

        $fn_desktop       = $_FILES["fn_desktop"];
        $fn_tablet        = $_FILES["fn_tablet"];
        $fn_phone         = $_FILES["fn_phone"];


        if (!empty($fn_desktop['name']) && is_image($fn_desktop['tmp_name'])) {


            $filename_desktop = md5(basename($fn_desktop['name'])) . "." . substr($fn_desktop['name'], -3);

            $destination_desktop = dirname(__FILE__, 3) . "/custom/{$tenant_id}/images/{$filename_desktop}";


            move_uploaded_file($fn_desktop['tmp_name'], $destination_desktop);

            $data['fn_desktop'] = $filename_desktop;


        }


        if (!empty($fn_tablet['name']) && is_image($fn_tablet['tmp_name'])) {


            $filename_tablet = md5(basename($fn_tablet['name'])) . "." . substr($fn_tablet['name'], -3);

            $destination_tablet = dirname(__FILE__, 3) . "/custom/{$tenant_id}/images/{$filename_tablet}";


            move_uploaded_file($fn_tablet['tmp_name'], $destination_tablet);

            $data['fn_tablet'] = $filename_tablet;


        }


        if (!empty($fn_phone['name']) && is_image($fn_phone['tmp_name'])) {


            $filename_phone = md5(basename($fn_phone['name'])) . "." . substr($fn_phone['name'], -3);

            $destination_phone = dirname(__FILE__, 3) . "/custom/{$tenant_id}/images/{$filename_phone}";


            move_uploaded_file($fn_phone['tmp_name'], $destination_phone);

            $data['fn_phone'] = $filename_phone;


        }


        $data['json_url']   = $kiw_db->escape($_REQUEST['json_url']);
        $data['json_path']  = $kiw_db->escape($_REQUEST['json_path']);
        $data['ads_max_no'] = $kiw_db->escape($_REQUEST['ads_max_no']);

        $data['random']       = (isset($_REQUEST['random']) ? "y" : "n");

        $kiw_mapping = json_decode($_REQUEST['mapping'], true);

        if ($kiw_mapping) {

            $data['mapping'] = $kiw_db->escape(base64_encode(json_encode($kiw_mapping)));

        }


        if($kiw_db->update("kiwire_campaign_ads", $data, "id = '{$id}' AND tenant_id = '{$_SESSION['tenant_id']}'")){
    
            sync_logger("{$_SESSION['user_name']} updated campaign {$data['name']}", $_SESSION['tenant_id']);
    
            echo json_encode(array("status" => "success", "message" => "SUCCESS: Campaign Ads {$data['name']} has been updated", "data" => null));

        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }

    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}


function delete_image($kiw_db){


    $kiw_ads = $kiw_db->escape($_REQUEST['ads']);

    $kiw_type = $kiw_db->escape($_REQUEST['type']);


    if (!empty($kiw_ads) && !empty($kiw_type)){


        if (in_array($_SESSION['permission'], array("w", "rw"))) {


            $kiw_ads = $kiw_db->query_first("SELECT * FROM kiwire_campaign_ads WHERE id = '{$kiw_ads}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");


            $kiw_path = dirname(__FILE__, 3) . "/custom/{$_SESSION['tenant_id']}/images/{$kiw_ads[$kiw_type]}";

            if (file_exists($kiw_path) == true) {

                unlink($kiw_path);

            }

            $kiw_db->query("UPDATE kiwire_campaign_ads SET updated_date = NOW(),  `{$kiw_type}` = '' WHERE id = '{$kiw_ads['id']}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");


            echo json_encode(array("status" => "success", "message" => "SUCCESS: Campaign image for [ {$kiw_ads['name']} ] has been deleted.", "data" => null));


        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

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
        'image/gif',
        'image/bmp',
        'video/mp4',
        'application/pdf',
        'audio/ogg',
        'video/ogg',
        'application/ogg'
    );

    return in_array($kiw_temp, $kiw_allow_list);

}