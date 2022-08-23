<?php

$kiw['module'] = "Finance -> Prepaid Creation";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Voucher Summary</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                Detailed voucher summary
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
                                        <th data-i18n="thead_id">BULK ID</th>
                                        <th data-i18n="thead_username">CREATED BY</th>
                                        <th data-i18n="thead_remark">REMARK</th>
                                        <th data-i18n="thead_unit">UNIT PRICE (MYR)</th>
                                        <th data-i18n="thead_qty">QTY</th>
                                        <th data-i18n="thead_total">TOTAL PRICE (MYR)</th>
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


<!-- Modal  Voucher Summary -->
<div class="modal fade text-left" id="voucherSummary" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel16" data-i18n="modal_1_title">Voucher Summary</h4>
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
                                        <thead>
                                            <tr class="text-uppercase">
                                                <th data-i18n="modal_1_thead_no">No</th>
                                                <th data-i18n="modal_1_thead_username">Username</th>
                                                <th data-i18n="modal_1_thead_created">Create Date</th>
                                                <th data-i18n="modal_1_thead_expiry">Expiry Date</th>
                                                <th data-i18n="modal_1_thead_price">Unit Price(MYR)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <td colspan="5">Loading</td>
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
<input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">

<?php
require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";
?>