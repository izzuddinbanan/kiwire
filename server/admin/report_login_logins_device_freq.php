<?php

$kiw['module'] = "Report -> Login Frequency -> Device";
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
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="login_logins_device_freq_title">Login Device Frequency</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="login_logins_device_freq_subtitle">
                                Information on device login frequency & unique device login
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- <div class="row">
        <div class="col-12 mb-1" >
            <button id="filter-btn" class="float-right btn btn-icon btn-primary btn-xs fa fa-filter"></button>
        </div>
    </div> -->

    <div class="content-body">

        <!-- <section id="css-classes" class="card">
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
                                <div class="form-group">
                                    <label data-i18n="data_zone">Zone</label>
                                    <?= report_project_select($kiw_db, $kiw_cache, $_SESSION['tenant_id']); ?>
                                </div>
                            </div>

                        </div>
                        <button type="submit" name='search' id='search' class="btn btn-primary waves-effect waves-light btn-search" data-i18n="bandwidth_account_usage_search">Search</button>

                    </div>
                </div>
            </div>
        </section> -->


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

                                <div class="col-md-4" id="zone-div" style="display:block">
                                    <div class="form-group">
                                        <form method="post">
                                            <ul class="list-unstyled mb-0">
                                                <li class="d-inline-block mr-2">
                                                    <fieldset>
                                                        <label for="zone" data-i18n="data_zone">Filter by:</label>
                                                    </fieldset>
                                                </li>
                                                <li class="d-inline-block mr-2">
                                                    <fieldset>
                                                        <div class="custom-control custom-radio">
                                                            <input type="radio" class="custom-control-input" name="customRadio" id="customRadio1" value="Zone" checked>
                                                            <label class="custom-control-label" for="customRadio1">Zone</label>
                                                        </div>
                                                    </fieldset>
                                                </li>
                                                <li class="d-inline-block mr-2">
                                                    <fieldset>
                                                        <div class="custom-control custom-radio">
                                                            <input type="radio" class="custom-control-input" name="customRadio" id="customRadio2" value="Project">
                                                            <label class="custom-control-label" for="customRadio2">Project</label>
                                                        </div>
                                                    </fieldset>
                                                </li>
                                            </ul>

                                            <div class="zone" style="display:block;">

                                                <?= report_project_select($kiw_db, $kiw_cache, $_SESSION['tenant_id']); ?>

                                            </div>

                                            <div class="project" style="display:none;">

                                                <?= report_project_option($kiw_db, $kiw_cache, $_SESSION['tenant_id']); ?>

                                            </div>

                                        </form>

                                        <!--?= report_project_select($kiw_db, $kiw_cache, $_SESSION['tenant_id']); ?-->
                                    </div>
                                </div>

                            </div>
                            <button type="submit" name='search' id='search' class="btn btn-primary waves-effect waves-light btn-search" data-i18n="bandwidth_account_usage_search">Search</button>

                        </div>
                    </div>
                </div>
            </div>
        </section>


        <section id="report_graph" class="card">
            <div class="row">
                <div class="col-12">
                    <div class="card-content">
                        <div class="card-body">
                            <div style="text-align:center;" id="data-chart"></div>
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
                                        <th data-i18n="login_logins_device_freq_no">No</th>
                                        <th data-i18n="login_logins_device_freq_date">Date</th>
                                        <th data-i18n="login_logins_device_freq_device_login">Total Device Login</th>
                                        <th data-i18n="login_logins_device_freq_unique_daily">Unique Device ( Daily )</th>
                                        <th data-i18n="login_logins_device_freq_unique_forever">Unique Device ( Forever )</th>
                                        <th data-i18n="login_logins_device_freq_hourly_view">Hourly view</th>
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
</div>



<div class="modal fade text-left" id="view-detail" role="dialog">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel16" data-i18n="login_logins_device_freq_modal_title">Login Device Frequency - Hourly</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" style="overflow: auto;">

                <div class="row">
                    <div class="col-12">
                        <div id="detail-chart" style="min-height: 500px;"></div>
                    </div>
                </div>

                <table class="table table-bordered table-detail">
                    <thead class="thead thead-light">
                        <tr>
                            <th data-i18n="login_logins_device_freq_modal_hour">Hour</th>
                            <th data-i18n="login_logins_device_freq_modal_device_login">Total Device Login</th>
                            <th data-i18n="login_logins_device_freq_modal_unique_daily">Unique Device ( Daily )</th>
                            <th data-i18n="login_logins_device_freq_modal_unique_forever">Unique Device ( Forever )</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>

            </div>

            <div class="modal-footer">
                <button class="btn btn-danger waves-effect waves-light" data-dismiss="modal" data-i18n="login_logins_device_freq_modal_close">Close</button>
            </div>

        </div>
    </div>
</div>

<!-- <div class="modal fade text-left" id="filter_modal" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="mythememodal">Filter</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">

                        <div class="card-content">
                            <div class="card-body">

                                <form class="form form-vertical">
                                    <div class="form-body">
                                        <div class="row">

                                            <div class="col-12">
                                                <div class="form-group" style="position:relative; left:auto; display:block;">
                                                    <label data-i18n="data_from">Date From</label>
                                                    <input type="text" class="form-control format-picker" name="startdate" id="startdate" value='<?= report_date_view(report_date_start("", 2)) ?>'>
                                                </div>

                                                <div class="form-group" style="position:relative; left:auto; display:block;">
                                                    <label data-i18n="data_until">Date Until</label>
                                                    <input type="text" class="form-control format-picker" name="enddate" id="enddate" value='<?= report_date_view(report_date_start("", 1)) ?>'>
                                                </div>

                                                <div class="form-group">
                                                    <label data-i18n="data_zone">Zone</label>
                                                    <?= report_project_select($kiw_db, $kiw_cache, $_SESSION['tenant_id']); ?>
                                                </div>

                                            </div>

                                            <div class="col-12">
                                                <button type="button" id="filter-data" class="btn btn-primary round mr-1 mb-1 waves-effect waves-light pull-right" data-i18n="login_logins_device_freq_modal_filter"> Filter</button>
                                                <button type="reset" class="btn btn-danger round mr-1 mb-1 waves-effect waves-light pull-right" data-dismiss="modal" data-i18n="login_logins_device_freq_modal_cancel">Cancel</button>
                                            </div>

                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div> -->



<?php

require_once "includes/include_footer.php";
require_once "includes/include_report_footer.php";
require_once "includes/include_datatable.php";

?>