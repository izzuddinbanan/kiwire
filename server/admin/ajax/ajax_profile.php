<?php

$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_general.php";
require_once "../includes/include_connection.php";

$user_data = $kiw_db->query_first("SELECT * FROM kiwire_admin WHERE id = '{$_SESSION["id"]}'");

if($user_data){

    csrf($kiw_db->escape($_REQUEST['token']));
    
    $data['fullname']   = $_REQUEST['fullname'];
    $data['email']      = $_REQUEST['email'];

    $img_photo          = $_FILES['photo'];

    if (!empty($img_photo)) {

        $kinfo = finfo_open(FILEINFO_MIME_TYPE);
        $ktype = finfo_file($kinfo, $img_photo['tmp_name']);

        if (file_exists( dirname(__FILE__, 3) . "/custom/{$tenant_id}/profile") == false) {

            mkdir( dirname(__FILE__, 3) . "/custom/{$tenant_id}/profile", 0755,true);

        }


        if (in_array($ktype, array("image/png", "image/jpeg"))) {

            while(true){
                
                $data['photo'] = time() . rand(10, 99) .".png";

                if(!$kiw_db->query_first("SELECT * FROM kiwire_admin WHERE photo='{$data['photo']}'")) break;

            }


            if (file_exists( dirname(__FILE__, 3) . "/custom/{$tenant_id}/profile/{$user_data['photo']}")) {

                system("rm -f ". dirname(__FILE__, 3) . "/custom/{$tenant_id}/profile/{$user_data['photo']}");

            }


            $destination = dirname(__FILE__, 3) . "/custom/{$tenant_id}/profile/{$data['photo']}";

            copy($img_photo['tmp_name'], $destination);


        } else {

            unlink($img_photo['tmp_name']);

        }

    }

    if($kiw_db->update("kiwire_admin", $data, "id = '{$_SESSION["id"]}' AND tenant_id = '{$tenant_id}' LIMIT 1")){

        $_SESSION['photo']      = $data['photo'];
        $_SESSION['full_name']  = $data['fullname'];
        $_SESSION['email']      = $data['email'];

        echo json_encode(array("status" => "success", "message" => "Password has been changed.", "data" => null));
    }
    else{

        echo json_encode(array("status" => "error", "message" => "There is an error occured. Please retry.", "data" => null));
    }

}
else{

    echo json_encode(array("status" => "error", "message" => "Invalid user.", "data" => null));

}
