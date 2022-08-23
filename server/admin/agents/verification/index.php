<?php

$kiw['module'] = "Account -> QR Verification";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

require_once dirname(__FILE__, 3) . '/includes/include_config.php';
require_once dirname(__FILE__, 3) . '/includes/include_session.php';
require_once dirname(__FILE__, 3) . '/includes/include_general.php';
require_once dirname(__FILE__, 3) . '/includes/include_connection.php';

require_once dirname(__FILE__, 4) . '/user/includes/include_account.php';



$kiw_qrlogin = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_qr WHERE tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");


if (!isset($_REQUEST['verification-code'])) {


    if ($kiw_qrlogin['enabled'] == "y") {


        // check if got zone force profile and allowed zone



        if (isset($_REQUEST['data']) && !empty($_REQUEST['data'])) {


            $kiw_data = base64_decode($_REQUEST['data']);

            if ($kiw_data) {


                $kiw_data = sync_decrypt($kiw_data);


                if ($kiw_data) {


                    $kiw_data = json_decode($kiw_data, true);

                    $kiw_profile = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_profiles WHERE name = '{$kiw_qrlogin['profile']}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");

                    $kiw_profile['attribute'] = json_decode($kiw_profile['attribute'], true);


                } else {

                    header("Location: /admin/");

                    die();

                }


            } else {

                header("Location: /admin/");

                die();

            }


        } else {

            header("Location: /admin/");

            die();

        }


    } else {

        header("Location: /admin/");

        die();

    }


    $_SESSION['verification-code'] = md5(time() . rand(1, 1000));


} else {


    header("Content-Type: application/json");


    if (!empty($_SESSION['verification-code']) && $_REQUEST['verification-code'] == $_SESSION['verification-code']){


        unset($_SESSION['verification-code']);


        $kiw_username = str_replace(array(":", "-"), "", $_REQUEST['user-mac']);

        $kiw_password = substr(md5(time() . rand(1000, 9999)), rand(1, 10), 12);


        $kiw_existed = $kiw_db->query_first("SELECT password FROM kiwire_account_auth WHERE username = '{$kiw_username}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");


        if (empty($kiw_existed['password'])){


            // check if got zone force profile and allowed zone




            $kiw_create_user = array();

            $kiw_create_user['tenant_id']      = $_SESSION['tenant_id'];
            $kiw_create_user['username']       = $kiw_username;
            $kiw_create_user['password']       = $kiw_password;
            $kiw_create_user['remark']         = "";
            $kiw_create_user['profile_subs']   = $kiw_qrlogin['profile'];
            $kiw_create_user['ktype']          = "account";
            $kiw_create_user['status']         = "active";
            $kiw_create_user['integration']    = "qr";
            $kiw_create_user['allowed_zone']   = $kiw_qrlogin['allowed_zone'];
            $kiw_create_user['date_value']     = "NOW()";
            $kiw_create_user['date_expiry']    = date("Y-m-d H:i:s", strtotime("+{$kiw_qrlogin['validity']} Day"));

            create_account($kiw_db, $kiw_cache, $kiw_create_user);


        } else {

            $kiw_password = sync_decrypt($kiw_existed['password']);

        }


        $kiw_cache->set("QR_LOGIN_AUTH:{$_SESSION['tenant_id']}:{$_REQUEST['random-string']}", array("username" => $kiw_username, "password" => $kiw_password), 300);

        echo json_encode(array("status" => "success", "message" => "The device should be online now!", "data" => ""));


    } else {


        echo json_encode(array("status" => "error", "message" => "Please re-scan the QR code", "data" => null));


    }


    die();


}

?>

<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">

<head>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">

    <title><?= sync_brand_decrypt(SYNC_PRODUCT) . " " . sync_brand_decrypt(SYNC_TITLE) ?><?= isset($kiw['page']) ? " | {$kiw['page']}" : '' ?></title>

    <link rel="apple-touch-icon" href="/app-assets/images/ico/apple-icon-120.png">
    <link rel="shortcut icon" type="image/x-icon" href="/app-assets/images/ico/favicon.ico">

    <link href="/libs/google-fonts/montserrat/montserrat.css" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/vendors.min.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/ui/prism.min.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/css/bootstrap-extended.css">

    <link rel="stylesheet" type="text/css" href="/app-assets/css/colors.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/css/components.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/css/themes/dark-layout.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/css/themes/semi-dark-layout.css">

    <link rel="stylesheet" type="text/css" href="/app-assets/css/core/menu/menu-types/vertical-menu.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/css/core/colors/palette-gradient.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/extensions/toastr.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/forms/spinner/jquery.bootstrap-touchspin.css">

    <link rel="stylesheet" type="text/css" href="/assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/parsley.css">

    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/forms/select/select2.min.css">

    <link rel="stylesheet" type="text/css" href="/admin/designer/contentbuilder/contentbuilder.css">
    <link rel="stylesheet" type="text/css" href="/admin/designer/assets/minimalist-blocks/content.css">


</head>

<body class="vertical-layout vertical-menu-modern 1-column  navbar-sticky footer-static" data-open="click" data-menu="vertical-menu-modern" data-col="1-column">

<div class="app-content content">

    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>

    <nav class="header-navbar navbar-expand-lg navbar navbar-light navbar-shadow">
        <div class="navbar-wrapper">
            <div class="navbar-container content">
                <div class="navbar-collapse" id="navbar-mobile">

                    <div class="mr-auto float-left bookmark-wrapper d-flex align-items-center">
                        <ul class="nav navbar-nav">
                            <li class="nav-item mobile-menu d-xl-none mr-auto"><a class="nav-link nav-menu-main menu-toggle hidden-xs" href="#"><i class="ficon feather icon-menu"></i></a></li>
                        </ul>

                        <ul class="nav navbar-nav">
                            <li class="nav-item d-none d-lg-block">
                                <span data-i18n="customer_name">Customer</span>: <?= $_SESSION['company_name'] ?>
                            </li>
                        </ul>
                    </div>

                    <ul class="nav navbar-nav float-right">


                        <li class="dropdown dropdown-user nav-item">

                            <a class="dropdown-toggle nav-link dropdown-user-link" href="#" data-toggle="dropdown">
                                <div class="user-nav d-sm-flex d-none">
                                    <span class="user-name text-uppercase text-bold-600"></span>
                                    <span class="user-status">default</span>
                                </div>
                                <span>
                                    <img class="round" src="/app-assets/images/portrait/small/avatar-s-11.png"
                                         alt="avatar" height="40" width="40"/>
                                </span>
                            </a>

                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="/admin/index.php?logout=now"><i class="feather icon-power"></i> Logout</a>
                            </div>

                        </li>


                    </ul>
                </div>
            </div>
        </div>
    </nav>


    <div class="content-wrapper" style="margin-top: auto;">
        <div class="content-body">
            <div class="row">

                <div class="col-md-6 offset-md-3">

                    <div class="card">
                        <div class="card-body">
                            <div class="card-content">

                                <div class="mb-1" style="">Please confirm to allow internet access for the below device:</div>

                                <form action="" method="post" class="form form-verify">

                                    <div class="form-body">

                                        <div class="form-group">
                                            <label for="profile-name">Profile Name</label>
                                            <input type="text" id="profile-name" name="profile-name" class="form-control" value="<?= $kiw_profile['name'] ?>" readonly />
                                        </div>

                                        <div class="form-group">
                                            <label for="profile-name">Profile Download Speed</label>
                                            <input type="text" id="profile-download-speed" name="profile-download-speed" class="form-control" value="<?= ($kiw_profile['attribute']['reply:WISPr-Bandwidth-Max-Down'] / pow(1024, 2)) ?> Mbps" readonly />
                                        </div>

                                        <div class="form-group">
                                            <label for="profile-name">Profile Upload Speed</label>
                                            <input type="text" id="profile-upload-speed" name="profile-upload-speed" class="form-control" value="<?= ($kiw_profile['attribute']['reply:WISPr-Bandwidth-Max-Up'] / pow(1024, 2)) ?> Mbps" readonly />
                                        </div>

                                        <div class="form-group">
                                            <label for="profile-name">Profile Quota</label>
                                            <input type="text" id="profile-quota" name="profile-quota" class="form-control" value="<?= ($kiw_profile['attribute']['control:Kiwire-Total-Quota'] == 0) ? "Unlimited" : ($kiw_profile['attribute']['control:Kiwire-Total-Quota'] / pow(1024, 2)) ?> Mb" readonly />
                                        </div>

                                        <div class="form-group">
                                            <label for="profile-name">User MAC Address</label>
                                            <input type="text" id="user-mac" name="user-mac" class="form-control" value="<?= $kiw_data['mac_address'] ?>" readonly />
                                        </div>

                                        <div class="form-group">
                                            <label for="profile-name">User IP Address</label>
                                            <input type="text" id="user-ip" name="user-ip" class="form-control" value="<?= $kiw_data['ip_address'] ?>" readonly />
                                        </div>

                                        <div class="form-group">
                                            <label for="profile-name">Device Type</label>
                                            <input type="text" id="device-type" name="device-type" class="form-control" value="<?= (empty($kiw_data['device_type']) ? "Unknown" : $kiw_data['device_type']) ?>" readonly />
                                        </div>

                                        <div class="form-group">
                                            <label for="profile-name">Device Brand</label>
                                            <input type="text" id="device-brand" name="device-brand" class="form-control" value="<?= (empty($kiw_data['device_brand']) ? "Unknown" : $kiw_data['device_brand']) ?>" readonly />
                                        </div>

                                        <div class="row">
                                            <div class="col-6">
                                                <button class="btn btn-primary" type="submit">Confirm</button>
                                            </div>
                                            <div class="col-6">
                                                <button class="btn btn-danger pull-right btn-cancel" type="button">Cancel</button>
                                            </div>
                                        </div>

                                    </div>

                                    <input type="hidden" name="random-string" value="<?= $kiw_data['random_string'] ?>">
                                    <input type="hidden" name="verification-code" value="<?= $_SESSION['verification-code'] ?>">

                                </form>


                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

</div>

</body>

<script src="/app-assets/vendors/js/vendors.min.js"></script>
<script src="/app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>
<script src="/app-assets/vendors/js/extensions/toastr.min.js"></script>

<script src="/app-assets/vendors/js/forms/select/select2.min.js"></script>

<script src="/assets/js/parsley.js"></script>
<script src="/app-assets/js/core/app-menu.js"></script>
<script src="/app-assets/js/core/app.js"></script>
<script src="/app-assets/js/scripts/components.js"></script>
<script src="/app-assets/vendors/js/forms/spinner/jquery.bootstrap-touchspin.js"></script>

<script src="/assets/js/jquery-ui.min.js" type="text/javascript"></script>
<script src="/assets/js/jquery.ui.touch-punch.min.js" type="text/javascript"></script>


<script>

    $(".btn-cancel").on("click", function(){

        window.location.href = "/admin/";

    });


    $("form.form-verify").on("submit", function (e) {


        e.preventDefault();


        $.ajax({
            url: "/admin/agents/verification/",
            method: "post",
            data: $(this).serialize(),
            success: function (response) {

                if (response['status'] === "success"){


                    swal("Success", response['message'], "success");


                    setTimeout(function () {

                        window.location.href = "/admin/";

                    }, 3000);


                } else {

                    swal("Error", response['message'], "error");

                }

            },
            error: function (response) {

                swal("Error", "There is unexpected error. Please try again.", "error");

            }
        });



    });

</script>


</html>
