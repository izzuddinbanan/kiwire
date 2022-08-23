<?php 
header("Content-Type: application/json");

require_once dirname(__FILE__, 2) . "/includes/include_session.php";
require_once dirname(__FILE__, 2) . "/includes/include_general.php";
require_once dirname(__FILE__, 3) . "/admin/includes/include_connection.php";



$kiw_request['mac_address']     = $kiw_db->escape($_REQUEST['mac_address']);
$kiw_request['survey_id']       = $kiw_db->escape($_REQUEST['survey_id']);


$kiw_question_list = $kiw_db->query_first("SELECT mac_address FROM kiwire_survey_respond WHERE mac_address = '{$kiw_request['mac_address']  }' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' AND unique_id = '{$kiw_request['survey_id']}' LIMIT 1");

echo json_encode(array("answered" => (empty($kiw_question_list) ? "false" : "true") ));