<?php

$kiw['module'] = "Integration -> E-Payment";
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

$kiw_row = $kiw_db->query_first("SELECT * FROM kiwire_int_payment_gateways WHERE tenant_id = '$tenant_id'");

if (empty($kiw_row)) $kiw_db->query("INSERT INTO kiwire_int_payment_gateways(tenant_id) VALUE('$tenant_id')");

?>


<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="integration_payment_gateway_title">Payment Gateways</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="integration_payment_gateway_subtitle">
                                Manage e-payment gateway
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <section id="basic-tabs-components">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card overflow-hidden">
                        <div class="card-content">
                            <div class="card-body">

                                <form id="update-form">

                                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                                    


                                    <ul class="nav nav-tabs" role="tablist">

                                        <li class="nav-item">
                                            <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" aria-controls="general" role="tab" aria-selected="true" data-i18n="integration_payment_gateway_general">GENERAL</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="payment_type-tab" data-toggle="tab" href="#payment_type" aria-controls="payment_type" role="tab" aria-selected="false" data-i18n="integration_payment_gateway_type">PAYMENT TYPE</a>
                                        </li>

                                    </ul>

                                    <br><br>
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="general" aria-labelledby="general-tab" role="tabpanel">

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_payment_gateway_enable">Enable</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <div class="custom-control custom-switch custom-control-inline">
                                                            <input type="checkbox" class="custom-control-input" name="enabled" id="enabled" <?= ($kiw_row['enabled'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="enabled"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="">24 Hours</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <div class="custom-control custom-switch custom-control-inline">
                                                            <input type="checkbox" class="custom-control-input" name="is_24" id="is_24" <?= ($kiw_row['is_24_hour'] == "1") ? 'checked' : '' ?> value="1" class="toggle" />
                                                            <label class="custom-control-label" for="is_24"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 time" style="display: <?= $kiw_row['is_24_hour'] ? 'none' : 'block' ?>">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="">Start Time</span> 
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" name=start_time id=start_time value="<?= $kiw_row['start_time']; ?>" class="form-control datetime"  />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 time" style="display: <?= $kiw_row['is_24_hour'] ? 'none' : 'block' ?>">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="">Stop Time</span> 
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" name=stop_time id=stop_time value="<?= $kiw_row['stop_time']; ?>" class="form-control datetime"  />
                                                    </div>
                                                </div>
                                            </div> -->

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_payment_gateway_allowed_profile">Allowed Profile</span>
                                                    </div>
                                                    <div class="col-md-10">

                                                        <select name="allowed_profile" id="allowed_profile" class="select2 form-control" data-style="btn-default" tabindex="-98">
                                                            <option value="none" data-i18n="integration_payment_gateway_none">None</option>
                                                            <?
                                                            $sql = "select * from kiwire_allowed_zone where tenant_id = '{$tenant_id}' group by name order by name";
                                                            $rows = $kiw_db->fetch_array($sql);
                                                            foreach ($rows as $record) {
                                                                $selected = "";
                                                                if ($record['name'] == $kiw_row['allowed_profile']) $selected = 'selected="selected"';
                                                                echo "<option value =\"$record[name]\" $selected> $record[name]</option> \n";
                                                            }
                                                            ?>
                                                        </select>

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_payment_gateway_validity">Validity</span> <span class="text-danger">*</span>
                                                    </div>
                                                    <div class="col-md-10"><input type="text" name="validity" id="validity" value="<?= $kiw_row['validity']; ?>" class="form-control" required>
                                                        <div style="font-size: smaller; padding: 10px;" data-i18n="integration_payment_gateway_days">days</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_payment_gateway_send_email">Send Email to User</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <div class="custom-control custom-switch custom-control-inline">
                                                            <input type="checkbox" class="custom-control-input" name="notification_send" id="notification_send" <?= ($kiw_row['notification_send'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="notification_send"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_payment_gateway_on_success">On Success</span>
                                                    </div>
                                                    <div class="col-md-4">

                                                        <?php
                                                        $optionstemp = array("create_voucher" => "Create Voucher", "create_user" => "Create User", "renew_user" => "Renew User");
                                                        ?>
                                                        <select name="on_success" id="on_success" class="select2 form-control" data-style="btn-default" tabindex="-98">
                                                            <?php foreach ($optionstemp as $e_key => $e_optstemp) : $selected = ($e_key == $kiw_row['on_success']) ? "selected" : ""; ?>
                                                                <option value="<?= $e_key ?>" <?= $selected ?>><?= $e_optstemp ?></option>
                                                            <?php endforeach; ?>
                                                        </select>

                                                    </div>

                                                    <div class="text-center col-md-2">
                                                        <span data-i18n="integration_payment_gateway_then">Then</span>
                                                    </div>
                                                    <div class="col-md-4">

                                                        <?php
                                                        $optionstemp1 = array("redirect_success_page" => "Redirect to Success Page", "redirect_login" => "Automatically Login");
                                                        ?>
                                                        <select name="on_after_success" id="on_after_success" class="select2 form-control" data-style="btn-default" tabindex="-98">
                                                            <?php foreach ($optionstemp1 as $e_key => $e_optstemp) : $selected = ($e_key == $kiw_row['on_after_success']) ? "selected" : ""; ?>
                                                                <option value="<?= $e_key ?>" <?= $selected ?>><?= $e_optstemp ?></option>
                                                            <?php endforeach; ?>
                                                        </select>

                                                    </div>

                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_payment_gateway_success_page">On Success Page</span>
                                                    </div>
                                                    <div class="col-md-10">

                                                        <select name="page_success" id="page_success" class="select2 form-control" data-style="btn-default" tabindex="-98">
                                                            <option value="none" data-i18n="integration_payment_gateway_none2">None</option>
                                                            <?
                                                            $sql = "SELECT SQL_CACHE page_name FROM kiwire_login_pages WHERE tenant_id = '$tenant_id'";
                                                            $rows = $kiw_db->fetch_array($sql);
                                                            foreach ($rows as $record) {
                                                                $selected = "";
                                                                if ($record['page_name'] == $kiw_row['page_success']) $selected = 'selected="selected"';
                                                                echo "<option value =\"$record[page_name]\" $selected> $record[page_name]</option> \n";
                                                            }
                                                            ?>

                                                        </select>

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_payment_gateway_failure_page">On Failure Page</span>
                                                    </div>
                                                    <div class="col-md-10">

                                                        <select name="page_failed" id="page_failed" class="select2 form-control" data-style="btn-default" tabindex="-98">
                                                            <option value="none" data-i18n="integration_payment_gateway_none3">None</option>
                                                            <?
                                                            $sql = "SELECT SQL_CACHE page_name FROM kiwire_login_pages WHERE tenant_id = '$tenant_id'";
                                                            $rows = $kiw_db->fetch_array($sql);
                                                            foreach ($rows as $record) {
                                                                $selected = "";
                                                                if ($record['page_name'] == $kiw_row['page_failed']) $selected = 'selected="selected"';
                                                                echo "<option value =\"$record[page_name]\" $selected> $record[page_name]</option> \n";
                                                            }
                                                            ?>

                                                        </select>

                                                    </div>
                                                </div>
                                            </div>


                                        </div>

                                        <div class="tab-pane" id="payment_type" aria-labelledby="payment_type-tab" role="tabpanel">

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_payment_gateway_pay_type">Payment Type</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <select name="paymenttype" class="select2 form-control change-provider" data-for="sms-test" data-style="btn-default" tabindex="-98">
                                                            <option value="payfast" <?= (($kiw_row['paymenttype'] == "payfast") ? " selected" : ""); ?>>Payfast</option>
                                                            <option value="paypal" <?= (($kiw_row['paymenttype'] == "paypal") ? " selected" : ""); ?>>Paypal</option>
                                                            <option value="wirecard" <?= (($kiw_row['paymenttype'] == "wirecard") ? " selected" : ""); ?>>Wirecard</option>
                                                            <option value="alipay" <?= (($kiw_row['paymenttype'] == "alipay") ? " selected" : ""); ?>>Alipay</option>
                                                            <option value="stripe" <?= (($kiw_row['paymenttype'] == "stripe") ? " selected" : ""); ?>>Stripe</option>
                                                            <option value="senangpay" <?= (($kiw_row['paymenttype'] == "senangpay") ? " selected" : ""); ?>>Senangpay</option>
                                                            <option value="adyen" <?= (($kiw_row['paymenttype'] == "adyen") ? " selected" : ""); ?>>Adyen</option>
                                                            <option value="ipay88" <?= (($kiw_row['paymenttype'] == "ipay88") ? " selected" : ""); ?>>Ipay88</option>
                                                            <option value="sarawakpay" <?= (($kiw_row['paymenttype'] == "sarawakpay") ? " selected" : ""); ?>>SarawakPay</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12 payfast wirecard alipay senangpay adyen sarawakpay provider-input">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_payment_gateway_merchant_id">Merchant ID</span>
                                                    </div>
                                                    <div class="col-md-10"><input type="text" name="merchant_id1" id="merchant_id1" value="<?= $kiw_row['merchant_id']; ?>" class="form-control">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 ipay88 provider-input">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_payment_gateway_merchant_code">Merchant Code</span>
                                                    </div>
                                                    <div class="col-md-10"><input type="text" name="merchant_code" id="merchant_code" value="<?= $kiw_row['merchant_id']; ?>" class="form-control">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 sarawakpay  provider-input">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_payment_gateway_merchant_name">Merchant Name</span>
                                                    </div>
                                                    <div class="col-md-10"><input type="text" name="merchant_name" id="merchant_name" value="<?= $kiw_row['merchant_key']; ?>" class="form-control">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 sarawakpay  provider-input">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_payment_gateway_username">Username</span>
                                                    </div>
                                                    <div class="col-md-10"><input type="text" name="username" id="username" value="<?= $kiw_row['reference']; ?>" class="form-control">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 payfast ipay88 provider-input">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_payment_gateway_marchant_key">Merchant Key</span>
                                                    </div>
                                                    <div class="col-md-10"><input type="text" name="merchant_key1" id="merchant_key1" value="<?= $kiw_row['merchant_key']; ?>" class="form-control">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 sarawakpay  provider-input">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_payment_gateway_password">Password</span>
                                                    </div>
                                                    <div class="col-md-10"><input type="password" name="password" id="password" value="<?= $kiw_row['passphrase']; ?>" class="form-control">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 payfast provider-input">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_payment_gateway_passphrase">Passphrase</span>
                                                    </div>
                                                    <div class="col-md-10"><input type="text" name="passphrase1" id="passphrase1" value="<?= $kiw_row['passphrase']; ?>" class="form-control">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 payfast provider-input">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_payment_gateway_reference">Reference</span>
                                                    </div>
                                                    <div class="col-md-10"><input type="text" name="reference" id="reference" value="<?= $kiw_row['reference']; ?>" class="form-control">
                                                        <div style="font-size: smaller; padding: 10px;" data-i18n="integration_payment_gateway_payment_ref">Payment reference prefix send to Payfast</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 payfast provider-input">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_payment_gateway_description">Description</span>
                                                    </div>
                                                    <div class="col-md-10"><input type="text" name="description" id="description" value="<?= $kiw_row['description']; ?>" class="form-control">
                                                        <div style="font-size: smaller; padding: 10px;" data-i18n="integration_payment_gateway_gen_desc">General description about the payment</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 payfast paypal provider-input">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_payment_gateway_confirm_email">Confirmation Email</span>
                                                    </div>
                                                    <div class="col-md-10"><input type="text" name="confirmation_email" id="confirmation_email" value="<?= $kiw_row['confirmation_email']; ?>" class="form-control">
                                                        <div style="font-size: smaller; padding: 10px;" data-i18n="integration_payment_gateway_leave_field">Leave this field blank if you dont want any confirmation email send to you</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 wirecard provider-input">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_payment_gateway_security">Security Sequence</span>
                                                    </div>
                                                    <div class="col-md-10"><input type="text" name="security_sequence" id="security_sequence" value="<?= $kiw_row['security_sequence']; ?>" class="form-control">
                                                        <div style="font-size: smaller;">(e.g: amt,ref,cur,mid,transtype)</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 stripe provider-input">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_payment_gateway_publishable_key">Publishable Key</span>
                                                    </div>
                                                    <div class="col-md-10"><input type="text" name="publishable_key" id="publishable_key" value="<?= $kiw_row['merchant_id']; ?>" class="form-control">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 wirecard stripe senangpay provider-input">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_payment_gateway_secret_key">Secret Key</span>
                                                    </div>
                                                    <div class="col-md-10"><input type="text" name="secret_key" id="secret_key" value="<?= $kiw_row['merchant_key']; ?>" class="form-control">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 adyen provider-input">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_payment_gateway_skin_code">Skin Code</span>
                                                    </div>
                                                    <div class="col-md-10"><input type="text" name="skin_code" id="skin_code" value="<?= $kiw_row['merchant_key']; ?>" class="form-control">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 adyen provider-input">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_payment_gateway_hmac_key">HMAC Key</span>
                                                    </div>
                                                    <div class="col-md-10"><input type="text" name="HMAC_key" id="HMAC_key" value="<?= $kiw_row['passphrase']; ?>" class="form-control">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 alipay provider-input">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_payment_gateway_md5_key">MD5 Signature Key</span>
                                                    </div>
                                                    <div class="col-md-10"><input type="text" name="MD5_signature_key" id="MD5_signature_key" value="<?= $kiw_row['merchant_key']; ?>" class="form-control">
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                </form>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-primary save-button waves-effect waves-light" data-i18n="integration_payment_gateway_save">Save</button>
                            </div>
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

<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
<script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>