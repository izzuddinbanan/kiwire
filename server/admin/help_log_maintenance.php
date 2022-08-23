<?php

$kiw['module'] = "Help -> System Logs";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

require_once 'includes/include_config.php';
require_once 'includes/include_session.php';
require_once 'includes/include_general.php';
require_once 'includes/include_header.php';
require_once 'includes/include_nav.php';
require_once 'includes/include_access.php';
require_once "includes/include_report.php";


?>

    <style>

        .log-space {

            font-family: "Lucida Console", serif;
            overflow: auto;

        }

    </style>

    <div class="content-wrapper">

        <div class="content-header row">
            <div class="content-header-left col-12 mb-2">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Logs
                            Maintenance</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item active" data-i18n="subtitle">
                                    Download log files
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-md-12">
            <div class="card">
                <div class="card-header mb-1">
                    <h4 class="card-title">SYSTEM [ ADMIN ] LOGS</h4>
                    <span class="ml-5 text-uppercase font-size-xsmall">Last 100 lines of the last log file</span>
                </div>
                <div class="card-content">
                    <div class="card-body space-for-system log-space" style="height: 300px;">
                        <p class="card-text">Loading log file..</p>
                    </div>
                </div>
                <div class="card-footer text-muted">
                    <span class="float-left">
                        <a href="javascript:void(0);" class="card-link" data-action="refresh" data-type="system">Refresh &nbsp; <i class="fa fa-refresh"></i></a>
                    </span>
                    <span class="float-right">
                        <a href="javascript:void(0);" class="card-link" data-action="download" data-type="system">Download All &nbsp; <i class="fa fa-download"></i></a>
                    </span>
                </div>
            </div>
        </div>


        <div class="col-md-12">
            <div class="card">
                <div class="card-header mb-1">
                    <h4 class="card-title">INTEGRATION LOGS</h4>
                    <span class="ml-5 text-uppercase font-size-xsmall">Last 100 lines of the last log file</span>
                </div>
                <div class="card-content">
                    <div class="card-body space-for-integration log-space" style="height: 300px;">
                        <p class="card-text">Loading log file..</p>
                    </div>
                </div>
                <div class="card-footer text-muted">
                    <span class="float-left">
                        <a href="javascript:void(0);" class="card-link" data-action="refresh" data-type="integration">Refresh &nbsp; <i class="fa fa-refresh"></i></a>
                    </span>
                    <span class="float-right">
                        <a href="javascript:void(0);" class="card-link" data-action="download" data-type="integration">Download All &nbsp; <i class="fa fa-download"></i></a>
                    </span>
                </div>
            </div>
        </div>


        <div class="col-md-12">
            <div class="card">
                <div class="card-header mb-1">
                    <h4 class="card-title">SERVICE LOGS</h4>
                    <span class="ml-5 text-uppercase font-size-xsmall">Last 100 lines of the last log file</span>
                </div>
                <div class="card-content">
                    <div class="card-body space-for-service log-space" style="height: 300px;">
                        <p class="card-text">Loading log file..</p>
                    </div>
                </div>
                <div class="card-footer text-muted">
                    <span class="float-left">
                        <a href="javascript:void(0);" class="card-link" data-action="refresh" data-type="service">Refresh &nbsp; <i class="fa fa-refresh"></i></a>
                    </span>
                    <span class="float-right">
                        <a href="javascript:void(0);" class="card-link" data-action="download" data-type="service">Download All &nbsp; <i class="fa fa-download"></i></a>
                    </span>
                </div>
            </div>
        </div>


        <div class="col-md-12">
            <div class="card">
                <div class="card-header mb-1">
                    <h4 class="card-title">USER [ CAPTIVE ] LOGS</h4>
                    <span class="ml-5 text-uppercase font-size-xsmall">Last 100 lines of the last log file</span>
                </div>
                <div class="card-content">
                    <div class="card-body space-for-user log-space" style="height: 300px;">
                        <p class="card-text">Loading log file..</p>
                    </div>
                </div>
                <div class="card-footer text-muted">
                    <span class="float-left">
                        <a href="javascript:void(0);" class="card-link" data-action="refresh" data-type="user">Refresh &nbsp; <i class="fa fa-refresh"></i></a>
                    </span>
                    <span class="float-right">
                        <a href="javascript:void(0);" class="card-link" data-action="download" data-type="user">Download All &nbsp; <i class="fa fa-download"></i></a>
                    </span>
                </div>
            </div>
        </div>


        <div class="col-md-12">
            <div class="card">
                <div class="card-header mb-1">
                    <h4 class="card-title">PMS LOGS</h4>
                    <span class="ml-5 text-uppercase font-size-xsmall">Last 100 lines of the last log file</span>
                </div>
                <div class="card-content">
                    <div class="card-body space-for-pms log-space" style="height: 300px;">
                        <p class="card-text">Loading log file..</p>
                    </div>
                </div>
                <div class="card-footer text-muted">
                    <span class="float-left">
                        <a href="javascript:void(0);" class="card-link" data-action="refresh" data-type="pms">Refresh &nbsp; <i class="fa fa-refresh"></i></a>
                    </span>
                    <span class="float-right">
                        <a href="javascript:void(0);" class="card-link" data-action="download" data-type="pms">Download All &nbsp; <i class="fa fa-download"></i></a>
                    </span>
                </div>
            </div>
        </div>

    </div>


<?php
require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";
?>