<?php

require_once "../../includes/include_session.php";

if (!empty($_SESSION['controller']['tenant_id'])) {


    $_SESSION['system']['payment_status'] = "success";

    $_SESSION['system']['payment_status'] = "failed";


    header("Location: /user/payment/?session={$session_id}");


} else {

    error_redirect($_SERVER['HTTP_REFERER'], "Please try again.");

}
