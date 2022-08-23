<?php


session_start();


$session_id = session_id();

$kiw_device_type = trim($_REQUEST['request'], "/");


if (empty($kiw_device_type)) {


    die("Please provide a correct device type");


} elseif ($kiw_device_type == "mikrotik") {


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


} elseif ($kiw_device_type == "cmcc") {


    $_SESSION['user']['mac']            = htmlspecialchars($_REQUEST['mac']);
    $_SESSION['user']['ip']             = htmlspecialchars($_REQUEST['wlanuserip']);
    $_SESSION['user']['destination']    = htmlspecialchars($_REQUEST['url']);
    $_SESSION['user']['time']           = htmlspecialchars($_REQUEST['t']);

    $_SESSION['controller']['ip']       = htmlspecialchars($_REQUEST['nasip']);
    $_SESSION['controller']['login']    = "";
    $_SESSION['controller']['id']       = htmlspecialchars($_REQUEST['wlanacname']);
    $_SESSION['controller']['vlan']     = "";
    $_SESSION['controller']['type']     = "cmcc";
    $_SESSION['controller']['ssid']     = "";
    $_SESSION['controller']['zone']     = htmlspecialchars($_REQUEST['zone']);

    $_SESSION['response']['error']      = "";


} elseif ($kiw_device_type == "cisco_wlc") {


    $_SESSION['user']['mac']            = htmlspecialchars($_REQUEST['client_mac']);
    $_SESSION['user']['ip']             = "";
    $_SESSION['user']['destination']    = htmlspecialchars($_REQUEST['redirect']);
    $_SESSION['user']['time']           = time();

    $_SESSION['controller']['ip']       = "";
    $_SESSION['controller']['login']    = htmlspecialchars($_REQUEST['switch_url']);
    $_SESSION['controller']['id']       = htmlspecialchars($_REQUEST['wlan']);
    $_SESSION['controller']['vlan']     = "";
    $_SESSION['controller']['type']     = "cisco_wlc";
    $_SESSION['controller']['ssid']     = htmlspecialchars($_REQUEST['wlan']);
    $_SESSION['controller']['zone']     = htmlspecialchars($_REQUEST['zone']);

    $_SESSION['response']['error']      = "";


} elseif ($kiw_device_type == "nomadix") {


    $_SESSION['user']['mac']            = strtoupper(implode(":", str_split(htmlspecialchars($_REQUEST['MA']), 2)));
    $_SESSION['user']['ip']             = htmlspecialchars($_REQUEST['SIP']);
    $_SESSION['user']['destination']    = htmlspecialchars($_REQUEST['OS']);
    $_SESSION['user']['time']           = time();

    $_SESSION['controller']['ip']       = "";
    $_SESSION['controller']['login']    = htmlspecialchars($_REQUEST['UIP']);
    $_SESSION['controller']['id']       = htmlspecialchars($_REQUEST['NI']);
    $_SESSION['controller']['vlan']     = htmlspecialchars($_REQUEST['PORT']);
    $_SESSION['controller']['type']     = "nomadix";
    $_SESSION['controller']['ssid']     = "";
    $_SESSION['controller']['zone']     = htmlspecialchars($_REQUEST['zone']);

    $_SESSION['response']['error']      = "";


} elseif ($kiw_device_type == "nomadix_xml") {


    $_SESSION['user']['mac']            = strtoupper(implode(":", str_split(htmlspecialchars($_REQUEST['MA']), 2)));
    $_SESSION['user']['ip']             = htmlspecialchars($_REQUEST['SIP']);
    $_SESSION['user']['destination']    = htmlspecialchars($_REQUEST['OS']);
    $_SESSION['user']['time']           = time();

    $_SESSION['controller']['ip']       = "";
    $_SESSION['controller']['login']    = htmlspecialchars($_REQUEST['UIP']);
    $_SESSION['controller']['id']       = htmlspecialchars($_REQUEST['NI']);
    $_SESSION['controller']['vlan']     = "";
    $_SESSION['controller']['type']     = "nomadix_xml";
    $_SESSION['controller']['ssid']     = "";
    $_SESSION['controller']['zone']     = htmlspecialchars($_REQUEST['zone']);

    $_SESSION['response']['error']      = "";


} elseif ($kiw_device_type == "meraki") {


    $_SESSION['user']['mac']            = htmlspecialchars($_REQUEST['client_mac']);
    $_SESSION['user']['ip']             = htmlspecialchars($_REQUEST['client_ip']);
    $_SESSION['user']['destination']    = urldecode($_REQUEST['continue_url']);
    $_SESSION['user']['time']           = time();

    $_SESSION['controller']['ip']       = "";
    $_SESSION['controller']['login']    = urldecode($_REQUEST['login_url']);
    $_SESSION['controller']['id']       = htmlspecialchars($_REQUEST['ap_mac']);
    $_SESSION['controller']['vlan']     = "";
    $_SESSION['controller']['type']     = "meraki";
    $_SESSION['controller']['ssid']     = "";
    $_SESSION['controller']['zone']     = htmlspecialchars($_REQUEST['zone']);

    $_SESSION['response']['error']      = "";


} elseif ($kiw_device_type == "fortios") {


    $_SESSION['user']['mac']            = htmlspecialchars($_REQUEST['station_mac']);
    $_SESSION['user']['ip']             = htmlspecialchars($_REQUEST['station_ip']);
    $_SESSION['user']['destination']    = htmlspecialchars($_REQUEST['original_url']);
    $_SESSION['user']['time']           = time();

    $_SESSION['controller']['ip']       = htmlspecialchars($_REQUEST['server_ip']);
    $_SESSION['controller']['login']    = htmlspecialchars($_REQUEST['login_url']);
    $_SESSION['controller']['id']       = htmlspecialchars($_REQUEST['ap_mac']);
    $_SESSION['controller']['vlan']     = "";
    $_SESSION['controller']['type']     = "fortigate";
    $_SESSION['controller']['ssid']     = "";
    $_SESSION['controller']['zone']     = htmlspecialchars($_REQUEST['zone']);

    $_SESSION['response']['error']      = "";


} elseif ($kiw_device_type == "fortiap") {


    if ($_REQUEST['Auth'] == "Failed"){


        $_SESSION['response']['error'] = "Internal server error. Please try again.";

        header("Location: /user/pages/?session={$session_id}");

        die();


    }


    $_SESSION['user']['mac']            = htmlspecialchars($_REQUEST['usermac']);
    $_SESSION['user']['ip']             = htmlspecialchars($_REQUEST['userip']);
    $_SESSION['user']['destination']    = "";
    $_SESSION['user']['time']           = time();

    $_SESSION['controller']['ip']       = "";
    $_SESSION['controller']['login']    = htmlspecialchars($_REQUEST['post']);
    $_SESSION['controller']['id']       = htmlspecialchars($_REQUEST['apmac']);
    $_SESSION['controller']['vlan']     = "";
    $_SESSION['controller']['type']     = "fortiap";
    $_SESSION['controller']['ssid']     = "";
    $_SESSION['controller']['zone']     = htmlspecialchars($_REQUEST['zone']);
    $_SESSION['controller']['magic']    = htmlspecialchars($_REQUEST['magic']);

    $_SESSION['response']['error']      = htmlspecialchars("");

    if (empty($_SESSION['controller']['id']) || $_SESSION['controller']['id'] == "00:00:00:00:00:00")
        $_SESSION['controller']['id'] = htmlspecialchars($_REQUEST['apname']);


} elseif ($kiw_device_type == "ruckus_ap") {


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


} elseif (in_array($kiw_device_type, array("ruckus_vsz", "ruckus_scg"))) {


    $_SESSION['user']['enc_mac']        = htmlspecialchars($_REQUEST['client_mac']);
    $_SESSION['user']['enc_ip']         = htmlspecialchars($_REQUEST['uip']);
    $_SESSION['user']['destination']    = (!isset($_SESSION['user']['destination']) ? urldecode($_REQUEST['url']) : $_SESSION['user']['destination']);
    $_SESSION['user']['time']           = time();

    $_SESSION['controller']['ip']       = htmlspecialchars($_REQUEST['apip']);
    $_SESSION['controller']['login']    = htmlspecialchars($_REQUEST['nbiIP']);
    $_SESSION['controller']['id']       = str_replace(":", "-", strtoupper(htmlspecialchars($_REQUEST['mac'])));
    $_SESSION['controller']['vlan']     = htmlspecialchars($_REQUEST['vlan']);

    $_SESSION['controller']['type']     = "ruckus_vsz";
    // $_SESSION['controller']['ssid']     = htmlspecialchars($_REQUEST['wlanName']);
    $_SESSION['controller']['ssid']     = htmlspecialchars($_REQUEST['ssid']);
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



} elseif ($kiw_device_type == "motorola") {


    $kiw_decryptd = array();

    for ($x = 0; $x < strlen($_REQUEST['Qv']); $x++){

        $c_string = substr($_REQUEST['Qv'], $x, 1);

        if (in_array($c_string, array("_", "=", "@")) == false) {

            $dec_string .= chr(ord($c_string) - 1);

        } else {

            $dec_string .= $c_string;

        }

    }

    parse_str($dec_string, $kiw_decryptd);

    unset($dec_string);

    $_SESSION['user']['mac']            = htmlspecialchars($kiw_decryptd['client_mac']);
    $_SESSION['user']['ip']             = "";
    $_SESSION['user']['destination']    = "";
    $_SESSION['user']['time']           = time();

    $_SESSION['controller']['ip']       = "";
    $_SESSION['controller']['login']    = htmlspecialchars($_REQUEST['hs_server']);
    $_SESSION['controller']['id']       = htmlspecialchars($kiw_decryptd['ap_mac']);
    $_SESSION['controller']['vlan']     = htmlspecialchars("");
    $_SESSION['controller']['type']     = "motorola";
    $_SESSION['controller']['ssid']     = htmlspecialchars($kiw_decryptd['ssid']);
    $_SESSION['controller']['zone']     = htmlspecialchars($_REQUEST['zone']);
    $_SESSION['controller']['qv']       = htmlspecialchars($_REQUEST['Qv']);

    $_SESSION['response']['error']      = "";


} elseif ($kiw_device_type == "xirrus" || $kiw_device_type == "chillispot") {


    // if successful login, then redirect user to their final destination

    if ($_REQUEST['res'] == "success") {

        header("Location: {$_REQUEST['userurl']}");
        die();

    }

    $_SESSION['user']['mac']            = htmlspecialchars($_REQUEST['mac']);
    $_SESSION['user']['ip']             = "";
    $_SESSION['user']['destination']    = htmlspecialchars($_REQUEST['userurl']);
    $_SESSION['user']['challenge']      = (isset($_REQUEST['challenge']) ? $_REQUEST['challenge'] : $_REQUEST['chal']);
    $_SESSION['user']['time']           = time();

    $_SESSION['controller']['ip']       = "";
    $_SESSION['controller']['login']    = htmlspecialchars($_REQUEST['uamip'] . ":" . $_REQUEST['uamport'] . "/logon");
    $_SESSION['controller']['id']       = htmlspecialchars(isset($_REQUEST['nasid']) ? $_REQUEST['nasid'] : $_REQUEST['apmac']);
    $_SESSION['controller']['vlan']     = htmlspecialchars($_REQUEST['vlan']);
    $_SESSION['controller']['type']     = $kiw_device_type;
    $_SESSION['controller']['ssid']     = htmlspecialchars($_REQUEST['ssid']);
    $_SESSION['controller']['zone']     = htmlspecialchars($_REQUEST['zone']);

    $_SESSION['response']['error']      = "";


} elseif ($kiw_device_type == "aruba") {


    $_SESSION['user']['mac']            = htmlspecialchars($_REQUEST['mac']);
    $_SESSION['user']['ip']             = htmlspecialchars($_REQUEST['ip']);
    $_SESSION['user']['destination']    = htmlspecialchars($_REQUEST['url']);
    $_SESSION['user']['time']           = time();

    $_SESSION['controller']['ip']       = htmlspecialchars($_REQUEST['switchip']);
    $_SESSION['controller']['login']    = htmlspecialchars($_REQUEST['switchip']);
    $_SESSION['controller']['id']       = htmlspecialchars($_REQUEST['apmac']);
    $_SESSION['controller']['vlan']     = htmlspecialchars($_REQUEST['vlan']);
    $_SESSION['controller']['type']     = "aruba";
    $_SESSION['controller']['ssid']     = htmlspecialchars($_REQUEST['essid']);
    $_SESSION['controller']['zone']     = htmlspecialchars($_REQUEST['zone']);

    $_SESSION['response']['error']      = "";

    if (empty($_SESSION['controller']['id'])) $_SESSION['controller']['id'] = htmlspecialchars($_REQUEST['NI']);

} elseif ($kiw_device_type == "aruba_os") {


        $_SESSION['user']['mac']            = htmlspecialchars($_REQUEST['mac']);
        $_SESSION['user']['ip']             = htmlspecialchars($_REQUEST['ip']);
        $_SESSION['user']['destination']    = htmlspecialchars($_REQUEST['url']);
        $_SESSION['user']['time']           = time();
    
        $_SESSION['controller']['ip']       = htmlspecialchars($_REQUEST['switchip']);
        $_SESSION['controller']['login']    = htmlspecialchars($_REQUEST['switchip']);
        $_SESSION['controller']['id']       = htmlspecialchars($_REQUEST['apmac']);
        $_SESSION['controller']['vlan']     = htmlspecialchars($_REQUEST['vlan']);
        $_SESSION['controller']['type']     = "aruba_os";
        $_SESSION['controller']['ssid']     = htmlspecialchars($_REQUEST['essid']);
        $_SESSION['controller']['zone']     = htmlspecialchars($_REQUEST['zone']);
    
        $_SESSION['response']['error']      = "";
    
        if (empty($_SESSION['controller']['id'])) $_SESSION['controller']['id'] = htmlspecialchars($_REQUEST['NI']);
    
    
} elseif ($kiw_device_type == "engenius") {


    parse_str($_REQUEST['actionurl'], $kiw_en_data);


    $_SESSION['user']['mac']            = htmlspecialchars($kiw_en_data['client_mac']);
    $_SESSION['user']['ip']             = htmlspecialchars($kiw_en_data['client_ip']);
    $_SESSION['user']['destination']    = htmlspecialchars($kiw_en_data['userurl']);
    $_SESSION['user']['time']           = time();

    $_SESSION['controller']['ip']       = htmlspecialchars($kiw_en_data['ap_ip']);
    $_SESSION['controller']['login']    = htmlspecialchars($_REQUEST['actionurl']);
    $_SESSION['controller']['id']       = htmlspecialchars($kiw_en_data['nas_id']);
    $_SESSION['controller']['vlan']     = htmlspecialchars($kiw_en_data['vlan']);
    $_SESSION['controller']['type']     = "engenius";
    $_SESSION['controller']['ssid']     = htmlspecialchars($kiw_en_data['ssidProfileId']);
    $_SESSION['controller']['zone']     = htmlspecialchars($kiw_en_data['zone']);

    $_SESSION['response']['error']      = "";


} elseif ($kiw_device_type == "cambium") {


    $_SESSION['user']['mac']            = htmlspecialchars($_REQUEST['ga_cmac']);
    $_SESSION['user']['ip']             = "";
    $_SESSION['user']['destination']    = htmlspecialchars($_REQUEST['ga_orig_url']);
    $_SESSION['user']['time']           = time();

    $_SESSION['controller']['ip']       = "";
    $_SESSION['controller']['login']    = htmlspecialchars($_REQUEST['ga_srvr']);
    $_SESSION['controller']['id']       = htmlspecialchars($_REQUEST['ga_nas_id']);
    $_SESSION['controller']['vlan']     = "";
    $_SESSION['controller']['type']     = "cambium";
    $_SESSION['controller']['ssid']     = htmlspecialchars($_REQUEST['ga_ssid']);
    $_SESSION['controller']['zone']     = htmlspecialchars($_REQUEST['zone']);
    $_SESSION['controller']['qv']       = htmlspecialchars($_REQUEST['ga_Qv']);

    $_SESSION['response']['error']      = "";


} elseif ($kiw_device_type == "huawei") {


    $_SESSION['user']['mac']            = htmlspecialchars($_REQUEST['user-mac']);
    $_SESSION['user']['ip']             = htmlspecialchars($_REQUEST['user-ipaddress']);
    $_SESSION['user']['destination']    = htmlspecialchars($_REQUEST['redirect-url']);
    $_SESSION['user']['time']           = time();

    $_SESSION['controller']['ip']       = htmlspecialchars($_REQUEST['AP-IP']);
    $_SESSION['controller']['login']    = "";
    $_SESSION['controller']['id']       = htmlspecialchars($_REQUEST['AP-MAC']);
    $_SESSION['controller']['vlan']     = "";
    $_SESSION['controller']['type']     = "huawei";
    $_SESSION['controller']['ssid']     = htmlspecialchars($_REQUEST['ssid']);
    $_SESSION['controller']['zone']     = htmlspecialchars($_REQUEST['zone']);

    $_SESSION['response']['error']      = "";



} elseif ($kiw_device_type == "huawei-nce") {


    $_SESSION['user']['mac']            = htmlspecialchars($_REQUEST['user-mac']);
    $_SESSION['user']['ip']             = htmlspecialchars($_REQUEST['user-ip']);
    $_SESSION['user']['destination']    = htmlspecialchars($_REQUEST['redirect-url']);
    $_SESSION['user']['time']           = time();

//    $_SESSION['controller']['ip']       = parse_url(htmlspecialchars($_REQUEST['loginurl']), PHP_URL_HOST));
    $_SESSION['controller']['login']    = htmlspecialchars($_REQUEST['loginurl']);
    $_SESSION['controller']['id']       = htmlspecialchars($_REQUEST['ap-mac']);
    $_SESSION['controller']['vlan']     = "";
    $_SESSION['controller']['type']     = "huawei-nce";
    $_SESSION['controller']['ssid']     = htmlspecialchars($_REQUEST['ssid']);
    $_SESSION['controller']['zone']     = htmlspecialchars($_REQUEST['zone']);

    $_SESSION['response']['error']      = "";



} elseif ($kiw_device_type == "huawei-cloud-ugw") {


    $_SESSION['user']['mac']            = htmlspecialchars($_REQUEST['user-mac']);
    $_SESSION['user']['ip']             = htmlspecialchars($_REQUEST['user-ip']);
    $_SESSION['user']['destination']    = htmlspecialchars($_REQUEST['redirect-url']);
    $_SESSION['user']['time']           = time();

//    $_SESSION['controller']['ip']       = parse_url(htmlspecialchars($_REQUEST['loginurl']), PHP_URL_HOST));
    $_SESSION['controller']['login']    = htmlspecialchars($_REQUEST['loginurl']);
    $_SESSION['controller']['id']       = htmlspecialchars($_REQUEST['ap-mac']);
    $_SESSION['controller']['vlan']     = "";
    $_SESSION['controller']['type']     = "huawei-nce";
    $_SESSION['controller']['ssid']     = htmlspecialchars($_REQUEST['ssid']);
    $_SESSION['controller']['zone']     = htmlspecialchars($_REQUEST['zone']);

    $_SESSION['response']['error']      = "";



} elseif ($kiw_device_type == "sundray") {


    $_SESSION['user']['mac']            = htmlspecialchars($_REQUEST['user-mac']);
    $_SESSION['user']['ip']             = htmlspecialchars($_REQUEST['user-ip']);
    $_SESSION['user']['destination']    = htmlspecialchars($_REQUEST['user-url']);
    $_SESSION['user']['time']           = time();

    $_SESSION['controller']['ip']       = "";
    $_SESSION['controller']['login']    = "";
    $_SESSION['controller']['id']       = htmlspecialchars($_REQUEST['sundray-sysname']);
    $_SESSION['controller']['vlan']     = "";
    $_SESSION['controller']['type']     = "sundray";
    $_SESSION['controller']['ssid']     = htmlspecialchars($_REQUEST['sundray-ssid']);
    $_SESSION['controller']['zone']     = htmlspecialchars($_REQUEST['zone']);

    $_SESSION['response']['error']      = "";


} elseif ($kiw_device_type == "ubnt") {

    $_SESSION['user']['mac']            = htmlspecialchars($_REQUEST['id']);
    $_SESSION['user']['ip']             = "";
    $_SESSION['user']['destination']    = "";//htmlspecialchars($_REQUEST['url']);
    $_SESSION['user']['time']           = $_REQUEST['t'];

    $_SESSION['controller']['ip']       = "";
    $_SESSION['controller']['login']    = htmlspecialchars($_REQUEST['url']);
    $_SESSION['controller']['id']       = strtoupper(htmlspecialchars($_REQUEST['ap']));
    $_SESSION['controller']['vlan']     = "";
    $_SESSION['controller']['type']     = "ubnt";
    $_SESSION['controller']['ssid']     = htmlspecialchars($_REQUEST['ssid']);
    $_SESSION['controller']['zone']     = htmlspecialchars($_REQUEST['zone']);

    $_SESSION['response']['error']      = "";


} elseif ($kiw_device_type == "other") {


    $_SESSION['user']['mac']            = "";
    $_SESSION['user']['ip']             = "";
    $_SESSION['user']['destination']    = "";
    $_SESSION['user']['time']           = time();

    $_SESSION['controller']['ip']       = "";
    $_SESSION['controller']['login']    = "";
    $_SESSION['controller']['id']       = "";
    $_SESSION['controller']['vlan']     = "";
    $_SESSION['controller']['type']     = "";
    $_SESSION['controller']['ssid']     = "";
    $_SESSION['controller']['zone']     = htmlspecialchars($_REQUEST['zone']);

    $_SESSION['response']['error']      = "";


} elseif ($kiw_device_type == "sonicwall") {


    $_SESSION['user']['mac']            = htmlspecialchars($_REQUEST['mac']);
    $_SESSION['user']['ip']             = htmlspecialchars($_REQUEST['ip']);
    $_SESSION['user']['destination']    = htmlspecialchars($_REQUEST['req']);
    $_SESSION['user']['time']           = time();

    $_SESSION['controller']['ip']       = "";
    $_SESSION['controller']['login']    = htmlspecialchars($_REQUEST['mgmtBaseUrl']);
    $_SESSION['controller']['id']       = htmlspecialchars($_REQUEST['nas']);
    $_SESSION['controller']['vlan']     = "";
    $_SESSION['controller']['type']     = "";
    $_SESSION['controller']['ssid']     = htmlspecialchars($_REQUEST['ssid']);
    $_SESSION['controller']['zone']     = htmlspecialchars($_REQUEST['zone']);

    $_SESSION['sw']['sw_session']       = htmlspecialchars($_REQUEST['sessionId']);
    $_SESSION['sw']['sw_redirect']      = htmlspecialchars($_REQUEST['clientRedirectUrl']);
    $_SESSION['sw']['sw_ufi']           = htmlspecialchars($_REQUEST['ufi']);

    $_SESSION['response']['error']      = "";


} elseif (explode("-", $kiw_device_type)[0] == "virtual") {


    if (file_exists("custom.php")) require_once "custom.php";
    die("Unsupported / unknown device type - {$kiw_device_type}");


} elseif ($kiw_device_type == "pfsense") {


    $_SESSION['user']['mac']            = htmlspecialchars($_REQUEST['CLIENT_MAC']);
    $_SESSION['user']['ip']             = htmlspecialchars($_REQUEST['CLIENT_IP']);
    $_SESSION['user']['time']           = time();
    $_SESSION['controller']['ip']       = htmlspecialchars("");
    $_SESSION['controller']['id']       = htmlspecialchars("CaptivePortal-{$_REQUEST['zone']}");
    $_SESSION['controller']['type']     = "pfsense";
    $_SESSION['controller']['zone']     = htmlspecialchars($_REQUEST['zone']);
    $_SESSION['controller']['login']    = htmlspecialchars($_REQUEST['loginurl']);



} else {

    die("Unsupported / unknown device type - {$kiw_device_type}");

}


$_SESSION['user']['mac'] = str_replace("-", ":", strtolower($_SESSION['user']['mac']));


?>

<script>

    window.onload = function () {

        var language = window.navigator.language;

        window.location.href = '/user/init/?session=<?= $session_id ?>&lang=' + language;

    }

</script>














