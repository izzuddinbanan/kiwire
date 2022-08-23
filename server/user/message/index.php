<?php


session_start();


if ($_SESSION['system']['checked'] == "true") {


    $kiw_source = $_REQUEST['source'];


    header("Content-Type: application/json");


    if ($kiw_source == "message") {


        $_SESSION['response']['error'] = preg_replace('/[^A-Za-z0-9 ._\[\]-]/', '', $_REQUEST['message']);

        echo json_encode(array("status" => "success"));


    } elseif ($kiw_source == "next") {


        $_SESSION['response']['error'] = preg_replace('/[^A-Za-z0-9 ._\[\]-]/', '', $_REQUEST['message']);

        echo json_encode(array("status" => "success"));


    }


}