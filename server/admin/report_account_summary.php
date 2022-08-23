<?php

$kiw['module'] = "Report -> Accounts -> Account Summary";
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


$row = "SELECT COUNT(*) AS prepaidtotal FROM kiwire_account_auth WHERE ktype = 'voucher' AND tenant_id = '{$tenant_id}'";
$row = $kiw_db->query_first($row);

$total_voucher = $row['prepaidtotal'];

$row = "SELECT COUNT(*) AS usertotal FROM kiwire_account_auth WHERE ktype = 'account' AND tenant_id = '{$tenant_id}'";
$row = $kiw_db->query_first($row);

$total_user = $row['usertotal'];


$row = "SELECT COUNT(*) AS simcardtotal FROM kiwire_account_auth WHERE ktype = 'simcard' AND tenant_id = '{$tenant_id}'";
$row = $kiw_db->query_first($row);

$total_simcard = $row['simcardtotal'];


$row = "SELECT COUNT(*) AS act FROM kiwire_account_auth WHERE status = 'active' AND tenant_id = '{$tenant_id}'";
$row = $kiw_db->query_first($row);

$total_activated = $row['act'];

$row = "SELECT COUNT(*) AS exp FROM kiwire_account_auth WHERE status = 'expired' AND tenant_id = '{$tenant_id}'";
$row = $kiw_db->query_first($row);

$total_expired = $row['exp'];

$row = "SELECT COUNT(*) AS sus FROM kiwire_account_auth WHERE status = 'suspend' AND tenant_id = '{$tenant_id}'";
$row = $kiw_db->query_first($row);

$total_user_suspend = $row['sus'];


$total_user_account = $total_voucher + $total_user + $total_simcard;


?>

<style>
    .apexcharts-canvas {
        position: absolute  !important;
        left: 0  !important;
        margin: -16px !important;
    }
</style>

<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase"  data-i18n="account_summary_title">Account Summary</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="account_summary_subtitle">
                                Your current system account summary
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="content-body">


        <div class="row">
            <div class="col-md-6 col-sm-6">
                <div class="card">

                    <div class="card-header">
                        <h4 class="card-title"></h4>
                    </div>

                    <div class="card-content">
                        <div class="card-body">
                            <div id="pie-chart-1" class="height-400"></div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-md-6 col-sm-6">
                <div class="card">

                    <div class="card-header">
                        <h4 class="card-title"></h4>
                    </div>

                    <div class="card-content">
                        <div class="card-body">
                            <div id="pie-chart-2" class="height-400"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>


        <section id="css-classes" class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="card-text">
                        <div class="table-responsive">
                            <table class="table table-hover table-data">
                                <thead style="width:100%;text-align:left;background-color:WhiteSmoke;">
                                    <tr class="text-uppercase">
                                        <th>#</th>
                                        <th  data-i18n="account_summary_description">Description</th>
                                        <th  data-i18n="account_summary_count">Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="">1</td>
                                        <td id="td1" class="" data-i18n="account_summary_voucher">Number of Vouchers</td>
                                        <td>
                                            <?= $total_voucher ?>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="">2</td>
                                        <td id="td3" class="" data-i18n="account_summary_users">Number of Users</td>
                                        <td>
                                            <?= $total_user ?>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="">3</td>
                                        <td id="td6" class="" data-i18n="account_summary_simcard">Number of Sim Card</td>
                                        <td>
                                            <?= $total_simcard ?>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>4</td>
                                        <td id="td5" data-i18n="account_summary_total_account">Total Number of Account ( 1 + 2 + 3 )</td>
                                        <td>
                                            <?= $total_user_account ?>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="">5</td>
                                        <td id="td2" class="" data-i18n="account_summary_activated_account">Total Number of Activated Accounts</td>
                                        <td>
                                            <?= $total_activated ?>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="">6</td>
                                        <td id="td4" class=""  data-i18n="account_summary_expired_account">Total Number of Expired Accounts</td>
                                        <td>
                                            <?= $total_expired ?>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="">7</td>
                                        <td id="td5" class=""  data-i18n="account_summary_suspended_account">Total Number of Suspended Account</td>
                                        <td>
                                            <?= $total_user_suspend ?>
                                        </td>
                                    </tr>
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>


<script>
    var total_voucher = parseInt('<?= $total_voucher ?>');
    var total_user = parseInt('<?= $total_user ?>');
    var total_simcard = parseInt('<?= $total_simcard ?>');
    var total_activated = parseInt('<?= $total_activated ?>');
    var total_user_suspend = parseInt('<?= $total_user_suspend ?>');
    var total_expired = parseInt('<?= $total_expired ?>');

</script>

<?php

require_once "includes/include_report_footer.php";
require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";

?>