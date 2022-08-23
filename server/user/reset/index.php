<?php


require_once dirname(__FILE__, 2) . "/includes/include_session.php";


if ($_SESSION['system']['checked'] == "true") {


    // redirect to previous page

    $kiw_journey = $_SESSION['user']['journey'];

    $kiw_current = $_SESSION['user']['current'];


    if (isset($_SESSION['user']['journey']) && !empty($_SESSION['user']['journey'])) {


        while (current($kiw_journey) != $kiw_current) next($kiw_journey);


    } elseif (isset($_SESSION['user']['default']) && !empty($_SESSION['user']['default'])) {


        $kiw_previous = $_SESSION['user']['default'];


    }


    $_SESSION['user']['current'] = $kiw_previous;


    echo json_encode(array("status" => "success"));


}

