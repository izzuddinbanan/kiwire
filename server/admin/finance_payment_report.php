<?php

$kiw['module'] = "Finance -> E-Payment Transaction";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Payment Report</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                List of e-payment transaction
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
                                <h6 class="text-bold-500" data-i18n="date_search">Date Search:</h6>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group row">

                                <div class="col-md-3 position-relative has-icon-left">
                                    <input type="text" class="form-control format-picker" name="startdate" id="startdate" value='<?= report_date_view(report_date_start("", 2)) ?>'>
                                    <div class="form-control-position">
                                        <i class="feather icon-calendar"></i>
                                    </div>
                                </div>

                                <span data-i18n="date_search_to">to</span>

                                <div class="col-md-3 position-relative has-icon-left">
                                    <input type="text" class="form-control format-picker" name="enddate" id="enddate" value='<?= report_date_view(report_date_start("", 1)) ?>'>
                                    <div class="form-control-position">
                                        <i class="feather icon-calendar"></i>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="" style="margin-bottom:6px;">
                                        <select class="select2 form-control" name=payment_type id="payment_type">
                                            <option value="payfast" data-i18n="payment_type_payfast">PayFast</option>
                                            <option value="wirecard" data-i18n="payment_type_wirecard">Wirecard</option>
                                            <option value="paypal" data-i18n="payment_type_paypal">Paypal</option>
                                            <option value="alipay" data-i18n="payment_type_alipay">Alipay</option>
                                            <option value="stripe" data-i18n="payment_type_stripe">Stripe</option>
                                            <option value="senangpay" data-i18n="payment_type_senangpay">SenangPay</option>
                                            <option value="adyen" data-i18n="payment_type_adyen">Adyen</option>
                                            <option value="ipay88" data-i18n="payment_type_ipay88">iPay88</option>
                                            <option value="midtrans" data-i18n="payment_type_midtrans">Midtrans</option>
                                        </select>
                                    </div>
                                </div>

                                <button type="submit" name='search' id='search' class="btn btn-primary waves-effect waves-light btn-search" data-i18n="btn_search">Search</button>

                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="row">


            <!-- <div class="col-sm-6 col-md-6">
                <div class="card text-white bg-gradient-primary">
                    <div class="card-content d-flex">
                        <div class="card-body">
                            <h5 class="text-white" id="total-transaction" style="font-size: xx-large; font-weight: bolder;">0</h5>
                            <h5 class="text-white" data-i18n="total_transaction">TOTAL SUCCEED TRANSACTION</h5>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-sm-6 col-md-6">
                <div class="card text-white bg-gradient-primary">
                    <div class="card-content d-flex">
                        <div class="card-body">
                            <h5 class="text-white" id="total-amount" style="font-size: xx-large; font-weight: bolder;">0</h5>
                            <h5 class="text-white" data-i18n="total_amount">TOTAL PAID AMOUNT (MYR)</h5>
                        </div>
                    </div>
                </div>
            </div> -->



        </div>

        <section id="report_table" class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="card-text">
                        <div class="table-responsive">
                            <table class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="report_table_thead_no">No</th>
                                        <th data-i18n="report_table_thead_date">Date</th>
                                        <th data-i18n="report_table_thead_ref">Ref No.</th>
                                        <th data-i18n="report_table_thead_username">Username</th>
                                        <th data-i18n="report_table_thead_user_name">Name</th>
                                        <th data-i18n="report_table_thead_email">Email</th>
                                        <th data-i18n="report_table_thead_phone">Phone No</th>
                                        <th data-i18n="report_table_thead_amount">Amount</th>
                                        <th data-i18n="report_table_thead_status">status</th>
                                        <th data-i18n="report_table_thead_action">Action</th>
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