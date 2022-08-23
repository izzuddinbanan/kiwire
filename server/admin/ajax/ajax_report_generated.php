<?php

$kiw['module'] = "Report -> Generated Reports";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_connection.php";
require_once "../includes/include_general.php";


if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}


$action = $_REQUEST['action'];


switch ($action) {

    case "get_all": get_data(); break;
    case "delete": delete(); break;
    case "download": download(); break;
    default: echo "ERROR: Wrong implementation";
}


function get_data() {

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_reports = scandir("/var/www/kiwire/server/custom/{$_SESSION['tenant_id']}/reports");

        $i = 1;
        $key = 0;
        foreach($kiw_reports as $file){

            if(!in_array($file, array(".", ".."))){

                $file_parts = pathinfo($file);

                if($file_parts['extension'] == 'csv'){
                    $file = str_replace('.csv', '', $file);
                    $ext = 'Completed';

                    if(file_exists( dirname(__FILE__, 3) . "/custom/{$_SESSION['tenant_id']}/reports/{$file}.log")){
                        $ext = 'Pending';
                    }

                    $kiw_data[$key] = [$i,$file,$ext];

                    $i++;
                    $key++;

                }


            }
            
        }



        echo json_encode(array('data' => $kiw_data));


    }



}


function delete()
{
    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        $filename = trim($kiw_db->escape($_POST['filename']));

        if (!empty($filename)) {


            if (file_exists( dirname(__FILE__, 3) . "/custom/{$tenant_id}/reports/{$filename}")) {

                unlink(dirname(__FILE__, 3) . "/custom/{$tenant_id}/reports/{$filename}");

                echo json_encode(array("status" => "success", "message" => "SUCCESS: File {$filename} deleted ", "data" => null));
                
            }
            else{

                echo json_encode(array("status" => "error", "message" => "ERROR: Something Wrong.", "data" => null));

            }
            
        }
        else {


            echo json_encode(array("status" => "error", "message" => "ERROR: Device not found.", "data" => null));


        }



    } else {


        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));


    }


}


function download()
{
    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        $filename = trim($kiw_db->escape($_POST['filename']));

        if (!empty($filename)) {


            if (file_exists( dirname(__FILE__, 3) . "/custom/{$tenant_id}/reports/{$filename}")) {

                echo json_encode(array("status" => "success", "url" => "/custom/{$tenant_id}/reports/{$filename}"));
                
            }
            
        }
        
    }

}