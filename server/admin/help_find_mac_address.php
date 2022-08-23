<?php

$kiw['module'] = "Help -> Find Mac Address";
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


<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="h_find_mac_addr_title">Find Mac Address</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="h_find_mac_addr_subtitle">
                                Locate MAC address
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
                                <h6 class="text-bold-500" data-i18n="h_find_mac_mac_address">MAC Address:</h6>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group row">

                                <div class="col-md-10">
                                    <input type="text" placeholder="EG. 00:00:00:00:00:00" name="mac_address" id="mac_address" value="" class="form-control" />
                                </div>

                                <button type="submit" name='search' id='search' class="btn btn-primary waves-effect waves-light btn-search" data-i18n="h_find_mac_search">Search</button>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="diagnose_result" class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="card-text">
                        <div class="col-12">
                            <div class="form-group row">
                                <h6 class="text-bold-500" data-i18n="h_find_mac_dr_brand">Diagnostic Result : Brand & Associated Account</h6>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group row">

                                <div class="col-6">

                                    <table class="table table-bordered">
                                        <tr>
                                            <td data-i18n="h_find_mac_last_acc">Last Account Used</td>
                                            <td class="d-info-account"></td>
                                        </tr>
                                        <tr>
                                            <td data-i18n="h_find_mac_type">Type</td>
                                            <td class="d-info-type"></td>
                                        </tr>
                                        <tr>
                                            <td data-i18n="h_find_mac_brand">Brand</td>
                                            <td class="d-info-brand"></td>
                                        </tr>
                                        <tr>
                                            <td data-i18n="h_find_mac_model">Model</td>
                                            <td class="d-info-model"></td>
                                        </tr>
                                        <tr>
                                            <td data-i18n="h_find_mac_operating_system">Operating System</td>
                                            <td class="d-info-os"></td>
                                        </tr>
                                    </table>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="report_table" class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="card-text">
                        <div class="col-12">
                            <div class="form-group row">
                                <h6 class="text-bold-500" data-i18n="h_find_mac_dr_login">Diagnostic Result : Login History</h6>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="h_find_mac_no">No</th>
                                        <th data-i18n="h_find_mac_login_datetime">Login Date / Time</th>
                                        <th data-i18n="h_find_mac_username">Username</th>
                                        <th data-i18n="h_find_mac_mac_addr">MAC Address</th>
                                        <th data-i18n="h_find_mac_ip_addr">IP Address</th>
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