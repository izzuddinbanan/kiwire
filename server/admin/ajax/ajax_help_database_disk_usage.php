<?php

$kiw['module'] = "Help -> Database Disk Usage";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_connection.php";

if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));
    
}

$action = $_REQUEST['action'];

switch ($action) {

    case "get_all": get_all(); break;
    
    default: echo "ERROR: Wrong implementation";
}


function get_all(){

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        $sql = "
            SELECT
                IF(ISNULL(DB)+ISNULL(ENGINE)=2,'Database total',
                CONCAT(DB,' ',IFNULL(ENGINE,'Total'))) \"stats\",
                LPAD(CONCAT(FORMAT(DAT/POWER(1024,pw1),2),' ',
                SUBSTR(units,pw1*2+1,2)),17,' ') \"data\",
                LPAD(CONCAT(FORMAT(NDX/POWER(1024,pw2),2),' ',
                SUBSTR(units,pw2*2+1,2)),17,' ') \"index\",
                LPAD(CONCAT(FORMAT(TBL/POWER(1024,pw3),2),' ',
                SUBSTR(units,pw3*2+1,2)),17,' ') \"total\"
            FROM
            (
                SELECT DB,ENGINE,DAT,NDX,TBL,
                IF(px>4,4,px) pw1,IF(py>4,4,py) pw2,IF(pz>4,4,pz) pw3
                FROM
                (SELECT *,
                    FLOOR(LOG(IF(DAT=0,1,DAT))/LOG(1024)) px,
                    FLOOR(LOG(IF(NDX=0,1,NDX))/LOG(1024)) py,
                    FLOOR(LOG(IF(TBL=0,1,TBL))/LOG(1024)) pz
                FROM
                (SELECT
                    DB,ENGINE,
                    SUM(data_length) DAT,
                    SUM(index_length) NDX,
                    SUM(data_length+index_length) TBL
                FROM
                (
                    SELECT table_schema DB,ENGINE,data_length,index_length FROM
                    information_schema.tables WHERE table_schema NOT IN
                    ('information_schema','performance_schema','mysql')
                    AND ENGINE IS NOT NULL
                ) AAA GROUP BY DB,ENGINE WITH ROLLUP
            ) AAA) AA) A,(SELECT ' BKBMBGBTB' units) B";


        $kiw_temp = $kiw_db->fetch_array($sql);


        for ($i = 0; $i < count($kiw_temp); $i++){

            $kiw_temp[$i]['stats'] = str_replace(array("kiwire", "Kiwire"), "System", $kiw_temp[$i]['stats']);

        }
                
        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));

    }

}
