<?php

$kiw['module'] = "Report -> Sign Up Info";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="insight_signup_summary_title">Sign Up Summary</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="insight_signup_summary_subtitle">
                                Summary of user sign up
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
                                <h6 class="text-bold-500" data-i18n="insight_signup_summary_date_search">Date Search :</h6>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group row">

                                <div class="col-md-5">
                                    <i class="feather icon-calendar"></i>
                                    <input type="text" class="form-control format-picker" name="startdate" id="startdate" value='<?= report_date_view(report_date_start("", 2)) ?>' />
                                </div>

                                <span data-i18n="insight_signup_summary_to">to</span>

                                <div class="col-md-5">
                                    <i class="feather icon-calendar"></i>
                                    <input type="text" class="form-control format-picker" name="enddate" id="enddate" value='<?= report_date_view(report_date_start("", 1)) ?>' />
                                </div>

                                <button type="submit" name='search' id='search' class="btn btn-primary round waves-effect waves-light btn-search" data-i18n="insight_signup_summary_search">Search</button>

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
                        <h4 class="card-title" data-i18n="insight_signup_summary_piechart">Age Group</h4>
                    </div>

                    <div class="card-content">
                        <div class="card-body">
                            <div id="pie-chart-age" class="height-400"></div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-md-6 col-sm-6">
                <div class="card">
                    
                    <div class="card-header">
                        <h4 class="card-title" data-i18n="insight_signup_summary_piechart2">Gender</h4>
                    </div>

                    <div class="card-content">
                        <div class="card-body">
                            <div id="pie-chart-gender" class="height-400"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- End Pie Chart -->

        <!-- <section id="report_table" class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="card-text">
                        <div class="table-responsive">
                            <table class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="insight_signup_summary_no>No</th>
                                        <th data-i18n="insight_signup_summary_sponsor">Sponsor</th>
                                        <th data-i18n="insight_signup_summary_user">Total User Sign Up</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section> -->

    </div>
</div>



<?php

require_once "includes/include_footer.php";
require_once "includes/include_report_footer.php";
require_once "includes/include_datatable.php";

?>