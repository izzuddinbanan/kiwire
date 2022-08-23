<?php


require_once dirname(__FILE__, 2) . "/includes/include_session.php";
require_once dirname(__FILE__, 2) . "/includes/include_general.php";

require_once dirname(__FILE__, 3) . "/admin/includes/include_connection.php";
require_once dirname(__FILE__, 3) . "/admin/includes/include_general.php";



// if got error, need to display error first

if (strlen($_SESSION['response']['error']) > 0) {


    logger($_SESSION['user']['mac'], "Display error message to user", $_SESSION['controller']['tenant_id']);

    header("Location: /user/pages/?session={$session_id}");

    die();
}


/********* for captcha login  ******************************/

$kiw_login['captcha'] = $_REQUEST['using_captcha'];

$kiw_using_captcha   = (strlen($kiw_login['captcha']) > 0) ? $kiw_db->escape($kiw_login['captcha']) : "NA";

$_SESSION['isCaptcha'] = $kiw_using_captcha;

/************ end *************************************/



$kiw_notification = $kiw_cache->get("NOTIFICATION_DATA:{$_SESSION['controller']['tenant_id']}");

if (empty($kiw_notification)) {


    $kiw_notification = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_notification WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

    if (empty($kiw_notification)) $kiw_notification = array("dummy" => true);

    $kiw_cache->set("NOTIFICATION_DATA:{$_SESSION['controller']['tenant_id']}", $kiw_notification, 1800);
}


// collect user input data

// check if auto-login, get the data from session


if ($_SESSION['system']['auto'] == true) {


    // unset if already auto-login

    unset($_SESSION['system']['auto']);

    $kiw_user['username'] = $kiw_db->escape($_SESSION['user']['auser']);
    $kiw_user['password'] = $kiw_db->escape($_SESSION['user']['apass']);


    // unset if already auto-login

    unset($_SESSION['user']['auser']);
    unset($_SESSION['user']['apass']);
} else {

    $kiw_user['username'] = $kiw_db->escape($_REQUEST['username']);
    $kiw_user['password'] = $kiw_db->escape($_REQUEST['password']);
}


// check for empty string within the username

if (strpos($kiw_user['username'], " ") > 0) {


    error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_username_cannot_space']);
} elseif (empty($kiw_user['username'])) {


    error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_no_credential']);
} elseif (empty($kiw_user['password'])) {


    $kiw_temp = $kiw_db->query_first("SELECT password,ktype FROM kiwire_account_auth WHERE username = '{$kiw_user['username']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

    if (empty($kiw_temp) || $kiw_temp['ktype'] != "voucher") {


        error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_no_credential']);
    } else {


        $kiw_user['password'] = sync_decrypt($kiw_temp['password']);
    }

    unset($kiw_temp);
}


// check if realm provided and prepend to username if so

if (isset($_REQUEST['domain']) && !empty($_REQUEST['domain'])) {

    $kiw_user['username'] .= "@" . $kiw_db->escape($_REQUEST['domain']);
}


// check if two factor authentication required

$kiw_policies = $kiw_cache->get("POLICIES_DATA:{$_SESSION['controller']['tenant_id']}");

if (empty($kiw_policies)) {


    $kiw_policies = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_policies WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

    if (empty($kiw_policies)) $kiw_policies = array("dummy" => true);

    $kiw_cache->set("POLICIES_DATA:{$_SESSION['controller']['tenant_id']}", $kiw_policies, 1800);
}



// check for policies if any applied

if ($kiw_policies['two-factors'] == "y") {


    if (!isset($_SESSION['user']['two_factor'])) {


        if ($_SESSION['user']['two_factor_succeed'] != true) {


            $_SESSION['user']['two_factor'] = $kiw_user['password'];

            logger($_SESSION['user']['mac'], "Redirect for 2-factor authentication", $_SESSION['controller']['tenant_id']);

            post_user_to("/user/verification/sms/otp/?session={$session_id}", array("username" => $kiw_user['username']));
        }


        unset($_SESSION['user']['two_factor_succeed']);
    }


    unset($_SESSION['user']['two_factor']);
}


// check if user submit captcha login form & captcha enabled

if ($_SESSION['isCaptcha'] == "yes") {

    if ($kiw_policies['captcha'] == "y") {

        if (!isset($_SESSION['user']['captcha']) || $_SESSION['user']['captcha']['code'] != $_REQUEST['captcha']) {

            error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_wrong_captcha']);
        } else {

            unset($_SESSION['user']['captcha']);
        }
    }
}


// check for remember me enabled

if ($kiw_policies['remember_me'] == "y") {


    if (isset($_REQUEST['remember_me'])) {


        $_SESSION['user']['remember_me'] = "y";
    } else {


        unset($_SESSION['user']['remember_me']);

        setcookie("smart-wifi-login", "", time() - 3600, "/");
    }
} elseif (isset($_COOKIE['smart-wifi-login'])) {

    setcookie("smart-wifi-login", "", time() - 3600, "/");
}



// check if password policy enabled

if ($kiw_policies['password_policy'] == "y") {


    if ($kiw_policies['password_first_login'] == "y") {


        $kiw_user_record = $kiw_db->query_first("SELECT username,password,date_password,ktype FROM kiwire_account_auth WHERE username = '{$kiw_user['username']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

        if (!empty($kiw_user_record)) {

            if (strtotime($kiw_user_record['date_password']) == 0 && $kiw_user_record['ktype'] !== "voucher") {


                if (!empty($kiw_policies['change_passpage']) && $kiw_policies['change_passpage'] != "none") {

                    $_SESSION['user']['current'] = $kiw_policies['change_passpage'];
                }


                $_SESSION['user']['page_data'] = base64_encode(json_encode(array("username" => $kiw_user['username'], "password" => $kiw_user['password'], "input_type_username" => "hidden", "input_type_password" => "hidden")));

                error_redirect("", $kiw_notification['error_password_need_to_change']);
            }
        }
    }


    if ($kiw_policies['password_days'] == "y") {


        if (empty($kiw_user_record)) {

            $kiw_user_record = $kiw_db->query_first("SELECT username,password,date_password,ktype FROM kiwire_account_auth WHERE username = '{$kiw_user['username']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");
        }


        if (!empty($kiw_user_record)) {


            if ((time() - strtotime($kiw_user_record['date_password']) > (90 * 86400)) && $kiw_user_record['ktype'] !== "voucher") {


                if (!empty($kiw_policies['change_passpage']) && $kiw_policies['change_passpage'] != "none") {

                    $_SESSION['user']['current'] = $kiw_policies['change_passpage'];
                }


                $_SESSION['user']['page_data'] = base64_encode(json_encode(array("username" => $kiw_user['username'])));

                error_redirect("", $kiw_notification['error_password_change_day']);
            }
        }
    }
}



if (isset($_SESSION['check'])) unset($_SESSION['check']);

$_SESSION['check']['username']  = $kiw_user['username'];
$_SESSION['check']['password']  = $kiw_user['password'];
$_SESSION['check']['tenant_id'] = $_SESSION['controller']['tenant_id'];

// CUSTOM SYNC-DATA
if(file_exists("/var/www/kiwire/server/custom/{$_SESSION['check']['tenant_id']}/sync-data")) {


    $kiw_campaign_detail['action_value'] = "https://captive.synchroweb.com/custom/{$_SESSION['check']['tenant_id']}/sync-data/?username=". $_SESSION['check']['username'] ."&tenant=" . $_SESSION['check']['tenant_id'];

    $kiw_temp = curl_init();

    curl_setopt($kiw_temp, CURLOPT_URL, $kiw_campaign_detail['action_value']);
    curl_setopt($kiw_temp, CURLOPT_POST, false);
    curl_setopt($kiw_temp, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($kiw_temp, CURLOPT_TIMEOUT, 5);
    curl_setopt($kiw_temp, CURLOPT_CONNECTTIMEOUT, 5);


    $kiw_response_sync   = curl_exec($kiw_temp);
    curl_close($kiw_temp);

    @file_put_contents(dirname(__FILE__, 4) . "/logs/{$_SESSION['check']['tenant_id']}/api-sync-data-" . date("Ymd") . ".log", date("Y-m-d H:i:s :: RESPOND ACOUSTIC :: ") . json_encode($kiw_response_sync) . "\n", FILE_APPEND);

    unset($kiw_temp);
    unset($kiw_response_sync);
}

// CUSTOM SYNC-DATA


?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Wifi Login - Please Wait</title>

    <style>
        @font-face {
            font-family: "Roboto";
            src: url(/libs/fonts/Roboto-Regular.ttf) format("truetype");
        }

        html,
        body {
            background: linear-gradient(132deg, #24c6dc, #3494e6, #ACDFF6, #6CC5EC, #237699);
            background-size: 700% 700%;
            animation: Gradient 15s ease infinite;
            height: 100%;
            overflow: hidden;
        }

        .pls-wait {
            font-size: larger;
            text-align: center;
            color: #ffffff;
            font-family: 'Roboto', sans-serif;
            position: absolute;
            letter-spacing: 2px;
            top: 30%;
            right: 0;
            left: 0;
        }

        @keyframes Gradient {
            0% {
                background-position: 0% 50%
            }

            50% {
                background-position: 100% 50%
            }

            100% {
                background-position: 0% 50%
            }
        }

        @-webkit-keyframes wobbleSpin {
            0% {
                -webkit-transform: rotate(0);
                transform: rotate(0);
            }

            16% {
                -webkit-transform: rotate(168deg);
                transform: rotate(168deg);
            }

            37% {
                -webkit-transform: rotate(68deg);
                transform: rotate(68deg);
            }

            72% {
                -webkit-transform: rotate(384deg);
                transform: rotate(384deg);
            }

            100% {
                -webkit-transform: rotate(360deg);
                transform: rotate(360deg);
            }
        }

        @keyframes wobbleSpin {
            0% {
                -webkit-transform: rotate(0);
                transform: rotate(0);
            }

            16% {
                -webkit-transform: rotate(168deg);
                transform: rotate(168deg);
            }

            37% {
                -webkit-transform: rotate(68deg);
                transform: rotate(68deg);
            }

            72% {
                -webkit-transform: rotate(384deg);
                transform: rotate(384deg);
            }

            100% {
                -webkit-transform: rotate(360deg);
                transform: rotate(360deg);
            }
        }

        @-webkit-keyframes pulse {

            0%,
            100% {
                box-shadow: 0 0 0 0 #ffffff;
            }

            50% {
                box-shadow: 0 0 10px 15px #ffffff;
            }
        }

        @keyframes pulse {

            0%,
            100% {
                box-shadow: 0 0 0 0 #ffffff;
            }

            50% {
                box-shadow: 0 0 10px 15px #ffffff;
            }
        }

        @-webkit-keyframes spin {
            100% {
                -webkit-transform: rotate(360deg);
                transform: rotate(360deg);
            }
        }

        @keyframes spin {
            100% {
                -webkit-transform: rotate(360deg);
                transform: rotate(360deg);
            }
        }

        .loadIcon {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
            width: 100%;
        }

        .loading {
            position: relative;
            height: 152px;
            width: 152px;
            border-radius: 50%;
            padding: 0;
            margin: 0 20px;
        }

        .loading:nth-child(1) {
            border: 15px solid transparent;
        }

        .loading:nth-child(1):after {
            content: " ";
            position: absolute;
            top: 0;
            left: 0;
            height: 152px;
            width: 152px;
            border-radius: 50%;
            border: 0 solid #24c6dc;
            -webkit-animation: pulse 1.5s infinite ease-out;
            animation: pulse 1.5s infinite ease-out;
        }
    </style>

</head>

<body>


    <span class="pls-wait">PLEASE WAIT</span>
    <div class='loadIcon'>
        <div class='loading'></div>
    </div>


</body>

<script>
    var session_id = '<?= $_REQUEST['session'] ?>';
</script>


<script type="application/javascript" src="/app-assets/vendors/js/vendors.min.js"></script>
<script type="application/javascript" src="/user/login.js"></script>


</html>