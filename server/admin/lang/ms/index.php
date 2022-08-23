<?php


$page_name = $_SERVER['HTTP_REFERER'];


if (!empty($page_name) && strpos($page_name, "/admin/")){


    $lang_json_nav = @file_get_contents("nav.json");
    $lang_json_nav = json_decode($lang_json_nav, true);

    if (!is_array($lang_json_nav)) $lang_json_nav = [];

    $lang_json_page = @file_get_contents(basename($page_name, ".php") . ".json");
    $lang_json_page = json_decode($lang_json_page, true);

    if (!is_array($lang_json_page)) $lang_json_page = [];


    echo json_encode(array_merge($lang_json_nav, $lang_json_page));


}
