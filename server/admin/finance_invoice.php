<?php

$kiw['module'] = "Finance -> Report";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Invoice</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                View financial report & invoices
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

                        <div class="table-responsive">
                            <table id="itemlist" class="table table-hover table-data">
                                <thead>
                                    <tr>
                                        <th data-i18n="thead_no">NO</th>
                                        <th data-i18n="thead_invoice_no">INVOICE NO</th>
                                        <th data-i18n="thead_invoice_date">INVOICE DATE</th>
                                        <th data-i18n="thead_username">USERNAME</th>
                                        <th data-i18n="thead_profile">PROFILE</th>
                                        <th data-i18n="thead_balance">OUTSTANDING BALANCE (MYR)</th>
                                        <th data-i18n="thead_status">STATUS</th>
                                        <th data-i18n="thead_action">ACTION</th>
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



<div class="modal fade text-left" id="payForm" role="dialog">

    <div class="modal-dialog modal-dialog-centered" role="document">

        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel33" >Pay</h4>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="pay-form" action="#">

                <div class="modal-body">

                    <label>Amount (MYR): </label>
                    <div class="form-group">
                        <input type="number" step="0.01" name="totalpay" id="totalpay" value="" class="form-control" placeholder="" required>
                    </div>

                </div>

                <div class="modal-footer">

                    <input type="hidden" id="id" name="id" value="" />
                    <button type="button" class="btn btn-danger round waves-effect waves-light flang-form_cancel_button" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-update-pay">Update</button>

                </div>
            </form>

        </div>
    </div>
</div>


<?php
require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";
?>