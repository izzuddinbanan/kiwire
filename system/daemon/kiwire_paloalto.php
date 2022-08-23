<?php


require_once dirname(__FILE__, 3) . "/server/admin/includes/include_config.php";
require_once dirname(__FILE__, 3) . "/server/user/includes/include_radius.php";
require_once dirname(__FILE__, 3) . "/server/libs/class.sql.helper.php";

$paloalto_data = fopen('/var/log/messages', 'r');// Read only


Swoole\Runtime::enableCoroutine();

go(function () use ($paloalto_data) {
    
    $kiw_db = new Swoole\Coroutine\MySQL();
    $kiw_db->connect([
        'host'      => SYNC_DB1_HOST,
        'user'      => SYNC_DB1_USER,
        'password'  => SYNC_DB1_PASSWORD,
        'database'  => SYNC_DB1_DATABASE,
        'port'      => SYNC_DB1_PORT
    ]);

    $kiw_cache = new Swoole\Coroutine\Redis();
    $kiw_cache->connect(SYNC_REDIS_HOST, SYNC_REDIS_PORT, true);
    
    if($paloalto_data){

        $kiw_pa_date = @file_get_contents(dirname(__FILE__, 3) . "/server/custom/pa_last_run.json");
        $kiw_pa_date = json_decode($kiw_pa_date);


        while (true) {

            $line = stream_get_line($paloalto_data, 1024 * 1024, "\n");// Full line found ? (searches for a line break)
            if ($line === false) {
                usleep(100000);// 100ms
                continue;
            }

            $line = trim($line);
            $data = explode(',',$line); //explode the data with space
        
        
            if(is_array($data)){

                
                if(strtotime($data[1]) > strtotime($kiw_pa_date)){

                    //data into variable
                    $kiw_data = array();
                    $kiw_data['date_time']          = date('Y-m-d H:i:s', strtotime($data[1]));
                    $kiw_data['san1']               = $data[2];
                    $kiw_data['level']              = $data[3];
                    $kiw_data['type']               = $data[4];
                    $kiw_data['source_ip']          = $data[7];
                    $kiw_data['dest_ip']            = $data[8];
                    $kiw_data['inf']                = $data[11];
                    $kiw_data['source_user']        = $data[12];
                    $kiw_data['service']            = $data[14];
                    $kiw_data['port']               = $data[25];
                    $kiw_data['proto']              = $data[29];
                    $kiw_data['action']             = $data[30];
                    $kiw_data['host']               = $data[31];
                    $kiw_data['vuln']               = $data[32];
                    $kiw_data['severity']           = $data[34];



                    if(strtotime($kiw_data['date_time']) && $kiw_data['date_time'] != "1970-01-01 00:00:00"){ //only need  valid date
                        
                        if(!empty($data[7])){

                            if($data[4] != "spyware" && $data[4] != "wildfire-virus" && $data[4] != "virus" && $data[4] != "flood"){
                        
                                @file_put_contents(dirname(__FILE__, 3) . "/server/custom/pa_last_run.json", json_encode(date('Y-m-d H:i:s', strtotime($kiw_data['date_time']))));
                                
                                continue;
                            }
    
                            // check ip user in active session have in log
                            $kiw_active_session = $kiw_db->query("SELECT tenant_id, username FROM kiwire_active_session WHERE ip_address = '".$data[7]."'")[0];

                            file_put_contents(dirname(__FILE__, 3) . "/logs/general/kiwire-paloalto-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s :: ") .  "QUERY : SELECT tenant_id, username FROM kiwire_active_session WHERE ip_address = '".$data[7]."'.\n", FILE_APPEND);
                            
                            if($kiw_active_session){

                                $kiw_data['tenant_id']  = $kiw_active_session['tenant_id'];
                                $kiw_data['username']   = $kiw_active_session['username'];

                                $kiw_db->query(sql_insert($kiw_db, 'kiwire_paloalto', $kiw_data));

                                //check if severity high/critical then block user
                                if($kiw_data['severity'] == 'high' || $kiw_data['severity'] == 'critical'){

                                    $kiw_policies = $kiw_db->query("SELECT security_block FROM kiwire_policies WHERE tenant_id = '".$kiw_data['tenant_id']."'")[0];

                                    if($kiw_policies){

                                        if($kiw_policies['security_block'] == 'y'){

                                            //block detected user
                                            $kiw_db->query("UPDATE kiwire_account_auth SET status = 'blocked' WHERE username='".$kiw_data['username']."' AND tenant_id='".$kiw_data['tenant_id']."'");

                                            disconnect_device($kiw_db, $kiw_cache, $kiw_data['tenant_id'], $kiw_active_session['mac_address']);

                                        }
                                    }

                                }

                                
                            }
                            
                            @file_put_contents(dirname(__FILE__, 3) . "/server/custom/pa_last_run.json", json_encode(date('Y-m-d H:i:s', strtotime($kiw_data['date_time']))));
                        }
    
                    }

                }

            }  
        }

    }

});
