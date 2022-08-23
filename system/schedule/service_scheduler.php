<?php


error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);


$kiw_lock_name = "kiwire-service-monitor.lock";

require_once "scheduler_lock.php";


require_once dirname(__FILE__, 3) . "/server/admin/includes/include_config.php";


$kiw_log_path = dirname(__FILE__, 3) . "/server/custom";


// services check to make sure all running well

$kiw_service_down = [];


// get the current status to be update

$kiw_service_status = @file_get_contents("{$kiw_log_path}/service_report.json");

$kiw_service_status = json_decode($kiw_service_status, true);


// get the replication setting to check again replication service

$kiw_replication = @file_get_contents("{$kiw_log_path}/ha_setting.json");

$kiw_replication = json_decode($kiw_replication, true);


$kiw_system = @file_get_contents("{$kiw_log_path}/system_setting.json");

$kiw_system = json_decode($kiw_system, true);


foreach (array("mysqld", "radiusd", "redis", "kiwire_integration", "kiwire_service", "nginx", "php-fpm", "kiwire_replication", "kiwire_replication_account") as $service) {


    ob_start();


    // if external database, no need to monitor this service

    if ($service == "mysqld" && SYNC_DB1_HOST != "127.0.0.1"){

        continue;

    }



    if (in_array($service, array("kiwire_replication", "kiwire_replication_account"))){


        if ($service == "kiwire_replication" && ($kiw_replication['enabled'] == "n" || $kiw_replication['role'] == "master")) {

            continue;

        } elseif ($service == "kiwire_replication_account" && ($kiw_replication['enabled'] == "n" || $kiw_replication['role'] == "backup")) {

            continue;

        }


    }


    // if external redis server, then ignore

    if ($service == "redis" && SYNC_REDIS_HOST != "127.0.0.1"){

        continue;

    }


    $kiw_temp = system("systemctl status {$service} | grep 'Active:' | awk -F ' ' '{print $2}'", $kiw_error);


    if (trim($kiw_temp) == "active" && $kiw_error == 0) {


        $kiw_service_status[$service] = date("Y-m-d H:i:s");


    } else {


        $kiw_service_down[] = $service;

        system("systemctl restart {$service} 2>&1 1>/dev/null");


    }


    ob_end_clean();


    unset($kiw_temp);


}


unset($kiw_replication);

unset($service);


@file_put_contents("{$kiw_log_path}/service_report.json", json_encode($kiw_service_status));


unset($kiw_service_status);


if (count($kiw_service_down) > 0){


    require_once dirname(__FILE__, 3) . "/server/libs/phpmailer/Exception.php";
    require_once dirname(__FILE__, 3) . "/server/libs/phpmailer/PHPMailer.php";
    require_once dirname(__FILE__, 3) . "/server/libs/phpmailer/SMTP.php";


    $kiw_last_notified = @file_get_contents("{$kiw_log_path}/service_notification.json");


    // default value for notification interval = 5 minutes

    if ($kiw_system['notification_interval'] < 5) {

        $kiw_system['notification_interval'] = 5;

    }


    if (empty($kiw_last_notified) || (time() - $kiw_last_notified) >= ($kiw_system['notification_interval'] * 60)) {


        $kiw_system_smtp = @file_get_contents("{$kiw_log_path}/system_smtp.json");

        $kiw_system_smtp = json_decode($kiw_system_smtp, true);


        if (!empty($kiw_system_smtp['notification']) && !empty($kiw_system_smtp['host'])) {


            $kiw_system_smtp['notification'] = explode(";", $kiw_system_smtp['notification']);


            // get the template to send out

            $kiw_template = @file_get_contents(dirname(__FILE__, 3) . "/server/user/templates/notification-service-down.html");

            if (empty($kiw_template)) $kiw_template = "Service [ {{service_name}} ] is down.";


            if (count($kiw_service_down) > 1) {

                $kiw_service_list = "multiple";

            } else $kiw_service_list = $kiw_service_down[0];


            // send email to system-wise

            $kiw_email = array();


            // get the current time in local timezone

            $kiw_time = new DateTime('now', new DateTimeZone('UTC'));

            $kiw_time->setTimezone(new DateTimeZone(empty($kiw_system['timezone']) ? "Asia/Kuala_Lumpur" : $kiw_system['timezone']));

            $kiw_time = $kiw_time->format("Y-m-d H:i:s");


            // update the template with the correct value

            $kiw_template = str_replace(
                array(
                    "{{service_name}}",
                    "{{time_now}}"
                ),
                array(
                    implode(", ", $kiw_service_down),
                    $kiw_time
                ),
                $kiw_template
            );


            $kiw_smtp_send = new PHPMailer\PHPMailer\PHPMailer(false);

            $kiw_smtp_send->Timeout = 10;
            $kiw_smtp_send->SMTPDebug = 0;

            $kiw_smtp_send->Host = trim($kiw_system_smtp['host']);
            $kiw_smtp_send->Port = trim($kiw_system_smtp['port']);


            if (!empty($kiw_system_smtp['auth']) && $kiw_system_smtp['auth'] != "none") {

                $kiw_smtp_send->SMTPSecure = trim($kiw_system_smtp['auth']);

            }


            if (!empty($kiw_system_smtp['user']) && !empty($kiw_system_smtp['password'])) {

                $kiw_smtp_send->SMTPAuth = true;

                $kiw_smtp_send->Username = trim($kiw_system_smtp['user']);
                $kiw_smtp_send->Password = trim($kiw_system_smtp['password']);

            }


            $kiw_smtp_send->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );


            try {


                $kiw_smtp_send->isSMTP();
                $kiw_smtp_send->setFrom(trim($kiw_system_smtp['from_email']), trim($kiw_system_smtp['from_name']));


                foreach ($kiw_system_smtp['notification'] as $kiw_recipient) {

                    $kiw_smtp_send->addAddress($kiw_recipient, $kiw_recipient);

                }

                $kiw_smtp_send->addReplyTo(trim($kiw_system_smtp['from_email']));
                $kiw_smtp_send->isHTML(true);

                $kiw_smtp_send->Subject = "Kiwire Notification: Service Down [ {$kiw_service_list} ] !!";
                $kiw_smtp_send->Body = $kiw_template;


                // send the email to smtp server

                $kiw_smtp_send->send();


            } catch (Exception $e){


                $kiw_smtp_send->Body = "";
                $kiw_smtp_send->clearAllRecipients();
                $kiw_smtp_send->clearAttachments();


            }


            $kiw_smtp_send->smtpClose();


            unset($kiw_smtp_send);
            unset($kiw_recipient);
            unset($kiw_system_smtp);
            unset($kiw_email);
            unset($service);


        }


        @file_put_contents("{$kiw_log_path}/service_notification.json", time());


    }


}

unset($kiw_service_down);


