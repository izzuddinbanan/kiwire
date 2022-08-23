<?php

require_once dirname(__FILE__, 2) . "/includes/include_session.php";
require_once dirname(__FILE__, 2) . "/includes/include_connection.php";

header("Content-Type: application/json");

if (!empty($_POST['page_name']) && !empty($_POST['page_content'])){


    if (!empty($_SESSION['tenant_id'])) {


        $kiw_data['content']            = base64_decode($_POST['page_content']);

        $kiw_data['page_name']          = $kiw_db->escape($_POST['page_name']);
        $kiw_data['page_unique_id']     = $kiw_db->escape($_POST['page_unique_id']);
        $kiw_data['page_purpose']       = $kiw_db->escape($_POST['page_purpose']);
        $kiw_data['page_remark']        = $kiw_db->escape($_POST['page_remark']);

        $kiw_data['page_default']       = ($_POST['page_default'] == "true") ? "y" : "n";
        $kiw_data['count_impress']       = ($_POST['count_impress'] == "true") ? "y" : "n";

        if(!empty($_POST['bg_sm'])) $kiw_data['page_bg_sm']    = $kiw_db->escape($_POST['bg_sm']);
        if(!empty($_POST['bg_md'])) $kiw_data['page_bg_md']    = $kiw_db->escape($_POST['bg_md']);
        if(!empty($_POST['bg_lg'])) $kiw_data['page_bg_lg']    = $kiw_db->escape($_POST['bg_lg']);
        if(!empty($_POST['bg_css'])) $kiw_data['page_bg_css']  = $kiw_db->escape($_POST['bg_css']);


        if ($kiw_data['content']) {


            $kiw_update = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_login_pages WHERE unique_id = '{$kiw_data['page_unique_id']}' AND tenant_id = '{$_SESSION['tenant_id']}'");

            $kiw_data['content'] = base64_encode($kiw_data['content']);


            // only one default page should existed

            if ($kiw_data['page_default'] == "y"){

                $kiw_db->query("UPDATE kiwire_login_pages SET updated_date = NOW(), default_page = 'n' WHERE tenant_id = '{$_SESSION['tenant_id']}'");

            }


            // check if we need to update or create new page

            if ($kiw_update['kcount'] > 0) {


                $kiw_background = "";


                if (!empty($kiw_data['page_bg_sm'])){

                    $kiw_background .= ", bg_sm = '{$kiw_data['page_bg_sm']}'";

                }


                if (!empty($kiw_data['page_bg_md'])){

                    $kiw_background .= ", bg_md = '{$kiw_data['page_bg_md']}'";

                }


                if (!empty($kiw_data['page_bg_lg'])){

                    $kiw_background .= ", bg_lg = '{$kiw_data['page_bg_lg']}'";

                }


                if (!empty($kiw_data['page_bg_css'])) {

                    $kiw_background .= ", bg_css = '{$kiw_data['page_bg_css']}'";

                }


                $kiw_db->query("UPDATE kiwire_login_pages SET updated_date = NOW(), page_name = '{$kiw_data['page_name']}', purpose = '{$kiw_data['page_purpose']}', content = '{$kiw_data['content']}', remark = '{$kiw_data['page_remark']}', default_page = '{$kiw_data['page_default']}', count_impress = '{$kiw_data['count_impress']}'{$kiw_background} WHERE tenant_id = '{$_SESSION['tenant_id']}' AND unique_id = '{$kiw_data['page_unique_id']}' LIMIT 1");


            } else {


                $kiw_db->query("INSERT INTO kiwire_login_pages (tenant_id, unique_id, page_name, purpose, content, remark, default_page, bg_lg, bg_md, bg_sm, bg_css, count_impress) VALUE('{$_SESSION['tenant_id']}', '{$kiw_data['page_unique_id']}', '{$kiw_data['page_name']}', '{$kiw_data['page_purpose']}', '{$kiw_data['content']}', '{$kiw_data['page_remark']}', '{$kiw_data['page_default']}', '{$kiw_data['page_bg_lg']}', '{$kiw_data['page_bg_md']}', '{$kiw_data['page_bg_sm']}', '{$kiw_data['page_bg_css']}', '{$kiw_data['count_impress']}')");


            }


            echo json_encode(array("status" => "success", "message" => "", "data" => ""));


        } else {


            echo json_encode(array("status" => "error", "message" => "Invalid data provided", "data" => ""));


        }


    }


}

