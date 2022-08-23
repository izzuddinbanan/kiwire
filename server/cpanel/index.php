<?php


$kiw_page = "Login";


session_start();


require_once "includes/include_general.php";


global $kiw_db;


$kiw_logout       = $_GET['logout'];
$kiw_destination  = $_GET['dst'];
$kiw_session      = $_GET['session'];
$kiw_redirect     = $_GET['redirect'];



if ($kiw_logout == "true"){


    // if came from tenant landing page / mikrotik status page, redirect back there after logout

    unset($_SESSION['cpanel']['username']);

    if ($kiw_destination == "mainpage") {

        // unset($_SESSION['cpanel']['username']);

        header("Location: /user/pages/?session=".$kiw_session);

    } else if ($kiw_destination == "status-mk") {

        header("Location: /custom/".$_SESSION['cpanel']['redirect']);

    }

    // unset($_SESSION['cpanel']['username']);

    $kiw_status = "You have been logged out.";


}

if (isset($_SESSION['cpanel']['username'])){

    header("Location: /cpanel/dashboard.php");

}





// check if not multi-tenant then set the tenant to the only one

if (file_exists(dirname(__FILE__, 2) . "/custom/cloud.license")) {


    $kiw_multi = @file_get_contents(dirname(__FILE__, 2) . "/custom/cloud.license");

    $kiw_multi = sync_license_decode($kiw_multi);


    if (is_array($kiw_multi) && $kiw_multi['multi-tenant'] == true) {

        $kiw_multi = true;

    } else {

        $kiw_multi = false;

    }


} else {

    $kiw_multi = false;

}


$kiw_username   = $kiw_db->escape($_REQUEST['username']);

$kiw_password   = $kiw_db->escape($_REQUEST['password']);

$kiw_login_type = $kiw_db->escape($_REQUEST['login_type']);


// For numix tenant, if user login from landing page save info in session

$kiw_temp['landing_page']           = $kiw_db->escape($_GET['page']);

$_SESSION['cpanel']['landing_page'] = (strlen($kiw_temp['landing_page']) > 0) ? $kiw_db->escape($kiw_temp['landing_page']) : "NA";

$_SESSION['mainpage']['session']    = $kiw_db->escape($_GET['session']);

$_SESSION['cpanel']['redirect']     = $kiw_redirect;



if(empty($kiw_login_type)) $kiw_login_type = "account";


if ($kiw_multi == false){


    $kiw_tenant = "default";


} else {


    $kiw_tenant = $kiw_db->escape($_REQUEST['tenant']);


    if (empty($kiw_tenant)){

        $kiw_tenant = $_SESSION['cpanel']['tenant_id'];

    }


    if (empty($kiw_tenant)){

        $kiw_tenant = $_SESSION['controller']['tenant_id'];

    }


}


if (!empty($kiw_tenant)) {


    $kiw_logo = "";

    foreach(array("jpg", "jpeg", "png") as $kiw_ext) {

        if (file_exists(dirname(__FILE__, 2) . "/custom/{$kiw_tenant}/logo.{$kiw_ext}") == true) {

            $kiw_logo = dirname(__FILE__, 2) . "/custom/{$kiw_tenant}/logo.{$kiw_ext}";

        }

    }


    if (!empty($kiw_logo)){

        $kiw_logo = substr($kiw_logo, strlen(dirname(__FILE__, 2)));

    }


    $kiw_cpanel = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_cpanel_template WHERE tenant_id = '{$kiw_tenant}' LIMIT 1");

    if($kiw_cpanel){

        if ($kiw_cpanel['enabled'] != "y"){
    
            // http_response_code(404);
            // include_once "../400.php";
            // die();

            $kiw_status = $kiw_cpanel['label_wrong_credential'];
    
        }

    }
    else{

        $kiw_tenant = '';

    }

    
    $_SESSION['cpanel']['tenant_id']    = $kiw_tenant;
    $_SESSION['cpanel']['login_type']   = $kiw_login_type;



    if (!empty($kiw_username)) {


        $kiw_inactive = ($kiw_cpanel['allow_inactive'] == "y") ? "" : "AND status = 'active'";

        $kiw_user = $kiw_db->query_first("SELECT tenant_id,username,password,ktype,profile_subs FROM kiwire_account_auth WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant}' {$kiw_inactive} LIMIT 1");


        if (!empty($kiw_user)) {


            if ($kiw_user['password'] == sync_encrypt($kiw_password) || ($kiw_login_type == "voucher" && $kiw_user['ktype'] == "voucher")) {


                $_SESSION['cpanel']['logo']         = $kiw_logo;
                $_SESSION['cpanel']['username']     = $kiw_username;
                $_SESSION['cpanel']['tenant_id']    = $kiw_tenant;
                $_SESSION['cpanel']['profile_subs'] = $kiw_user['profile_subs'];

                sync_logger("{$_SESSION['cpanel']['username']} login to user panel system", $_SESSION['cpanel']['tenant_id']);

                header("Location: /cpanel/dashboard.php");
                

                die();


            } else {


                $kiw_status = $kiw_cpanel['label_wrong_credential'];


            }


        } else {

            $kiw_status = $kiw_cpanel['label_wrong_credential'] ? $kiw_cpanel['label_wrong_credential'] : 'Wrong credential provided';

        }


    }



}


require_once "includes/include_header.php";


?>

    <div class="be-wrapper be-login">
        <div class="container-fluid">
            <div class="splash-container">
                <div class="panel panel-default panel-border-color panel-border-color-primary">

                    <?php if (!empty($kiw_logo)){ ?>
                    <div class="panel-heading">
                        <img src="<?= $kiw_logo ?>" alt="Logo" style="max-width: 300px;" class="logo-img">
                    </div>
                    <?php } ?>

                    <span class="splash-description" style="font-size: large;">Please enter your credential.</span>
                    <div class="panel-body">
                        <form action="<?php echo htmlspecialchars($_SERVER[" PHP_SELF "]);?>" method="post">

                            <div class="font-weight-bold font-size-base text-danger">
                                <?= $kiw_status ?>
                            </div>

                            <div class="login-form">

                                <?php if (empty($kiw_tenant)){ ?>
                                <div class="form-group">
                                    <label for="tenant"></label>
                                    <input id="tenant" type="text" placeholder="<?= (empty($kiw_cpanel['label_tenant']) ? "Tenant Identity" : $kiw_cpanel['label_tenant']) ?>" name="tenant" autocomplete="off" class="form-control">
                                </div>
                                <?php } ?>

                                <div class="form-group">
                                    <label for="username"></label>
                                    <input id="username" type="text" placeholder="<?= (empty($kiw_cpanel['label_username']) ? "Username" : $kiw_cpanel['label_username']) ?>" name="username" autocomplete="off" class="form-control">
                                </div>

                                <?php if ($kiw_login_type != "voucher" || empty($kiw_cpanel)){ ?>
                                <div class="form-group">
                                    <label for="password"></label>
                                    <input id="password" type="password" placeholder="<?= (empty($kiw_cpanel['label_password']) ? "Password" : $kiw_cpanel['label_password']) ?>" name="password" class="form-control">
                                </div>
                                <?php } ?>

                                <input type="hidden" name="login" class="form-control">

                                <div class="form-group row login-submit">
                                    <div class="col-xs-12">
                                        <button data-dismiss="modal" type="submit" class="btn btn-primary btn-xl">Sign in</button>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

<script src="assets/lib/jquery/jquery.min.js" type="text/javascript"></script>
<script src="assets/lib/perfect-scrollbar/js/perfect-scrollbar.jquery.min.js" type="text/javascript"></script>
<script src="assets/js/main.js" type="text/javascript"></script>
<script src="assets/lib/bootstrap/dist/js/bootstrap.min.js" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function() {
        App.init();

        $(function() {
            $('.dropdown-toggle a').click(function(e) {
                $('.active').removeClass('active');
            });
        });


        $('#username').keyup(function(e) {  
            
            let string = $(this).val();
            let result = string.replace(" ", "");
            $(this).val(result);
        });
        
    });
</script>

</body>

</html>