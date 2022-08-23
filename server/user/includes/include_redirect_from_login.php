<?php

if (strpos($_SERVER['HTTP_REFERER'], "/user/login.php")){

    header("Location: /user/pages/?session=" . $_REQUEST['session']);

    die();

}

