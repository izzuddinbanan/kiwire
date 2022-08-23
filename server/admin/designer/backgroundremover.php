<?php

require_once dirname(__FILE__, 2) . "/includes/include_session.php";
require_once dirname(__FILE__, 2) . "/includes/include_connection.php";


$kiw_temp = dirname(__FILE__, 3) . "/";


header("Content-Type: application/json");


$kiw_id = $kiw_db->escape($_REQUEST['page']);

if (!empty($kiw_id)) {


    $kiw_background = $kiw_db->query_first("SELECT * FROM kiwire_login_pages WHERE unique_id = '{$kiw_id}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");

    if (file_exists($kiw_temp . $kiw_background['bg_lg'])) unlink($kiw_temp . ltrim($kiw_background['bg_lg'], "/"));
    if (file_exists($kiw_temp . $kiw_background['bg_md'])) unlink($kiw_temp . ltrim($kiw_background['bg_md'], "/"));
    if (file_exists($kiw_temp . $kiw_background['bg_sm'])) unlink($kiw_temp . ltrim($kiw_background['bg_sm'], "/"));

    $kiw_db->query("UPDATE kiwire_login_pages SET updated_date = NOW(), bg_lg = '', bg_md = '', bg_sm = '', bg_css = '' WHERE unique_id = '{$kiw_id}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");

    echo json_encode(array("status" => "success"));


}
