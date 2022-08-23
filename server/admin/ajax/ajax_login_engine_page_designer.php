<?php

$kiw['module'] = "Login Engine -> Desiger Tool -> List";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

require_once "../includes/include_session.php";
require_once "../includes/include_general.php";
require_once "../includes/include_connection.php";

require_once dirname(__FILE__, 3) . "/libs/class.sql.helper.php";

if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}


header("Content-Type: application/json");


$action = $_REQUEST['action'];

switch ($action) {

    case "get_tenant": get_tenant(); break;
    case "get_all": get_data(); break;
    case "delete": delete(); break;
    case "duplicate": duplicate(); break;
    default: echo "ERROR: Wrong implementation";

}


function get_data(){

    global $kiw_db;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->fetch_array("SELECT  * FROM kiwire_login_pages WHERE tenant_id = '{$_SESSION['tenant_id']}' LIMIT 100");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }


}


function delete(){


    global $kiw_db;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $id = $kiw_db->escape($_POST['id']);


        if (!empty($id)) {


            $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_login_pages WHERE unique_id = '{$id}' AND tenant_id = '{$_SESSION['tenant_id']}'");

            if (!empty($kiw_temp)) {

                $kiw_db->query("DELETE FROM kiwire_login_pages WHERE unique_id = '{$id}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");


                sync_logger("{$_SESSION['user_name']} updated Page {$kiw_temp['page_name']}", $_SESSION['tenant_id']);

                echo json_encode(array("status" => "success", "message" => "SUCCESS: Page {$kiw_temp['page_name']} has been deleted", "data" => ""));

            } else echo json_encode(array("status" => "failed", "message" => "ERROR: The page is not existed", "data" => ""));


        }



    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => ""));

    }

}


function duplicate(){

    global $kiw_db;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        $kiw_id = $kiw_db->escape($_REQUEST['page_id']);
        $kiw_page = $kiw_db->escape($_REQUEST['page_name']);


        if (!empty($kiw_page)){


            $kiw_existing_content = $kiw_db->query_first("SELECT * FROM kiwire_login_pages WHERE tenant_id = '{$_SESSION['tenant_id']}' AND unique_id = '{$kiw_id}' LIMIT 1");

            if (!empty($kiw_existing_content['content'])) {


                $kiwire_existed['kcount'] = 1;

                while ($kiwire_existed['kcount'] > 0) {


                    $kiw_page_unique = hash("sha256", time() + $_SERVER['name']);

                    $kiw_page_unique = substr($kiw_page_unique, 2, 8);


                    $kiwire_existed = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_login_pages WHERE unique_id = '{$kiw_page_unique}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");

                }


                $kiw_new_page['tenant_id']  = $_SESSION['tenant_id'];

                $kiw_new_page['page_name']  = $kiw_page;
                $kiw_new_page['purpose']    = $kiw_existing_content['purpose'];
                $kiw_new_page['content']    = $kiw_existing_content['content'];

                $kiw_new_page['bg_lg']      = $kiw_existing_content['bg_lg'];
                $kiw_new_page['bg_md']      = $kiw_existing_content['bg_md'];
                $kiw_new_page['bg_sm']      = $kiw_existing_content['bg_sm'];
                $kiw_new_page['bg_css']     = $kiw_existing_content['bg_css'];

                $kiw_new_page['unique_id']      = $kiw_page_unique;
                $kiw_new_page['updated_date']   = "NOW()";
                $kiw_new_page['default_page']   = "n";

                // $kiw_db->query(sql_insert($kiw_db, "kiwire_login_pages", $kiw_new_page));

                $kiw_db->query("INSERT INTO kiwire_login_pages(tenant_id, page_name, purpose, content, bg_lg, bg_md, bg_sm, bg_css, unique_id, updated_date, default_page) VALUE ('{$kiw_new_page['tenant_id']}', '{$kiw_new_page['page_name']}', '{$kiw_new_page['purpose']}', '{$kiw_new_page['content']}', '{$kiw_new_page['bg_lg']}', '{$kiw_new_page['bg_md']}', '{$kiw_new_page['bg_sm']}', '{$kiw_new_page['bg_css']}', '{$kiw_new_page['unique_id']}', NOW(), '{$kiw_new_page['default_page']}')");


                // duplicate the screenshot

                copy(dirname(__FILE__, 3) . "/custom/{$_SESSION['tenant_id']}/thumbnails/{$kiw_id}.png", dirname(__FILE__, 3) . "/custom/{$_SESSION['tenant_id']}/thumbnails/{$kiw_page_unique}.png");

                echo json_encode(array("status" => "success", "message" => "Success: You page has been duplicate with id [{$kiw_page_unique}]", "data" => ""));


            } else {

                echo json_encode(array("status" => "failed", "message" => "ERROR: Empty page or page has been deleted", "data" => ""));

            }


        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please provide a page name", "data" => ""));

        }


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => ""));

    }

}

function get_tenant(){

    echo json_encode(array("status" => "success", "message" => null, "data" => $_SESSION['tenant_id']));

}