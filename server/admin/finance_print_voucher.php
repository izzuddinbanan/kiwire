<?php

$kiw['module'] = "Finance -> Print Prepaid Slip";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

require_once 'includes/include_config.php';
require_once 'includes/include_session.php';
require_once 'includes/include_general.php';
require_once 'includes/include_access.php';
require_once "includes/include_connection.php";


$kiw_voucher = $kiw_db->escape($_REQUEST['voucher']);
$kiw_format = $kiw_db->escape($_REQUEST['format']);


if (empty($kiw_voucher)) {

    header("Location: /admin/finance_voucher_slip.php");

    die();

}


// set the timezone for this cloud tenant

$kiw_timezone = $_SESSION['timezone'];

if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


// get the cloud logo path

foreach (array("jpg", "jpeg", "png") as $kiw_extension){


    if (file_exists(dirname(__FILE__, 2) . "/custom/{$_SESSION['tenant_id']}/logo-{$_SESSION['tenant_id']}.{$kiw_extension}") == true){


        $kiw_logo = "/custom/{$_SESSION['tenant_id']}/logo-{$_SESSION['tenant_id']}.{$kiw_extension}";

        break;


    }


}


$kiw_logo = "<img src='{$kiw_logo}' style='max-height: 300px; max-width: 300px;'>";


// get the list of voucher code

$kiw_voucher_ids = $kiw_db->fetch_array("SELECT username,password,CONVERT_TZ(date_expiry, 'UTC', '{$kiw_timezone}') AS date_expiry,remark FROM kiwire_account_auth WHERE bulk_id = '{$kiw_voucher}' AND tenant_id = '{$_SESSION['tenant_id']}'");


if (is_array($kiw_voucher_ids) && count($kiw_voucher_ids) > 0) {


    $kiw_voucher_template = $kiw_db->query_first("SELECT SQL_CACHE content FROM kiwire_html_template WHERE tenant_id = '{$_SESSION['tenant_id']}' AND type = 'voucher' AND id = (SELECT voucher_template FROM kiwire_clouds WHERE tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1) LIMIT 1");


    if (empty($kiw_voucher_template)) $kiw_voucher_template = "Username: {{username}}<br>Password: {{password}}<br>Expiry Date: {{date_expiry}}";
    else $kiw_voucher_template = $kiw_voucher_template['content'];


    $kiw_voucher_template = stripcslashes($kiw_voucher_template);


    if (strpos($kiw_voucher_template, "{{password}}") > 0) {

        for ($x = 0; $x < count($kiw_voucher_ids); $x++) {

            $kiw_voucher_ids[$x]['password'] = sync_decrypt($kiw_voucher_ids[$x]['password']);

        }

    }


    // render the voucher for printing


    echo "<table style='width: 100%;'>";


    if ($kiw_format == "one") {


        for ($x = 0; $x < count($kiw_voucher_ids); $x++) {

            echo "<tr><td style='padding: 10px;'>";
            echo str_replace(array('{{logo}}', '{{remark}}','{{username}}', '{{password}}', '{{date_expiry}}'), array($kiw_logo, $kiw_voucher_ids[$x]['remark'], $kiw_voucher_ids[$x]['username'], $kiw_voucher_ids[$x]['password'], $kiw_voucher_ids[$x]['date_expiry']), $kiw_voucher_template);
            echo "</td></tr>";

        }


    } elseif ($kiw_format == "two") {


        for ($x = 0; $x < count($kiw_voucher_ids); $x++) {

            if (!isset($kiw_voucher_ids[$x])) break;

            echo "<tr><td style='width: 50%; padding: 10px;'>";
            echo str_replace(array('{{logo}}', '{{remark}}','{{username}}', '{{password}}', '{{date_expiry}}'), array($kiw_logo, $kiw_voucher_ids[$x]['remark'], $kiw_voucher_ids[$x]['username'], $kiw_voucher_ids[$x]['password'], $kiw_voucher_ids[$x]['date_expiry']), $kiw_voucher_template);
            echo "</td>";

            $x++;

            if (!isset($kiw_voucher_ids[$x])) break;

            echo "<td style='width: 50%; padding: 10px;'>";
            echo str_replace(array('{{logo}}', '{{remark}}','{{username}}', '{{password}}', '{{date_expiry}}'), array($kiw_logo, $kiw_voucher_ids[$x]['remark'], $kiw_voucher_ids[$x]['username'], $kiw_voucher_ids[$x]['password'], $kiw_voucher_ids[$x]['date_expiry']), $kiw_voucher_template);
            echo "</td></tr>";

        }


    } elseif ($kiw_format == "pos") {


        for ($x = 0; $x < count($kiw_voucher_ids); $x++) {

            echo "<tr><td style='border-bottom: black thin solid; padding-bottom: 10px;'>";
            echo str_replace(array('{{logo}}', '{{remark}}','{{username}}', '{{password}}', '{{date_expiry}}'), array($kiw_logo, $kiw_voucher_ids[$x]['remark'], $kiw_voucher_ids[$x]['username'], $kiw_voucher_ids[$x]['password'], $kiw_voucher_ids[$x]['date_expiry']), $kiw_voucher_template);
            echo "</td></tr>";

        }


    } elseif ($kiw_format == "qr") {


        for ($x = 0; $x < count($kiw_voucher_ids); $x++) {

            echo "<tr><td style='border-bottom: black thin solid; padding-bottom: 10px;'>";
            echo "<img src='/admin/ajax/ajax_finance_print_voucher.php?username={$kiw_voucher_ids[$x]['username']}' alt=''><br>";
            echo str_replace(array('{{logo}}', '{{remark}}','{{username}}', '{{password}}', '{{date_expiry}}'), array($kiw_logo, $kiw_voucher_ids[$x]['remark'], $kiw_voucher_ids[$x]['username'], $kiw_voucher_ids[$x]['password'], $kiw_voucher_ids[$x]['date_expiry']), $kiw_voucher_template);
            echo "</td></tr>";

        }


    }


    echo "</table>";


}



?>
<script>

    window.onload = function () {

        window.print();

    }

</script>