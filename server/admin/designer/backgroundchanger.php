<?php

require_once dirname(__FILE__, 2) . "/includes/include_session.php";
require_once dirname(__FILE__, 2) . "/includes/include_connection.php";


$kiw_temp['path'] = dirname(__FILE__, 3) . "/custom/{$_SESSION['tenant_id']}/backgrounds/";


header("Content-Type: application/json");


// create if folder not existed

if (file_exists($kiw_temp['path']) == false){

    mkdir($kiw_temp['path'], 0755, true);

}


foreach (array("background-sm", "background-md", "background-lg") as $background){


    if (isset($_FILES[$background]) && !empty($_FILES[$background]["tmp_name"])){


        if (getimagesize($_FILES[$background]["tmp_name"])[0] > 0){


            $kiw_file_name = preg_replace('/[^A-Za-z0-9 _.-]/', '', $_FILES[$background]["name"]);
            $kiw_file_name = str_replace(" ", "", $kiw_file_name);


            if (move_uploaded_file($_FILES[$background]["tmp_name"], $kiw_temp['path'] . $kiw_file_name)){

                $kiw_temp['success'] = true;

                $kiw_temp['user_path'][$background] = substr($kiw_temp['path'], strpos($kiw_temp['path'], "/server/") + 7) . $kiw_file_name;

            }


        } else {


            die(json_encode(array("status" => "error", "path" => "[{$_FILES[$background]["name"]}] possible invalid image file.")));


        }


    }


}


if ($kiw_temp['success']) {


    echo json_encode(array("status" => "success", "path" => $kiw_temp['user_path']));


} else {

    echo json_encode(array("status" => "error", "path" => "No file uploaded"));

}