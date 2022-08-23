<?php


go(function () {


    $kiw_db = new Swoole\Coroutine\MySQL();

    $kiw_db->connect(array('host' => "localhost", 'user' => "root", 'password' => "", 'database' => "kiwire"));


    $kiw_clouds_db = $kiw_db->query("SELECT tenant_id,timezone,carry_forward_topup FROM kiwire_clouds LIMIT 1000");


    foreach ($kiw_clouds_db as $kiw_cloud) {



        $kiw_profiles = $kiw_db->query("SELECT * FROM kiwire_auto_reset WHERE exec_when = 'h' AND tenant_id = '{$kiw_cloud['tenant_id']}'");


        foreach ($kiw_profiles as $kiw_profile) {


            $kiw_profile['attribute'] = json_decode($kiw_profile['attribute'], true);


            $kiw_accounts = $kiw_db->query("SELECT username, profile_cus, quota_in, quota_out, session_time FROM kiwire_account_auth WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND profile_subs = '{$kiw_profile['profile']}'");


            foreach ($kiw_accounts as $kiw_account) {



                if (!empty($kiw_account['profile_cus']) && $kiw_cloud['carry_forward_topup'] == 'y') {


                    $kiw_account['profile_cus'] = json_decode($kiw_account['profile_cus'], true);


                    $kiw_usage['quota'] = ($kiw_account['quota_in'] + $kiw_account['quota_out']);


                    if ($kiw_account['profile_cus']['time'] > 0) {


                        if ($kiw_account['session_time'] < ($kiw_profile['attribute']['control:Max-All-Session'] + $kiw_profile['attribute']['control:Access-Period'])) {


                            $kiw_temp['time'] = (($kiw_profile['attribute']['control:Max-All-Session'] + $kiw_profile['attribute']['control:Access-Period']) + $kiw_account['profile_cus']['time']) - $kiw_account['session_time'];


                        } else {

                            $kiw_temp['time'] = $kiw_account['profile_cus']['time'];


                        }

                        if ($kiw_temp['time'] < 0) $kiw_temp['time'] = 0;


                    }


                    if ($kiw_account['profile_cus']['quota'] > 0) {


                        if ($kiw_usage['quota'] > $kiw_profile['attribute']['control:Kiwire-Total-Quota']) {


                            $kiw_temp['quota'] = ($kiw_profile['attribute']['control:Kiwire-Total-Quota'] + $kiw_account['profile_cus']['quota']) - $kiw_usage['quota'];


                        } else {

                            $kiw_temp['quota'] = $kiw_account['profile_cus']['quota'];


                        }


                        if ($kiw_temp['quota'] < 0) $kiw_temp['quota'] = 0;


                    }


                    echo json_encode($kiw_temp);


                    unset($kiw_usage);


                    $kiw_temp = json_encode($kiw_temp);

                    //$kiw_db->query("UPDATE kiwire_account_auth SET session_time = 0, quota_in = 0, quota_out = 0, date_activate = NULL, profile_curr = profile_subs, profile_cus = '{$kiw_temp}' WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND profile_subs = '{$kiw_profile['profile']}'");


                } else {

                    //$kiw_db->query("UPDATE kiwire_account_auth SET session_time = 0, quota_in = 0, quota_out = 0, date_activate = NULL, profile_curr = profile_subs, profile_cus = '' WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND profile_subs = '{$kiw_profile['profile']}'");


                }

            }

        }


        unset($kiw_profile);
        unset($kiw_profiles);

    }


});


