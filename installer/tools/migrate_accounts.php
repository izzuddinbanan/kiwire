<?php


$kiw_action = "";


$kiw_input = fopen("php://stdin", "r");


while (!in_array($kiw_action, array("dump", "update"))){


    echo "Please confirm your action [ dump / update / cancel ] : ";

    $kiw_action = strtolower(trim(fread($kiw_input, 10)));

    if ($kiw_action == "cancel") die("\n");


}


fclose($kiw_input);


ini_set("memory_limit", "2G");


if ($kiw_action == "dump"){


    echo "Check database connection.. ";

    $kiw_db = new mysqli("127.0.0.1", "root", "", "kiwire");

    if ($kiw_db->connect_errno) die("Unable to connect to Kiwire Database\n");

    echo "OK\n";


    if (file_exists("accounts") == false){

        mkdir("accounts", 0755, true);

    }


    $kiw_tenants = $kiw_db->query("SELECT DISTINCT(cloud_id) AS cloud_id FROM kiwire_conf");

    if ($kiw_tenants) $kiw_tenants = $kiw_tenants->fetch_all(MYSQLI_ASSOC);


    $kiw_total_admins = count($kiw_tenants);

    $kiw_current_count = 1;


    foreach ($kiw_tenants as $kiw_tenant) {


        echo "Processing {$kiw_current_count}/{$kiw_total_admins} for tenant [ {$kiw_tenant['cloud_id']} ]\n";


        $kiw_users = $kiw_db->query("SELECT username, fullname, remark, who AS creator, price, plan AS profile_subs, plan AS profile_curr, createdate AS date_create, valuedate AS date_value, status, allownas AS allowed_zone, expiry AS date_expiry, oldpass AS password, mac AS allowed_mac, tag AS bulk_id, actdate AS date_activate, IF(prepaid = 'y', 'voucher', 'account') AS ktype, edx_auth AS integration, email AS email_address, phone AS phone_number, cloud_id AS tenant_id, last_pass_chg AS date_password FROM kiwire_user WHERE cloud_id = '{$kiw_tenant['cloud_id']}'");


        if ($kiw_users) {


            $kiw_users = $kiw_users->fetch_all(MYSQLI_ASSOC);

            if (!empty($kiw_users)) {


                file_put_contents("accounts/{$kiw_tenant['cloud_id']}.json", json_encode($kiw_users));

                echo "Accounts data has been saved to [ accounts/{$kiw_tenant['cloud_id']}.json ]..\n";


            } else {

                echo "No account found..\n";

            }


        } else {

            echo "No account found..\n";

        }


    }


    echo "Done\n";


} elseif ($kiw_action == "update"){


    require_once "/var/www/kiwire/server/admin/includes/include_connection.php";
    require_once "/var/www/kiwire/server/admin/includes/include_general.php";

    $kiw_db = Database::obtain();


    if (file_exists("accounts") == false){

        die("Directory account not found..\n");

    }


    $kiw_datas = scandir("accounts");


    $kiw_total_tenants = count($kiw_datas);

    $kiw_current_tenant = 1;


    foreach ($kiw_datas as $kiw_data) {


        $kiw_current_tenant++;


        if (in_array($kiw_data, array(".", ".."))) continue;


        $kiw_accounts = file_get_contents("accounts/{$kiw_data}");


        if (!empty($kiw_accounts)) {


            $kiw_accounts = json_decode($kiw_accounts, true);


            if ($kiw_accounts) {


                $kiw_total_accounts = count($kiw_accounts);

                $kiw_current_count = 1;


                foreach ($kiw_accounts as $kiw_account) {


                    echo "Processing [ {$kiw_current_tenant}/{$kiw_total_tenants} ] {$kiw_current_count}/{$kiw_total_accounts} tenant [ {$kiw_account['tenant_id']} ] account [ {$kiw_account['username']} ]..\n";

                    $kiw_current_count++;


                    $kiw_temp = array();


                    $kiw_temp['id']                  = "NULL";
                    $kiw_temp['tenant_id']           = $kiw_account['tenant_id'];
                    $kiw_temp['updated_date']        = "NOW()";
                    $kiw_temp['creator']             = $kiw_account['creator'];
                    $kiw_temp['username']            = $kiw_account['username'];
                    $kiw_temp['fullname']            = $kiw_account['fullname'];
                    $kiw_temp['email_address']       = $kiw_account['email_address'];
                    $kiw_temp['phone_number']        = $kiw_account['phone_number'];
                    $kiw_temp['password']            = sync_encrypt($kiw_account['password']);
                    $kiw_temp['remark']              = $kiw_account['remark'];
                    $kiw_temp['profile_subs']        = $kiw_account['profile_subs'];
                    $kiw_temp['profile_curr']        = $kiw_account['profile_curr'];
                    $kiw_temp['price']               = $kiw_account['price'];
                    $kiw_temp['ktype']               = $kiw_account['ktype'];
                    $kiw_temp['bulk_id']             = $kiw_account['bulk_id'];
                    $kiw_temp['status']              = $kiw_account['status'];
                    $kiw_temp['allowed_zone']        = $kiw_account['allowed_zone'];
                    $kiw_temp['allowed_mac']         = $kiw_account['allowed_mac'];
                    $kiw_temp['date_create']         = $kiw_account['date_create'];
                    $kiw_temp['date_value']          = $kiw_account['date_value'];
                    $kiw_temp['date_expiry']         = $kiw_account['date_expiry'];
                    $kiw_temp['date_last_login']     = "NOW()";
                    $kiw_temp['date_last_logout']    = "NOW()";
                    $kiw_temp['date_activate']       = $kiw_account['date_activate'];
                    $kiw_temp['date_remove']         = $kiw_account['date_remove'];
                    $kiw_temp['date_password']       = $kiw_account['date_password'];

                    if ($kiw_account['integration'] == "int") $kiw_temp['integration'] = "internal";

                    $kiw_db->insert("kiwire_account_auth", $kiw_temp);

                    unset($kiw_temp);


                }


            } else {

                echo "Not valid [ {$kiw_data} ] file.\n";

            }


        }


    }


    echo "Done\n";


}
















