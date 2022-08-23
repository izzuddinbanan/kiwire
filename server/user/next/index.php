<?php

require_once dirname(__FILE__, 2) . "/includes/include_session.php";
require_once dirname(__FILE__, 2) . "/includes/include_general.php";


if ($_SESSION['system']['checked'] == "true") {


    $_SESSION['user']['current'] = next_page($_SESSION['user']['journey'], $_SESSION['user']['current'], $_SESSION['user']['default']);

    echo json_encode(array("status" => "success"));


}