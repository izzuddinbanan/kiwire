<?php 


if(isset($_REQUEST['session'])) {

    $session_id = $_REQUEST['session'];
    session_id($session_id);
    session_start();

}else {
    session_start();
    $session_id = session_id();
}


require_once dirname(__FILE__, 5) . "/admin/includes/include_connection.php";
require_once dirname(__FILE__, 5) . "/admin/includes/include_general.php";

require_once dirname(__FILE__, 4) . "/includes/include_general.php";


$kiw_tenant = dirname(__FILE__);
if (strpos($kiw_tenant, "bpanel/custom") == false){

    print_error_message(100, "Invalid tenant", "Invalid tenant info has been provided");

}

$kiw_tenant = array_filter(explode("/", $kiw_tenant));


foreach ($kiw_tenant as $kiw_index => $kiw_tenant_){

    if ($kiw_tenant_ == "custom"){

        $kiw_tenant = $kiw_tenant[$kiw_index + 1];

        break;

    }

    
    
}


unset($kiw_index);

unset($kiw_tenant_);


if (is_array($kiw_tenant)){

    print_error_message(101, "Invalid tenant", "Invalid tenant info has been provided");

}


$_SESSION['bpanel']['tenant_id'] = $kiw_tenant;


$kiw_bpanel = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_bpanel_template WHERE tenant_id = '{$kiw_tenant}' LIMIT 1");

if ($kiw_bpanel['enabled'] == "y") {
    
    $kiw_profiles    = implode("','", json_decode($kiw_bpanel['profile'])); 
    
    $kiw_profiles     = $kiw_db->fetch_array("SELECT * FROM kiwire_profiles WHERE tenant_id = '{$kiw_tenant}' AND name IN ('" . $kiw_profiles . "') AND price > 0");



        
    $user_page = $kiw_db->query_first("SELECT SQL_CACHE content FROM kiwire_login_pages WHERE tenant_id = '{$kiw_tenant}' AND unique_id = '{$kiw_bpanel['page']}' LIMIT 1");


    if(empty($user_page)) error_redirect($_SERVER['HTTP_REFERER'], "No page found.");


    $user_page = $user_page['content'];
    $user_page = urldecode(base64_decode($user_page));

        

    $html['profile_list'] = "";

    if(isset($_SESSION['bpanel']['voucher'])) {

        if(!empty($_SESSION['bpanel']['voucher'])) {


            $html['profile_list'] .= '<div class="col-md-12">
            <div class="card">
            <div class="card-content">
                <div class="card-header">
                    <h5>List of buy voucher </h5>
                </div>
                <br><ol>';
                    foreach ($_SESSION['bpanel']['voucher'] as $valueVoucher) {
                        $html['profile_list'] .= '<li> '. $valueVoucher .'</li>';
                    }
            
            $html['profile_list'] .= '</ol>
                </div>
                </div>
            </div>';
        }


    }


    foreach($kiw_profiles as $kiw_profile) {



        $kiw_profile['attribute'] = json_decode($kiw_profile['attribute'], true);
        $quota          = $kiw_profile['attribute']['control:Kiwire-Total-Quota'];
        if($quota == 0) $quota = "Unlimited"; 
        $html['profile_list'] .= '<div class="col-md-3 col-6">
                                <div class="card">
                                    <div class="card-content">
                                        <div class="card-header">
                                            <h4 class="card-title">'. ucwords($kiw_profile['name']) .'</h4>
                                        </div>
                                        <div class="card-body">

                                            <label class="">Total Quota : </label>
                                            <br>
                                            <label class="">'. ($quota == 'Unlimited' ? 'Unlimited' : ($quota . 'Mb')) .'</label>
                                            <form action="/custom/'. $kiw_tenant .'/payment-gateway/midtrans/init/?session='. $session_id .'" method="POST">
                                                <input type="hidden" name="profile" value="'. $kiw_profile['name'] .'" >
                                                <button class="btn btn-success btn-sm"  onclick="loadSpin()" type="submit" style="padding: 0px !important;width:100% !important;">Rp '. $kiw_profile['price'] .'</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>';
    }




    $user_page = str_replace(array("{{profile_list}}"), array($html['profile_list']), $user_page);



        

    // require_once "assets/header.php";
    require_once dirname(__FILE__, 5) . "/user/header.php";

    ?>
    <style type="text/css">

        .loader-spinner {
            background-color: rgb(0 0 0 / 0.4);
            /*background: #f4f3ef;*/
            position: absolute;
            width: 100%;
            height: 100%;
            left: 0;
            top: 0;
            bottom: 0;
            z-index: 99;
            overflow: hidden !important;
            animation: rotation 10s infinite linear;
        }

        .loader-spinner img {
            width: 100px;
            height: 100px;
            position: sticky;
            top: 40%;
            left: 40%;
            margin-left: -50px;
            margin-right: -50px;
            -webkit-animation: spin 1s linear infinite;
            animation: spin 1s linear infinite;
            background-color: transparent;
        }

    

    </style>


    <div id="loader-spinner" class="loader-spinner" style="display:none;">
        <img src="/bpanel/templates/load.gif" style="animation: rotation 1s infinite linear;">
    </div>

    <script>


    // document.getElementsByClassName("btn-buy-profile").addEventListener("click", loadSpin);
    function loadSpin() {
        let load = document.getElementById("loader-spinner");
        load.style.display = "block";
    }

    </script>
    <?php
    echo html_entity_decode($user_page);

    require_once dirname(__FILE__, 5) . "/user/footer.php";



}else{

    print_error_message(111, "Function not available", "Please ask your network administrator to check.");

}


// $_SESSION['controller']['tenant_id'] = 'default';

// var_dump(strtotime('0000-00-00 00:00:00')); exit;
// if(empty($_SESSION['controller']['tenant_id'])){
    
//     print_error_message(110, "Invalid / Unknown Tenant ID", "Please ask your network administrator to check.");
    
// }
