<?php

$kiw_attributes = [];

$kiw_method = "post";

$kiw_url = "http://synchroweb.com";


require_once dirname(__FILE__, 2) . "/includes/include_session.php";

require_once dirname(__FILE__, 3) . "/admin/includes/include_connection.php";


$kiw_profile = $kiw_db->escape($_REQUEST['profile']);


$kiw_profile = $kiw_cache->get("PROFILE_DATA:{$_SESSION['controller']['tenant_id']}:{$kiw_profile}");

if (empty($kiw_profile)) {

    $kiw_profile = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_profiles WHERE name = '{$kiw_profile}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

    if (empty($kiw_profile)) $kiw_profile = array("dummy" => true);

    $kiw_cache->set("PROFILE_DATA:{$_SESSION['controller']['tenant_id']}:{$kiw_profile}", $kiw_profile, 1800);

}


$kiw_gateway = $kiw_cache->get("PAYMENT_CONF:{$_SESSION['controller']['tenant_id']}");

if (empty($kiw_gateway)) {


    $kiw_gateway = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_int_payment_gateways WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

    if (empty($kiw_gateway)) $kiw_gateway = array("dummy" => true);

    $kiw_cache->set("PAYMENT_CONF:{$_SESSION['controller']['tenant_id']}", $kiw_gateway, 1800);


}


if ($kiw_gateway['enabled'] == "y") {


    if ($kiw_profile['price'] > 0) {


        // do checking to redirect user to payment gateway


        if ($kiw_gateway['paymenttype'] == "paypal") {


        } elseif ($kiw_gateway['paymenttype'] == "payfast") {


        } elseif ($kiw_gateway['paymenttype'] == "wirecard") {


        } elseif ($kiw_gateway['paymenttype'] == "alipay") {


        } elseif ($kiw_gateway['paymenttype'] == "stripe") {


        } elseif ($kiw_gateway['paymenttype'] == "senangpay") {


        } elseif ($kiw_gateway['paymenttype'] == "adyen") {


        } elseif ($kiw_gateway['paymenttype'] == "ipay88") {


        }


    }


}


?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment</title>
</head>
<body>
<div class="mainbox-spinner">
    <div>
        <div class="spinner"></div>
        <br/>
        <h2>Processing Transaction. Please Wait..</h2>
    </div>
</div>

<style>

    .mainbox-spinner {
        position: fixed;
        left: 0;
        top: 0;
        right: 0;
        bottom: 0;
        display: table;
        height: 100%;
        width: 100%;
    }

    .mainbox-spinner > div {
        display: table-cell;
        vertical-align: middle;
        text-align: center;
    }

    .spinner {
        width: 40px;
        height: 40px;
        background-color: #333;

        margin: 100px auto;
        -webkit-animation: sk-rotateplane 1.2s infinite ease-in-out;
        animation: sk-rotateplane 1.2s infinite ease-in-out;
    }

    @-webkit-keyframes sk-rotateplane {
        0% {
            -webkit-transform: perspective(120px)
        }
        50% {
            -webkit-transform: perspective(120px) rotateY(180deg)
        }
        100% {
            -webkit-transform: perspective(120px) rotateY(180deg) rotateX(180deg)
        }
    }

    @keyframes sk-rotateplane {
        0% {
            transform: perspective(120px) rotateX(0deg) rotateY(0deg);
            -webkit-transform: perspective(120px) rotateX(0deg) rotateY(0deg)
        }
        50% {
            transform: perspective(120px) rotateX(-180.1deg) rotateY(0deg);
            -webkit-transform: perspective(120px) rotateX(-180.1deg) rotateY(0deg)
        }
        100% {
            transform: perspective(120px) rotateX(-180deg) rotateY(-179.9deg);
            -webkit-transform: perspective(120px) rotateX(-180deg) rotateY(-179.9deg);
        }
    }

</style>


<form name="payment" action="<?= $kiw_url ?>" method="<?= $kiw_method ?>">

    <?php foreach ($kiw_attributes as $kiw_name => $kiw_value){ ?>

        <input type="hidden" name="<?= $kiw_name ?>" value="<?= $kiw_value ?>">

    <?php } ?>

</form>

<script>

    window.onload = function () {

        setTimeout(function () {

            document.payment.submit();

        }, 1000);

    }

</script>

</body>
</html>
