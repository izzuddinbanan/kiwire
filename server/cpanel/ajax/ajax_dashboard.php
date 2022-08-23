<?php


header("Content-Type: application/json");


require_once "../includes/include_general.php";
require_once "../includes/include_session.php";


global $kiw_db;


$action = $_REQUEST['action'];

switch ($action) {

    case "overall_usage":
        overall_usage($kiw_db);
        break;
    case "monthly_usage":
        monthly_usage($kiw_db);
        break;
    case "login_activities":
        login_activities($kiw_db);
        break;

    default:
        echo "ERROR: Wrong implementation";
}


function overall_usage($kiw_db)
{

    $kiw_result = array();

    $kiw_username = $_SESSION['cpanel']['username'];

    $kiw_tenant   = $_SESSION['cpanel']['tenant_id'];

    $kiw_timezone = $_SESSION['timezone'];


    if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


    if (strlen($kiw_username) > 0) {


        $kiw_result['auth']     = $kiw_db->query_first("SELECT * FROM kiwire_account_auth WHERE tenant_id = '{$kiw_tenant}' AND username = '{$kiw_username}' LIMIT 1");
        $kiw_result['profile']  = $kiw_db->query_first("SELECT * FROM kiwire_profiles WHERE tenant_id = '{$kiw_tenant}' AND name = '{$kiw_result['auth']['profile_curr']}' LIMIT 1");


        $kiw_result['attribute'] = json_decode($kiw_result['profile']['attribute'], true);  //dalam mb

        // $kiw_result['total_quota'] = $kiw_result['attribute']['control:Kiwire-Total-Quota'];
        // $kiw_result['quota_auth'] = $kiw_result['attribute']['control:Kiwire-Total-Quota'];


        $kiw_result['profile_cus'] = json_decode($kiw_result['auth']['profile_cus'], true);

        $kiw_topup_quota = $kiw_result['profile_cus']['quota'] ?? 0;
        $kiw_topup_time  = $kiw_result['profile_cus']['time'] ?? 0;


        // convert to mb
        $kiw_result['topup_quota'] = $kiw_topup_quota / (1024 * 1024);


        // Add the quota and time if user topup / profile custom added, else use normal calculation
        if (!empty($kiw_result['profile_cus'])) {


            $kiw_result['total_quota'] = $kiw_result['topup_quota'] + $kiw_result['attribute']['control:Kiwire-Total-Quota'];


            if ($kiw_result['profile']['type'] == "free") {


                $kiw_result['remaining_time']  = "Unlimited";
                $kiw_result['quota_used']      = "Unlimited";

                $kiw_quota_percentage =  100;


            } elseif ($kiw_result['profile']['type'] == "countdown") {


                $kiw_result['remaining_time'] = SecondsToTime(($kiw_result['attribute']['control:Max-All-Session'] + $kiw_topup_time) - $kiw_result['auth']['session_time']);


            } elseif ($kiw_result['profile']['type'] == "expiration") {


                if (empty($kiw_result['auth']['date_activate'])) {


                    $kiw_result['remaining_time'] = SecondsToTime($kiw_result['attribute']['control:Access-Period']);

                    $kiw_quota_percentage = 0;
                
                
                } else {

                    $kiw_result['remaining_time'] = SecondsToTime(($kiw_result['attribute']['control:Access-Period'] + $kiw_topup_time) - (time() - strtotime($kiw_result['auth']['date_activate'])));
                
                }


            }


        } else {


            $kiw_result['total_quota'] = $kiw_result['attribute']['control:Kiwire-Total-Quota'];


            if ($kiw_result['profile']['type'] == "free") {


                $kiw_result['remaining_time']  = "Unlimited";
                $kiw_result['quota_used']      = "Unlimited";

                $kiw_quota_percentage =  100;


            } elseif ($kiw_result['profile']['type'] == "countdown") {

                $kiw_result['remaining_time'] = SecondsToTime($kiw_result['attribute']['control:Max-All-Session'] - $kiw_result['auth']['session_time']);
            
            
            } elseif ($kiw_result['profile']['type'] == "expiration") {


                if (empty($kiw_result['auth']['date_activate'])) {


                    $kiw_result['remaining_time'] = SecondsToTime($kiw_result['attribute']['control:Access-Period']);

                    $kiw_quota_percentage = 0;
                
                
                } else {

                    $kiw_result['remaining_time'] = SecondsToTime($kiw_result['attribute']['control:Access-Period'] - (time() - strtotime($kiw_result['auth']['date_activate'])));
                
                }
            
            }
        
        }



        $kiw_result['quota_used'] = ($kiw_result['auth']['quota_in'] + $kiw_result['auth']['quota_out']);


        $kiw_result['auth']['date_create']      = sync_tolocaltime($kiw_result['auth']['date_create'], $kiw_timezone);
        $kiw_result['auth']['date_value']       = sync_tolocaltime($kiw_result['auth']['date_value'], $kiw_timezone);
        $kiw_result['auth']['date_expiry']      = sync_tolocaltime($kiw_result['auth']['date_expiry'], $kiw_timezone);
        $kiw_result['auth']['date_last_login']  = sync_tolocaltime($kiw_result['auth']['date_last_login'], $kiw_timezone);
        $kiw_result['auth']['date_activate']    = sync_tolocaltime($kiw_result['auth']['date_activate'], $kiw_timezone);


        $kiw_quota_percentage = (($kiw_result['auth']['quota_in'] + $kiw_result['auth']['quota_out']) / ($kiw_result['total_quota'] * 1024 * 1024)) * 100;

        $kiw_result['balance_quota'] = round((($kiw_result['total_quota'] * 1024 * 1024) - ($kiw_result['auth']['quota_in'] + $kiw_result['auth']['quota_out'])) / (1024 * 1024), 3, PHP_ROUND_HALF_DOWN);
        $kiw_result['quota_usage']   = round(($kiw_result['quota_used']) / (1024 * 1024), 3, PHP_ROUND_HALF_DOWN);


        echo json_encode(
            [
                "status" => "success",
                "data" => $kiw_result,
                "quota_used" => round(($kiw_result['quota_usage'] > 0) ? $kiw_result['quota_usage'] : 0, 2, PHP_ROUND_HALF_UP),
                "remaining_quota" => round(($kiw_result['balance_quota'] > 0) ? $kiw_result['balance_quota'] : 0, 2, PHP_ROUND_HALF_UP),
                "remaining_time" => $kiw_result['remaining_time'],
                // "remaining_time" => round(($kiw_result['remaining_time']  > 0) ? ($kiw_result['remaining_time'] / 60) : 0, 1, PHP_ROUND_HALF_UP),
                "percentage_quota" => (int)$kiw_quota_percentage

            ]
        );


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: Missing account details", "data" => null));
    }
}




function monthly_usage($kiw_db)
{


    $kiw_result = array();

    $kiw_username = $_SESSION['cpanel']['username'];

    $kiw_tenant   = $_SESSION['cpanel']['tenant_id'];

    $kiw_timezone = $_SESSION['timezone'];



    if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


    foreach (range(0, 5) as $kiw_range) {


        $kiw_current_month = date("Ym", strtotime("-{$kiw_range} Month"));
        $month_name = date("M", strtotime("-{$kiw_range} Month"));


        $kiw_count =  $kiw_db->fetch_array("SELECT SUM(quota_in + quota_out) AS quota_used, date_format(start_time, '%M') AS monthly FROM kiwire_sessions_{$kiw_current_month} WHERE tenant_id = '{$kiw_tenant}' AND username = '{$kiw_username}'");
        

        if (is_array($kiw_count)) {

            if (empty($kiw_count[0]['monthly'])) $kiw_count[0]['monthly'] = $month_name;
            if (empty($kiw_count[0]['quota_used'])) $kiw_count[0]['quota_used'] = 0;

            $kiw_result = array_merge($kiw_result, $kiw_count);
        }


        // make sure not too many data send in one session

        if (count($kiw_result) > 1000) break;

    }

    if (!empty($kiw_count)) {



        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_result));

    } else echo json_encode(array("status" => "failed", "message" => "No data available", "data" => null));


    
}




function login_activities($kiw_db)
{


    $kiw_config = $kiw_db->query_first("SELECT timezone FROM kiwire_clouds WHERE tenant_id = '{$_SESSION['cpanel']['tenant_id']}' LIMIT 1");

    if (empty($kiw_config['timezone'])) $kiw_config['timezone'] = "Asia/Kuala_Lumpur";


    $kiw_result_data = `tail -n 10 $(ls -Art /var/www/kiwire/logs/{$_SESSION['cpanel']['tenant_id']}/kiwire-system-{$_SESSION['cpanel']['tenant_id']}* | tail -n 1) | grep '{$_SESSION['cpanel']['username']} login to user panel system'`;

    $kiw_result_data = explode(PHP_EOL, $kiw_result_data);


    $kiw_result = [];


    foreach ($kiw_result_data as $kiw_log) {


        $kiw_log = explode(" : ", $kiw_log);

        if (!empty($kiw_log[1])) {

            $kiw_result[] = array("date" => sync_tolocaltime(date("Y-m-d H:i:s", strtotime($kiw_log[0]))), "message" => ucfirst($kiw_log[1]));
        }
    }

    echo json_encode(array("status" => "success", "data" => $kiw_result));

}


// Function to convert seconds to days, hours, minutes, seconds
function secondsToTime($seconds)
{

    $dtF = new DateTime("@0");
    $dtT = new DateTime("@$seconds");

    return $dtF->diff($dtT)->format('%a days : %h hours : %i minutes : %s seconds');

}

