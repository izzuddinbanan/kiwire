<?php


error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);


$kiw_lock_name = "reporting-scheduler.lock";

require_once "scheduler_lock.php";


require_once dirname(__FILE__, 3) . "/server/admin/includes/include_config.php";
require_once dirname(__FILE__, 3) . "/server/admin/includes/include_general.php";
require_once dirname(__FILE__, 3) . "/server/user/includes/include_radius.php";

require_once dirname(__FILE__, 3) . "/server/libs/class.sql.helper.php";


ini_set("max_execution_time", 300);


go(function () {


    $kiw_this_file = dirname(__FILE__);

    $kiw_log_path = dirname($kiw_this_file, 2) . "/server/custom";


    // connection to mariadb server

    $kiw_db = new Swoole\Coroutine\MySQL();

    $kiw_db->connect(array('host' => SYNC_DB1_HOST, 'user' => SYNC_DB1_USER, 'password' => SYNC_DB1_PASSWORD, 'database' => SYNC_DB1_DATABASE, 'port' => SYNC_DB1_PORT));


    $kiw_cache = new Swoole\Coroutine\Redis();

    $kiw_cache->connect(SYNC_REDIS_HOST, SYNC_REDIS_PORT, true);

    $kiw_time['start'] = date('Y-m-d H:i:s');


    // get all tenant for this system

    $kiw_clouds_db = $kiw_db->query("SELECT tenant_id,timezone FROM kiwire_clouds LIMIT 1000");

    $kiw_system = @file_get_contents("{$kiw_log_path}/system_setting.json");
    $kiw_system = json_decode($kiw_system, true);
    
    foreach($kiw_clouds_db as $kiw_cloud){

        $timezone = empty($kiw_cloud['timezone']) ? ( empty($kiw_system['timezone']) ? 'Asia/Kuala_Lumpur' : $kiw_system['timezone'] ) : $kiw_cloud['timezone']; 

        //Enhancement : remove 0 data for table kiwire_report_login_dwell

        $kiwire_reports_dwell = $kiw_db->query("SELECT count(tenant_id) as total_row, sum(count) as count, report_date FROM kiwire_report_login_dwell WHERE tenant_id = '{$kiw_cloud['tenant_id']}' group by DATE(report_date)");

        foreach($kiwire_reports_dwell as $report_dwell){

            $report_date    = date('Y-m-d', strtotime($report_dwell['report_date']));
            $limit          = (int)$report_dwell['total_row'] - 1;

            if($report_dwell['count'] == 0){
                
                $kiw_db->query("DELETE FROM kiwire_report_login_dwell WHERE  tenant_id = '{$kiw_cloud['tenant_id']}' AND DATE(report_date)  = '{$report_date}' LIMIT $limit ");
                
            }
            else if($report_dwell['count'] > 0) {

                $kiw_db->query("DELETE FROM kiwire_report_login_dwell WHERE  tenant_id = '{$kiw_cloud['tenant_id']}' AND DATE(report_date)  = '{$report_date}' AND count = '0'");

            }

        }

        unset($kiwire_reports_dwell);
        unset($report_date);
        unset($limit);

        

        //Enhancement : remove 0 data for table kiwire_report_login_profile
        
        $kiwire_reports_profiles = $kiw_db->query("SELECT count(profile) as total_row,DATE(CONVERT_TZ(report_date, 'UTC', '{$timezone}')) AS xreport_date, profile, SUM(IFNULL(login, 0)) AS login, SUM(IFNULL(dwell, 0)) AS dwell FROM kiwire_report_login_profile  WHERE tenant_id = '{$kiw_cloud['tenant_id']}' GROUP BY xreport_date,profile");

        foreach($kiwire_reports_profiles as $report_profiles){
            
            $limit = (int)$report_profiles['total_row'] - 1;
            
            if($report_profiles['login'] == 0 &&  $report_profiles['dwell'] == 0){
                
                $kiw_db->query("DELETE FROM kiwire_report_login_profile WHERE  tenant_id = '{$kiw_cloud['tenant_id']}' AND DATE(CONVERT_TZ(report_date, 'UTC', '{$timezone}') ) = '{$report_profiles['xreport_date']}' AND profile = '{$report_profiles['profile']}' LIMIT $limit ");
                
            }
            else{
                
                $kiw_db->query("DELETE FROM kiwire_report_login_profile WHERE  tenant_id = '{$kiw_cloud['tenant_id']}' AND DATE(CONVERT_TZ(report_date, 'UTC', '{$timezone}') ) = '{$report_profiles['xreport_date']}' AND profile = '{$report_profiles['profile']}' AND login = 0 AND dwell = 0");
                
            }
            
        }
        
        unset($kiwire_reports_profiles);
        unset($limit);
        
        

        //Enhancement : remove 0 data for table kiwire_report_login_general

        $kiwire_reports_general = $kiw_db->query("SELECT 
                                                    COUNT(id) AS total_row, 
                                                    DATE(CONVERT_TZ(report_date, 'UTC', '{$timezone}')) AS xreport_date,
                                                    SUM(attemp) AS attemp, 
                                                    SUM(succeed) AS succeed,   
                                                    SUM(failed) AS failed,   
                                                    SUM(quota) AS quota,   
                                                    SUM(time) AS time,   
                                                    SUM(disconnect) AS disconnect,   
                                                    SUM(sms) AS sms,   
                                                    SUM(email) AS email,   
                                                    SUM(account_create) AS account_create,   
                                                    SUM(integration) AS integration,   
                                                    SUM(account_return) AS account_return,   
                                                    SUM(account_new) AS account_new,   
                                                    SUM(account_unique) AS account_unique,   
                                                    SUM(device_unique) AS device_unique,   
                                                    SUM(device_new) AS device_new,   
                                                    SUM(device_return) AS device_return,   
                                                    SUM(dwell) AS dwell,   
                                                    SUM(impression) AS impression,   
                                                    SUM(connected) AS connected,   
                                                    SUM(quota_in) AS quota_in,   
                                                    SUM(quota_out) AS quota_out,   
                                                    SUM(ulogin) AS ulogin,   
                                                    SUM(concurrent) AS concurrent 
                                                    FROM kiwire_report_login_general 
                                                    WHERE  tenant_id = '{$kiw_cloud['tenant_id']}' 
                                                    GROUP BY xreport_date");

        foreach($kiwire_reports_general as $report_general){

            $limit = (int)$report_general['total_row'] - 1;

            //check if all column no value
            if($report_general['attemp'] == 0 && $report_general['succeed'] == 0 && $report_general['failed'] == 0 && $report_general['quota'] == 0 && $report_general['time'] == 0 && $report_general['disconnect'] == 0 && $report_general['sms'] == 0 && $report_general['email'] == 0 && $report_general['account_create'] == 0 && $report_general['integration'] == 0 && $report_general['account_return'] == 0 && $report_general['account_new'] == 0 && $report_general['account_unique'] == 0 && $report_general['device_unique'] == 0  && $report_general['device_new'] == 0 && $report_general['device_return'] == 0 && $report_general['dwell'] == 0 && $report_general['impression'] == 0 && $report_general['connected'] == 0 && $report_general['quota_in'] == 0 && $report_general['quota_out'] == 0 && $report_general['ulogin'] == 0 && $report_general['concurrent'] == 0){


                $kiw_db->query("DELETE FROM kiwire_report_login_general WHERE  tenant_id = '{$kiw_cloud['tenant_id']}' AND DATE(CONVERT_TZ(report_date, 'UTC', '{$timezone}') ) = '{$report_general['xreport_date']}' LIMIT $limit ");


            }
            else{

                $kiw_db->query("DELETE FROM kiwire_report_login_general WHERE  tenant_id = '{$kiw_cloud['tenant_id']}' AND DATE(CONVERT_TZ(report_date, 'UTC', '{$timezone}') ) = '{$report_general['xreport_date']}' AND attemp = 0 AND succeed = 0 AND failed = 0 AND quota = 0 AND time = 0 AND disconnect = 0 AND sms = 0 AND email = 0 AND account_create = 0 AND integration = 0 AND account_return = 0 AND account_new = 0 AND account_unique = 0 AND device_unique = 0 AND device_new = 0 AND device_return = 0 AND dwell = 0 AND impression = 0 AND impression = 0 AND quota_in = 0 AND quota_out = 0 AND ulogin = 0 AND concurrent = 0 ");

            }

        }

        unset($kiwire_reports_profiles);
        unset($limit);
        

        //Enhancement : remove unused data for kiwire_report_controller_statistics

        $kiwire_reports_controller = $kiw_db->query("SELECT COUNT(id) as total_row, DATE(CONVERT_TZ(report_date, 'UTC', '{$timezone}')) AS xreport_date, unique_id, sum(quota_upload) as quota_upload, sum(quota_download) as quota_download , sum(avg_upload_speed) as avg_upload_speed, sum(avg_download_speed) as avg_download_speed, sum(avg_speed )as avg_speed 
        FROM kiwire_report_controller_statistics WHERE tenant_id = '{$kiw_cloud['tenant_id']}' GROUP BY xreport_date, unique_id");
        
        foreach($kiwire_reports_controller as $report_controller){

            $limit = (int)$report_controller['total_row'] - 1;

            if($report_controller['quota_upload'] == 0 && $report_controller['quota_download'] == 0 && $report_controller['avg_upload_speed'] == 0 && $report_controller['avg_download_speed'] == 0 && $report_controller['avg_speed'] == 0 ){


                $kiw_db->query("DELETE FROM kiwire_report_controller_statistics WHERE  tenant_id = '{$kiw_cloud['tenant_id']}' AND DATE(CONVERT_TZ(report_date, 'UTC', '{$timezone}') ) = '{$report_controller['xreport_date']}' AND unique_id = '{$report_controller['unique_id']}' LIMIT $limit ");


            }
            else{

                $kiw_db->query("DELETE FROM kiwire_report_controller_statistics WHERE  tenant_id = '{$kiw_cloud['tenant_id']}' AND DATE(CONVERT_TZ(report_date, 'UTC', '{$timezone}') ) = '{$report_controller['xreport_date']}' AND unique_id = '{$report_controller['unique_id']}' AND quota_upload = 0 AND quota_download = 0 AND avg_upload_speed = 0 AND avg_download_speed = 0 AND avg_speed = 0");

            }


        }


        unset($kiwire_reports_controller);
        unset($limit);
        
        unset($timezone);







    }

    
    $kiw_time['end'] = date('Y-m-d H:i:s');
    $kiw_cache->set("KIW_SCHEDULER:REPORTING_SCHEDULER:RUN_AT", $kiw_time);


});
