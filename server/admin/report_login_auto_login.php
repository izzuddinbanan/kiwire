<?php


$kiw['module'] = "Report -> Auto Login";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

require_once 'includes/include_config.php';
require_once 'includes/include_session.php';
require_once 'includes/include_general.php';
require_once 'includes/include_header.php';
require_once 'includes/include_nav.php';
require_once 'includes/include_access.php';
require_once "includes/include_connection.php";


$kiw_db = Database::obtain();


?>


<div class="content-wrapper">

    <div class="content-header row">
        <div class="content-header-left col-md-9 col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-11">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="login_logins_record_title">Auto Login</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="login_logins_record_subtitle">
                                Listing of devices with auto login
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <section id="report_table" class="card">
        <div class="card-content">
            <div class="card-body">
                <div class="card-text">
                    <div class="table-responsive">

                        <table class="table table-hover table-data">
                            <thead>
                                <tr class="text-uppercase">

                                    <th data-i18n="login_auto_login_no">No</th>
                                    <th data-i18n="login_auto_login_no">Action</th>
                                    <th data-i18n="login_auto_login_mac_address">mac address</th>
                                    <th data-i18n="login_auto_login_last_auto_login">last auto login</th>
                                    <th data-i18n="login_auto_login_class">System</th>
                                    <th data-i18n="">Class</th>
                                    <th data-i18n="">Brand</th>
                                    <th data-i18n="">Model</th>
                                    <th data-i18n="">id</th>

                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>


<script>
    var access_user = '<?= $_SESSION['access_level'] ?>';
</script>


<?php

require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";


?>

<script src="/assets/js/datejs/build/date.js"></script>