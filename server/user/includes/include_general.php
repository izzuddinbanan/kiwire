<?php


function logger($device_id, $message = "", $tenant = ""){

    if (strlen($message) > 0){


        // if empty tenant id try to populate from session

        if (empty($tenant)) $tenant = $_SESSION['controller']['tenant_id'];


        $tenant = preg_replace('/[^A-Za-z0-9 _ .-]/', '', $tenant);


        // check if directory existed, if not then create

        if (file_exists(dirname(__FILE__, 4) . "/logs/{$tenant}/") == false) mkdir(dirname(__FILE__, 4) . "/logs/{$tenant}/", 0755, true);


        file_put_contents(dirname(__FILE__, 4) . "/logs/{$tenant}/kiwire-user-{$tenant}-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s :: ") . "MAC: {$device_id} : " . $message . "\n", FILE_APPEND);


    }

}


function error_redirect($kiw_url = "", $kiw_messasge = ""){


    // if no url provided then redirect to /user/pages

    if (empty($kiw_url)) $kiw_url = "/user/pages/?session={$_GET['session']}";


    // if no message then no need to add in session

    if (!empty($kiw_messasge)) $_SESSION['response']['error'] = $kiw_messasge;


    // log the error for investigation

    logger($_SESSION['user']['mac'], $kiw_messasge);


    // redirect request

    header("Location: {$kiw_url}");


    die();


}


function next_page($kiw_journey, $kiw_current, $kiw_default, $kiw_skip = false){


    // make sure page id in journey, if not force to default

    if (in_array($kiw_current, $kiw_journey)) {


        while (current($kiw_journey) != $kiw_current) next($kiw_journey);

        $kiw_temp = next($kiw_journey);


    }


    // check if there is custom page provided.

    if (isset($_REQUEST['next_page']) && !empty($_REQUEST['next_page'])){


        $kiw_temp = preg_replace("/[^a-zA-Z0-9]+/", "", $_REQUEST['next_page']);

        if (strlen($kiw_temp) != 8) $kiw_temp = "";


    }


    if (empty($kiw_temp) && empty($kiw_default)){

        if ($kiw_skip == false) {

            error_redirect($_SERVER['HTTP_REFERER'], "No more page to go. Please check journey.");

        } else return $kiw_current;

    }

    return (empty($kiw_temp) ? $kiw_default : $kiw_temp);


}



function send_http_request($kiw_url, $kiw_data = array(), $kiw_method = "get", $kiw_header = array()){

    if (!empty($kiw_url)) {


        // if array then convert to url encoded data

        $kiw_data = is_array($kiw_data) ? http_build_query($kiw_data) : $kiw_data;


        $kiw_curl = curl_init();

        if (strtolower($kiw_method) == "post") {

            curl_setopt($kiw_curl, CURLOPT_URL, $kiw_url);
            curl_setopt($kiw_curl, CURLOPT_POST, true);
            curl_setopt($kiw_curl, CURLOPT_POSTFIELDS, $kiw_data);

        } else curl_setopt($kiw_curl, CURLOPT_URL, $kiw_url . ((strpos($kiw_url, "?") === false) ? "?" : "&" ) . $kiw_data);

        if (is_array($kiw_header) && count($kiw_header) > 0)
            curl_setopt($kiw_curl, CURLOPT_HTTPHEADER, $kiw_header);

        curl_setopt($kiw_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($kiw_curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($kiw_curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($kiw_curl, CURLOPT_TIMEOUT, 5);
        curl_setopt($kiw_curl, CURLOPT_CONNECTTIMEOUT, 5);

        $kiw_resp = curl_exec($kiw_curl);

        return array("status" => curl_errno($kiw_curl), "data" => $kiw_resp);

    } else {

        return false;

    }

}


function post_user_to($kiw_url, $kiw_data = array()){

    if (!empty($kiw_url)){

        $kiw_input = "";

        foreach ($kiw_data as $kiw_id => $kiw_value){

            $kiw_input .= "<input type='hidden' name='{$kiw_id}' value='{$kiw_value}'>\n";

        }

        ?>

        <form name="posting" action="<?= $kiw_url ?>" method="post">
            <?= $kiw_input ?>
        </form>
        <script>
            window.onload = function () {
                posting.submit();
            }
        </script>

        <?php

        die();

    } return false;

}











