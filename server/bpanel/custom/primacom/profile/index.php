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

// Get list of profile can sell
$kiw_bpanel = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_bpanel_template WHERE tenant_id = '{$kiw_tenant}' LIMIT 1");

if ($kiw_bpanel['enabled'] == "y") {


    // Get list of profile can sell
    $kiw_profiles    = implode("','", json_decode($kiw_bpanel['profile'])); 
    $kiw_profiles     = $kiw_db->fetch_array("SELECT * FROM kiwire_profiles WHERE tenant_id = '{$kiw_tenant}' AND name IN ('" . $kiw_profiles . "') AND price > 0");

    

    // Get page for show list of profile
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
    
    
    $html['profile_list'] .= "<div class='row'>";

    foreach($kiw_profiles as $kiw_profile) {



        $kiw_profile['attribute'] = json_decode($kiw_profile['attribute'], true);
        $quota          = $kiw_profile['attribute']['control:Kiwire-Total-Quota'];
        if($quota == 0) $quota = "Unlimited"; 
        $html['profile_list'] .= '<div class="col-md-4 col-12">
                                <div class="card">
                                    <div class="card-content">
                                        <div class="card-header">
                                            <h4 class="card-title">'. ucwords($kiw_profile['name']) .'</h4>
                                        </div>
                                        <div class="card-body">

                                            <label><b>Total Quota : </b></label>
                                            <br>
                                            <label class="">'. ($quota == 'Unlimited' ? 'Unlimited' : ($quota . 'Mb')) .'</label>
                                            <br><br>
                                            <form action="/custom/'. $kiw_tenant .'/payment-gateway/midtrans/init/?session='. $session_id .'" method="POST">
                                                <input type="hidden" name="profile" value="'. $kiw_profile['name'] .'" >

                                                <div class="row">
                                                    <div class="col-md-12 form-profile" id="form-profile-'. $kiw_profile['id'] .'" style="display:none">
                                                        <label>Name: </label> <span class="text-danger">*</span>
                                                        <div class="form-group">
                                                            <input type="text" name="name" id="user-name" class="form-control required" placeholder="eg: Johny"  required/>
                                                        </div>

                                                        <label>Email: </label> <span class="text-danger">*</span>
                                                        <div class="form-group">
                                                            <input type="email" name="email" id="user-email" class="form-control required" placeholder="eg: johny@gmail.com"  required/>
                                                        </div>

                                                        <label>Phone No: </label> <span class="text-danger">*</span>
                                                        <div class="form-group">
                                                            <input type="text" name="phone_no"  id="user-phone_no"class="form-control required" placeholder="eg: 0127886789"  required/>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button class="btn btn-info btn-sm btn-profile btn-profile-'.$kiw_profile['id'].'" onclick="buyThis('.$kiw_profile['id'].')" type="button" style="padding: 0px !important;width:100% !important;">Rp '. $kiw_profile['price'] .'</button>
                                                
                                                <button class="btn btn-success btn-sm btn-submit form-submit-'.$kiw_profile['id'].'" onclick="loadSpin()" type="submit" style="display:none;padding: 0px !important;width:100% !important;">Rp '. $kiw_profile['price'] .'</button>
                                                <hr style="display:none;margin:3px;" id="breaker-'.$kiw_profile['id'].'" class="breaker">
                                                <button class="btn btn-danger btn-sm btn-cancel form-cancel-'.$kiw_profile['id'].'" onclick="cancelBuy('.$kiw_profile['id'].')" type="button" style="display:none;padding: 0px !important;width:100% !important;">Cancel</button>

                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>';
    }


    $html['profile_list'] .= "</div>";



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


    <?php
    echo html_entity_decode($user_page);

    require_once dirname(__FILE__, 5) . "/user/footer.php";

    ?>

    <script>


    // document.getElementsByClassName("btn-buy-profile").addEventListener("click", loadSpin);
    function loadSpin() {

        if($("#user-name").val() == "" || $("#user-email").val() == "" || $("#user-phone_no").val() == "") {
            return false;
        }else {

            
            let load = document.getElementById("loader-spinner");
            load.style.display = "block";
        }
    }


    function cancelBuy(id) {
        $(".btn-profile-" + id).show();
        $("#form-profile-" + id).hide();
        $(".form-submit-" + id).hide();
        $(".form-cancel-" + id).hide();

        $(".breaker").hide();
    }
    
    function buyThis(id) {

        $(".form-profile").hide();
        $(".btn-profile").show();
        $(".btn-cancel").hide();
        $(".btn-profile-" + id).hide();

        $(".btn-submit").hide();
        
        $("#form-profile-" + id).show();
        $(".form-submit-" + id).show();
        $(".form-cancel-" + id).show();


        $(".breaker").hide();
        $("#breaker-" +id).show();
        // let form_profile_selected = document.getElementById("form-profile-" + id);
        // form_profile_selected.display = "block";

    }

    </script>

    <?php

}else{

    print_error_message(111, "Function bpanel is not available", "Please ask your network administrator to check.");

}


// $_SESSION['controller']['tenant_id'] = 'default';

// var_dump(strtotime('0000-00-00 00:00:00')); exit;
// if(empty($_SESSION['controller']['tenant_id'])){
    
//     print_error_message(110, "Invalid / Unknown Tenant ID", "Please ask your network administrator to check.");
    
// }
