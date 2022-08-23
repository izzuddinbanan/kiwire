<?php

require_once dirname(__FILE__, 2) . "/includes/include_session.php";

require_once dirname(__FILE__, 3) . "/admin/includes/include_connection.php";
require_once dirname(__FILE__, 3) . "/admin/includes/include_general.php";

require_once dirname(__FILE__, 3) . "/libs/jsonpath/autoload.php";

use Flow\JSONPath\JSONPath;


$kiw_position = $_REQUEST['position'];
$kiw_source = $_REQUEST['source'];


if (empty($kiw_source)) $kiw_source = "internal";


header("Content-Type: application/json");


// for pre and post login, try to populate persona

if (in_array($kiw_position, array("pre", "post"))){


    if (empty($_SESSION['user']['persona'])) {


        // get the persona setting

        $kiw_personas = $kiw_db->fetch_array("SELECT SQL_CACHE * FROM kiwire_persona WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}'");


        if (is_array($kiw_personas) && count($kiw_personas) > 0) {


            // try pull data for this device

            $kiw_last_account = $kiw_db->query_first("SELECT last_account FROM kiwire_device_history WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' AND mac_address = '{$_SESSION['user']['mac']}' LIMIT 1");

            if ($kiw_last_account) {


                $kiw_user = $kiw_db->query_first("SELECT * FROM kiwire_account_info WHERE tenant_id = '{$kiw_last_account['tenant_id']}' AND username = '{$kiw_last_account['username']}' LIMIT 1");


                foreach ($kiw_personas as $kiw_persona) {


                    $kiw_matched = array();

                    $kiw_rules = json_decode($kiw_persona['rule'], true);


                    if ($kiw_rules) {

                        foreach ($kiw_rules as $kiw_rule) {


                            if ($kiw_rule['operator'] == "is" && $kiw_user[$kiw_user['field']] == $kiw_rule['value']) {

                                $kiw_matched[] = true;


                            } elseif ($kiw_rule['operator'] == "is_not" && $kiw_user[$kiw_user['field']] != $kiw_rule['value']) {

                                $kiw_matched[] = true;


                            } elseif ($kiw_rule['operator'] == "contain" && (strpos($kiw_user[$kiw_user['field']], $kiw_rule['value']))) {

                                $kiw_matched[] = true;


                            } elseif ($kiw_rule['operator'] == "not_contain" && (strpos($kiw_user[$kiw_user['field']], $kiw_rule['value']) == false)) {

                                $kiw_matched[] = true;


                            } elseif ($kiw_rule['operator'] == "larger_than" && $kiw_user[$kiw_user['field']] > $kiw_rule['value']) {

                                $kiw_matched[] = true;


                            } elseif ($kiw_rule['operator'] == "smaller_than" && $kiw_user[$kiw_user['field']] < $kiw_rule['value']) {

                                $kiw_matched[] = true;


                            } elseif ($kiw_rule['operator'] == "start_with" && (substr($kiw_user[$kiw_user['field']], 0, strlen($kiw_rule['value'])) == $kiw_rule['value'])) {

                                $kiw_matched[] = true;


                            } elseif ($kiw_rule['operator'] == "end_with" && (substr($kiw_user[$kiw_user['field']], (strlen($kiw_rule['value']) * -1)) == $kiw_rule['value'])) {

                                $kiw_matched[] = true;


                            }


                        }


                        if (count($kiw_matched) == count($kiw_rules)) {


                            $_SESSION['user']['persona'] = $kiw_persona['name'];

                            break;


                        }


                    }


                }


            }


        }


        if (empty($_SESSION['user']['persona'])) $_SESSION['user']['persona'] = "no-persona";


    }


    $kiw_time = array();

    $kiw_time['full']   = sync_tolocaltime(date("Y-m-d H:i:s"), $_SESSION['system']['timezone']);
    $kiw_time['hour']   = date("H", strtotime($kiw_time['full']));
    $kiw_time['minute'] = date("i", strtotime($kiw_time['full']));
    $kiw_time['date']   = date("Y-m-d", strtotime($kiw_time['full']));
    $kiw_time['second'] = strtotime($kiw_time['full']);


    if ($kiw_position == "pre") $kiw_position = array("connect", "1stlogin", "recurring", "milestone");
    else $kiw_position = array("login");


    $kiw_campaigns = $kiw_cache->get("CAMPAIGN_DATA_ADS:{$_SESSION['controller']['tenant_id']}");


    if (empty($kiw_campaigns)) {


        $kiw_campaigns = $kiw_db->fetch_array("SELECT SQL_CACHE * FROM kiwire_campaign_manager WHERE date_start < '{$kiw_time['full']}' AND date_end > '{$kiw_time['full']}' AND c_trigger IN ('" . implode("','", $kiw_position) . "') AND tenant_id = '{$_SESSION['controller']['tenant_id']}' AND status = 'active' ORDER BY c_order ASC LIMIT 20");

        if (empty($kiw_campaigns)) $kiw_campaigns = array("dummy" => true);

        $kiw_cache->set("CAMPAIGN_DATA_ADS:{$_SESSION['controller']['tenant_id']}", $kiw_campaigns, 1800);


    }


    // split random campaign and non-random


    $kiw_campaign_not_random = [];

    $kiw_campaign_random = [];


    foreach ($kiw_campaigns as $kiw_campaign){

        if ($kiw_campaign['c_order'] != "0") {

            $kiw_campaign_not_random[] = $kiw_campaign;

        } else {

            $kiw_campaign_random[] = $kiw_campaign;

        }

    }


    unset($kiw_campaigns);


    // get the campaign setting

    $kiw_campaign_setting = $kiw_cache->get("CAMPAIGN_CONF:{$_SESSION['controller']['tenant_id']}");

    if (empty($kiw_campaign_setting)) {

        $kiw_campaign_setting = $kiw_db->query_first("SELECT campaign_autoplay,campaign_multi_ads,campaign_wait_second FROM kiwire_clouds WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

        $kiw_cache->set("CAMPAIGN_CONF:{$_SESSION['controller']['tenant_id']}", $kiw_campaign_setting, 1800);

    }


    // shuffle the random campaign

    shuffle($kiw_campaign_random);


    // check if need to display multiple or single ads

    if (is_array($kiw_campaign_not_random) && is_array($kiw_campaign_random)) {

        $kiw_campaigns = array_merge($kiw_campaign_random, $kiw_campaign_not_random);

    } elseif (is_array($kiw_campaign_not_random)) {

        $kiw_campaigns = $kiw_campaign_not_random;

    } elseif (is_array($kiw_campaign_random)) {

        $kiw_campaigns = $kiw_campaign_random;

    }


    // unset to save memory

    unset($kiw_campaign_random);

    unset($kiw_campaign_not_random);


    if ($kiw_campaign_setting['campaign_multi_ads'] !== "y") {

        $kiw_campaigns = [$kiw_campaigns[0]];

    }


    foreach ($kiw_campaigns as $kiw_campaign){


        // check if expiry or impress reached max

        if ($kiw_campaign['expire_click'] > 0 && $kiw_campaign['current_click'] >= $kiw_campaign['expire_click']){

            continue;

        }

        if ($kiw_campaign['expire_impress'] > 0 && $kiw_campaign['current_impress'] >= $kiw_campaign['expire_impress']){

            continue;

        }



        // check for campaign zone or persona

        if ($kiw_campaign['target'] == "zone"){

            if ($kiw_campaign['target_value'] == "custom"){


                if (!empty($kiw_campaign['target_option'])) {


                    // if zone set for ads not matched with the current zone, then skip

                    if (preg_match("/{$kiw_campaign['target_option']}/i", $_SESSION['user']['zone']) == false) {

                        continue;

                    }


                }


            } else {

                if ($_SESSION['user']['zone'] != $kiw_campaign['target_value']) {

                    continue;

                }

            }


        } elseif ($kiw_campaign['target'] == "persona"){

            if ($_SESSION['user']['persona'] != $kiw_campaign['target_value']){

                continue;

            }


        }


        // check for interval

        if ($kiw_campaign['c_interval'] == "timeframe"){

            $kiw_time_start = strtotime(date("Y-m-d H:i:s", strtotime("{$kiw_time['date']} {$kiw_campaign['c_interval_time_start']}:00")));
            $kiw_time_stop = strtotime(date("Y-m-d H:i:s", strtotime("{$kiw_time['date']} {$kiw_campaign['c_interval_time_stop']}:00")));

            if ($kiw_time['second'] < $kiw_time_start || $kiw_time['second'] > $kiw_time_stop){

                continue;

            }


        }


        // check for action

        if ($kiw_campaign['action'] != "ads"){


            if ($kiw_campaign['action'] == "redirect"){


                $kiw_campaigns_result['campaign-1'][] = array(
                    "type"  => "redirect",
                    "url"   => $kiw_campaign['action_value'],
                    "image" => "",
                    "name"  => "",
                );


            } else {


                $kiw_user = $kiw_db->query_first("SELECT tenant_id,username,email_address,phone_number,fullname FROM kiwire_account_auth WHERE username = '{$_SESSION['user']['user-name']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

                if ($kiw_user){


                    if ($kiw_campaign['action_method'] == "sms"){


                        // send sms to user

                        if (!empty($kiw_user['phone_number'])) {


                            $kiw_content = $kiw_db->query_first("SELECT SQL_CACHE *  FROM kiwire_html_template WHERE name = '{$kiw_campaign['action_value']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");
                            $kiw_content = strip_tags($kiw_content['content']);

                            $kiw_content = str_replace("{{tenant_id}}", $kiw_user['tenant_id'], $kiw_content);
                            $kiw_content = str_replace("{{username}}", $kiw_user['username'], $kiw_content);
                            $kiw_content = str_replace("{{fullname}}", $kiw_user['fullname'], $kiw_content);


                            $kiw_sms['action']       = "send_sms";
                            $kiw_sms['tenant_id']    = $_SESSION['controller']['tenant_id'];
                            $kiw_sms['phone_number'] = $kiw_user['phone_number'];
                            $kiw_sms['content']      = $kiw_content;


                            unset($kiw_content);


                            $kiw_temp = curl_init();

                            curl_setopt($kiw_temp, CURLOPT_URL, "http://127.0.0.1:9956");
                            curl_setopt($kiw_temp, CURLOPT_POST, true);
                            curl_setopt($kiw_temp, CURLOPT_POSTFIELDS, http_build_query($kiw_sms));
                            curl_setopt($kiw_temp, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($kiw_temp, CURLOPT_TIMEOUT, 5);
                            curl_setopt($kiw_temp, CURLOPT_CONNECTTIMEOUT, 5);

                            unset($kiw_content);

                            curl_exec($kiw_temp);


                        }



                    } elseif ($kiw_campaign['action_method'] == "email") {


                        // send email to user

                        $kiw_email_content = $kiw_db->query_first("SELECT SQL_CACHE *  FROM kiwire_html_template WHERE name = '{$kiw_campaign['action_value']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

                        $kiw_content = str_replace("{{tenant_id}}", $kiw_user['tenant_id'], $kiw_email_content['content']);
                        $kiw_content = str_replace("{{username}}", $kiw_user['username'], $kiw_content);
                        $kiw_content = str_replace("{{fullname}}", $kiw_user['fullname'], $kiw_content);

                        $kiw_email['action']        = "send_email";
                        $kiw_email['tenant_id']     = $_SESSION['controller']['tenant_id'];
                        $kiw_email['email_address'] = $kiw_user['username'];
                        $kiw_email['subject']       = $kiw_email_content['subject'];
                        $kiw_email['content']       = $kiw_content;
                        $kiw_email['name']          = $kiw_user['fullname'];


                        // send email to agent

                        $kiw_temp = curl_init();

                        curl_setopt($kiw_temp, CURLOPT_URL, "http://127.0.0.1:9956");
                        curl_setopt($kiw_temp, CURLOPT_POST, true);
                        curl_setopt($kiw_temp, CURLOPT_POSTFIELDS, http_build_query($kiw_email));
                        curl_setopt($kiw_temp, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($kiw_temp, CURLOPT_TIMEOUT, 5);
                        curl_setopt($kiw_temp, CURLOPT_CONNECTTIMEOUT, 5);

                        unset($kiw_email);

                        curl_exec($kiw_temp);

                        unset($kiw_temp);


                        unset($kiw_content);


                    } elseif ($kiw_campaign['action_method'] == "api"){


                        // send api to user

                        $kiw_content = str_replace("{{tenant_id}}", $kiw_user['tenant_id'], $kiw_campaign['action_value']);
                        $kiw_content = str_replace("{{username}}", $kiw_user['username'], $kiw_content);
                        $kiw_content = str_replace("{{campaign}}", $kiw_position, $kiw_content);
                        $kiw_content = str_replace("{{fullname}}", $kiw_user['fullname'], $kiw_content);

                        $kiw_temp = curl_init();

                        curl_setopt($kiw_temp, CURLOPT_URL, $kiw_content);
                        curl_setopt($kiw_temp, CURLOPT_POST, false);
                        curl_setopt($kiw_temp, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($kiw_temp, CURLOPT_TIMEOUT, 5);
                        curl_setopt($kiw_temp, CURLOPT_CONNECTTIMEOUT, 5);

                        unset($kiw_content);

                        curl_exec($kiw_temp);


                    } elseif ($kiw_campaign['action_method'] == "webpush"){


                        $kiw_push['action']   = "webpush";
                        $kiw_push['username'] = $kiw_user['username'];
                        $kiw_push['url']      = $kiw_campaign['action_value'];

                        // send web push

                        $kiw_temp = curl_init();

                        curl_setopt($kiw_temp, CURLOPT_URL, $kiw_campaign['action_value']);
                        curl_setopt($kiw_temp, CURLOPT_POST, true);
                        curl_setopt($kiw_temp, CURLOPT_POSTFIELDS, http_build_query($kiw_push));
                        curl_setopt($kiw_temp, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($kiw_temp, CURLOPT_TIMEOUT, 5);
                        curl_setopt($kiw_temp, CURLOPT_CONNECTTIMEOUT, 5);


                        unset($kiw_push);

                        curl_exec($kiw_temp);


                    }


                }


            }



        } else {


            $kiw_ads = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_campaign_ads WHERE name = '{$kiw_campaign['action_value']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");


            if ($kiw_ads) {


                if (empty($kiw_campaign['c_space'])) $kiw_campaign['c_space'] = "campaign-1";


                if (!empty($kiw_ads['captcha_txt']) && empty($kiw_campaigns_result['captcha'])) {

                    $kiw_campaigns_result['captcha'] = $kiw_ads['name'];

                }


                if ($kiw_ads['type'] != "json") {


                    $kiw_ads_temp["type"]       = $kiw_ads['type'];
                    $kiw_ads_temp["name"]       = "{$kiw_campaign['id']} || {$kiw_ads['name']}";
                    $kiw_ads_temp["viewport"]   = $kiw_ads['viewport'];
                    $kiw_ads_temp["source"]     = "internal";
                    $kiw_ads_temp["desc"]       = $kiw_campaign['remark'];
                    $kiw_ads_temp["start"]      = $kiw_campaign['date_start'];
                    $kiw_ads_temp["end"]        = $kiw_campaign['date_end'];


                    if ($kiw_ads_temp['type'] == "img"){


                        $kiw_ads_temp["url"] = urlencode(sync_encrypt($kiw_ads['link']));


                        if (strtolower($_SESSION['user']['class']) == "tablet"){

                            $kiw_ads_temp["image"] = $kiw_ads['fn_tablet'];

                        } elseif (strtolower($_SESSION['user']['class']) == "desktop") {

                            $kiw_ads_temp["image"] = $kiw_ads['fn_desktop'];

                        } else {

                            $kiw_ads_temp["image"] = $kiw_ads['fn_phone'];

                        }


                        // if no target device background, use whatever image available

                        if (empty($kiw_ads_temp["image"])){

                            foreach (array("fn_desktop", "fn_tablet", "fn_phone") as $kiw_type){

                                if (!empty($kiw_ads[$kiw_type])){

                                    $kiw_ads_temp["image"] = $kiw_ads[$kiw_type];

                                    break;

                                }

                            }

                        }

                        if (strlen($kiw_ads_temp["image"]) > 0){

                            $kiw_ads_temp["image"] = "/custom/{$_SESSION['controller']['tenant_id']}/images/{$kiw_ads_temp["image"]}";

                        }


                    } elseif ($kiw_ads_temp['type'] == "youtube"){

                        $kiw_ads_temp["url"] = urlencode($kiw_ads['link']);

                    } elseif ($kiw_ads_temp['type'] == "vid"){

                        $kiw_ads_temp["url"] = "/custom/{$_SESSION['controller']['tenant_id']}/images/" . urlencode($kiw_ads['fn_desktop']);
                        $kiw_ads_temp["image"] = $kiw_ads['link'];

                    }



                    $kiw_campaigns_result[$kiw_campaign['c_space']][] = $kiw_ads_temp;

                    unset($kiw_ads_temp);


                } else {


                    // get content for aem

                    $kiw_unique = base64_encode($kiw_ads['name']);

                    $kiw_temp = $kiw_cache->get("CAMPAIGN_JSON:{$_SESSION['controller']['tenant_id']}:{$kiw_unique}");

                    if (!is_array($kiw_temp)) {

                        $kiw_temp = curl_init();

                        curl_setopt($kiw_temp, CURLOPT_URL, $kiw_ads['json_url']);
                        curl_setopt($kiw_temp, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($kiw_temp, CURLOPT_TIMEOUT, 10);
                        curl_setopt($kiw_temp, CURLOPT_CONNECTTIMEOUT, 10);

                        $kiw_temp = curl_exec($kiw_temp);
                        $kiw_temp = json_decode($kiw_temp);

                        if ($kiw_temp) $kiw_cache->set("CAMPAIGN_JSON:{$_SESSION['controller']['tenant_id']}:{$kiw_unique}", $kiw_temp, 1800);
                        else $kiw_cache->set("CAMPAIGN_JSON:{$_SESSION['controller']['tenant_id']}:{$kiw_unique}", array("dummy" => true), 1800);

                    }


                    if (is_object($kiw_temp) && !empty($kiw_ads['json_path'])) {


                        $kiw_temp = (new JSONPath($kiw_temp))->find($kiw_ads['json_path']);


                        // Order their AEM campaign based on the tags number

                        $kiw_tags_tenant = explode("-", $_SESSION['controller']['tenant_id']);

                        $kiw_tags_tenant = strtolower("{$kiw_tags_tenant[1]}_wifi");


                        foreach (range(1, count($kiw_temp)) as $kiw_range) {

                            foreach ($kiw_temp as $kiw_pending_sort) {


                                // do checking, if missing data then continue

                                if (!isset($kiw_pending_sort['jcr:title']) || empty($kiw_pending_sort['jcr:title'])) continue;

                                if (!isset($kiw_pending_sort['shortdescription']) || empty($kiw_pending_sort['shortdescription'])) continue;

                                if (!isset($kiw_pending_sort['enddate']) || empty($kiw_pending_sort['enddate'])) continue;

                                if (!isset($kiw_pending_sort['startdate']) || empty($kiw_pending_sort['startdate'])) continue;



                                foreach ($kiw_pending_sort["cq:tags"] as $kiw_tags) {


                                    $kiw_tags_comp = explode("_", $kiw_tags);


                                    if (strpos($kiw_tags, $kiw_tags_tenant) == true && $kiw_tags_comp[2] == $kiw_range) {


                                        $kiw_campaign_sorted[$kiw_tags_comp[2]][] = $kiw_pending_sort;


                                    }


                                }



                            }


                        }


                        unset($kiw_pending_sort);

                        unset($kiw_temp);


                        $kiw_temp = array();


                        asort($kiw_campaign_sorted);


                        foreach ($kiw_campaign_sorted as $kiw_campaign_temp) {

                            foreach ($kiw_campaign_temp as $kiw_campaign_restore) {


                                $kiw_temp[] = $kiw_campaign_restore;


                            }

                        }


                        $kiw_ads_count = 0;


                        // set the max number of ads if not set to 5

                        if ($kiw_ads['ads_max_no'] < 1) $kiw_ads['ads_max_no'] = 5;


                        // if set to random, shuffle the data

                        if ($kiw_ads['random'] == "y") {

                            $kiw_random = range(0, (count($kiw_temp) - 1));

                            shuffle($kiw_random);
                            shuffle($kiw_random);


                            foreach ($kiw_random as $kiw_range){

                                $kiw_temp_data[] = $kiw_temp[$kiw_range];

                            }


                            $kiw_temp = $kiw_temp_data;

                            unset($kiw_temp_data);


                        }


                        $kiw_mapping = json_decode(base64_decode($kiw_ads['mapping']), true);

                        $kiw_base_url = parse_url($kiw_ads['json_url']);

                        $kiw_temp_url = $kiw_base_url['port'];


                        if (!empty($kiw_temp_url) && ($kiw_temp_url != 80 || $kiw_temp_url != 443)){

                            $kiw_temp_url = ":{$kiw_temp_url}";

                        } else $kiw_temp_url = "";


                        $kiw_base_url = $kiw_base_url['scheme'] . "://" . $kiw_base_url['host'] . $kiw_temp_url . "/";


                        // collect each data

                        foreach ($kiw_temp as $kiw_json_ads) {


                            if ($kiw_ads_count >= $kiw_ads['ads_max_no']) break;
                            else $kiw_ads_count++;


                            $kiw_temp_url = $kiw_json_ads[$kiw_mapping['image']];


                            if (substr($kiw_temp_url, 0, 4) != "http"){


                                $kiw_temp_url = explode("/", ltrim($kiw_temp_url, "/"));

                                for ($x = 0; $x < count($kiw_temp_url); $x++) {

                                    $kiw_temp_url[$x] = rawurlencode(rawurldecode($kiw_temp_url[$x]));

                                }


                                $kiw_temp_url = implode("/", $kiw_temp_url);

                                $kiw_temp_url = ltrim($kiw_temp_url, "/");

                                $kiw_temp_url = $kiw_base_url . $kiw_temp_url;


                            }


                            $kiw_campaigns_result[$kiw_campaign['c_space']][] = array(
                                "type"      => "img",
                                "url"       => sync_encrypt($kiw_json_ads[$kiw_mapping['url']]),
                                "image"     => $kiw_temp_url,
                                "name"      => "{$kiw_campaign['id']} || " . htmlentities(substr($kiw_json_ads[$kiw_mapping['name']], 0, 60), ENT_QUOTES),
                                "desc"      => htmlentities($kiw_json_ads[$kiw_mapping['desc']], ENT_QUOTES),
                                "start"     => (is_int($kiw_json_ads[$kiw_mapping['start']]) ? date("Y-m-d", substr($kiw_json_ads[$kiw_mapping['start']], 0, 10)) : $kiw_json_ads[$kiw_mapping['start']]),
                                "end"       => (is_int($kiw_json_ads[$kiw_mapping['end']]) ? date("Y-m-d", substr($kiw_json_ads[$kiw_mapping['end']], 0, 10)) : $kiw_json_ads[$kiw_mapping['end']]),
                                "source"    => "external",
                                "viewport"  => $kiw_ads['viewport']
                            );


                        }

                    }


                }

            }


        }


    }


    $kiw_campaigns_result['autoplay'] = $kiw_campaign_setting['campaign_autoplay'];
    $kiw_campaigns_result['multiple'] = $kiw_campaign_setting['campaign_multi_ads'];
    $kiw_campaigns_result['second']   = $kiw_campaign_setting['campaign_wait_second'];


    echo json_encode(array("status" => "success", "data" => $kiw_campaigns_result));


} elseif ($kiw_position == "impress"){


    $kiw_temp = $kiw_db->escape($_REQUEST['name']);

    $kiw_temp = urldecode(base64_decode($kiw_temp));


    if (strpos($kiw_temp, "||") == false){

        $kiw_temp = base64_decode($kiw_temp);

    }


    if (strpos($kiw_temp, "||")) {


        $kiw_temp = base64_encode(urlencode($kiw_temp));


        $kiw_impressed = $kiw_db->query_first("SELECT mac_address,impress FROM kiwire_device_unique WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' AND mac_address = '{$_SESSION['user']['mac']}' LIMIT 1");

        $kiw_new_device = empty($kiw_impressed['mac_address']);

        $kiw_impressed = array_filter(explode(",", $kiw_impressed['impress']));


        if (!in_array($kiw_temp, $kiw_impressed)) {


            $kiw_cache->incr("REPORT_CAMPAIGN_UIMPRESS:{$kiw_time}:{$_SESSION['controller']['tenant_id']}:{$_SESSION['user']['zone']}:{$kiw_source}:{$kiw_temp}");

            $kiw_impressed[] = $kiw_temp;


        }


        $kiw_impressed = implode(",", $kiw_impressed);


        if ($kiw_new_device == true) {

            $kiw_db->query("INSERT INTO kiwire_device_unique(id, tenant_id, impress, mac_address) VALUE (NULL, '{$_SESSION['controller']['tenant_id']}', '{$kiw_impressed}', '{$_SESSION['user']['mac']}')");

        } else {

            $kiw_db->query("UPDATE kiwire_device_unique SET updated_date = NOW(), impress = '{$kiw_impressed}' WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' AND mac_address = '{$_SESSION['user']['mac']}' LIMIT 1");

        }



        $kiw_cache->incr("REPORT_CAMPAIGN_IMPRESS:{$kiw_time}:{$_SESSION['controller']['tenant_id']}:{$_SESSION['user']['zone']}:{$kiw_source}:{$kiw_temp}");

        kiw_logger($_SESSION['controller']['tenant_id'], $kiw_temp, "impression", $_SESSION['user']['mac'] . " | " . $_SESSION['user']['username']);


        echo json_encode(array("status" => "success"));


    } else {

        echo json_encode(array("status" => "failed"));

    }


} elseif ($kiw_position == "captcha"){


    $kiw_captcha = $kiw_db->escape($_REQUEST['captcha_text']);

    $kiw_name = $kiw_db->escape($_REQUEST['captcha']);
    $kiw_name = $kiw_db->escape(base64_decode($kiw_name));


    $kiw_campaign = $kiw_cache->get("CAPTCHA_TXT:{$_SESSION['controller']['tenant_id']}:{$kiw_name}");

    if (empty($kiw_campaign)) {


        $kiw_campaign = $kiw_db->query_first("SELECT captcha_txt FROM kiwire_campaign_ads WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' AND name = '{$kiw_name}' LIMIT 1");

        $kiw_cache->set("CAPTCHA_TXT:{$_SESSION['controller']['tenant_id']}:{$kiw_name}", $kiw_campaign, 1800);


    }



    if (!empty($kiw_campaign['captcha_txt']) && $kiw_campaign['captcha_txt'] == $kiw_captcha) {

        echo json_encode(array("status" => "success"));

    } else {

        echo json_encode(array("status" => "failed"));

    }


}


function kiw_logger($kiw_tenant, $kiw_campaign_id, $kiw_action, $kiw_data){


    if (file_exists(dirname(__FILE__, 4) . "/logs/campaign/") == false) mkdir(dirname(__FILE__, 4) . "/logs/campaign/", 755, true);


    if (!empty($kiw_tenant) && !empty($kiw_campaign_id)) {

        file_put_contents(dirname(__FILE__, 4) . "/logs/campaign/kiwire-campaign-{$kiw_action}-{$kiw_tenant}-{$kiw_campaign_id}", date("Y-m-d H:i:s") . " :: " . $kiw_data . "\n", FILE_APPEND);

    }


}

