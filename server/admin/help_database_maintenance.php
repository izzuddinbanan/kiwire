<?php

$kiw['module'] = "Help -> Database Maintenance";
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



<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Database Maintenance</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                Perform database maintenance and operation
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

                                <div class="col-md-2">
                                    <span data-i18n="select_backup">Select backup file</span>
                                </div>

                                <div class="col-md-8">

                                    <select class="select2 form-control backup-date" data-style="btn-default">

                                        <option value="none" data-i18n="select_backup_opt_none">Please select a date to download</option>

                                        <?php


                                        $directory = dirname(__FILE__, 3) . "/backups/";

                                        $scanned_directory = array_diff(scandir($directory), array('..', '.'));

                                        foreach ($scanned_directory as $value) {

                                            if (!empty($value)) {

                                                echo "<option value ='{$value}'>{$value}</option> \n";
                                            }
                                        }

                                        ?>

                                    </select>

                                </div>

                                <div class="col-md-2">

                                    <button type="button" class="btn btn-primary waves-effect waves-light btn-download-backup" data-i18n="btn_download">Download</button>

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
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="thead-dark">
                                    <tr>
                                        <th scope="col" data-i18n="thead_purge">PURGE</th>
                                        <th scope="col" data-i18n="thead_action">ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <tr>
                                        <td data-i18n="td_purge_inactive">Purge account inactive</td>
                                        <td>
                                            <button type="button" data-table="account" class="btn btn-icon btn-primary btn-sm btn-purge"><i class="feather icon-check-circle"></i></button>
                                        </td>
                                    </tr>


                                    <tr>
                                        <td data-i18n="td_purge_created">Purge user information</td>
                                        <td>
                                            <button type="button" data-table="info" class="btn btn-icon btn-primary btn-sm btn-purge"><i class="feather icon-check-circle"></i></button>
                                        </td>
                                    </tr>


                                    <tr>
                                        <td data-i18n="td_purge_history">Purge device history inactive</td>
                                        <td>
                                            <button type="button" data-table="history" class="btn btn-icon btn-primary btn-sm btn-purge"><i class="feather icon-check-circle"></i></button>
                                        </td>
                                    </tr>


                                    <tr>
                                        <td data-i18n="td_purge_survey">Purge survey response</td>
                                        <td>
                                            <button type="button" data-table="survey" class="btn btn-icon btn-primary btn-sm btn-purge"><i class="feather icon-check-circle"></i></button>
                                        </td>
                                    </tr>


                                    <tr>
                                        <td data-i18n="td_purge_network">Purge network monitoring system (nms) log</td>
                                        <td>
                                            <button type="button" data-table="nms" class="btn btn-icon btn-primary btn-sm btn-purge"><i class="feather icon-check-circle"></i></button>
                                        </td>
                                    </tr>


                                    <tr>
                                        <td data-i18n="td_purge_general">Purge general report data</td>
                                        <td>
                                            <button type="button" data-table="greport" class="btn btn-icon btn-primary btn-sm btn-purge"><i class="feather icon-check-circle"></i></button>
                                        </td>
                                    </tr>


                                    <tr>
                                        <td data-i18n="td_purge_device">Purge device report data</td>
                                        <td>
                                            <button type="button" data-table="dreport" class="btn btn-icon btn-primary btn-sm btn-purge"><i class="feather icon-check-circle"></i></button>
                                        </td>
                                    </tr>


                                    <tr>
                                        <td data-i18n="td_purge_dwell">Purge dwell type report data</td>
                                        <td>
                                            <button type="button" data-table="dwreport" class="btn btn-icon btn-primary btn-sm btn-purge"><i class="feather icon-check-circle"></i></button>
                                        </td>
                                    </tr>


                                    <tr>
                                        <td data-i18n="td_purge_error">Purge error report data</td>
                                        <td>
                                            <button type="button" data-table="ereport" class="btn btn-icon btn-primary btn-sm btn-purge"><i class="feather icon-check-circle"></i></button>
                                        </td>
                                    </tr>


                                    <tr>
                                        <td data-i18n="td_purge_profile">Purge profile report data</td>
                                        <td>
                                            <button type="button" data-table="preport" class="btn btn-icon btn-primary btn-sm btn-purge"><i class="feather icon-check-circle"></i></button>
                                        </td>
                                    </tr>


                                    <tr>
                                        <td data-i18n="td_purge_campaign">Purge campaign report data</td>
                                        <td>
                                            <button type="button" data-table="creport" class="btn btn-icon btn-primary btn-sm btn-purge"><i class="feather icon-check-circle"></i></button>
                                        </td>
                                    </tr>


                                    <tr>
                                        <td data-i18n="td_purge_controller">Purge controller report data</td>
                                        <td>
                                            <button type="button" data-table="coreport" class="btn btn-icon btn-primary btn-sm btn-purge"><i class="feather icon-check-circle"></i></button>
                                        </td>
                                    </tr>


                                    <tr>
                                        <td data-i18n="td_purge_campaign_uniq">Purge campaign unique data for click and impression</td>
                                        <td>
                                            <button type="button" data-table="cunique" class="btn btn-icon btn-primary btn-sm btn-purge"><i class="feather icon-check-circle"></i></button>
                                        </td>
                                    </tr>


                                    <tr>
                                        <td data-i18n="td_purge_messages">Purge messages</td>
                                        <td>
                                            <button type="button" data-table="message" class="btn btn-icon btn-primary btn-sm btn-purge"><i class="feather icon-check-circle"></i></button>
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>



        <!-- Modal -->
        <div class="modal fade text-left" id="inlineForm" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">

                    <form class="create-form" action="#">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel33" data-i18n="db_retain_data">Retain data for</h4>
                            <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">

                            <label data-i18n="db_no_days">Number of days: </label>
                            <div class="form-group">
                                <input type="text" name="days" id="days" value="" class="form-control" required>
                            </div>

                        </div>

                        <div class="modal-footer">

                            <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="db_cancel">Cancel</button>
                            <button type="button" class="btn btn-primary round waves-effect waves-light btn-submit" data-i18n="db_search">Submit</button>

                        </div>

                    </form>
                </div>
            </div>
        </div>


    </div>
</div>


<?php
require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";
?>