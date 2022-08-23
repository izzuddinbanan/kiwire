<?php



require_once dirname(__FILE__, 4) . "/admin/includes/include_config.php";
require_once dirname(__FILE__, 4) . "/admin/includes/include_general.php";
require_once dirname(__FILE__, 4) . "/user/includes/include_radius.php";

require_once dirname(__FILE__, 4) . "/libs/class.sql.helper.php";


// connection to mariadb server
go(function () {

    $kiw_db = new Swoole\Coroutine\MySQL();

    $kiw_db->connect(array('host' => SYNC_DB1_HOST, 'user' => SYNC_DB1_USER, 'password' => SYNC_DB1_PASSWORD, 'database' => SYNC_DB1_DATABASE, 'port' => SYNC_DB1_PORT));


    // connection to redis server

    $kiw_cache = new Swoole\Coroutine\Redis();

    $kiw_cache->connect(SYNC_REDIS_HOST, SYNC_REDIS_PORT, true);


    $kiw_cloud['tenant_id'] = "numix";





    $kiw_tenant = dirname(__FILE__);

    if (strpos($kiw_tenant, "server/custom") == false){

        echo "This script need to be run from the tenant folder. Example: /custom/default/pms/\n";

    }

    $kiw_tenant = array_filter(explode("/", $kiw_tenant));


    foreach ($kiw_tenant as $kiw_index => $kiw_tenant_){

        if ($kiw_tenant_ == "custom"){

            $kiw_tenant = $kiw_tenant[$kiw_index + 1];

            break;

        }

    }

    unset($kiw_index);

    unset($kiw_tenant_);

    if (is_array($kiw_tenant)){

        echo "This script need to be run from the tenant folder. Example: /custom/default/pms/\n";

    }

    if(empty($kiw_tenant)) die();

    // CODE START HERE


    // EXAMPLE = "('profile_1', 'profile_2')"
    $kiw_list_profiles = "('500MB_Profile', 'Limit_512MB')";

    $kiw_profiles = $kiw_db->query("SELECT * FROM kiwire_profiles WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND name IN {$kiw_list_profiles}");



    foreach ($kiw_profiles as $kiw_profile) {



        $kiw_profile['attribute'] = json_decode($kiw_profile['attribute'], true);


        $kiw_accounts = $kiw_db->query("SELECT username, profile_cus, quota_in, quota_out, session_time, date_activate FROM kiwire_account_auth WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND profile_subs = '{$kiw_profile['name']}' AND date_activate IS NOT NULL AND date_activate < CURDATE() GROUP BY date_activate");


        $kiw_cf = $kiw_db->query("SELECT * from kiwire_clouds WHERE tenant_id = '{$kiw_cloud['tenant_id']}'")[0];


        foreach ($kiw_accounts as $kiw_account) {

            $kiw_date["renew"] = date("Y-m-d 00:00:00", strtotime("+30 Day", strtotime($kiw_account['date_activate'])));

            $kiw_date["start"] = date("Y-m-d 00:00:00", strtotime($kiw_account["date_activate"]));

            $kiw_cur_date = date("Y-m-d 00:00:00");


            if($kiw_date["renew"] == $kiw_cur_date) {



                if (!empty($kiw_account['profile_cus']) && $kiw_cf['carry_forward_topup'] == 'y') {




                    $kiw_account['profile_cus'] = json_decode($kiw_account['profile_cus'], true);


                    $kiw_usage['quota'] = ($kiw_account['quota_in'] + $kiw_account['quota_out']);



                    if ($kiw_account['profile_cus']['time'] > 0) {


                        if ($kiw_account['session_time'] > ($kiw_profile['attribute']['control:Max-All-Session'] + $kiw_profile['attribute']['control:Access-Period'])) {


                            $kiw_temp['time'] = (($kiw_profile['attribute']['control:Max-All-Session'] + $kiw_profile['attribute']['control:Access-Period']) + $kiw_account['profile_cus']['time']) - $kiw_account['session_time'];


                        } else {

                            $kiw_temp['time'] = $kiw_account['profile_cus']['time'];


                        }

                        if ($kiw_temp['time'] < 0) $kiw_temp['time'] = 0;


                    }



                    if ($kiw_account['profile_cus']['quota'] > 0) {


                        if ($kiw_usage['quota'] > $kiw_profile['attribute']['control:Kiwire-Total-Quota']) {


                            $kiw_temp['quota'] = ($kiw_profiles['attribute']['control:Kiwire-Total-Quota'] + $kiw_account['profile_cus']['quota']) - $kiw_usage['quota'];


                        } else {

                            $kiw_temp['quota'] = $kiw_account['profile_cus']['quota'];


                        }


                        if ($kiw_temp['quota'] < 0) $kiw_temp['quota'] = 0;


                    }


                    unset($kiw_usage);


                    if($kiw_temp["quota"] == 0  && $kiw_temp["quota"] == 0) $kiw_temp = NULL;
                    $kiw_temp = $kiw_temp == NULL ? NULL : json_encode($kiw_temp);


                    $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), session_time = 0, quota_in = 0, quota_out = 0, date_activate = '{$kiw_date["renew"]}', profile_curr = profile_subs, profile_cus = '{$kiw_temp}' WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND profile_subs = '{$kiw_profile['name']}' AND date_activate = '{$kiw_account["date_activate"]}'");


                } else {


                    $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), session_time = 0, quota_in = 0, quota_out = 0, date_activate = '{$kiw_date["renew"]}', profile_curr = profile_subs, profile_cus = '' WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND profile_subs = '{$kiw_profile['name']}' AND date_activate = '{$kiw_account["date_activate"]}'");


                }


            }




        }

    }


    unset($kiw_profile);
    unset($kiw_profiles);

});