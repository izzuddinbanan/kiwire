<?php

require_once dirname(__FILE__, 2) . "/admin/includes/config.php";
require_once dirname(__FILE__, 2) . "/includes/include_session.php";
require_once dirname(__FILE__, 3) . "/libs/simplecaptcha/simple-php-captcha.php";


try {


    $_SESSION['user']['captcha'] = simple_php_captcha();

    echo json_encode(array("status" => "success", "message" => null, "data" => $_SESSION['user']['captcha']['image_src']));


} catch (Exception $e) {

    echo json_encode(array("status" => "error", "message" => "ERROR: " . $e->getMessage(), "data" => null));

}

echo json_encode(array("status" => "success", "message" => null, "data" => $_SESSION['captcha']['image_src']));

