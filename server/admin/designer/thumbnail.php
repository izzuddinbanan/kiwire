<?php


require_once dirname(__FILE__, 2) . "/includes/include_session.php";


if (!empty($_POST['name']) && !empty($_POST['image'])){


    $kiw_temp = dirname(__FILE__, 3) . "/custom/{$_SESSION['tenant_id']}/thumbnails/";

    if (file_exists($kiw_temp) == false) mkdir($kiw_temp, 0755, true);


    $kiw_image = $_POST['image'];

    $kiw_image = str_replace('data:image/png;base64,', '', $kiw_image);
    $kiw_image = str_replace(' ', '+', $kiw_image);

    $kiw_image = base64_decode($kiw_image);


    $kiw_name = preg_replace('/[^A-Za-z0-9 _ .-]/', '', $_POST['name']);
    $kiw_name = preg_replace('/\s/', '_', $kiw_name);

    if ($kiw_image){

        @file_put_contents("{$kiw_temp}/{$kiw_name}.png", $kiw_image);

        echo json_encode(array("status" => "success", "message" => "", "data" => ""));

    } else {

        echo json_encode(array("status" => "error", "message" => "Invalid payload data", "data" => ""));

    }

} else {

    echo json_encode(array("status" => "error", "message" => "", "data" => ""));

}

