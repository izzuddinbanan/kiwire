<?php


function print_error_message($err_code, $err_title, $err_msg = ""){


    $kiw_error_page = @file_get_contents(dirname(__FILE__, 2) . "/templates/error.html");


    $kiw_error_setting = @file_get_contents(dirname(__FILE__, 3) . "/custom/system_setting.json");
    $kiw_error_setting = json_decode($kiw_error_setting, true);


    $err_code   = (int)$err_code;
    $err_title  = ucwords($err_title);
    $err_msg    = ucfirst($err_msg);


    $kiw_error_page = str_replace('{{retry-url}}', $kiw_error_setting['system_url'], $kiw_error_page);


    if (empty($kiw_error_page)) {


        ?>

        <p><h3>ERROR</h3></p>
        <p><h5><?= $err_code ?> : <?= $err_title ?></h5></p>

        <p><h5><?= $err_msg ?></h5></p>

        <?php


    } else {


        $kiw_error_page = str_replace(array('{{error-code}}', '{{error-title}}', '{{error-message}}', '{{retry-url}}'), array($err_code, $err_title, $err_msg, $kiw_error_setting['system_url']), $kiw_error_page);

        echo $kiw_error_page;


    }


    die();


}