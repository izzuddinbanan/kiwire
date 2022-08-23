<?php

###########################################################################
#                                                                         #
# to allow this module need to modified user nginx to use sudo command    # 
# in server type visudo                                                   # 
# add this at bottom of file %nginx ALL=(ALL) NOPASSWD:ALL                # 
#                                                                         # 
###########################################################################


$kiw['module'] = "Configuration -> High Availability";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_general.php";
require_once "../includes/include_connection.php";

if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}


$action = $_REQUEST['action'];

switch ($action) {

    case "update": update(); break;
    case "reset_error": reset_error(); break;
    case "reset_server": reset_server(); break;
    case "latest_log": latest_log(); break;
    case "keygen": keygen(); break;
    case "revoke": revoke(); break;
    case "status": status(); break;
    case "check": check(); break;
    case "latest": latest(); break;
    default: echo "ERROR: Wrong implementation";
    
}


$kiw_tables = [
    "kiwire_blacklist_domain",
    "kiwire_report_login_dwell",
    "kiwire_report_login_profile",
    "kiwire_report_login_device",
    "kiwire_report_login_error",
    "kiwire_report_campaign_general",
    "kiwire_report_login_general",
    "kiwire_report_controller_statistics",
    "kiwire_report_controller",
    "kiwire_device_history",
    "kiwire_device_register",
    "kiwire_facebook_reputation",
    "kiwire_invoice",
    "kiwire_message",
    "kiwire_nms_log",
    "kiwire_nps_score",
    "kiwire_push_subscription",
    "kiwire_reputation_data",
    "kiwire_survey_respond",
    "kiwire_active_session",
    "kiwire_account_info",
    "kiwire_account_auth",
    "kiwire_payment_trx",
    "kiwire_int_pms_transaction"
];


function update(){


    global $kiw_tables, $kiw_db;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));
        if (!empty($_POST['role'])) {


            foreach ($_POST as $key => $value) {

                $kiw_temp[$key] = $kiw_db->escape($value);

            }


            if (isset($_POST['enabled'])) $kiw_temp['enabled'] = "y";
            else $kiw_temp['enabled'] = "n";

            //write ssh key to authorized_keys
            if ($kiw_temp['role'] == "master"){

                system("echo '" . sync_decrypt($kiw_temp['backup_key']) . "' | sudo tee -a /root/.ssh/authorized_keys 2>/dev/null 1>/dev/null");

            }  else system("echo '" . sync_decrypt($kiw_temp['master_key']) . "' | sudo tee -a /root/.ssh/authorized_keys 2>/dev/null 1>/dev/null");


            // get the current status
            $kiw_current = @file_get_contents(dirname(__FILE__, 3) . "/custom/ha_setting.json");
            $kiw_current = json_decode($kiw_current, true);

            $kiw_status = @file_get_contents(dirname(__FILE__, 3) . "/custom/ha_status.json");
            $kiw_status = json_decode($kiw_status, true);


            ob_start();


            if ($kiw_temp['enabled'] == "n" || $kiw_temp['role'] == "master") {


                if ($kiw_temp['role'] == "master"){

                    //master role  will use kiwire_replication_account.service
                    if ($kiw_temp['role'] == "master" && $kiw_current['role'] == "master"){

                        system("sudo systemctl restart kiwire_replication_account.service 2>/dev/null 1>/dev/null");

                    } else {

                        system("sudo systemctl start kiwire_replication_account.service 2>/dev/null 1>/dev/null");
                        system("sudo systemctl enable kiwire_replication_account.service 2>/dev/null 1>/dev/null");

                    }


                }

                //stop service backup role
                system("sudo systemctl stop kiwire_replication.service 2>/dev/null 1>/dev/null");
                system("sudo systemctl disable kiwire_replication.service 2>/dev/null 1>/dev/null");

                //modify firewall to  allow permission
                system("sudo firewall-cmd --permanent --remove-port=9958/tcp 2>/dev/null 1>/dev/null");
                system("sudo firewall-cmd --permanent --remove-service=mysql 2>/dev/null 1>/dev/null");
                system("sudo firewall-cmd --reload 2>/dev/null 1>/dev/null");


            } elseif ($kiw_temp['role'] == "backup") {

                //check curent setting
                if ($kiw_current['role'] == "master" && $kiw_current['enabled'] == "y" && $kiw_temp['enabled'] == "y"){


                    $kiw_path = dirname(__FILE__, 4) . "/CB-" . date("YmdHi") . "/";

                    if (file_exists($kiw_path) == false){

                        mkdir($kiw_path, 0755, true);

                    }


                    foreach ($kiw_tables as $kiw_table){


                        $kiw_fn = "{$kiw_path}{$kiw_table}.sql.gz";

                        system("mysqldump --no-create-info --single-transaction --replace kiwire {$kiw_table} --where=\"updated_date > '{$kiw_status['tables'][$kiw_table]}'\" | gzip -c > $kiw_fn");

                        unset($kiw_fn);


                    }


                }

                //backup role  will use kiwire_replication.service
                if ($kiw_temp['role'] == "backup" && $kiw_current['role'] == "backup"){

                    system("sudo systemctl restart kiwire_replication.service 2>/dev/null 1>/dev/null");

                } else {

                    system("sudo systemctl start kiwire_replication.service 2>/dev/null 1>/dev/null");
                    system("sudo systemctl enable kiwire_replication.service 2>/dev/null 1>/dev/null");

                }

                //stop main role service
                system("sudo systemctl stop kiwire_replication_account.service 2>/dev/null 1>/dev/null");
                system("sudo systemctl disable kiwire_replication_account.service 2>/dev/null 1>/dev/null");

                //modify firewall to  allow permission
                system("sudo firewall-cmd --permanent --add-port=9958/tcp 2>/dev/null 1>/dev/null");
                system("sudo firewall-cmd --permanent --add-service=mysql 2>/dev/null 1>/dev/null");
                system("sudo firewall-cmd --reload 2>/dev/null 1>/dev/null");


            }


            if ($kiw_temp['enabled'] == "y"){

                //create database user
                if ($kiw_temp['role'] == "backup"){

                    $kiw_create_user = "GRANT ALL PRIVILEGES ON *.* to kiwire_replication@'{$kiw_temp['master_ip_address']}' IDENTIFIED BY 'nfsV8GzCNy25'";

                } else $kiw_create_user = "GRANT ALL PRIVILEGES ON *.* to kiwire_replication@'{$kiw_temp['backup_ip_address']}' IDENTIFIED BY 'nfsV8GzCNy25'";


            } else {

                //remove database user
                if ($kiw_temp['role'] == "backup"){

                    $kiw_create_user = "DROP USER kiwire_replication@'{$kiw_temp['master_ip_address']}'";

                } else $kiw_create_user = "DROP USER kiwire_replication@'{$kiw_temp['backup_ip_address']}'";


            }


            $kiw_replication_db = new mysqli(SYNC_DB1_HOST, SYNC_DB1_USER, SYNC_DB1_PASSWORD, SYNC_DB1_DATABASE, SYNC_DB1_PORT);

            if ($kiw_replication_db->connect_errno == 0) {

                $kiw_replication_db->query($kiw_create_user);
                $kiw_replication_db->query("FLUSH PRIVILEGES");

            }

            $kiw_replication_db->close();


            if ($kiw_temp['t_interval'] < 10) $kiw_temp['t_interval'] = 10;     //min interval
            elseif ($kiw_temp['t_interval'] > 60) $kiw_temp['t_interval'] = 60; //max interval


            ob_end_clean();

            //update setting in file
            @file_put_contents(dirname(__FILE__, 3) . "/custom/ha_setting.json", json_encode($kiw_temp));


            sync_logger("{$_SESSION['user_name']} updated High availability setting", $_SESSION['tenant_id']);

            echo json_encode(array("status" => "success", "message" => "SUCCESS: High availability setting has been updated", "data" => null));


        } else {

            echo json_encode(array("status" => "error", "message" => "ERROR: Incorrect request sent", "data" => null));

        }


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}


function keygen(){


    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        ob_start();


        $kiw_temp = exec("sudo cat /root/.ssh/id_rsa.pub 2>/dev/null");

        if (empty(trim($kiw_temp))){

            system('sudo ssh-keygen -t rsa -N "" -f /root/.ssh/id_rsa 2>/dev/null 1>/dev/null');

        }


        $kiw_temp = system("sudo cat /root/.ssh/id_rsa.pub 2>&1");


        ob_end_clean();


        echo json_encode(array("status" => "success", "message" => null, "data" => array("key" => sync_encrypt($kiw_temp))));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}


function revoke(){


    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        exec("sudo rm -rf /root/.ssh/authorized_keys 2>/dev/null 1>/dev/null");

        echo json_encode(array("status" => "success", "message" => "All keys has been revoke from this server.", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}


function status(){


    $kiw_temp = @file_get_contents(dirname(__FILE__, 3) . "/custom/ha_status.json");

    echo json_encode(array("status" => "success", "message" => "", "data" => json_decode($kiw_temp, true)));


}


function check(){


    $kiw_ping = @file_get_contents(dirname(__FILE__, 3) . "/custom/ha_setting.json");

    $kiw_ping = json_decode($kiw_ping, true);


    if ($kiw_ping['role'] == "master" && !empty($kiw_ping['backup_ip_address'])) {


        $kiw_curl = curl_init();

        curl_setopt($kiw_curl, CURLOPT_URL, "http://{$kiw_ping['backup_ip_address']}:9958");
        curl_setopt($kiw_curl, CURLOPT_POST, true);
        curl_setopt($kiw_curl, CURLOPT_POSTFIELDS, "ping");

        curl_setopt($kiw_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($kiw_curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($kiw_curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($kiw_curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($kiw_curl, CURLOPT_CONNECTTIMEOUT, 10);

        unset($kiw_file);

        $kiw_temp = curl_exec($kiw_curl);

        curl_close($kiw_curl);


    }


    if ($kiw_temp == "pong"){

        echo json_encode(array("status" => "success", "message" => "", "data" => json_decode($kiw_temp, true)));

    } else echo json_encode(array("status" => "failed", "message" => "Unable to connect to backup server [ {$kiw_ping['backup_ip_address']} ]", "data" => ""));


}

function latest(){


    global $kiw_cache;


    $kiw_temp = $kiw_cache->get("REPLICATION_RECEIVED");


    if ((time() - $kiw_temp) >= 300){

        echo json_encode(array("status" => "success", "message" => "Last received at " . sync_tolocaltime(date("Y-m-d H:i:s", $kiw_temp)), "data" => ""));

    } else echo json_encode(array("status" => "failed", "message" => "Last received at " . sync_tolocaltime(date("Y-m-d H:i:s", $kiw_temp)), "data" => ""));


    $kiw_cache->close();


}


function reset_error(){


    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        $kiw_path = dirname(__FILE__, 3);


        $kiw_error_message = system("sudo truncate -s 0 {$kiw_path}/custom/ha_error.json", $kiw_test);


        if ($kiw_test == 0) {

            echo json_encode(array("status" => "success", "message" => "SUCCESS: Error count has been cleared", "data" => ""));

        } else echo json_encode(array("status" => "error", "message" => "ERROR: {$kiw_error_message}", "data" => ""));


    } else echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));


}


function reset_server(){


    global $kiw_cache, $kiw_db;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        $kiw_config = @file_get_contents(dirname(__FILE__, 3) . "/custom/ha_setting.json");

        $kiw_config = json_decode($kiw_config, true);


        if ($kiw_config['enabled'] == "y" && !empty($kiw_config['master_ip_address'])) {


            $kiw_clear_status = system("sudo ssh -o StrictHostKeyChecking=no root@{$kiw_config['master_ip_address']} 'systemctl stop kiwire_replication_account.service 2>/dev/null 1>/dev/null'", $kiw_error);


            if ($kiw_error == 0) {


                $kiw_clear_status = system("sudo ssh -o StrictHostKeyChecking=no root@{$kiw_config['master_ip_address']} 'truncate -s 0 /var/www/kiwire/server/custom/ha_status.json'", $kiw_error);


                if ($kiw_error == 0) {


                    $kiw_cache->set("REPLICATION_RESET", time(), 60);


                    system("sudo truncate -s 0 /var/www/kiwire/server/custom/ha_status.json", $kiw_error);


                    sleep(5);


                    $kiw_tables = $kiw_db->fetch_array("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'kiwire'");


                    $kiw_truncate = new mysqli(SYNC_DB1_HOST, SYNC_DB1_USER, SYNC_DB1_PASSWORD, SYNC_DB1_DATABASE, SYNC_DB1_PORT);


                    foreach ($kiw_tables as $kiw_table) {


                        if (strpos($kiw_table['TABLE_NAME'], "kiwire_sessions_") == false) {

                            $kiw_truncate->query("TRUNCATE kiwire.{$kiw_table['TABLE_NAME']}");

                        }


                    }


                    $kiw_truncate->close();


                    session_destroy();


                    echo json_encode(array("status" => "success", "message" => "SUCCESS: Server has been prepare to reset", "data" => ""));


                } else {

                    echo json_encode(array("status" => "failed", "message" => "ERROR: Unable to connect to main server.<br><br>{$kiw_clear_status}", "data" => null));

                }


                system("sudo ssh -o StrictHostKeyChecking=no root@{$kiw_config['master_ip_address']} 'systemctl start kiwire_replication_account.service 2>/dev/null 1>/dev/null'", $kiw_error);


            } else {

                echo json_encode(array("status" => "failed", "message" => "ERROR: Unable to connect to main server.<br><br>{$kiw_clear_status}", "data" => null));

            }


        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please save your setting and enable replication", "data" => null));

        }


    } else echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));


}


function latest_log(){


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        ob_start();

        $kiw_result_data = system("tail -n 100 \"$(ls -Art /var/www/kiwire/logs/general/kiwire-replication* | tail -n 1)\"", $kiw_error);

        ob_end_clean();


        if ($kiw_error == 0) {


            echo json_encode(array("status" => "success", "message" => "", "data" => $kiw_result_data));


        } else echo json_encode(array("status" => "success", "message" => "ERROR: Unable to collect log data", "data" => ""));


    } else echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

}