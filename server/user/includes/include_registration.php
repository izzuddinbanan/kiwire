<?php


function check_password_policy($kiw_db, $kiw_cache, $kiw_tenant, $kiw_notification, $kiw_username, $kiw_password_new, $kiw_password_old = "", $kiw_password_history = ""){


    $kiw_policies = $kiw_cache->get("PASSWORD_POLICIES:{$kiw_tenant}");

    if (empty($kiw_policies)){


        $kiw_policies = $kiw_db->query_first("SELECT * FROM kiwire_policies WHERE tenant_id = '{$kiw_tenant}' LIMIT 1");

        if (empty($kiw_policies)) $kiw_policies = array("dummy" => true);

        $kiw_cache->set("PASSWORD_POLICIES:{$kiw_tenant}", $kiw_policies, 1800);


    }



    if ($kiw_policies['password_policy'] == "y"){


        if ($kiw_policies['password_character'] == "y"){

            if (strlen($kiw_password_new) < 8){

                return str_replace("{{character_count}}", 8, $kiw_notification['error_password_length']);

            }

        }


        if ($kiw_policies['password_alphabet'] == "y"){

            if (preg_match("/[a-zA-Z]/", $kiw_password_new) == false){

                return $kiw_notification['error_password_contained_alp'];

            }

        }


        if ($kiw_policies['password_numeral'] == "y"){

            if (preg_match("/[0-9]/", $kiw_password_new) == false){

                return $kiw_notification['error_password_contained_num'];

            }

        }


        if ($kiw_policies['password_symbol'] == "y") {

            if (preg_match("/\W+/", $kiw_password_new) == false){

                return $kiw_notification['error_password_contained_sym'];

            }

        }


        if ($kiw_policies['password_same'] == "y"){

            if ($kiw_username == $kiw_password_new){

                return $kiw_notification['error_pass_username_matched'];

            }

        }


        if ($kiw_policies['password_reused'] == "y") {


            // straight check with entered password

            if (strlen($kiw_password_old)) {

                if ($kiw_policies['password_reused'] == "y") {

                    if ($kiw_password_old == $kiw_password_new) {

                        return $kiw_notification['error_password_not_same'];

                    }

                }

            }


            // check with history

            $kiw_pass_list = array_filter(explode(",", $kiw_password_history));

            $kiw_password_test = sync_encrypt($kiw_password_new);


            if (in_array($kiw_password_test, $kiw_pass_list) == true) {

                return $kiw_notification['error_password_reused'];

            }


            // remove the first password if more than 3 and add the latest password

            if (count($kiw_pass_list) >= 3) {

                array_shift($kiw_pass_list);

            }


            $kiw_pass_list[] = $kiw_password_test;

            $kiw_pass_list = implode(",", $kiw_pass_list);


            $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), password_history = '{$kiw_pass_list}' WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant}' LIMIT 1");


        }



    }


    return true;


}