<?php

$kiw['module'] = "Report -> Bandwidth Usage User";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="bandwidth_account_usage_title">Account Usage Summary</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="bandwidth_account_usage_subtitle">
                                Information how much bandwidth is used by per user
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- <div class="row">
        <div class="col-12 mb-1" >
            <button id="filter-btn" class="float-right btn btn-icon btn-primary btn-xs fa fa-filter"></button>
        </div>
    </div> -->

    <div class="content-body">

        <section id="css-classes" class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="card-text">
                        <div class="col-12">
                            <div class="form-group row">
                                <h6 class="text-bold-500" data-i18n="report_account_expiry_search">Filter :</h6>
                            </div>
                        </div>

                        <div class="row">

                            <div class="col-md-4">
                                <div class="form-group" style="position:relative; left:auto; display:block;">
                                    <label for="startdate" data-i18n="data_from">Date From</label>
                                    <input type="text" class="form-control format-picker" name="startdate" id="startdate" value='<?= report_date_view(report_date_start("", 2)) ?>'>
                                </div>

                            </div>

                            <div class="col-md-4">
                                <div class="form-group" style="position:relative; left:auto; display:block;">
                                    <label for="enddate" data-i18n="data_until">Date Until</label>
                                    <input type="text" class="form-control format-picker" name="enddate" id="enddate" value='<?= report_date_view(report_date_start("", 1)) ?>'>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="username" data-i18n="bandwidth_account_usage_username">Username: </label>
                                    <input type="text" placeholder="" name="username" id="username" value="" class="form-control" />
                                </div>
                            </div>

                        </div>
                        <button type="submit" name='search' id='search' class="btn btn-primary waves-effect waves-light btn-search" data-i18n="bandwidth_account_usage_search">Search</button>

                    </div>
                </div>
            </div>
        </section>

        <section id="report_graph" class="card" style="display: none;">
            <div class="row">
                <div class="col-12">
                    <div class="card-content">
                        <div class="card-body">
                            <div id="data-chart" class="height-400"></div>
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
                                        <th data-i18n="bandwidth_account_usage_no">No</th>
                                        <th data-i18n="bandwidth_account_usage_date">Date</th>
                                        <th><span data-i18n="bandwidth_account_usage_download">Download</span> ( <?= $_SESSION['metrics'] ?> )</th>
                                        <th><span data-i18n="bandwidth_account_usage_upload">Upload</span> ( <?= $_SESSION['metrics'] ?> )</th>
                                        <th><span data-i18n="bandwidth_account_usage_average_download">Average Download</span> ( <?= $_SESSION['metrics'] ?>ps )</th>
                                        <th><span data-i18n="bandwidth_account_usage_average_upload">Average Upload</span> ( <?= $_SESSION['metrics'] ?>ps )</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <th>
                                    <td colspan="6" style="text-align: center;" data-i18n="bandwidth_account_usage_proceed">
                                        Please provide a username to proceed.
                                    </td>
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

<!-- <div class="modal fade text-left" id="filter_modal" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="mythememodal">Filter</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">

                        <div class="card-content">
                            <div class="card-body">

                                <form class="form form-vertical">
                                    <div class="form-body">
                                        <div class="row">

                                            <div class="col-12">

                                                <div class="form-group" style="position:relative; left:auto; display:block;">
                                                    <label for="startdate" data-i18n="bandwidth_account_usage_username_data_from">Date From</label>
                                                    <input type="text" class="form-control format-picker" name="startdate" id="startdate" value='<?= report_date_view(report_date_start("", 2)) ?>'>
                                                </div>

                                                <div class="form-group" style="position:relative; left:auto; display:block;">
                                                    <label for="enddate" data-i18n="bandwidth_account_usage_data_until">Date Until</label>
                                                    <input type="text" class="form-control format-picker" name="enddate" id="enddate" value='<?= report_date_view(report_date_start("", 1)) ?>'>
                                                </div>

                                                <div class="form-group">
                                                    <label for="username" data-i18n="bandwidth_account_usage_username">Username: </label>
                                                    <input type="text" placeholder="" name="username" id="username" value="" class="form-control" />
                                                </div>

                                            </div>

                                            <div class="col-12">
                                                <button type="button" id="filter-data" class="btn btn-primary round mr-1 mb-1 waves-effect waves-light pull-right">Filter</button>
                                                <button type="reset" class="btn btn-danger round mr-1 mb-1 waves-effect waves-light pull-right" data-dismiss="modal">Cancel</button>
                                            </div>

                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div> -->


<?php
require_once "includes/include_footer.php";
require_once "includes/include_report_footer.php";
require_once "includes/include_datatable.php";
?>