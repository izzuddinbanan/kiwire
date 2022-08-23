<?php


error_reporting(0);


if ($argc > 1){

    $data_date = preg_replace('/\D/', '', $argv[1]);

    if (!empty($data_date) && strlen($data_date) >= 8){

        $data_date = date("Y-m-d 22:00:00", strtotime($data_date));

    } else {

        $data_date = "NOW";

    }

} else {

    $data_date = "NOW";

}


try {

    $current_date = new DateTime($data_date, new DateTimeZone("UTC"));
    $current_date->setTimezone(new DateTimeZone("Asia/Singapore"));
    $current_date = $current_date->format("Y-m-d 00:00:00");

    $current_date = date("Y-m-d H:i:s", strtotime("{$current_date} -1 Day"));


    $utc_time = new DateTime($current_date, new DateTimeZone("Asia/Singapore"));
    $utc_time->setTimezone(new DateTimeZone("UTC"));
    $utc_time = $utc_time->format("Y-m-d H:i:s");


    $week_number = new DateTime($current_date);
    $day_number = $week_number->format("w");
    $week_number = $week_number->format("W");

    if ($day_number == 1){

        $weekly_name = (new DateTime($current_date))->format("Ymd");

    } else {

        $weekly_name = (new DateTime($current_date))->modify("Last Monday")->format("Ymd");

    }

} catch (Exception $e) {

    die();

}


if (file_exists("/tmp/gbtb/") == false) mkdir("/tmp/gbtb/", 0777, true);

system("chmod a+w -R /tmp/gbtb");


$file_date = date("Ymd", strtotime($current_date));

$end_date = date("Y-m-d H:i:s", strtotime("{$utc_time} +24 Hour -1 Second"));


$db = new mysqli("127.0.0.1", "root", "", "kiwire");

$db->set_charset("utf8mb4");


if (!$db->connect_errno) {


    $db->query("SELECT CONVERT_TZ(start_time, 'UTC', 'Asia/Singapore'), CONVERT_TZ(stop_time, 'UTC', 'Asia/Singapore'), username, SEC_TO_TIME(session_time), controller, `zone`, mac_address, terminate_reason, ip_address, (quota_out + quota_in) / (1024 * 1024) AS traffic, quota_out / (1024 * 1024) AS upload, quota_in / (1024 * 1024) AS download, class, brand, model, hostname INTO OUTFILE '/tmp/gbtb/kiwire-login-{$file_date}.csv' FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '\n' FROM kiwire_sessions_201509 WHERE tenant_id = 'GBTB' AND (start_time BETWEEN '{$utc_time}' AND '{$end_date}')");

    $db->query("SELECT CONVERT_TZ(updated_date, 'UTC', 'Asia/Singapore'),username,namegbtb,emailgbtb,countrygbtb,gendergbtb,agegbtb,infogbtb INTO OUTFILE '/tmp/gbtb/kiwire-register-{$file_date}.csv' FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '\n' FROM kiwire_account_info WHERE tenant_id = 'GBTB' AND (updated_date BETWEEN '{$utc_time}' AND '{$end_date}')");

    $db->query("SELECT CONVERT_TZ(updated_date, 'UTC', 'Asia/Singapore'), source, username, fullname, email_address, gender, age_group, country, birthday, location, interest, mac, subscribe INTO OUTFILE '/tmp/gbtb/kiwire-social-{$file_date}.csv' FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '\n' FROM kiwire_account_info WHERE tenant_id = 'GBTB' AND (updated_date BETWEEN '{$utc_time}' AND '{$end_date}')");


}


$db->close();


$gbtb_server = ssh2_connect("161.202.26.157", "22");

ssh2_auth_password($gbtb_server, "synchroweb", "7Bh26)EUr7");

$gbtb_sftp = ssh2_sftp($gbtb_server);


foreach (array("login", "register", "social") as $report) {

    if (file_exists("/tmp/gbtb/kiwire-{$report}-{$file_date}.csv")) {

        if ($day_number == 1) {

            switch ($report) {

                case "login" :
                    $header = "Login,Logout,Username,Total_Time,Mac_Adress,Zone,NAS_ID,Reason,IP_Address,Traffic(MB),Upload(MB),Download(MB),Class,Brand,Model,Hostname";
                    break;
                case "register" :
                    $header = "Date,Username,Name,Email_Address,Country_of_Residence,Gender,Age_Range,Subscribe";
                    break;
                case "social" :
                    $header = "Date,Social_Type,Username,Name,Email_Address,Gender,Age_Range,Country,Birthday,Location,Interest,MAC_Address,Subscribe";
                    break;

            }

            system("sudo sed -i '1i{$header}' /tmp/gbtb/kiwire-{$report}-{$file_date}.csv");

        }


        $gbtb_stream = fopen("ssh2.sftp://{$gbtb_sftp}/home/synchroweb/kiwire/{$report}/kiwire-{$report}-week{$week_number}-{$weekly_name}.csv", "a+");

        if (!$gbtb_stream){

            $gbtb_stream = fopen("ssh2.sftp://{$gbtb_sftp}/home/synchroweb/kiwire/{$report}/kiwire-{$report}-week{$week_number}-{$weekly_name}.csv", "w+");

        }


        fwrite($gbtb_stream, file_get_contents("/tmp/gbtb/kiwire-{$report}-{$file_date}.csv"));

        fclose($gbtb_stream);


        system("sudo rm -rf /tmp/gbtb/kiwire-{$report}-{$file_date}.csv");


    }

}
