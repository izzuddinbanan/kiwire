<?php

$kiw['module'] = "Device -> Monitoring -> Status";
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

$kiw_db = Database::obtain();

?>


<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Monitoring Status</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                List of device status
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

                <div class="row">
                    <div class="col-12">
                        <div class="pull-right mt-75 pt-50 pr-75 mr-75">
                            <span>RELOAD IN </span> <span class="remaining">60</span>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="card-text">

                        <div class="table-responsive">
                            <table id="itemlist" class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th>No</th>
                                        <th>Identity</th>
                                        <th>IP Address</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Last Running Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                  <th>
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


<?php
require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";
?>
