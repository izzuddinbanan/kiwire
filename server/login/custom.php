<?php 
if(explode("-", $kiw_device_type)[1] == "mikrotik"){
    
    $_SESSION['user']['mac']            = htmlspecialchars($_REQUEST['mac']);
    $_SESSION['user']['ip']             = htmlspecialchars($_REQUEST['ip']);
    $_SESSION['user']['destination']    = htmlspecialchars($_REQUEST['link-orig']);
    $_SESSION['user']['time']           = time();

    $_SESSION['controller']['ip']       = htmlspecialchars("");
    $_SESSION['controller']['id']       = htmlspecialchars($_REQUEST['nasid']);
    $_SESSION['controller']['vlan']     = htmlspecialchars($_REQUEST['vlan']);
    $_SESSION['controller']['type']     = "mikrotik";
    $_SESSION['controller']['ssid']     = htmlspecialchars($_REQUEST['ssid']);
    $_SESSION['controller']['zone']     = htmlspecialchars($_REQUEST['zone']);

    $_SESSION['response']['error']      = htmlspecialchars($_REQUEST['error']);


    $kiw_path = urldecode($_REQUEST['link-login']);

    $kiw_path = parse_url($kiw_path);


    $_SESSION['controller']['login']    = htmlspecialchars("{$kiw_path['scheme']}://{$kiw_path['host']}/{$kiw_path['path']}");

}
else if(explode("-", $kiw_device_type)[1] == "ruckus_ap"){

    $_SESSION['user']['mac']            = htmlspecialchars($_REQUEST['client_mac']);
    $_SESSION['user']['ip']             = htmlspecialchars($_REQUEST['uip']);
    $_SESSION['user']['destination']    = (!isset($_SESSION['user']['destination']) ? urldecode($_REQUEST['url']) : $_SESSION['user']['destination']);
    $_SESSION['user']['time']           = time();

    $_SESSION['controller']['ip']       = htmlspecialchars($_REQUEST['sip']);
    $_SESSION['controller']['login']    = htmlspecialchars($_REQUEST['sip']);
    $_SESSION['controller']['id']       = str_replace(":", "-", strtoupper(htmlspecialchars($_REQUEST['mac'])));
    $_SESSION['controller']['vlan']     = htmlspecialchars($_REQUEST['vlan']);

    $_SESSION['controller']['type']     = "ruckus_ap";
    $_SESSION['controller']['ssid']     = htmlspecialchars($_REQUEST['ssid']);
    $_SESSION['controller']['zone']     = "";
    $_SESSION['controller']['proxy']    = htmlspecialchars($_REQUEST['proxy']);

    $_SESSION['response']['error']      = "";


    if (empty($_SESSION['user']['destination']) && !empty($_REQUEST['startUrl'])){

        $_SESSION['user']['destination'] = urlencode($_REQUEST['startUrl']);

    }

}
else if(in_array(explode("-", $kiw_device_type)[1], array("ruckus_vsz", "ruckus_scg"))){

    

    $_SESSION['user']['enc_mac']        = htmlspecialchars($_REQUEST['client_mac']);
    $_SESSION['user']['enc_ip']         = htmlspecialchars($_REQUEST['uip']);
    $_SESSION['user']['destination']    = (!isset($_SESSION['user']['destination']) ? urldecode($_REQUEST['url']) : $_SESSION['user']['destination']);
    $_SESSION['user']['time']           = time();

    $_SESSION['controller']['ip']       = htmlspecialchars($_REQUEST['apip']);
    $_SESSION['controller']['login']    = htmlspecialchars($_REQUEST['nbiIP']);
    $_SESSION['controller']['id']       = str_replace(":", "-", strtoupper(htmlspecialchars($_REQUEST['mac'])));
    $_SESSION['controller']['vlan']     = htmlspecialchars($_REQUEST['vlan']);

    $_SESSION['controller']['type']     = "ruckus_vsz";
    $_SESSION['controller']['ssid']     = htmlspecialchars($_REQUEST['wlanName']);
    $_SESSION['controller']['zone']     = htmlspecialchars($_REQUEST['zoneName']);;

    $_SESSION['response']['error']      = "";


    require_once "../admin/includes/include_config.php";


    $kiw_cache = new Redis();

    $kiw_cache->connect(SYNC_REDIS_HOST, SYNC_REDIS_PORT);

    $kiw_cache->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);


    $kiw_temp = $kiw_cache->get("RUCKUS_DATA:{$_SESSION['controller']['id']}");


    if (empty($kiw_temp)){


        $kiw_db = new mysqli(SYNC_DB1_HOST, SYNC_DB1_USER, SYNC_DB1_PASSWORD, SYNC_DB1_DATABASE, SYNC_DB1_PORT);


        $kiw_temp = $kiw_db->query("SELECT SQL_CACHE password,device_ip FROM kiwire_controller WHERE unique_id = 'Ruckus_Controller' AND tenant_id = (SELECT tenant_id FROM kiwire_controller WHERE unique_id = '{$_SESSION['controller']['id']}' LIMIT 1) LIMIT 1");

        if ($kiw_temp) $kiw_temp = $kiw_temp->fetch_all(MYSQLI_ASSOC)[0];
        else $kiw_temp = array("dummy" => true);

        $kiw_db->close();


        $kiw_cache->set("RUCKUS_DATA:{$_SESSION['controller']['id']}", $kiw_temp, 1800);


    }


    if (!empty($kiw_temp['device_ip'])){

        $_SESSION['controller']['login'] = $kiw_temp['device_ip'];

    }


    if ($kiw_temp['dummy'] == true || empty($kiw_temp['password'])) {


        require_once "../user/includes/include_error.php";

        print_error_message(200, "Ruckus no password", "Please contact your network administrator");


    }


    $_SESSION['controller']['password'] = $kiw_temp['password'];


    // check if mac address encrypted

    if (strtoupper(substr($_SESSION['user']['enc_mac'], 0, 3)) == "ENC"){



        // decrypt mac address for current device

        $kiw_request = array(
            "Vendor"            => "ruckus",
            "RequestPassword"   => $_SESSION['controller']['password'],
            "APIVersion"        => "1.0",
            "RequestCategory"   => "GetConfig",
            "RequestType"       => "Decrypt",
            "Data"              => $_SESSION['user']['mac']
        );


        $kiw_curl = curl_init("http://{$_SESSION['controller']['login']}:9080/portalintf");


        curl_setopt($kiw_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($kiw_curl, CURLOPT_POST, true);
        curl_setopt($kiw_curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($kiw_curl, CURLOPT_POSTFIELDS, json_encode($kiw_request));
        curl_setopt($kiw_curl, CURLOPT_TIMEOUT, 5);
        curl_setopt($kiw_curl, CURLOPT_CONNECTTIMEOUT, 5);


        $kiw_temp = json_decode(curl_exec($kiw_curl), true);


        curl_close($kiw_curl);

        unset($kiw_curl);


        if ($kiw_temp) {

            $_SESSION['user']['mac'] = $kiw_temp->Data;

        }


        // decrypt ip address for current device

        $kiw_request = array(
            "Vendor"            => "ruckus",
            "RequestPassword"   => $_SESSION['controller']['password'],
            "APIVersion"        => "1.0",
            "RequestCategory"   => "GetConfig",
            "RequestType"       => "Decrypt",
            "Data"              => $_SESSION['user']['enc_ip']
        );


        $kiw_curl = curl_init("http://{$_SESSION['controller']['login']}:9080/portalintf");


        curl_setopt($kiw_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($kiw_curl, CURLOPT_POST, true);
        curl_setopt($kiw_curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($kiw_curl, CURLOPT_POSTFIELDS, json_encode($kiw_request));
        curl_setopt($kiw_curl, CURLOPT_TIMEOUT, 5);
        curl_setopt($kiw_curl, CURLOPT_CONNECTTIMEOUT, 5);


        $kiw_temp = json_decode(curl_exec($kiw_curl), true);


        curl_close($kiw_curl);

        unset($kiw_curl);


        if ($kiw_temp) {

            $_SESSION['user']['ip'] = $kiw_temp->Data;

        }


    } else {


        $_SESSION['user']['mac'] = $_SESSION['user']['enc_mac'];

        $_SESSION['user']['ip'] = $_SESSION['user']['enc_ip'];


    }


    if (empty($_SESSION['user']['destination']) && !empty($_REQUEST['startUrl'])){

        $_SESSION['user']['destination'] = urlencode($_REQUEST['startUrl']);

    }
    
}
