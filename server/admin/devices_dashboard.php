<?php

$kiw['module'] = "Device -> Monitoring -> Dashboard";
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


$kiw_system = @file_get_contents(dirname(__FILE__, 2) . "/custom/system_setting.json");

$kiw_system = json_decode($kiw_system, true);


?>

<div class="content-wrapper">

    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Monitoring Dashboard</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                Dashboard for monitoring devices
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

                <div class="col-sm-6 col-md-3">
                    <div class="card text-white bg-gradient-primary">
                        <div class="card-content d-flex">
                            <div class="card-body">
                                <h5 class="text-white" id="current-device-total" style="font-size: xx-large; font-weight: bolder;" data-i18n="current-device-total">0</h5>
                                <h4 class="text-white" data-i18n="total_device">TOTAL DEVICES</h4>
                                <p class="card-text" style="font-size: smaller;"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-md-3">
                    <div class="card text-white bg-gradient-primary">
                        <div class="card-content d-flex">
                            <div class="card-body">
                                <h5 class="text-white" id="current-device-running" style="font-size: xx-large; font-weight: bolder;" data-i18n="current-device-running">0</h5>
                                <h4 class="text-white" data-i18n="online">ONLINE</h4>
                                <p class="card-text" style="font-size: smaller;"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-md-3">
                    <div class="card text-white bg-gradient-primary">
                        <div class="card-content d-flex">
                            <div class="card-body">
                                <h5 class="text-white" id="current-device-down" style="font-size: xx-large; font-weight: bolder;" data-i18n="current-device-down">0</h5>
                                <h4 class="text-white" data-i18n="critical">CRITICAL</h4>
                                <p class="card-text" style="font-size: smaller;"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-md-3">
                    <div class="card text-white bg-gradient-primary">
                        <div class="card-content d-flex">
                            <div class="card-body">
                                <h5 class="text-white" id="current-device-warning" style="font-size: xx-large; font-weight: bolder;" data-i18n="current-device-warning">0</h5>
                                <h4 class="text-white" data-i18n="warning">WARNING</h4>
                                <p class="card-text" style="font-size: smaller;"></p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row d-flex">

                <div class="col-7">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title" data-i18n="device_availability">Device Availability</h4>
                        </div>

                        <div class="card-content">
                            <div class="card-body">
                                <div id="pie-chart-device" class="height-400"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-5">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title" data-i18n="warning_crit">Warning / Critical</h4>
                        </div>
                        <div class="card-content">
                            <div class="card-body">
                                <ul class="activity-timeline timeline-left list-unstyled" data-i18n="pls_wait">Please wait..</ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div>


            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">

                                <div class="row pb-50">
                                    <div class="col-4 offset-8 text-right font-weight-bolder">
                                        * Volume for the past <?= $kiw_system['device_monitor'] ?> minute(s).
                                    </div>
                                </div>

                                <table class="table table-bordered table-hover table-data">
                                    <thead>
                                    <tr class="thead-dark">
                                        <th data-i18n="thead_no">No</th>
                                        <th data-i18n="thead_uid">Unique ID</th>
                                        <th data-i18n="thead_ip">IP Address</th>
                                        <th data-i18n="thead_loc">Location</th>
                                        <th data-i18n="thead_report">Last Report</th>
                                        <th data-i18n="thead_status">Status</th>
                                        <th data-i18n="thead_upload">Upload (Mb) *</th>
                                        <th data-i18n="thead_download">Download (Mb) *</th>
                                        <th data-i18n="thead_avgspeed">Avg Speed (Mbps)</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>

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
