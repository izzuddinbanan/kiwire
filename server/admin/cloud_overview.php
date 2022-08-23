<?php

$kiw['module'] = "Cloud -> Overview";
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

?>

<div class="content-wrapper">

    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="">Overview</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="dashboard_subtitle">
                                Current system overview
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

                <div class="col-sm-6 col-md-6">
                    <div class="card text-white bg-gradient-primary">
                        <div class="card-content d-flex">
                            <div class="card-body">
                                <h4 class="text-white" id="total-account" style="font-size: xx-large; font-weight: bolder;">0</h4>
                                <h4 class="text-white" data-i18n="">TOTAL ACCOUNT</h4>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="col-sm-6 col-md-6">
                    <div class="card text-white bg-gradient-primary">
                        <div class="card-content d-flex">
                            <div class="card-body">
                                <h4 class="text-white" id="active-session" style="font-size: xx-large; font-weight: bolder;">0</h4>
                                <h4 class="text-white" data-i18n="">ACTIVE SESSIONS</h4>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row">

                <div class="col-sm-6 col-md-4">
                    <div class="card text-white bg-gradient-primary">
                        <div class="card-content d-flex">
                            <div class="card-body">
                                <h4 class="text-white" id="total-voucher" style="font-size: xx-large; font-weight: bolder;">0</h4>
                                <h4 class="text-white" data-i18n="">TOTAL VOUCHER</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-md-4">
                    <div class="card text-white bg-gradient-primary">
                        <div class="card-content d-flex">
                            <div class="card-body">
                                <h4 class="text-white" id="total-email" style="font-size: xx-large; font-weight: bolder;">0</h4>
                                <h4 class="text-white" data-i18n="">TOTAL EMAIL</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-md-4">
                    <div class="card text-white bg-gradient-primary">
                        <div class="card-content d-flex">
                            <div class="card-body">
                                <h4 class="text-white" id="total-sms" style="font-size: xx-large; font-weight: bolder;">0</h4>
                                <h4 class="text-white" data-i18n="">TOTAL SMS</h4>
                            </div>
                        </div>
                    </div>
                </div>
<!-- 
                <div class="col-sm-6 col-md-4">
                    <div class="card text-white bg-gradient-primary">
                        <div class="card-content d-flex">
                            <div class="card-body">
                                <h4 class="text-white" id="active-controller" style="font-size: xx-large; font-weight: bolder;">0</h4>
                                <h4 class="text-white" data-i18n="">ACTIVE CONTROLLERS</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-md-4">
                    <div class="card text-white bg-gradient-primary" id="offline-controller-space">
                        <div class="card-content d-flex">
                            <div class="card-body">
                                <h4 class="text-white" id="offline-controller" style="font-size: xx-large; font-weight: bolder;">0</h4>
                                <h4 class="text-white" data-i18n="">OFFLINE CONTROLLER</h4>
                            </div>
                        </div>
                    </div>
                </div> -->

            </div>


            <div class="row">
                <div class="col-12">
                    <div class="card">

                        <div class="card-header">
                            <h4 class="card-title">LIST OF TENANTS</h4>
                        </div>
                        <div class="card-content">
                            <div class="card-body">

                                <div class="table-responsive">

                                    <table class="table tenant-list">
                                        <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Tenant ID</th>
                                            <th>Client Name</th>
                                            <th>Total Account</th>
                                            <th>Active Sessions</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td style="text-align: center;" colspan="5">Loading...</td>
                                        </tr>
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
require_once "includes/include_datatable.php";

?>

