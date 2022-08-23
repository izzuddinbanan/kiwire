<?php 

// require_once dirname(__FILE__, 3) . "/includes/include_session.php";
// require_once dirname(__FILE__, 3) . "/includes/include_general.php";
// require_once dirname(__FILE__, 3) . "/includes/include_error.php";

require_once dirname(__FILE__, 4) . "/admin/includes/include_connection.php";
require_once dirname(__FILE__, 4) . "/admin/includes/include_general.php";

$_SESSION['controller']['tenant_id'] = 'default';

var_dump(strtotime('0000-00-00 00:00:00')); exit;
if(empty($_SESSION['controller']['tenant_id'])){
    
    print_error_message(110, "Invalid / Unknown Tenant ID", "Please ask your network administrator to check.");
    
}


$kiw_bpanel = $kiw_cache->get("BPANEL_DATA:{$_SESSION['controller']['tenant_id']}");

// if (empty($kiw_bpanel)) {
    
    
    $kiw_bpanel = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_bpanel_template WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");
    
//     if (empty($kiw_bpanel)) $kiw_bpanel = array("dummy" => true);
    
//     $kiw_bpanel->set("BPANEL_DATA:{$_SESSION['controller']['tenant_id']}", $kiw_bpanel, 1800);
    
    
// }


// check function is enabled

if ($kiw_bpanel['enabled'] == "y") {
    
    $kiw_profiles    = implode("','", json_decode($kiw_bpanel['profile'])); 
    
    $kiw_profiles     = $kiw_db->fetch_array("SELECT * FROM kiwire_profiles WHERE name IN ('" . $kiw_profiles . "')");

    $str_opt = "";

    foreach($kiw_profiles as $profile){

        $str_opt .= "<option value='{$profile['price']}'>{$profile['name']}</option>";
        
    }
    
    var_dump($str_opt); 
    exit;

}else{

    print_error_message(111, "Function not available", "Please ask your network administrator to check.");

}
