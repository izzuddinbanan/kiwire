<?php

$kiw['module'] = "General -> Register 2-Factors";
$kiw['page'] = "Dashboard";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['custom'] = false;

require_once 'includes/include_config.php';
require_once 'includes/include_session.php';
require_once 'includes/include_general.php';
require_once 'includes/include_header.php';
require_once 'includes/include_nav.php';
require_once 'includes/include_access.php';
require_once "includes/include_connection.php";
require_once "includes/include_report.php";

$kiw_db = Database::obtain();

?>

<div class="content-wrapper">

    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">2-Factors Registration</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                Register 2-factors authentication for Administrator
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">

        <div class="col-6" style="margin-top: 20px;">

            <section class="card">
                <div class="card-content">

                    <div class="card-body">

                        <div class="row">
                            <div class="col-12 center text-center">

                                <br>

                                <form id="password-confirm" action="#" method="post" class="form form-horizontal">

                                    <div class="form-body">

                                        <div class="col-12 mb-75 text-justify">
                                            <?php

                                            if ($_REQUEST['reason'] == "missing-key"){

                                                echo 'Your account or tenant has been set to use 2-factors authentication, however you have not register any key.';
                                                echo '<br>&nbsp;<br>';

                                            }

                                            ?>
                                            <span data-i18n="msg_1">Please provide your current password to generate key and QR code to be registered in your Google Authenticator app.</span>
                                            <br>&nbsp;<br>
                                            <span data-i18n="msg_2">Once you registered a key, the following login onward, you will be required to provide the 6 digit code generated in Google Authenticator.</span>
                                            <br>&nbsp;<br>
                                            <span data-i18n="msg_3">Please take note that every time you refresh this page and provide the password, the key will be updated and previous keys are no longer valid.</span>
                                            <br>&nbsp;<br>
                                            <span data-i18n="msg_4">You need to make sure that your Google Authenticator using the latest key.</span>
                                            <br>&nbsp;<br>
                                            <span data-i18n="msg_5">For security reason, this page will auto refresh after 60 seconds the key generated.</span>
                                            <br>&nbsp;<br>
                                        </div>

                                        <div class="col-6 d-inline-flex">
                                            <input type="password" class="form-control" name="password" placeholder="Password">
                                        </div>

                                        <div class="col-4 d-inline-flex">
                                            <button type="submit" class="btn btn-primary" data-i18n="btn_confirm">Confirm</button>
                                        </div>

                                    </div>

                                </form>

                            </div>
                        </div>


                        <div class="row" id="display-qr" style="display: none;">
                            <div class="col-12 center text-center">

                                <img class="qr-factor border p-50" src="designer/uploads/dummy.jpg" alt="">

                            </div>

                            <div class="col-12 center text-center mt-75 text-bold-500" data-i18n="pls_scan">

                                Please scan this QR code in your Google Authenticator app.
                                <br>&nbsp;<br>
                                <span data-i18n="your_key">Your key: </span><span id="key"></span>

                            </div>

                        </div>


                        <div class="row">
                            <div class="col-12 center text-center mt-75">

                                <br>&nbsp;<br>

                                <span data-i18n="download">You can download Google Authenticator here;</span>

                                <br>&nbsp;<br>

                                <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank"><i class="fa fa-android font-large-3"></i></a>
                                &nbsp; &nbsp;
                                <a href="https://apps.apple.com/my/app/google-authenticator/id388497605#?platform=iphone" target="_blank"><i class="fa fa-apple font-large-3"></i></a>

                                <br>&nbsp;<br>

                            </div>
                        </div>


                    </div>

                </div>
            </section>

        </div>


    </div>

</div>


<?php

require_once "includes/include_footer.php";

?>