<?php

$key = $_REQUEST['key'];
$cloud = $_REQUEST['cloud'];

$data = base64_decode($_REQUEST['data']);

if (!empty($key) && !empty($cloud) && !empty($data)) {

    require_once dirname(__FILE__, 2) . "/includes/connections.php";


    header("Content-Type: application/json");


    $db = Database::obtain();

    $cloud = $db->escape($cloud);
    $key = $db->escape($key);


    $zapier_status = $db->query_first("SELECT enabled FROM kiwire_zapier_data WHERE cloud_id = '{$cloud}' AND api_key = '{$key}' LIMIT 1");


    if ($zapier_status['enable'] == "y") {


        $max_count = @file_get_contents("zapier_max_{$cloud}.log");

        if (!empty($max_count)) $max_count = json_decode($max_count, true);
        else $max_count = array();


        if ($data == "user") {


            if (!isset($max_count['user'])) $max_count['user'] = 0;


            $return = $db->fetch_array("SELECT SQL_CACHE user_id AS id,username,email,phone,actdate AS active_date,status,createdate AS create_date,expiry AS account_expiry_on,plan AS profile,remark FROM kiwire_user WHERE user_id > {$max_count['user']} AND prepaid IS NULL AND cloud_id = '{$cloud}' ORDER BY user_id DESC LIMIT 100");


            foreach ($return as $item) {

                if ($item['user_id'] > $max_count['user']) {

                    $max_count['user'] = $item['user_id'];

                }

            }


        } elseif ($data == "login_new") {


            if (!isset($max_count['login_new'])) $max_count['login_new'] = 0;


            $return = $db->fetch_array("SELECT SQL_CACHE radacctid AS id,username,acctstarttime AS start_time,framedipaddress AS ip_address,dclass AS device_class,dbrand AS device_brand,dmodel AS device_model,dos AS device_os FROM radacct WHERE radacctid > {$max_count['login_new']} AND cloud_id = '{$cloud}' AND acctstoptime IS NULL AND acctterminatecause = '' ORDER BY acctstarttime DESC LIMIT 100");


            foreach ($return as $item) {

                if ($item['id'] > $max_count['login_new']) {

                    $max_count['login_new'] = $item['id'];

                }

            }



        } elseif ($data == "login_dc") {


            if (!isset($max_count['login_dc'])) $max_count['login_dc'] = 0;


            $return = $db->fetch_array("SELECT SQL_CACHE radacctid AS id,username,acctstarttime AS start_time,acctstoptime AS stop_time, (acctinputoctets + acctoutputoctets) AS quota, acctsessiontime AS duration_time,framedipaddress AS ip_address,dclass AS device_class,dbrand AS device_brand,dmodel AS device_model,dos AS device_os FROM radacct WHERE radacctid > {$max_count['login_dc']} AND cloud_id = '{$cloud}' AND acctstoptime IS NOT NULL AND acctterminatecause <> '' ORDER BY acctstarttime DESC LIMIT 100");


            foreach ($return as $item) {

                if ($item['id'] > $max_count['login_dc']) {

                    $max_count['login_dc'] = $item['id'];

                }

            }


        } elseif ($data == "social") {


            if (!isset($max_count['social'])) $max_count['social'] = 0;


            $return = $db->fetch_array("SELECT SQL_CACHE id,social,username,fullname,email,gender,age_range,location FROM kiwire_social_data WHERE id > {$max_count['social']} AND cloud_id = '{$cloud}' ORDER BY id DESC lIMIT 100");


            foreach ($return as $item) {

                if ($item['id'] > $max_count['social']) {

                    $max_count['social'] = $item['id'];

                }

            }


        } elseif ($data == "signup") {


            $columns = $db->query_first("SELECT SQL_CACHE extra_data FROM kiwire_signup_public WHERE cloud_id = '{$cloud}' LIMIT 1");

            if (strlen($columns['extra_data']) > 0) $columns = explode(",", $columns['extra_data']);

            foreach ($columns as $column){

                $column_str .= $column . ",";

            }

            unset($column);
            unset($columns);

            $column_str = trim($column_str, ",");

            if (!empty($column_str)) $column_str = "," . $column_str;


            if (!isset($max_count['signup'])) $max_count['signup'] = 0;


            $return = $db->fetch_array("SELECT SQL_CACHE id,cdate AS create_date,username,{$column_str} FROM kiwire_signup_visitor_data WHERE id > {$max_count['signup']} AND cloud_id = '{$cloud}' ORDER BY cdate DESC LIMIT 100");


            foreach ($return as $item) {

                if ($item['id'] > $max_count['signup']) {

                    $max_count['signup'] = $item['id'];

                }

            }


        } else {

            $return = array(array("status" => "success", "message" => "no data"));

        }


        @file_put_contents("zapier_max_{$cloud}.log", json_encode($max_count));


        echo json_encode($return);


    } else {

        echo json_encode(array(array("status" => "zapier integration disabled")));

    }

}
