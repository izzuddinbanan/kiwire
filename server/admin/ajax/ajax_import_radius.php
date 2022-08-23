<?php

$kiw['module'] = "Integration -> Realm";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_connection.php";

if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));
}


$tag = uniqid();
$tag = $tag . "_" . date("Y-m-d");

$destination = "../../server/custom/radius_import_$tag.csv";

$result_s = array();
$result_s[] = array("REALM", "STATUS");

$csv_p = $_FILES['csv-file']['tmp_name'];

if (($handle = fopen($csv_p, "r")) !== FALSE) {

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

        $realm           = $kiw_db->escape($data[1]);

        if (!empty($realm)) {

        $sql = "SELECT COUNT(*) AS ccount FROM kiwire_int_radius WHERE realm= '$realm' ";
        $existed = $kiw_db->query_first($sql);

        if ($existed['ccount'] == 0) {

            $rad['id']                = "null";
            $rad['realm']             = $realm;
           
            $rad['auhost']            = $data[2];
            $rad['achost']            = $data[3];
            $rad['secret']            = $data[4];
            $rad['nasid']             = $data[5];
           
            $rad['linkProfile']       = $data[6];
            $rad['expiry']            = $data[7];
            $rad['keyword_str']       = $data[8];
            $rad['data_type']         = $data[9];
           
            $rad['allowed_zone']      = $data[10];
            $rad['profile_linked']    = $data[11];
            $rad['completeusername']  = $data[12];
            $rad['enabled']           = $data[13];

            $kiw_db->insert("kiwire_int_radius", $rad);
            $status = "Success";

        } else {

            $status = "Failed";
        }

        $result_s[] = array("realm" => $realm, "status" => $status);
        }
    }

    fclose($handle);
}

$handle = fopen($result_f, 'w');

foreach ($result_s as $fields) {
    fputcsv($handle, $fields);
}

fclose($handle);

echo json_encode(array("status" => "success", "result" => "radius_import_$tag.csv"));

