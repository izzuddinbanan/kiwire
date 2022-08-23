<?php

$kiw['module'] = "Finance -> Print Prepaid Slip";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

require_once 'includes/include_config.php';
require_once 'includes/include_session.php';
require_once 'includes/include_general.php';
require_once 'includes/include_header.php';
require_once 'includes/include_nav.php';
require_once 'includes/include_access.php';


?>



<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Voucher Slip</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                Print voucher slip
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
                                        <th data-i18n="thead_no">CREATED BY</th>
                                        <th data-i18n="thead_qty">QTY</th>
                                        <th data-i18n="thead_remark">REMARK</th>
                                        <th data-i18n="thead_created">CREATE DATE</th>
                                        <th data-i18n="thead_expiry">EXPIRY DATE</th>
                                        <th data-i18n="thead_print_onecol">PRINT (A4) ONE COLUMN</th>
                                        <th data-i18n="thead_print_twocol">PRINT (A4) TWO COLUMN</th>
                                        <th data-i18n="thead_print_pos">PRINT (POS) SLIP</th>
                                        <th data-i18n="thead_print_qr">PRINT (QRCODE) TWO COLUMN</th>
                                        <th data-i18n="thead_export">EXPORT</th>
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

<script src="/assets/js/datejs/build/date.js"></script>
