<?php

$kiw['module'] = "Report -> Insight -> Net Promoter Summary";
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

<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="insight_netpromoter_title">Net Promoter Summary</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="insight_netpromoter_subtitle">
                                Information on net promoter scoring
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
                                <h6 class="text-bold-500" data-i18n="insight_netpromoter_date_search">Date Search :</h6>
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

                                <span data-i18n="insight_netpromoter_to">to</span>

                                <div class="col-md-5 position-relative has-icon-left">
                                    <input type="text" class="form-control format-picker" name="enddate" id="enddate" value='<?= report_date_view(report_date_start("", 1)) ?>'>
                                    <div class="form-control-position">
                                        <i class="feather icon-calendar"></i>
                                    </div>
                                </div>

                                <button type="submit" name='search' id='search' class="btn btn-primary waves-effect waves-light btn-search" data-i18n="insight_netpromoter_search">Search</button>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>



        <div class="row">

            <div class="col-sm-6 col-md-6">
                <div class="card text-white bg-gradient-success">
                    <div class="card-content d-flex">
                        <div class="card-body">
                            <h5 class="text-white" id="count-positive" style="font-size: xx-large; font-weight: bolder;">0</h5>
                            <h5 class="text-white" data-i18n="insight_netpromoter_positive_feedback">Total Positive Feedback</h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-md-6">
                <div class="card text-white bg-gradient-danger">
                    <div class="card-content d-flex">
                        <div class="card-body">
                            <h5 class="text-white" id="count-negative" style="font-size: xx-large; font-weight: bolder;">0</h5>
                            <h5 class="text-white" data-i18n="insight_netpromoter_negative_feedback">Total Negative Feedback</h5>
                        </div>
                    </div>
                </div>
            </div>


        </div>

        <!-- Pie Chart -->

        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="card">

                    <div class="card-header">
                        <h4 class="card-title" data-i18n="insight_netpromoter_piechart">Positive / Negative Feedback Percentage</h4>
                    </div>

                    <div class="card-content">
                        <div class="card-body">
                            <div id="pie-chart-feedback" class="height-400"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- End Pie Chart -->


        <section id="report_table" class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="card-text">
                        <div class="table-responsive">
                            <table class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="insight_netpromoter_no">No</th>
                                        <th data-i18n="insight_netpromoter_username">Username</th>
                                        <th data-i18n="insight_netpromoter_score">Score</th>
                                        <th data-i18n="insight_netpromoter_sentiment">Sentiment</th>
                                        <th data-i18n="insight_netpromoter_comment">Comment</th>
                                        <th data-i18n="insight_netpromoter_date">Date</th>
                                        <th data-i18n="insight_netpromoter_magnitude">Magnitude</th>
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



<?php
require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";
?>