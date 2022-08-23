<?php

require_once dirname(__FILE__, 1) . "/include_config.php";
require_once dirname(__FILE__, 3) . "/config.php";


function sync_license_decode($license_string = null)
{

    if (!empty($license_string)) {

        $license_string = json_decode(openssl_decrypt(base64_decode($license_string), "AES-256-CBC", "e1gOtk*9Ox_R", 0, "7vO*STBUdm_7tU4i"), true);

        if (is_array($license_string)) return $license_string;
        else return false;
    } else return false;
}


function sync_logger($message = "", $tenant_id = "")
{


    $tenant_id = preg_replace('/[^A-Za-z0-9 _ .-]/', '', $tenant_id);


    // check if path available. if not then create

    if (file_exists(dirname(__FILE__, 4) . "/logs/{$tenant_id}/") == false) mkdir(dirname(__FILE__, 4) . "/logs/{$tenant_id}/", 0755, true);


    // if empty tenant, set to general

    if (empty($tenant_id)) $tenant_id = "general";


    // set the filename

    $filename = "kiwire-system-{$tenant_id}-" . date("Ymd-H") . ".log";


    if ($_SESSION['access_level'] == "superuser") {

        $message = "[ SU ] {$message}";
    }


    // push message to log file

    file_put_contents(dirname(__FILE__, 4) . "/logs/{$tenant_id}/{$filename}", date("Y-m-d H:i:s") . " : {$message}" . "\n", FILE_APPEND);
}

function sync_tolocaltime($time = null, $zone = "Asia/Kuala_Lumpur")
{

    if ($time == null) return "-";

    try {

        $x = str_replace("/", "-", $time);
        $x = date('Y-m-d H:i:s', strtotime($x));

        $x = new DateTime($x, new DateTimeZone("UTC"));
        $x->setTimezone(new DateTimeZone($zone));

        return $x->format('Y-m-d H:i:s');
    } catch (Exception $e) {

        return false;
    }
}


function sync_toutctime($time = null, $zone = "Asia/Kuala_Lumpur")
{

    if ($time == null) return "-";

    try {

        $x = str_replace("/", "-", $time);
        $x = date('Y-m-d H:i:s', strtotime($x));

        $x = new DateTime($x, new DateTimeZone($zone));
        $x->setTimezone(new DateTimeZone("UTC"));

        return $x->format('Y-m-d H:i:s');
    } catch (Exception $e) {

        return false;
    }
}


function sync_accessible($page_module = null, $module_list = null)
{

    if (!empty($page_module) && !empty($module_list)) {

        if (in_array($page_module, $module_list)) return true;
        return false;
    }
}


function sync_encrypt($raw_string)
{

    return base64_encode(openssl_encrypt($raw_string, "AES-256-CBC", SYNC_ENC_KEY, 0, SYNC_ENC_IV));
}


function sync_decrypt($raw_string)
{

    return openssl_decrypt(base64_decode($raw_string), "AES-256-CBC", SYNC_ENC_KEY, 0, SYNC_ENC_IV);
}


// warning: do not use this sync_brand_encrypt function, use sync_encrypt instead
// this function just for internal use, not suppose to be exposed to public

function sync_brand_encrypt($raw_string)
{

    return base64_encode(openssl_encrypt($raw_string, "AES-256-CBC", "sync_*lifKx/", 0, "L*qF_n8QZslc5qVO"));
}

// warning: do not use this sync_brand_decrypt function, use sync_decrypt instead
// this function just for internal use, not suppose to be exposed to public

function sync_brand_decrypt($raw_string)
{

    return openssl_decrypt(base64_decode($raw_string), "AES-256-CBC", "sync_*lifKx/", 0, "L*qF_n8QZslc5qVO");
}


function sync_hash_message($raw_string)
{

    return hash_hmac("SHA256", $raw_string, "synchro*hash_mac");
}


function base_api_respond($data, $type = true, $message = "", $code = 200)
{


    $respond["status"] = [
        "type"  => $type ? true : false,
        "code"  => $code,
        "message" => $message,
    ];


    if (empty($data)) {

        $data["data"] = "";
    }


    return array_merge($data, $respond);
}


if (!function_exists('str_contains')) {

    function str_contains($haystack, $needle)
    {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}


function recharge_topup($kiw_db, $kiw_account, $kiw_topup, $kiw_username)
{

    $kiw_tenant = $kiw_topup['tenant_id'];

    
    if ($kiw_account['status'] == "active" || $kiw_account['status'] == "suspend") {


        if (!empty($kiw_account['profile_cus'])) {


            $kiw_profile_extend = json_decode($kiw_account['profile_cus'], true);
    
            $kiw_profile_extend['quota'] += $kiw_topup['quota'];
            $kiw_profile_extend['time'] += $kiw_topup['time'];
    
        } else {
    
            $kiw_profile_extend['quota'] = $kiw_topup['quota'];
            $kiw_profile_extend['time'] = $kiw_topup['time'];
        }

        $kiw_profile_extend = json_encode($kiw_profile_extend);


        $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), profile_cus = '{$kiw_profile_extend}', status = 'active' WHERE tenant_id = '{$kiw_tenant}' AND username = '{$kiw_username}' LIMIT 1");

        $kiw_db->query("UPDATE kiwire_topup_code SET updated_date = NOW(), status = 'y', username = '{$kiw_username}', date_activate = NOW() WHERE tenant_id = '{$kiw_tenant}' AND code = '{$kiw_topup['code']}' LIMIT 1");

        return true;


    } else return false;
        

}


function random_string_id($kiw_length = 6, $kiw_avoid_ambiguous = 'n') {


    $kiw_char = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    if($kiw_avoid_ambiguous == "y") $kiw_char = remove_ambiguous($kiw_char);


    $kiw_char_len   = strlen($kiw_char);
    $kiw_rand_str   = '';


    for ($i = 0; $i < $kiw_length; $i++) {

        $kiw_rand_str .= $kiw_char[random_int(0, $kiw_char_len - 1)];

    }


    return $kiw_rand_str;


}


function remove_ambiguous($kiw_string){


    $kiw_string = preg_replace('/[iIoO0|lL1]/', "", $kiw_string);

    $kiw_string = count_chars(strtoupper($kiw_string), 3);

    return $kiw_string;


}

function csrf($token){

    if (!$token || $token !== $_SESSION['token']) {
        // return 405 http status code
        header($_SERVER['SERVER_PROTOCOL'] . ' 405 Method Not Allowed');
        // echo json_encode(array("status" => "failed", "message" => "Failed: Invalid access", "data" => null));
        exit;
    }

    return true;

}