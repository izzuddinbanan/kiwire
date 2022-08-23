<?php

$kiw['module'] = "Report -> Insight -> Social Network Reputation";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="insight_reputation_review_title">Online Reputation Review</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="insight_reputation_review_subtitle">
                                Net promoter score and facebook reviews
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">

        <div class="row">

            <div class="col-sm-6 col-md-6">
                <div class="card text-white bg-gradient-danger">
                    <div class="card-content d-flex">
                        <div class="card-body">
                            <h5 class="text-white" id="count-positive" style="font-size: xx-large; font-weight: bolder;">0</h5>
                            <h5 class="text-white" data-i18n="insight_reputation_review_positive_feedback">TOTAL POSITIVE FEEDBACK</h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-md-6">
                <div class="card text-white bg-gradient-warning">
                    <div class="card-content d-flex">
                        <div class="card-body">
                            <h5 class="text-white" id="count-negative" style="font-size: xx-large; font-weight: bolder;">0</h5>
                            <h5 class="text-white" data-i18n="insight_reputation_review_negative_feedback">TOTAL NEGATIVE FEEDBACK</h5>
                        </div>
                    </div>
                </div>
            </div>


        </div>


        <div class="row">
            <div class="col-md-6 col-sm-6">
                <div class="card">

                    <div class="card-header">
                        <h4 class="card-title" data-i18n="insight_reputation_review_piechart1">User Review Based on Net Promoter Score</h4>
                    </div>

                    <div class="card-content">
                        <div class="card-body">
                            <div id="pie-chart-nps" class="height-400"></div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-md-6 col-sm-6">
                <div class="card">

                    <div class="card-header">
                        <h4 class="card-title" data-i18n="insight_reputation_review_piechart2">User Review Based on Facebook Data</h4>
                    </div>

                    <div class="card-content">
                        <div class="card-body">
                            <div id="pie-chart-fb" class="height-400"></div>
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
                            <table class="table table-hover table-data-1">

                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="insight_reputation_review_no">No</th>
                                        <th data-i18n="insight_reputation_review_page_name">Page Name</th>
                                        <th data-i18n="insight_reputation_review_reviewer_id">Reviewer Id</th>
                                        <th data-i18n="insight_reputation_review_reviewer_name">Reviewer Name</th>
                                        <th data-i18n="insight_reputation_review_recommendation">Recommendation</th>
                                        <th data-i18n="insight_reputation_review_reviewer_text">Review Text</th>
                                        <th data-i18n="insight_reputation_review_score">Score</th>
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