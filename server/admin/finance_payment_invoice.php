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


$kiw_invoice = $kiw_db->escape($_REQUEST['id']);
// $kiw_format = $kiw_db->escape($_REQUEST['format']);


if (empty($kiw_invoice)) {

    header("Location: /admin/finance_payment_invoice.php");

    die();
}


// set the timezone for this cloud tenant

$kiw_timezone = $_SESSION['timezone'];

if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


// get the cloud logo path

foreach (array("jpg", "jpeg", "png", "webp") as $kiw_extension) {


    if (file_exists(dirname(__FILE__, 2) . "/custom/{$_SESSION['tenant_id']}/uploads/logo-{$_SESSION['tenant_id']}.{$kiw_extension}") == true) {


        $kiw_logo = "/custom/{$_SESSION['tenant_id']}/uploads/logo-{$_SESSION['tenant_id']}.{$kiw_extension}";

        break;
    }
}


$kiw_logo = "<img src='{$kiw_logo}' style='max-height: 300px; max-width: 300px;'>";

$kiw_inv = $kiw_db->query_first("SELECT *, DATE(updated_date) AS invoice_date FROM kiwire_payment_trx WHERE tenant_id = '{$tenant_id}' and id = '{$kiw_invoice}'  LIMIT 1");

$kiw_info = json_decode($kiw_inv['payload'], true);

$kiw_user = json_decode($kiw_inv['user_info'], true);


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
                            <p style="font-size:40px;"><strong>INVOICE</strong></p>
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
                            // $kiw_client_details = $kiw_db->query_first("SELECT * FROM kiwire_account_auth WHERE username = '{$kiw_inv['username']}' AND tenant_id = '$tenant_id' LIMIT 1");
                            ?>

                            <h3>Client Information</h3>
                            <strong>Username:</strong>
                            <? echo $kiw_inv['username']; ?><br>

                            <? if (!empty($kiw_user)) { ?>

                                <strong>Name:</strong>
                                <? $kiw_name = $kiw_user['first_name'] . " " . $kiw_user['last_name'];
                                echo $kiw_name; ?><br>
 
                                <strong>Email:</strong>
                                <? echo $kiw_user['email']; ?><br>

                                <strong>Phone:</strong>
                                <? echo $kiw_user['phone']; ?>
                            
                            <? } ?>

      

                        </div>
                    </div>

                    <div class="row col-md-12 mt-3">
                        <div class="col-md-6">
                            <div class="pull-left">
                                <strong>Invoice Date:</strong> <? echo  date('d/m/Y', strtotime($kiw_inv['invoice_date'])); ?><br>
                                <strong>Order ID:</strong> <? echo $kiw_info['transaction_details']['order_id'] ?><br>
                                <strong>Status  :</strong> 
                                <? if ($kiw_inv['status'] == "settlement" || $kiw_inv['status'] == "capture") {

                                    echo "Success";
                                
                                } else {

                                    echo "Pending";

                                }
                                ?>
                                
                            </div>
                        </div>

                    </div>


                    <div class="col-md-12 mt-5">
                        <table class="table table-striped table-hover" style="border-color:black;">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Item (Profile)</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>
                                        <? echo $kiw_info['item_details'][0]['name'] ?>
                                    </td>
                                    <td>
                                        <? echo $kiw_info['item_details'][0]['quantity'] ?>
                                    </td>
                                    <td>
                                        <? echo $kiw_inv['amount'] ?>
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
                                <h5>Total (<? echo $kiw_comp_details['currency'] ?>):
                                    <? echo $kiw_inv['amount'] ?>
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