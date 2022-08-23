<?php

$kiw['module'] = "Help -> Database Diagnostic";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

require_once "../includes/include_session.php";
require_once "../includes/include_general.php";
require_once "../includes/include_connection.php";
require_once "../includes/include_config.php";


header("Content-Type: application/json");


if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}


$kiw_db = Database::obtain();

if (in_array($_SESSION['permission'], array("r", "rw"))) {

    $kiw_temps = "mysqlcheck ".SYNC_DB1_DATABASE." -h ".SYNC_DB1_HOST." -u ".SYNC_DB1_USER." -p".SYNC_DB1_PASSWORD." -P". SYNC_DB1_PORT; //connect db at other server (db use proxy)

    if (!empty($kiw_temps)) {

        $kiw_temps = shell_exec($kiw_temps);
        $kiw_temps = explode(PHP_EOL, $kiw_temps);

        foreach ($kiw_temps as $kiw_temp) {
            if (!empty($kiw_temp)) {
        
        
                $kiw_temp = array_filter(explode(" ", $kiw_temp));
        
                $kiw_tables[] = array_values($kiw_temp);
        
        
            }
        
        }

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_tables));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: Unable to execute to check database.", "data" => null));

    }


} else {


    json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));


}