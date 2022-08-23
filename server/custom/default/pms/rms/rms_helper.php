<?php

function rms_logger($kiw_message, $kiw_pms_type = "", $kiw_tenant = "default"){


    $kiw_path = "/var/www/kiwire/logs/pms/{$kiw_pms_type}/{$kiw_tenant}/";

    if (file_exists($kiw_path) == false) mkdir($kiw_path, 0755, true);

    @file_put_contents($kiw_path . "kiwire-pms-" . date("YmdH") . ".log", date("Y-m-d H:i:s") . " :: " . $kiw_message . "\n", FILE_APPEND);


}

function exc_char($str){
    
    $str = removeSpecialChar($str);

    $exc_name = array(
        'Mr ',
        'Ms ',
        'Mrs ',

    );

    return trim(str_ireplace( $exc_name, '', $str));

}

function removeSpecialChar($str) {
      
    $esc = array( 
        '\'', 
        '"',
        ',', 
        ';', 
        '<', 
        '>', 
        '_', 
        '-' 
    );
      
    return trim(str_replace( $esc, '', $str));

}