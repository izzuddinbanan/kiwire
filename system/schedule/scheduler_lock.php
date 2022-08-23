<?php


global $kiw_lock_name;


// check if lock directory available

if (file_exists("/tmp/locks/") == false){

    mkdir("/tmp/locks/", 0755, true);

}


// check if lock file available

if (file_exists("/tmp/locks/{$kiw_lock_name}") == false){

    system("touch /tmp/locks/{$kiw_lock_name}");

}


// check lock, if available then proceed

$kiw_lock = fopen("/tmp/locks/{$kiw_lock_name}", "w");


if (!flock($kiw_lock, LOCK_EX | LOCK_NB)){

    exit();

}

