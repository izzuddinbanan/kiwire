<?php


require_once dirname(__FILE__, 2) . "/includes/include_session.php";


header("Content-Type: application/json");


echo json_encode(array("status" => "success", "data" => $_SESSION['access_list']));



