<?php

$kiw['module'] = "Report -> Monitor -> Service";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_config.php";
require_once "../includes/include_session.php";
require_once "../includes/include_connection.php";
require_once "../includes/include_general.php";

if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}


$action = $_REQUEST['action'];

switch ($action) {

    case "get_service": get_service($kiw_db, $_SESSION['tenant_id']); break;
    case "restart_service": restart_service(); break;
   
    default: echo "ERROR: Wrong implementation";

}


function get_service($kiw_db, $tenant_id){

    $kiw_temp = array(
        'MariaDB'                       => 'mariadb',
        'Nginx'                         => 'nginx',
        'php-fpm'                       => 'php-fpm',
        'Redis'                         => 'redis',
        'Kiwire Service'                => 'kiwire_service.service',
        'Kiwire Integration'            => 'kiwire_integration.service',
        'Kiwire Replication Account'    => 'kiwire_replication_account.service',
        'Kiwire Replication'            => 'kiwire_replication.service',
    );

    $kiw_service = $kiw_db->fetch_array("SELECT * FROM kiwire_services WHERE tenant_id = '{$tenant_id}' ");

    if(sizeof($kiw_service) == 0){

        foreach($kiw_temp as $name => $service){
            
            $data['tenant_id'] = $tenant_id;
            $data['name'] = $name;
            $data['service_name'] = $service;

            $kiw_db->insert("kiwire_services", $data);
        }

        unset($kiw_temp); unset($data);
        $kiw_service = $kiw_db->fetch_array("SELECT * FROM kiwire_services WHERE tenant_id = '{$tenant_id}' ");

    }

    $kiw_services = [];
    foreach($kiw_service as $service){
                            
        $kiw_services[$service['name']]['name'] = $service['name'];
        $kiw_services[$service['name']]['service_name'] = $service['service_name'];

        if($service['name'] == 'MariaDB'){

            
            $kiw_services[$service['name']]['since'] = '-';
            $kiw_services[$service['name']]['days'] = '-' ;

            if((SYNC_DB1_HOST == "localhost" || SYNC_DB1_HOST == "127.0.0.1") ) {

                $service_active = exec("systemctl is-active mariadb", $kiw_error);
                $kiw_temp = exec("systemctl status mariadb | grep 'Active:'", $kiw_error);

                $kiw_temp = explode(' ', $kiw_temp);
            
                if (trim($service_active) == "active") {

                    $kiw_services[$service['name']]['status'] = "Active";

                    $date_since = $kiw_temp[8] ." ". $kiw_temp[9]; 
                    if(strtotime($date_since)) $since = date('d M Y H:i:s', strtotime($date_since));
            
                    $kiw_services[$service['name']]['since'] = $since;
                    $kiw_services[$service['name']]['days'] = "( " . $kiw_temp[11] . " " . $kiw_temp[12] . " " . $kiw_temp[13] . " " . $kiw_temp[14] . " " . $kiw_temp[15] . " )";

                } else {

                    $status = "Down";

                    shell_exec('systemctl restart mariadb > /dev/null 2>/dev/null &');
                }

                unset($kiw_temp);



            }else {

                $kiw_services[$service['name']]['status'] = "Database not in same server";

            }

        }
        else{

            $service_active = exec("systemctl is-active  {$service['service_name']}", $kiw_error);
            
            $kiw_temp = exec("systemctl status {$service['service_name']} | grep 'Active:' ", $kiw_error);

            $kiw_temp = explode(' ', $kiw_temp);
        
            if ( $service_active == "active") {
        
                $kiw_services[$service['name']]['status'] = "Active";
        
                $date_since = $kiw_temp[8] ." ". $kiw_temp[9]; 
                if(strtotime($date_since)) $since = date('d M Y H:i:s', strtotime($date_since));
        
                $kiw_services[$service['name']]['since'] = $since;
                $kiw_services[$service['name']]['days'] = "( " . $kiw_temp[11] . " " . $kiw_temp[12] . " " . $kiw_temp[13] . " " . $kiw_temp[14] . " " . $kiw_temp[15] . " )";
        
            } else {
                
                $kiw_services[$service['name']]['status'] = "Down";
                $kiw_services[$service['name']]['since'] = '-';
                $kiw_services[$service['name']]['days'] = '-' ;
        
                shell_exec('systemctl restart '. $service['service_name'] .' > /dev/null 2>/dev/null &');
            }

        }   
        
    }

    echo json_encode(array("status" => "success", "message" => "", "data" => $kiw_services));

}

function restart_service(){

    $kiw_service = $_GET['service'];

    if(!empty($kiw_service)){

        $data = shell_exec('sudo systemctl restart '. $kiw_service .' > /dev/null 2>/dev/null &');

        echo json_encode(array("status" => "success", "message" => "", "data" => $data));

    }else{

        echo json_encode(array("status" => "failed", "message" => "Service not found", "data" => NULL));

    }

}


















