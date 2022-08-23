<?php

$kiw['module'] = "Report -> Impression vs Login Report by Zone";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="impression_vs_login_title">Captive Portal Impression VS Login By Zone</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="impression_vs_login_subtitle">
                                Impression summary on captive portal vs user login page by zone
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <section id="css-classes" class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="card-text">
                        <div class="col-12">
                            <div class="form-group row">
                                <h6 class="text-bold-500" data-i18n="impression_vs_login_date_search">Date Search :</h6>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group row">

                                <div class="col-md-3 position-relative has-icon-left">
                                    <input type="text" class="form-control format-picker" name="startdate" id="startdate" value='<?= report_date_view(report_date_start("", 2)) ?>'>
                                    <div class="form-control-position">
                                        <i class="feather icon-calendar"></i>
                                    </div>
                                </div>

                                <span data-i18n="impression_vs_login_to">to</span>

                                <div class="col-md-3 position-relative has-icon-left">
                                    <input type="text" class="form-control format-picker" name="enddate" id="enddate" value='<?= report_date_view(report_date_start("", 1)) ?>'>
                                    <div class="form-control-position">
                                        <i class="feather icon-calendar"></i>
                                    </div>
                                </div>


                                <div class="col-md-4">
                                    <?= report_project_select($kiw_db, $kiw_cache, $_SESSION['tenant_id']); ?>
                                </div>

                                <button type="submit" name='search' id='search' class="btn btn-primary waves-effect waves-light btn-search" data-i18n="impression_vs_login_search">Search</button>

                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="row">


            <div class="col-sm-6 col-md-3">
                <div class="card text-white bg-gradient-success">
                    <div class="card-content d-flex">
                        <div class="card-body">
                            <h5 class="text-white" id="totalimp" style="font-size: xx-large; font-weight: bolder;">0</h5>
                            <h5 class="text-white" data-i18n="impression_vs_login_impression">TOTAL IMPRESSION</h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-md-3">
                <div class="card text-white bg-gradient-warning">
                    <div class="card-content d-flex">
                        <div class="card-body">
                            <h5 class="text-white" id="avgimp" style="font-size: xx-large; font-weight: bolder;">0</h5>
                            <h5 class="text-white" data-i18n="impression_vs_login_average_impression">AVERAGE IMPRESSION</h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-md-3">
                <div class="card text-white bg-gradient-danger">
                    <div class="card-content d-flex">
                        <div class="card-body">
                            <h5 class="text-white" id="totallogin" style="font-size: xx-large; font-weight: bolder;">0</h5>
                            <h5 class="text-white" data-i18n="impression_vs_login_total_login">TOTAL LOGIN</h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-md-3">
                <div class="card text-white bg-gradient-primary">
                    <div class="card-content d-flex">
                        <div class="card-body">
                            <h5 class="text-white" id="avglogin" style="font-size: xx-large; font-weight: bolder;">0</h5>
                            <h5 class="text-white" data-i18n="impression_vs_login_average_login">AVERAGE LOGIN</h5>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Line Chart -->
        <section id="report_graph" class="card">
            <div class="row">
                <div class="col-12">
                    <div class="card-content">
                        <div class="card-body">
                            <div id="data-chart"></div>
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
                                        <th data-i18n="impression_vs_login_no">No</th>
                                        <th data-i18n="impression_vs_login_date">Date</th>
                                        <th data-i18n="impression_vs_login_total_impression">Total Impression</th>
                                        <th data-i18n="impression_vs_login_login">Total Login</th>
                                        <th data-i18n="impression_vs_login_hourly_view">Hourly View</th>
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
                <h4 class="modal-title" id="myModalLabel16" data-i18n="impression_vs_login_modal_title">Campaign Impression vs Success Login- Hourly</h4>
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
                            <th data-i18n="impression_vs_login_modal_hour">Hour</th>
                            <th data-i18n="impression_vs_login_modal_impression">Total Impression</th>
                            <th data-i18n="impression_vs_login_modal_login">Total Login</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>

            </div>

            <div class="modal-footer">
                <button class="btn btn-danger waves-effect waves-light" data-dismiss="modal"data-i18n="impression_vs_login_modal_close">Close</button>
            </div>

        </div>
    </div>
</div>


<?php

require_once "includes/include_footer.php";
require_once "includes/include_report_footer.php";
require_once "includes/include_datatable.php";
