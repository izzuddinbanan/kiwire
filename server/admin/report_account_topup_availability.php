<?php

$kiw['module'] = "Report -> Accounts -> Topup Availibility";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="account_topup_availability_title">Topup Availability</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="account_topup_availability_subtitle">
                                View topup availability
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- <div class="row">
        <div class="col-12 mb-1">
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
                                <h6 class="text-bold-500" data-i18n="report_account_expiry_search">Date Search :</h6>
                            </div>
                        </div>
                        <div class="col-12">

                            <div class="form-group row">

                                <div class="col-md-4 position-relative has-icon-left">
                                    <input type="text" class="form-control format-picker" name="startdate" id="startdate" value='<?= report_date_view(report_date_start("", 2)) ?>'>
                                    <div class="form-control-position">
                                        <i class="feather icon-calendar"></i>
                                    </div>
                                </div>

                                <span data-i18n="report_account_expiry_to">to</span>

                                <div class="col-md-4 position-relative has-icon-left">
                                    <input type="text" class="form-control format-picker" name="enddate" id="enddate" value='<?= report_date_view(report_date_start("", 1)) ?>'>
                                    <div class="form-control-position">
                                        <i class="feather icon-calendar"></i>
                                    </div>
                                </div>

                                <button type="submit" name='search' id='search' class="btn btn-primary waves-effect waves-light btn-search" data-i18n="bandwidth_account_usage_search">Search</button>

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
                            <table class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="account_voucher_availability_no">No</th>
                                        <th data-i18n="account_voucher_availability_bulk_id">Bulk Id</th>
                                        <th data-i18n="account_voucher_availability_createdby">Created By</th>
                                        <th data-i18n="account_voucher_availability_quantity">Quantity</th>
                                        <th data-i18n="account_voucher_availability_activated">Activated</th>
                                        <th data-i18n="account_voucher_availability_fresh">Fresh</th>
                                        <th data-i18n="account_voucher_availability_remark">Remark</th>
                                        <th data-i18n="account_voucher_availability_action">Action</th>
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


<!-- Modal  Voucher Availability Listing -->
<div class="modal fade text-left" id="freshTopup" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel16" data-i18n="account_topup_availability_modal_listing">Topup Availability Listing</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="col-12">
                    <div class="card">

                        <div class="card-content">
                            <div class="card-body">

                                <div class="table-responsive">
                                    <table class="table table-hover table-detail-1">
                                        <h4 class="card-title" data-i18n="account_voucher_availability_modal_title"><b>Activated Topup Listing</b></h4>
                                        <thead>
                                            <tr class="text-uppercase">
                                                <th data-i18n="account_topup_availability_modal_no">No</th>
                                                <th data-i18n="account_topup_availability_modal_code">Topup Code</th>
                                                <th data-i18n="account_topup_availability_modal_create_date">Create Date</th>
                                                <th data-i18n="account_topup_availability_modal_activated_date">Date Activate</th>
                                                <th data-i18n="account_topup_availability_modal_price">Unit Price(MYR)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tbody>
                                    </table>
                                </div>
                                <br><br>


                                <div class="table-responsive">
                                    <table class="table table-hover table-detail-2">
                                        <h4 class="card-title" data-i18n="account_voucher_availability_table_title"><b>Fresh Topup Listing</b></h4>
                                        <thead>
                                            <tr class="text-uppercase">
                                                <th data-i18n="account_topup_availability_table_no">No</th>
                                                <th data-i18n="account_topup_availability_table_code">Topup Code</th>
                                                <th data-i18n="account_topup_availability_table_create_date">Create Date</th>
                                                <th data-i18n="account_topup_availability_table_expiry_date">Expiry Date</th>
                                                <th data-i18n="account_topup_availability_table_price">Unit Price(MYR)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tbody>
                                    </table>
                                </div>
                                <br><br>

                                <div class="table-responsive">
                                    <table class="table table-hover table-detail-3">
                                        <h4 class="card-title" data-i18n="account_topup_availability_table_title"><b>Expired Topup Listing</b></h4>
                                        <thead>
                                            <tr class="text-uppercase">
                                                <th data-i18n="account_topup_availability_table_no">No</th>
                                                <th data-i18n="account_topup_availability_table_code">Topup Code</th>
                                                <th data-i18n="account_topup_availability_table_create_date">Create Date</th>
                                                <th data-i18n="account_topup_availability_table_expiry_date">Expiry Date</th>
                                                <th data-i18n="account_topup_availability_table_price">Unit Price(MYR)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
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