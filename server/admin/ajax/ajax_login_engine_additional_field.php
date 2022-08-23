<?php

$kiw['module'] = "Login Engine -> Additional Field";
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


if ($_REQUEST['action'] == "delete") {


    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        $kiw_datas = @file_get_contents(dirname(__FILE__, 3) . "/custom/{$_SESSION['tenant_id']}/data-mapping.json");

        if (empty($kiw_datas)) $kiw_datas = @file_get_contents(dirname(__FILE__, 3) . "/user/templates/kiwire-data-mapping.json");


        $kiw_datas = json_decode($kiw_datas, true);


        $kiw_id = strtolower(preg_replace('/[^a-zA-Z0-9_-]+/', '', $_REQUEST['field']));;



        for ($x = 0; $x < count($kiw_datas); $x++){


            if ($kiw_datas[$x]['field'] == $kiw_id){

                $kiw_datas[$x]['variable'] = "[empty]";
                $kiw_datas[$x]['display']  = "[empty]";
                $kiw_datas[$x]['required'] = "No";

                break;

            }


        }


        @file_put_contents(dirname(__FILE__, 3) . "/custom/{$_SESSION['tenant_id']}/data-mapping.json", json_encode($kiw_datas));


        sync_logger("{$_SESSION['user_name']} deleted Field setting ", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Field has been deleted", "data" => ""));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


} else {

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $kiw_datas = @file_get_contents(dirname(__FILE__, 3) . "/custom/{$_SESSION['tenant_id']}/data-mapping.json");

        if (empty($kiw_datas)) $kiw_datas = @file_get_contents(dirname(__FILE__, 3) . "/user/templates/kiwire-data-mapping.json");


        // decode the value

        $kiw_datas = json_decode($kiw_datas, true);


        for ($x = 0; $x < count($kiw_datas); $x++) {

            if ($kiw_datas[$x]['field'] == $_REQUEST['field']) {

                $kiw_datas[$x]['variable'] = strtolower(preg_replace('/[^a-zA-Z0-9_-]+/', '', $_REQUEST['variable']));
                $kiw_datas[$x]['display']  = $kiw_db->escape($_REQUEST['display']);
                $kiw_datas[$x]['required'] = ($_REQUEST['required'] == "true" ? "Yes" : "No");

                $kiw_respond['variable'] = $kiw_datas[$x]['variable'];
                $kiw_respond['display']  = $kiw_datas[$x]['display'];
                $kiw_respond['required'] = $kiw_datas[$x]['required'];

                break;

            }


        }


        @file_put_contents(dirname(__FILE__, 3) . "/custom/{$_SESSION['tenant_id']}/data-mapping.json", json_encode($kiw_datas));


        sync_logger("{$_SESSION['user_name']} updated Field setting ", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Field has been saved", "data" => $kiw_respond));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}
