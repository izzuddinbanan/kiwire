<?php

function report_date_format($date) {

    if (!empty($date)) {

        $timezone = $_SESSION['timezone'];
        if (empty($timezone)) $timezone = "Asia/Kuala_Lumpur";

        $date = new DateTime($date, new DateTimeZone("UTC"));
        $date->setTimeZone(new DateTimeZone($timezone));

        $date_format = $_SESSION['date_format'];

        return $date->format($date_format);
    }

}


function report_date_start($start_date = "", $interval = 30)
{
    $timezone = $_SESSION['timezone'];

    if (empty($timezone)) $timezone = "Asia/Kuala_Lumpur";

    if (!empty($start_date)) {

        $date = new DateTime(str_replace("/", "-", $start_date), new DateTimeZone($timezone));
        $date->setTimeZone(new DateTimeZone("UTC"));
    } else {

        $date = new DateTime("now", new DateTimeZone("UTC"));
        $date->setTimeZone(new DateTimeZone($timezone));
        $date = new DateTime($date->format("Y-m-d 00:00:00"), new DateTimeZone($timezone));
        $date->setTimeZone(new DateTimeZone("UTC"));

        if ($interval == 30) $interval = cal_days_in_month(CAL_GREGORIAN, date("m", strtotime("-1 month")), date("Y", strtotime("-1 month")));

        $interval = "P{$interval}D";
        $date->sub(new DateInterval($interval));
    }

    return $date->format("Y-m-d H:i:s");
}



function report_date_end($end_date = "", $interval = 1)
{

    $timezone = $_SESSION['timezone'];

    if (empty($timezone)) $timezone = "Asia/Kuala_Lumpur";

    if (!empty($end_date)) {

        $date = new DateTime(str_replace("/", "-", $end_date), new DateTimeZone($timezone));
        $date->setTimeZone(new DateTimeZone("UTC"));
    } else {

        $date = new DateTime("now", new DateTimeZone("UTC"));
        $date->setTimeZone(new DateTimeZone($timezone));
        $date = new DateTime($date->format("Y-m-d 00:00:00"), new DateTimeZone($timezone));
        $date->setTimeZone(new DateTimeZone("UTC"));

        $interval = "P{$interval}D";
        $date->sub(new DateInterval($interval));
    }

    return date("Y-m-d H:i:s", strtotime($date->format("Y-m-d H:i:s") . " +1 day -1 second"));
}



function report_date_view($date)
{

    if (!empty($date)) {

        $timezone = $_SESSION['timezone'];
        if (empty($timezone)) $timezone = "Asia/Kuala_Lumpur";
        $date = new DateTime($date, new DateTimeZone("UTC"));
        $date->setTimeZone(new DateTimeZone($timezone));

        return $date->format("d-m-Y");
    }
}


function report_project_select($kiw_db, $kiw_cache, $tenant_id){


    $kiw_zones = $kiw_cache->get("ZONE_FOR_REPORT_LIST:{$tenant_id}");

    if (empty($kiw_zones)){

        $kiw_zones = $kiw_db->fetch_array("SELECT name FROM kiwire_zone WHERE tenant_id = '{$tenant_id}'");

        if (empty($kiw_zones)) $kiw_zones = array("dummy" => true);

        $kiw_cache->set("ZONE_FOR_REPORT_LIST:{$tenant_id}", $kiw_zones, 1800);

    }


    $kiw_result = "<select name='zone' class='select2 form-control'>";

    $kiw_result .= "<option value=''>Zone: All</option>";
    $kiw_result .= "<option value='zone:nozone'>Zone: None</option>";

    foreach ($kiw_zones as $kiw_zone){

        $kiw_result .= "<option value='ZONE:{$kiw_zone['name']}'>Zone: {$kiw_zone['name']}</option>";

    }

    $kiw_result .= "<select>";


    return $kiw_result;


}


function report_project_option($kiw_db, $kiw_cache, $tenant_id){


    $kiw_projects = $kiw_cache->get("PROJECT_FOR_REPORT_LIST:{$tenant_id}");

    if (empty($kiw_projects)){

        $kiw_projects = $kiw_db->fetch_array("SELECT * FROM kiwire_project WHERE tenant_id = '{$tenant_id}'");

        if (empty($kiw_projects)) $kiw_projects = array("dummy" => true);

        $kiw_cache->set("PROJECT_FOR_REPORT_LIST:{$tenant_id}", $kiw_projects, 1800);

    }


    $kiw_result = "<select name='project' class='select2 form-control'>";

    $kiw_result .= "<option value=''>Project: All</option>";
    $kiw_result .= "<option value=''>Project: None</option>";


    foreach ($kiw_projects as $kiw_project){

        $kiw_result .= "<option value='PROJECT:{$kiw_project['name']}'>Project: {$kiw_project['name']}</option>";

    }

    $kiw_result .= "<select>";


    return $kiw_result;

    

}
