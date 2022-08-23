<?php


$kiw['module'] = "Report -> Login Authentication";
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
require_once "includes/include_report.php";


$kiw_db = Database::obtain();


?>


<div class="content-wrapper">

    <div class="content-header row">
        <div class="content-header-left col-md-9 col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-11">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="login_logins_record_title">Login Authentication</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="login_logins_record_subtitle">
                                Listing of authentication's user login 
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="custom-radio">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="card-text">
                        <div class="col-12">
                            <div class="form-group row">
                                <h6 class="text-bold-500" data-i18n="report_account_expiry_search">Filter :</h6>
                            </div>
                        </div>

                        <div class="row">

                            <div class="col-md-4">
                                <div class="form-group" style="position:relative; left:auto; display:block;">
                                    <label for="startdate" data-i18n="data_from">Date From</label>
                                    <input type="text" class="form-control format-picker" name="startdate" id="startdate" value='<?= report_date_view(report_date_start("", 2)) ?>'>
                                </div>

                            </div>

                            <div class="col-md-4">
                                <div class="form-group" style="position:relative; left:auto; display:block;">
                                    <label for="enddate" data-i18n="data_until">Date Until</label>
                                    <input type="text" class="form-control format-picker" name="enddate" id="enddate" value='<?= report_date_view(report_date_start("", 1)) ?>'>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group" style="position:relative; left:auto; display:block;">
                                    <label for="enddate" data-i18n="data_until">Username</label>
                                    <input type="text" class="form-control" name="username" id="username" value=''>
                                </div>
                            </div>

                    
                        </div>

                        <button type="submit" name='search' id='search' class="btn btn-primary waves-effect waves-light btn-search" data-i18n="bandwidth_account_usage_search">Search</button><br>

                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section id="report_table" class="card">
        <div class="card-content">
            <div class="card-body">
                <div class="card-text">
                    <div class="table-responsive">

                        <table class="table table-hover table-data">
                            <thead>
                                <tr class="text-uppercase">

                                    <th data-i18n="login_auto_login_no">No</th>
                                    <th data-i18n="">Date Login</th>
                                    <th data-i18n="">Username</th>
                                    <th data-i18n="">status</th>
                                    <th data-i18n="">reason</th>
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