<?php

 
$kiw['module'] = "Finance -> Report";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

require_once 'includes/include_config.php';
require_once 'includes/include_session.php';
require_once 'includes/include_general.php';
require_once 'includes/include_header.php';
require_once 'includes/include_access.php';
require_once "includes/include_connection.php";


$kiw_invoice = $kiw_db->escape($_REQUEST['invoice']);
$kiw_format = $kiw_db->escape($_REQUEST['format']);


if (empty($kiw_invoice)) {

    header("Location: /admin/finance_invoice.php");

    die();
}


// set the timezone for this cloud tenant

$kiw_timezone = $_SESSION['timezone'];

if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


// get the cloud logo path

foreach (array("jpg", "jpeg", "png") as $kiw_extension) {


    if (file_exists(dirname(__FILE__, 2) . "/custom/{$_SESSION['tenant_id']}/logo-{$_SESSION['tenant_id']}.{$kiw_extension}") == true) {


        $kiw_logo = "/custom/{$_SESSION['tenant_id']}/logo-{$_SESSION['tenant_id']}.{$kiw_extension}";

        break;
    }
}

$kiw_logo = "<img src='{$kiw_logo}' style='max-height: 300px; max-width: 300px;'>";




$kiw_inv = $kiw_db->query_first("SELECT *, DATE(updated_date) AS invoice_date FROM kiwire_invoice WHERE tenant_id = '$tenant_id'  LIMIT 1");


?>



<div class="content-wrapper">

    <div class="row">
        <div class="col-sm-12">
            <div class="card-box">
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="pull-left">
                            <? echo $kiw_logo; ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="pull-right">
                            <p class="invoice-nr"><strong>Invoice Date:</strong></h3>
                            </p>
                            <p class="invoice-date">
                                <? echo  date('d/m/Y', strtotime($kiw_inv['invoice_date'])); ?>

                            </p>
                        </div>
                    </div>
                </div>

                <div class="row mt-3" style="margin-top: 10px;">

                    <div class="col-md-6">
                        <div class="pull-left">

                            <?php
                            $kiw_comp_details = $kiw_db->query_first("SELECT * FROM kiwire_clouds WHERE tenant_id = '$tenant_id' LIMIT 1");
                            ?>

                            <h3>Company Information</h3>
                            <strong>
                                <? echo $kiw_comp_details['name']; ?></strong><br>
                            <? echo $kiw_comp_details['address']; ?><br>
                            <abbr title="Phone">P:</abbr>
                            <? echo $kiw_comp_details['phone']; ?>

                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="pull-right">

                            <?php
                            $kiw_client_details = $kiw_db->query_first("SELECT * FROM kiwire_account_auth WHERE username = '{$kiw_inv['username']}' AND tenant_id = '$tenant_id' LIMIT 1");
                            ?>

                            <h3>Client Information</h3>
                            <strong>
                                <? echo $kiw_client_details['username']; ?></strong><br>
                            <? echo $kiw_client_details['email_address']; ?><br>
                            <abbr title="Phone">P:</abbr>
                            <? echo $kiw_client_details['phone_number']; ?>

                        </div>
                    </div>

                    <div class="col-md-12 mt-5">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item</th>
                                    <th>Description</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>
                                        <? echo $kiw_inv['username']?>
                                    </td>
                                    <td>
                                        <? echo $kiw_inv['profile']?>
                                    </td>
                                    <td>
                                        <? echo $kiw_inv['balance']?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="row col-md-12 mt-3">
                        <div class="col-md-6">
                            <div class="pull-left">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="pull-right">
                                <h5>Total (MYR):
                                    <? echo $kiw_inv['balance'] ?>
                                </h5>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

</section>

<script>
    window.onload = function() {

        window.print();

    }
</script>