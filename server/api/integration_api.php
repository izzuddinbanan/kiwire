<?php


global $kiw_request, $kiw_api;

if ($kiw_request['method'] == "GET") {


    echo json_encode(array("status" => "success", "message" => "", "data" => ""));

}