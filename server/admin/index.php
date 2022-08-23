<?php

$kiw['page'] = "Login";

require_once "includes/include_config.php";
require_once "includes/include_general.php";
require_once "includes/include_connection.php";

// check for session first

session_start();

$server_host = $_SERVER['HTTP_HOST'];

$tenant = 'default';
$style = false;

$kiw_cloud = $kiw_db->query_first("SELECT tenant_id FROM kiwire_clouds WHERE ip_address = '{$server_host}'");


if ($kiw_cloud) {

    $tenant = $kiw_cloud['tenant_id'];

    $kiw_tenant = $kiw_db->query_first("SELECT custom_style FROM kiwire_clouds WHERE tenant_id = '{$tenant}'");

    if ($kiw_tenant['custom_style'] == 'y') $style = true;
}


if (isset($_SESSION['tenant_id']) && !empty($_SESSION['tenant_id']) && !isset($_REQUEST['logout'])) {

    if ((time() - $_SESSION['last_active']) < 900) {

        header("Location: /admin/dashboard.php");

        die();
    }
} elseif (isset($_REQUEST['logout'])) {


    sync_logger("{$_SESSION['user_name']} logout", $_SESSION['tenant_id']);

    session_destroy();

    header("Location: /admin/index.php?error=" . base64_encode("You have been logout."));

    exit();
} elseif (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) {

    $kiw_test = base64_decode($_REQUEST['page']);

    if ($kiw_test && filter_var($kiw_test, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {

        $_SESSION['page'] = base64_encode($kiw_test);
    }
}


// check if multi-tenant or single tenant

if (file_exists(dirname(__FILE__, 2) . "/custom/cloud.license")) {


    $kiw['multi-license'] = @file_get_contents(dirname(__FILE__, 2) . "/custom/cloud.license");
    $kiw['multi-license'] = sync_license_decode($kiw['multi-license']);

    if (is_array($kiw['multi-license']) && $kiw['multi-license']['multi-tenant'] == true) {

        if ($kiw['multi-license']['multi-tenant']['type'] !== "trial" || $kiw['multi-license']['multi-tenant']['expiry'] > time()) {

            $kiw['multi-tenant'] = true;
        } else {

            $kiw['multi-tenant'] = false;
        }
    } else {

        $kiw['multi-tenant'] = false;
    }
} else {

    $kiw['multi-tenant'] = false;
}


if (isset($_REQUEST['error']) && !empty($_REQUEST['error'])) {


    $kiw_error = base64_decode($_REQUEST['error']);

    if (!in_array(md5($kiw_error), array("e528ef292db929b911372d110dd180e5", "a190cafdd6cbd7ca8451cadfbe338bdb", "bc6ea9c527e9729050a10c77e9e508c6"))) {

        $kiw_error = "";
    }
} else $kiw_error = "";


$kiw_temp = @file_get_contents(dirname(__FILE__, 2) . "/custom/system_smtp.json");

$kiw_temp = json_decode($kiw_temp, true);

$kiw_smtp = array_filter($kiw_temp);


?>



<!--

=========================================================
* Volt Pro - Premium Bootstrap 5 Dashboard
=========================================================

* Product Page: https://themesberg.com/product/admin-dashboard/volt-bootstrap-5-dashboard
* Copyright 2021 Themesberg (https://www.themesberg.com)
* License (https://themes.getbootstrap.com/licenses/)

* Designed and coded by https://themesberg.com

=========================================================

* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software. Please contact us to request a removal.

-->



<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <!-- Primary Meta Tags -->
    <title><?= sync_brand_decrypt(SYNC_PRODUCT) . " " . sync_brand_decrypt(SYNC_TITLE) ?><?= isset($kiw['page']) ? " | {$kiw['page']}" : '' ?></title>
    <!-- <title>Kiwire Admin | Login</title> -->
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="title" content="Kiwire Admin | Login">
    <meta name="author" content="Themesberg">
    <meta name="description" content="Volt Pro is a Premium Bootstrap 5 Admin Dashboard featuring over 800 components, 10+ plugins and 20 example pages using Vanilla JS.">
    <meta name="keywords" content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, themesberg, themesberg dashboard, themesberg admin dashboard" />
    <link rel="canonical" href="https://themesberg.com/product/admin-dashboard/volt-premium-bootstrap-5-dashboard">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://demo.themesberg.com/volt-pro">
    <meta property="og:title" content="Volt Premium Bootstrap Dashboard - Sign in page">
    <meta property="og:description" content="Volt Pro is a Premium Bootstrap 5 Admin Dashboard featuring over 800 components, 10+ plugins and 20 example pages using Vanilla JS.">
    <meta property="og:image" content="https://themesberg.s3.us-east-2.amazonaws.com/public/products/volt-pro-bootstrap-5-dashboard/volt-pro-preview.jpg">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://demo.themesberg.com/volt-pro">
    <meta property="twitter:title" content="Volt Premium Bootstrap Dashboard - Sign in page">
    <meta property="twitter:description" content="Volt Pro is a Premium Bootstrap 5 Admin Dashboard featuring over 800 components, 10+ plugins and 20 example pages using Vanilla JS.">
    <meta property="twitter:image" content="https://themesberg.s3.us-east-2.amazonaws.com/public/products/volt-pro-bootstrap-5-dashboard/volt-pro-preview.jpg">

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="120x120" href="../assets/images/<?= sync_brand_decrypt(SYNC_ICON) ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/images/<?= sync_brand_decrypt(SYNC_ICON) ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/<?= sync_brand_decrypt(SYNC_ICON) ?>">

    <!-- <link rel="apple-touch-icon" sizes="120x120" href="/test/assets/img/favicon/apple-touch-icon.png"> -->
    <!-- <link rel="icon" type="image/png" sizes="32x32" href="/test/assets/img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/test/assets/img/favicon/favicon-16x16.png"> -->

    <link rel="manifest" href="../assets/images/favicon/site.webmanifest">
    <link rel="mask-icon" href="../assets/images/favicon/safari-pinned-tab.svg" color="#ffffff">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="theme-color" content="#ffffff">

    <!-- Sweet Alert -->
    <link type="text/css" href="../app-assets/vendors/css/sweetalert2/sweetalert2.min.css" rel="stylesheet">

    <!-- Notyf -->
    <link type="text/css" href="../app-assets/vendors/css/notyf/notyf.min.css" rel="stylesheet">

    <!-- Volt CSS -->
    <link type="text/css" href="../app-assets/vendors/css/volt.css" rel="stylesheet">


    <?php
    if ($style) {
        if (is_dir(dirname(__FILE__, 2) . "/custom/{$tenant}/stylesheets")) { ?>
            <link rel="stylesheet" type="text/css" href="/custom/<?= $tenant ?>/stylesheets/app.css">

    <?php }
    } ?>

</head>

<body>

    <main>

        <!-- Section -->
        <section class="vh-lg-100 mt-5 mt-lg-0 bg-soft d-flex align-items-center">
            <div class="container">

                <div class="row justify-content-center form-bg-image" data-background-lg="<?= $style ? (file_exists(dirname(__FILE__, 2) . '/custom/' . $tenant . '/stylesheets/images/login.png') ? '/custom/' . $tenant . '/stylesheets/images/login.png' : '/app-assets/images/pages/signin.svg') : '/app-assets/images/pages/signin.svg' ?>">
                    <div class="col-12 d-flex align-items-center justify-content-center">
                        <div class="bg-white shadow border-0 rounded border-light p-4 p-lg-5 w-100 fmxw-500">
                            <div class="text-center text-md-center mb-4 mt-md-0">
                                <img src="../assets/images/<?= sync_brand_decrypt(SYNC_LOGO_BIG) ?>" style="display: block; margin: 15px auto; margin-top: -14px; width: 50%; max-width: 200px;">
                                <h1 class="mb-0 h3">Sign in to your account</h1>
                            </div>

                            <form class="login" action="/admin/" method="post">

                                <?php
                                if ($_REQUEST['page']) { ?>
                                    <input type="hidden" value="<?= $_REQUEST['page'] ?>" name="page">
                                <?php    }
                                ?>
                                <?php if ($_SERVER['HTTPS'] != "on") { ?>
                                    <div class="alert alert-danger mt-1">WARNING: You are using non-secure connection.</div>
                                <?php } ?>


                                <div class="row">
                                    <div class="col-12 mb-2 mt-1">
                                        <h6 class="text-danger"><?= $kiw_error ?></h6>
                                    </div>
                                </div>


                                <!-- Form -->
                                <?php if ($kiw['multi-tenant'] == true) { ?>

                                    <div class="form-group mb-4">
                                        <label for="email">Tenant ID</label>
                                        <div class="input-group">
                                            <span class="input-group-text" id="basic-addon1">
                                                <svg class="icon icon-xs text-gray-600" fill="currentColor" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M447.465,221.387C442.055,155.672,387.109,104,320,104c-48.047,0-89.857,26.512-111.75,65.668C203.004,168.582,197.57,168,192,168c-33.766,0-62.502,20.984-74.242,50.57C110.756,216.945,103.496,216,96,216c-3.02,0-96,42.98-96,96s42.98,96,96,96h320c53.02,0,96-42.98,96-96C512,270.02485.008,234.422,447.465,221.387z M384,248c-8.836,0-16-7.164-16-16c0-26.469-21.531-48-48-48c-8.836,0-16-7.164-16-16s7.164-16,16-16c44.109,0,80,35.891,80,80C400,240.836,392.836,248,384,248z"></path>
                                                </svg>
                                            </span>
                                            <input type="text" class="form-control" placeholder="Tenant ID" name="tenant" id="tenant" value="<?= $tenant ?>" autofocus required>
                                        </div>
                                    </div>

                                <?php } else { ?>

                                    <input type="hidden" name="tenant" class="" id="tenant" value="default">

                                <?php } ?>
                                <!-- End of Form -->

                                <!-- Form -->
                                <div class="form-group mb-4">
                                    <label for="email">Username</label>
                                    <div class="input-group">
                                        <span class="input-group-text" id="basic-addon1">
                                            <svg class="icon icon-xs text-gray-600" fill="currentColor" viewBox="0 0 258.75 258.75" xmlns="http://www.w3.org/2000/svg">
                                                <circle cx="129.375" cy="60" r="60" />
                                                <path d="M129.375,150c-60.061,0-108.75,48.689-108.75,108.75h217.5C238.125,198.689,189.436,150,129.375,150z"></path>
                                            </svg>
                                        </span>
                                        <input type="text" class="form-control" placeholder="Username" name="username" id="user-name" autofocus required>
                                    </div>
                                </div>
                                <!-- End of Form -->

                                <div class="form-group">
                                    <!-- Form -->
                                    <div class="form-group mb-4">
                                        <label for="password">Your Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text" id="basic-addon2">
                                                <svg class="icon icon-xs text-gray-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                                </svg>
                                            </span>
                                            <input type="password" placeholder="Password" class="form-control" name="password" id="user-password" required>
                                            <span class="input-group-text" id="basic-addon2" onclick="myFunction()">
                                                <svg class="icon icon-xs text-gray-600" fill="currentColor" viewBox="0 0 612 612" xmlns="http://www.w3.org/2000/svg" style="enable-background:new 0 0 612 612; display:none" id="eye-show">
                                                    <path d="M609.608,315.426c3.19-5.874,3.19-12.979,0-18.853c-58.464-107.643-172.5-180.72-303.607-180.72S60.857,188.931,2.393,296.573c-3.19,5.874-3.19,12.979,0,18.853C60.858,423.069,174.892,496.147,306,496.147S551.143,423.069,609.608,315.426z M306,451.855c-80.554,0-145.855-65.302-145.855-145.855S225.446,160.144,306,160.144S451.856,225.446,451.856,306S386.554,451.855,306,451.855z"/>
		                                            <path d="M306,231.67c-6.136,0-12.095,0.749-17.798,2.15c5.841,6.76,9.383,15.563,9.383,25.198c0,21.3-17.267,38.568-38.568,38.5c-9.635,0-18.438-3.541-25.198-9.383c-1.401,5.703-2.15,11.662-2.15,17.798c0,41.052,33.279,74.33,74.33,74.33s74.33-33.279,74.33-74.33S347.052,231.67,306,231.67z"/>
                                                </svg>
                                                <svg class="icon icon-xs text-gray-600" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="display:block" id="eye-hide">
                                                    <path d="M2 2L22 22" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>   
		                                            <path d="M6.71277 6.7226C3.66479 8.79527 2 12 2 12C2 12 5.63636 19 12 19C14.0503 19 15.8174 18.2734 17.2711 17.2884M11 5.05822C11.3254 5.02013 11.6588 5 12 5C18.3636 5 22 12 22 12C22 12 21.3082 13.3317 20 14.8335" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M14 14.2362C13.4692 14.7112 12.7684 15.0001 12 15.0001C10.3431 15.0001 9 13.657 9 12.0001C9 11.1764 9.33193 10.4303 9.86932 9.88818" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-top mb-4">
                                        <?php

                                        if (!empty($kiw_smtp)) { ?>

                                            <div><a href="" class="small text-right forgot-password" data-bs-toggle="modal" data-bs-target="#forget-password">Forgot password?</a></div>

                                        <?php  } else {  ?>

                                            <div><a href="" class="small text-right"></a></div>

                                        <?php  } ?>

                                    </div>
                                    <!-- End of Form -->

                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-gray-800">Login</button>
                                </div>

                            </form>

                            <div class="row" id="browser-warning" style="display: none;">
                                <div class="col-md-12 text-center p-1">
                                    <span class="label">
                                        * For better experience while using this system, please use Google Chrome or Mozilla Firefox
                                    </span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Modal Content -->
    <div class="modal fade" id="forget-password" tabindex="-1" role="dialog" aria-labelledby="forget-passwordModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="card p-3 p-lg-4">

                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                        <div class="text-center text-md-center mb-4 mt-md-0">
                            <h1 class="mb-0 h4">Reset Password</h1>
                        </div>

                        <form class="mt-4 reset-password">

                            <!-- Form -->
                            <div class="form-group mb-4">
                                <label for="email">Your Email</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon1">
                                        <svg class="icon icon-xs text-gray-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                                        </svg>
                                    </span>
                                    <input type="email" class="form-control" placeholder="Enter your email address" name="email" id="email" autofocus required>
                                </div>
                            </div>
                            <!-- End of Form -->

                            <!-- Form -->
                            <div class="form-group mb-4">
                                <label for="email">Tenant ID</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon1">
                                        <svg class="icon icon-xs text-gray-600" fill="currentColor" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M447.465,221.387C442.055,155.672,387.109,104,320,104c-48.047,0-89.857,26.512-111.75,65.668C203.004,168.582,197.57,168,192,168c-33.766,0-62.502,20.984-74.242,50.57C110.756,216.945,103.496,216,96,216c-3.02,0-96,42.98-96,96s42.98,96,96,96h320c53.02,0,96-42.98,96-96C512,270.02485.008,234.422,447.465,221.387z M384,248c-8.836,0-16-7.164-16-16c0-26.469-21.531-48-48-48c-8.836,0-16-7.164-16-16s7.164-16,16-16c44.109,0,80,35.891,80,80C400,240.836,392.836,248,384,248z"></path>
                                        </svg>
                                    </span>
                                    <input type="text" class="form-control" placeholder="Enter your Tenant ID" name="tenant_id" id="tenant_id" autofocus required>
                                </div>
                            </div>
                            <!-- End of Form -->

                            <div class="form-group">
                                <!-- Form -->
                                <div class="form-group mb-4">
                                    <label for="password">Verification Code</label>
                                    <div class="input-group">
                                        <span class="input-group-text" id="basic-addon2">
                                            <svg class="icon icon-xs text-gray-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        </span>
                                        <input type="text" placeholder="Please type the code in the image below" class="form-control" id="captcha" name="captcha" required>
                                    </div>
                                </div>
                                <!-- End of Form -->
                            </div>


                            <!-- Form -->
                            <div class="form-group mb-4">
                                <!-- <div class="input-group"> -->
                                <img class="captcha-img" src="" alt="" style="height: 60px; margin: 10px 0px;">
                                <!-- </div> -->
                            </div>
                            <!-- End of Form -->


                            <div class="d-grid">
                                <button type="submit" class="btn btn-gray-800" id="recoverbtn">Recover Password</button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End of Modal Content -->

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    ...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="sampleButton">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        var mfactors_pending = <?= ($_REQUEST['mfactor'] === "false" ? "true" : "false") ?>;
    </script>

    <script>
        function myFunction() {

            var x = document.getElementById("user-password");
            var y = document.getElementById("eye-show");
            var z = document.getElementById("eye-hide");

            if (x.type === "password") {
                x.type = "text";
                y.style.display = "block";
                z.style.display = "none";

            } else {
                x.type = "password";
                z.style.display = "block";
                y.style.display = "none";
            }
        }
    </script>


    <!-- Core -->
    <script src="../app-assets/vendors/js/@popperjs/popper.min.js"></script>
    <script src="../app-assets/vendors/js/bootstrap/bootstrap.min.js"></script>

    <script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>


    <!-- Vendor JS -->
    <script src="../app-assets/vendors/js/onscreen/on-screen.umd.min.js"></script>

    <!-- Slider -->
    <script src="../app-assets/vendors/js/nouislider/nouislider.min.js"></script>

    <!-- Smooth scroll -->
    <script src="../app-assets/vendors/js/smooth-scroll/smooth-scroll.polyfills.min.js"></script>

    <!-- Charts -->
    <script src="../app-assets/vendors/js/chartist/chartist.min.js"></script>
    <script src="../app-assets/vendors/js/chartist-plugin-tooltips/chartist-plugin-tooltip.min.js"></script>

    <!-- Datepicker -->
    <script src="../app-assets/vendors/js/vanillajs-datepicker/datepicker.min.js"></script>

    <!-- Sweet Alerts 2 -->
    <script src="../app-assets/vendors/js/sweetalert2/sweetalert2.all.min.js"></script>

    <!-- Moment JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.27.0/moment.min.js"></script>

    <!-- Vanilla JS Datepicker -->
    <script src="../app-assets/vendors/js/vanillajs-datepicker/datepicker.min.js"></script>

    <!-- Notyf -->
    <script src="../app-assets/vendors/js/notyf/notyf.min.js"></script>

    <!-- Simplebar -->
    <script src="../app-assets/vendors/js/simplebar/simplebar.min.js"></script>

    <!-- Github buttons -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>

    <!-- Volt JS -->
    <script src="../assets/js/volt.js"></script>


    <script src="/app-assets/vendors/js/vendors.min.js"></script>
    <script src="/app-assets/js/core/app-menu.js"></script>
    <script src="/app-assets/js/core/app.js"></script>
    <script src="/app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>

    <script src="javascripts/index.js"></script>


</body>

</html>