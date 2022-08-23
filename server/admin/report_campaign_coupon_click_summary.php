<?php

$kiw['module'] = "Report -> Coupon -> View Report";
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

$kiw_coupon_list = $kiw_db->fetch_array("SELECT * FROM kiwire_coupon_generator WHERE tenant_id = '" . $tenant_id . "'");

if (!empty($coupon)) {
    foreach ($kiw_coupon_list as $item) {
        if ($item['id'] == $coupon) $coupon_name = $item['title'];
    }
}

?>

<script src="/app-assets/vendors/js/charts/echarts/echarts.min.js"></script>

<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="camp_coupon_click_title">Coupon Click Summary</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="camp_coupon_click_subtitle">
                                Total number of clicks for each coupon
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
                                <h6 class="text-bold-500" data-i18n="camp_coupon_click_date_search">Date Search :</h6>
                            </div>
                        </div>
                        <div class="col-12">

                            <div class="form-group row">

                                <div class="col-md-5 position-relative has-icon-left">
                                    <input type="text" class="form-control format-picker" name="startdate" id="startdate" value='<?= report_date_view(report_date_start("", 2)) ?>'>
                                    <div class="form-control-position">
                                        <i class="feather icon-calendar"></i>
                                    </div>
                                </div>

                                <span data-i18n="camp_coupon_click_to">to</span>

                                <div class="col-md-5 position-relative has-icon-left">
                                    <input type="text" class="form-control format-picker" name="enddate" id="enddate" value='<?= report_date_view(report_date_start("", 1)) ?>'>
                                    <div class="form-control-position">
                                        <i class="feather icon-calendar"></i>
                                    </div>
                                </div>

                                <button type="submit" name='search' id='search' class="btn btn-primary waves-effect waves-light btn-search" data-i18n="camp_coupon_click_search">Search</button>

                            </div>

                            <div class="form-group row">

                                <div class="col-md-5">
                                    <label class="" data-i18n="camp_coupon_click_select_coupon">Select Coupon</label>
                                    <select name="coupon" class="form-control select2">
                                        <option value="total">Total</option>
                                        <?php foreach ($kiw_coupon_list as $key => $value) { ?>
                                            <option value="<?= $value['id'] ?>" <?= ($value['id'] == $coupon ? "selected" : "") ?>><?= $value['title'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>

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
                            <h5 class="text-white" id="total-uniqView" style="font-size: xx-large; font-weight: bolder;">0</h5>
                            <h5 class="text-white" data-i18n="camp_coupon_click_total_unique_view">TOTAL UNIQUE VIEW</h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-md-3">
                <div class="card text-white bg-gradient-warning">
                    <div class="card-content d-flex">
                        <div class="card-body">
                            <h5 class="text-white" id="avg-uniqView" style="font-size: xx-large; font-weight: bolder;">0</h5>
                            <h5 class="text-white" data-i18n="camp_coupon_click_average_unique_view">AVERAGE UNIQUE VIEW</h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-md-3">
                <div class="card text-white bg-gradient-danger">
                    <div class="card-content d-flex">
                        <div class="card-body">
                            <h5 class="text-white" id="total-view" style="font-size: xx-large; font-weight: bolder;">0</h5>
                            <h5 class="text-white" data-i18n="camp_coupon_click_total_view">TOTAL VIEW</h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-md-3">
                <div class="card text-white bg-gradient-primary">
                    <div class="card-content d-flex">
                        <div class="card-body">
                            <h5 class="text-white" id="avg-view" style="font-size: xx-large; font-weight: bolder;">0</h5>
                            <h5 class="text-white" data-i18n="camp_coupon_click_average_view">AVERAGE VIEW</h5>
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
                            <div id="line-chart" style="width: 1659px; height: 400px; "></div>
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
                                        <th data-i18n="camp_coupon_click_table_no">No</th>
                                        <th data-i18n="camp_coupon_click_table_date">Date</th>
                                        <th data-i18n="camp_coupon_click_table_unique_view">Unique View</th>
                                        <th data-i18n="camp_coupon_click_table_total_view">Total View</th>
                                        <th data-i18n="camp_coupon_click_table_hourly_view">Hourly View</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <th data-i18n="camp_coupon_click_table_loading">
                                        Loading...
                                    </th>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>


<!-- Modal Coupon Click Summary -->
<div class="modal fade text-left" id="CouponClickSummary" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel16" data-i18n="camp_coupon_click_modal_title">Coupon View on Hourly Basis</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="col-12">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-detail">
                                        <thead>
                                            <tr class="text-uppercase">
                                                <th data-i18n="camp_coupon_click_modal_no">No</th>
                                                <th data-i18n="camp_coupon_click_modal_hour">Hour</th>
                                                <th data-i18n="camp_coupon_click_modal_unique_view">Unique View</th>
                                                <th data-i18n="camp_coupon_click_modal_total_view">Total View</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
require_once "includes/include_footer.php";
require_once "includes/include_report_footer.php";
require_once "includes/include_datatable.php";
?>