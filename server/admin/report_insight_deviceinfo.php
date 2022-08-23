<?php

$kiw['module'] = "Report -> Insight -> User Device Info";
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


<script src="/app-assets/vendors/js/charts/echarts/echarts.min.js"></script>

<style>
    .apexcharts-canvas {
        position: absolute  !important;
        left: 0  !important;
        margin: -16px !important;
    }
</style>
<div class="content-wrapper">

    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">

                    <?php if ($_SESSION['access_level'] == "superuser") { ?>
                        <div class="btn-group mb-1 float-right">
                            <div class="dropdown">
                                <span class="badge badge-danger">All Tenants</span>
                            </div>
                        </div>
                    <?php } ?>

                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="insight_deviceinfo_title">Devices Type Summary</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item" data-i18n="insight_deviceinfo_subtitle">
                                Information on users device connecting to your network
                            </li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- 
    <div class="row">
        <div class="col-12 mb-1">
            <button id="filter-btn" class="float-right btn btn-icon btn-primary btn-xs fa fa-filter"></button>
        </div>
    </div> -->

    <div class="content-body">

        <section id="css-classes" class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="card-text">
                        <div class="col-12">
                            <div class="form-group row">
                                <h6 class="text-bold-500" data-i18n="report_account_expiry_search">Date Search :</h6>
                            </div>
                        </div>
                        <div class="col-12">

                            <div class="form-group row">

                                <div class="col-md-4 position-relative has-icon-left">
                                    <input type="text" class="form-control format-picker" name="startdate" id="startdate" value='<?= report_date_view(report_date_start("", 2)) ?>'>
                                    <div class="form-control-position">
                                        <i class="feather icon-calendar"></i>
                                    </div>
                                </div>

                                <span data-i18n="report_account_expiry_to">to</span>

                                <div class="col-md-4 position-relative has-icon-left">
                                    <input type="text" class="form-control format-picker" name="enddate" id="enddate" value='<?= report_date_view(report_date_start("", 1)) ?>'>
                                    <div class="form-control-position">
                                        <i class="feather icon-calendar"></i>
                                    </div>
                                </div>

                                <button type="submit" name='search' id='search' class="btn btn-primary waves-effect waves-light btn-search" data-i18n="bandwidth_account_usage_search">Search</button>

                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pie Chart -->

        <div class="row">
            <div class="col-md-6 col-sm-6">
                <div class="card">

                    <div class="card-header">
                        <h4 class="card-title" data-i18n="insight_deviceinfo_piechart1">Device Types</h4>
                    </div>

                    <div class="card-content">
                        <div class="card-body">
                            <div id="pie-chart-dtype" class="height-400"></div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-md-6 col-sm-6">
                <div class="card">

                    <div class="card-header">
                        <h4 class="card-title" data-i18n="insight_deviceinfo_piechart2">Device Brands</h4>
                    </div>

                    <div class="card-content">
                        <div class="card-body">
                            <div id="pie-chart-dbrand" class="height-400"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- End Pie Chart -->

        <section id="report_table1" class="card">
            <div class="card-content">

                <div class="card-header">
                    <h4 class="card-title" data-i18n="insight_deviceinfo_table">Device Type Connecting to Network</h4>
                </div>

                <div class="card-body">
                    <div class="card-text">
                        <div class="table-responsive">
                            <table class="table table-hover table-data-1">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="insight_deviceinfo_no">No</th>
                                        <th data-i18n="insight_deviceinfo_type_device">Type of Device</th>
                                        <th data-i18n="insight_deviceinfo_total">Total</th>
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

        <section id="report_table2" class="card">
            <div class="card-content">

                <div class="card-header">
                    <h4 class="card-title" data-i18n="insight_deviceinfo_table2">Device Brand Connecting to Network</h4>
                </div>

                <div class="card-body">
                    <div class="card-text">
                        <div class="table-responsive">
                            <table class="table table-hover table-data-2">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="insight_deviceinfo_t2_no">No</th>
                                        <th data-i18n="insight_deviceinfo_t2_brand">Brand</th>
                                        <th data-i18n="insight_deviceinfo_t2_total">Total</th>
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
                                                    <label for="startdate" data-i18n="insight_deviceinfo_modal_date_from">Date From</label>
                                                    <input type="text" class="form-control format-picker" name="startdate" id="startdate" value='<?= report_date_view(report_date_start("", 2)) ?>'>
                                                </div>

                                                <div class="form-group" style="position:relative; left:auto; display:block;">
                                                    <label for="enddate" data-i18n="insight_deviceinfo_modal_date_until">Date Until</label>
                                                    <input type="text" class="form-control format-picker" name="enddate" id="enddate" value='<?= report_date_view(report_date_start("", 1)) ?>'>
                                                </div>

                                            </div>

                                            <div class="col-12">
                                                <button type="button" id="filter-data" class="btn btn-primary round mr-1 mb-1 waves-effect waves-light pull-right" data-i18n="insight_deviceinfo_modal_filter">Filter</button>
                                                <button type="reset" class="btn btn-danger round mr-1 mb-1 waves-effect waves-light pull-right" data-dismiss="modal" data-i18n="insight_deviceinfo_modal_cancel">Cancel</button>
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

require_once "includes/include_report_footer.php";
require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";

?>