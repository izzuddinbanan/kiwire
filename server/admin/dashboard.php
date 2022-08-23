<?php

$kiw['module'] = "General -> Dashboard";
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

$kiw_config = $kiw_db->query_first("SELECT SQL_CACHE timezone FROM kiwire_clouds WHERE tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");

$kiw_this_hour = date("H:00", strtotime(sync_tolocaltime(date("Y-m-d H:i:s"), $kiw_config['timezone'])));


?>

<div class="content-wrapper">

    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="dashboard_title">Dashboard</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="dashboard_subtitle">
                                Current information
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="content-body">

            <div class="row">


                <div class="col-sm-12 col-md-9">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title" data-i18n="dashboard_chart1">Page Impression vs Login</h4>
                        </div>
                        <div class="card-content">
                            <div class="card-body">
                                <div id="login-vs-impression"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 col-md-3">

                    <div class="card">
                        <div class="card-header text-center">
                            <h4 class="card-title text-capitalize" data-i18n="dashboard_current_session">SESSIONS SUMMARY</h4>
                        </div>
                        <div class="card-content">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-25">

                                    <div class="browser-info">
                                        <h4 data-i18n="dashboard_active">Active</h4>
                                        <label for="" data-i18n="dashboard_active_label">Current number of active sessions</label>
                                    </div>

                                    <div class="stastics-info text-right">
                                        <span><span class="font-large-1" id="session-active">0</span></span>
                                        <span class="text-muted d-block"></span>
                                    </div>

                                </div>

                                <div class="dropdown-divider pb-50"></div>

                                <div class="d-flex justify-content-between mb-35">

                                    <div class="browser-info">
                                        <h4 data-i18n="dashboard_connected">Connected</h4>
                                        <label for=""><span data-i18n="dashboard_connected_label">Login performed since</span> <?= $kiw_this_hour ?></label>
                                    </div>

                                    <div class="stastics-info text-right">
                                        <span><span class="font-large-1" id="session-connected">0</span></span>
                                        <span class="text-muted d-block"></span>
                                    </div>

                                </div>

                                <div class="dropdown-divider  pb-50"></div>

                                <div class="d-flex justify-content-between mb-25">

                                    <div class="browser-info">
                                        <h4 data-i18n="dashboard_disconnect">Disconnect</h4>
                                        <label for=""><span data-i18n="dashboard_disconnect_label">Disconnected session since</span> <?= $kiw_this_hour ?></label>
                                    </div>

                                    <div class="stastics-info text-right">
                                        <span><span class="font-large-1" id="session-disconnect">0</span></span>
                                        <span class="text-muted d-block"></span>
                                    </div>

                                </div>



                            </div>
                        </div>
                    </div>

                </div>


            </div>



            <div class="row">
                <div class="col-sm-6 col-md-3">
                    <div class="card text-white bg-gradient-primary">
                        <div class="card-content d-flex">
                            <div class="card-body">
                                <h5 class="text-white" id="current-view" style="font-size: xx-large; font-weight: bolder;">0</h5>
                                <h4 class="text-white" data-i18n="dashboard_page_impression">PAGE IMPRESSION</h4>
                                <p class="card-text" style="font-size: smaller;"><span data-i18n="dashboard_page_impression_label">PAGE VIEWED SINCE</span> <?= $kiw_this_hour ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card text-white bg-gradient-primary">
                        <div class="card-content d-flex">
                            <div class="card-body">
                                <h5 class="text-white" id="current-campaign" style="font-size: xx-large; font-weight: bolder;">0</h5>
                                <h4 class="text-white" data-i18n="dashboard_campaign_impression">CAMPAIGN IMPRESSION</h4>
                                <p class="card-text" style="font-size: smaller;"><span data-i18n="dashboard_campaign_impression_label">CAMPAIGN VIEWED SINCE</span> <?= $kiw_this_hour ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card text-white bg-gradient-primary">
                        <div class="card-content d-flex">
                            <div class="card-body">
                                <h5 class="text-white" id="current-clicked" style="font-size: xx-large; font-weight: bolder;">0</h5>
                                <h4 class="text-white" data-i18n="dashboard_campaign_click">CAMPAIGN CLICKED</h4>
                                <p class="card-text" style="font-size: smaller;"><span data-i18n="dashboard_campaign_click_label">CAMPAIGN CLICKED SINCE</span> <?= $kiw_this_hour ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card text-white bg-gradient-primary">
                        <div class="card-content d-flex">
                            <div class="card-body">
                                <h5 class="text-white" id="current-login" style="font-size: xx-large; font-weight: bolder;">0</h5>
                                <h4 class="text-white" data-i18n="dashboard_login">LOGIN</h4>
                                <p class="card-text" style="font-size: smaller;"><span data-i18n="dashboard_login_label">LOGIN SINCE</span> <?= $kiw_this_hour ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="row">

                <div class="col-sm-12 col-md-5">

                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title" data-i18n="dashboard_activity_timeline">Activity Timeline</h4>
                        </div>
                        <div class="card-content">
                            <div class="card-body">
                                <ul class="activity-timeline timeline-left list-unstyled">
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="col-sm-12 col-md-7">

                    <div class="card text-white">
                        <div class="card-header text-dark">
                            <h4 data-i18n="dashboard_average_dwell">Average Dwell For Past 24 Hours</h4>
                        </div>
                        <div class="card-content d-flex">
                            <div class="card-body">
                                <div id="dwell-chart"></div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>


        </div>
    </div>

</div>

<script src="/app-assets/vendors/js/charts/apexcharts.min.js"></script>
<script src="/assets/js/datejs/build/date.js"></script>

<?php

require_once "includes/include_footer.php";

?>

