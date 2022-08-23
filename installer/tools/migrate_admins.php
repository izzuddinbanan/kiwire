<?php


$kiw_action = "";


$kiw_input = fopen("php://stdin", "r");


while (!in_array($kiw_action, array("dump", "update"))){


    echo "Please confirm your action [ dump / update / cancel ] : ";

    $kiw_action = strtolower(trim(fread($kiw_input, 10)));

    if ($kiw_action == "cancel") die("\n");


}


fclose($kiw_input);


if ($kiw_action == "dump"){


    echo "Check database connection.. ";

    $kiw_db = new mysqli("127.0.0.1", "root", "", "kiwire");

    if ($kiw_db->connect_errno) die("Unable to connect to Kiwire Database\n");

    echo "OK\n";


    $kiw_admins = $kiw_db->query("SELECT username, password, groupname, fullname, lastlogin, email, monitor, cloud_id AS tenant_id, temp_pass, permission, bal_credit AS balance_credit, last_change_pass, last_pass, first_login, 'default' AS tenant_default FROM kiwire_admin");


    if ($kiw_admins){


        $kiw_admins = $kiw_admins->fetch_all(MYSQLI_ASSOC);

        if (!empty($kiw_admins)){


            file_put_contents("admins.json", json_encode($kiw_admins));

            echo "Admins data has been saved to [ admins.json ]..\n";


        } else {

            echo "No admin found..\n";

        }


    } else {

        echo "No admin found..\n";

    }


    echo "Done\n";


} elseif ($kiw_action == "update"){


    require_once "/var/www/kiwire/server/admin/includes/include_connection.php";
    require_once "/var/www/kiwire/server/admin/includes/include_general.php";

    $kiw_db = Database::obtain();

    $kiw_admins = file_get_contents("admins.json");


    if (!empty($kiw_admins)) {


        $kiw_admins = json_decode($kiw_admins, true);


        if ($kiw_admins) {


            $kiw_total_admins = count($kiw_admins);

            $kiw_current_count = 1;


            foreach ($kiw_admins as $kiw_admin) {


                echo "Processing {$kiw_current_count}/{$kiw_total_admins} admin username [ {$kiw_admin['username']} ]..\n";

                $kiw_current_count++;


                $kiw_temp = array();


                $kiw_temp['id']             = "NULL";
                $kiw_temp['tenant_id']      = $kiw_admin['tenant_id'];
                $kiw_temp['updated_date']   = $kiw_admin['updated_date'];
                $kiw_temp['username']       = $kiw_admin['username'];
                $kiw_temp['password']       = sync_encrypt($kiw_admin['password']);
                $kiw_temp['groupname']      = $kiw_admin['groupname'];
                $kiw_temp['fullname']       = $kiw_admin['fullname'];
                $kiw_temp['lastlogin']      = $kiw_admin['lastlogin'];
                $kiw_temp['email']          = $kiw_admin['email'];

                $kiw_temp['theme']              = $kiw_admin['theme'];
                $kiw_temp['monitor']            = $kiw_admin['monitor'];
                $kiw_temp['temp_pass']          = $kiw_admin['temp_pass'];
                $kiw_temp['permission']         = $kiw_admin['permission'];
                $kiw_temp['balance_credit']     = $kiw_admin['balance_credit'];
                $kiw_temp['last_change_pass']   = $kiw_admin['last_change_pass'];
                $kiw_temp['first_login']        = $kiw_admin['first_login'];
                $kiw_temp['tenant_default']     = $kiw_admin['tenant_default'];

                $kiw_db->insert("kiwire_admin", $kiw_temp);

                unset($kiw_temp);


            }


        } else {

            echo "Not valid [ admins.json ] file.\n";

        }


    } else {

        echo "File [ admins.json ] not found.\n";

    }

    echo "Done\n";


}
















