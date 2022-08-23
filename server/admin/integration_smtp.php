<?php

$kiw['module'] = "Integration -> SMTP";
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


$kiw_fields = @file_get_contents(dirname(__FILE__, 2) . "/custom/{$_SESSION['tenant_id']}/data-mapping.json");

if (empty($kiw_fields)) {

    $kiw_fields = @file_get_contents(dirname(__FILE__, 2) . "/user/templates/kiwire-data-mapping.json");
}

if (!empty($kiw_fields)) $kiw_fields = json_decode($kiw_fields, true);

$kiw_count = 1;


$kiw_row = $kiw_db->query_first("SELECT * FROM kiwire_int_email WHERE tenant_id = '{$tenant_id}' LIMIT 1");

if (empty($kiw_row)) $kiw_db->query("INSERT INTO kiwire_int_email(tenant_id) VALUE('$tenant_id')");


$kiw_row['data'] = explode(",", $kiw_row['data']);


?>

<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="integration_smtp_title">Email Server</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="integration_smtp_subtitle">
                                Manage Email Server (SMTP) Connection
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">

                <!-- <div class="card-content mt-4"> -->
                <div class="card-content">
                    <div class="card-body">
                        <form id="update-form" class="form-horizontal" method="post">
                            
                            <div class="form-content">


                                <div class="col-12">
                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <span data-i18n="integration_smtp_enable">Enable</span>
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
                                            <span data-i18n="integration_smtp_host">SMTP Host</span>
                                        </div>
                                        <div class="col-md-10">
                                            <input type="text" name="host" id="host" value="<? echo $kiw_row['host']; ?>" class="form-control" placeholder="eg: smtp.gmail.com" />
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <span data-i18n="integration_smtp_port">SMTP Port</span>
                                        </div>
                                        <div class="col-md-10">
                                            <input type="text" name="port" id="port" value="<? echo $kiw_row['port']; ?>" class="form-control" placeholder="eg: 587" />
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <span data-i18n="integration_smtp_authentication">SMTP Authentication</span>
                                        </div>
                                        <div class="col-md-10">
                                            <select name="auth" class="select2 form-control" data-style="btn-default" data-for="smtp-test">
                                                <option value="none" <?= ($kiw_row['auth'] == "none" ? "selected" : "") ?>><span data-i18n="integration_smtp_none">None</span></option>
                                                <option value="tls" <?= ($kiw_row['auth'] == "tls" ? "selected" : "") ?>><span data-i18n="integration_smtp_tls">Yes : TLS</span></option>
                                                <option value="ssl" <?= ($kiw_row['auth'] == "ssl" ? "selected" : "") ?>><span data-i18n="integration_smtp_ssl">Yes : SSL</span></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <span data-i18n="integration_smtp_uname">SMTP Auth Username</span>
                                        </div>
                                        <div class="col-md-10">
                                            <input type="text" name="user" id="user" value="<? echo $kiw_row['user']; ?>" class="form-control" placeholder="eg: email@address.com" />
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <span data-i18n="integration_smtp_pass">SMTP Auth Password</span>
                                        </div>
                                        <div class="col-md-10">
                                            <input type="password" name="password" id="password" value="<? echo $kiw_row['password']; ?>" class="form-control" placeholder="Uppercase/lowercase/numeric/special character" />
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <span data-i18n="integration_smtp_email_addr">Email From Address</span>
                                        </div>
                                        <div class="col-md-10">
                                            <input type="text" name="from_email" id="from_email" value="<? echo $kiw_row['from_email']; ?>" class="form-control" placeholder="email@address.com" />
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <span data-i18n="integration_smtp_sender_name">Sender Name</span>
                                        </div>
                                        <div class="col-md-10">
                                            <input type="text" name="from_name" id="from_name" value="<? echo $kiw_row['from_name']; ?>" class="form-control" placeholder="eg: noreply@gmail.com" />
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <span data-i18n="integration_smtp_cc">CC</span>
                                        </div>
                                        <div class="col-md-10">
                                            <input type="text" name="cc_email" id="cc_email" value="<? echo $kiw_row['cc_email']; ?>" class="form-control" />
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <span data-i18n="integration_smtp_link">Link With Profile</span>
                                        </div>
                                        <div class="col-md-10">

                                            <select name="profile" id="profile" class="select2 form-control" data-style="btn-default">
                                                <option value="none" data-i18n="integration_smtp_none2">None</option>
                                                <?php

                                                $kiw_temp = "SELECT * FROM kiwire_profiles WHERE tenant_id = '{$tenant_id}'";
                                                $kiw_rows = $kiw_db->fetch_array($kiw_temp);

                                                foreach ($kiw_rows as $record) {

                                                    $selected = "";

                                                    if ($record['name'] == $kiw_row['profile']) $selected = 'selected="selected"';

                                                    echo "<option value ='{$record['name']}' {$selected}> {$record['name']}</option> \n";

                                                }

                                                ?>

                                            </select>

                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <span data-i18n="integration_smtp_validity">Validity</span> <span class="text-danger">*</span>
                                        </div>
                                        <div class="col-md-10"><input type="text" name="validity" id="validity" value="<?= $kiw_row['validity']; ?>" class="form-control" placeholder="eg: 360" required>
                                            <div style="font-size: smaller; padding: 10px" data-i18n="integration_smtp_days">days</div>
                                        </div>
                                    </div>
                                </div>


                                <div class="col-12">
                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <span data-i18n="integration_smtp_confirm_page">Confirm Page</span>
                                        </div>
                                        <div class="col-md-10">

                                            <select name="confirm_page" id="confirm_page" class="select2 form-control" data-style="btn-default" tabindex="-98">
                                                <option value="none" data-i18n="integration_smtp_none3">None</option>
                                                <?php

                                                $kiw_temp = "select * from kiwire_login_pages where tenant_id = '{$tenant_id}'";
                                                $kiw_rows = $kiw_db->fetch_array($kiw_temp);

                                                foreach ($kiw_rows as $record) {

                                                    $selected = "";

                                                    if ($record['unique_id'] == $kiw_row['confirm_page']) $selected = 'selected="selected"';

                                                    echo "<option value =\"$record[unique_id]\" $selected> $record[page_name]</option> \n";
                                                }

                                                ?>

                                            </select>

                                        </div>
                                    </div>
                                </div>


                                <div class="col-12">
                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <span data-i18n="integration_smtp_email_template">Email Template</span>
                                        </div>
                                        <div class="col-md-10">

                                            <select name="email_template" id="email_template" class="select2 form-control" data-style="btn-default" tabindex="-98">
                                                <option value="none" data-i18n="integration_smtp_none4">None</option>
                                                <?php

                                                $kiw_temp = "SELECT * FROM kiwire_html_template WHERE tenant_id = '{$tenant_id}' AND type = 'email'";
                                                $kiw_rows = $kiw_db->fetch_array($kiw_temp);

                                                foreach ($kiw_rows as $record) {

                                                    $selected = "";

                                                    if ($record['name'] == $kiw_row['email_template']) $selected = 'selected="selected"';

                                                    echo "<option value ='{$record['name']}' {$selected}> {$record['name']}</option> \n";
                                                }

                                                ?>

                                            </select>

                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <span data-i18n="integration_smtp_zone_restriction">Zone Restriction</span>
                                        </div>
                                        <div class="col-md-10">

                                            <select name="allowed_domain" id="allowed_domain" class="select2 form-control" data-style="btn-default" tabindex="-98">
                                                <option value="none" data-i18n="integration_smtp_none5">None</option>
                                                <?php

                                                $kiw_temp = "SELECT * FROM kiwire_allowed_zone WHERE tenant_id = '{$tenant_id}'";
                                                $kiw_rows = $kiw_db->fetch_array($kiw_temp);

                                                foreach ($kiw_rows as $record) {

                                                    $selected = "";

                                                    if ($record['name'] == $kiw_row['allowed_domain']) $selected = 'selected="selected"';

                                                    echo "<option value ='{$record['name']}' $selected> {$record['name']}</option> \n";
                                                }

                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <span data-i18n="integration_smtp_add_field">Additional Fields</span>
                                        </div>
                                        <div class="col-10">

                                            <select name="data[]" id="data" class="select2 form-control" multiple="multiple">

                                                <?php foreach ($kiw_fields as $kiw_field) { ?>

                                                    <div data-field-info="<?= $kiw_field['field'] ?>">

                                                        <?php if ($kiw_field['display'] != "[empty]") { ?>

                                                            <option value="<?= $kiw_field['variable'] ?>" <?= (in_array($kiw_field['variable'], $kiw_row['data']) ? "selected" : "") ?>> <?= $kiw_field['display'] ?></option>

                                                        <?php } ?>

                                                    </div>

                                                <?php $kiw_count++; ?>

                                                <?php } ?>

                                            </select>

                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <hr>
                                    <div class="form-group row">
                                        <div class="text-right col-md-3">
                                            &nbsp;
                                        </div>
                                        <div class="col-md-7">
                                            <button type="button" class="btn btn-warning waves-effect waves-light btn-test-smtp" data-i18n="integration_smtp_test">Test SMTP Connection</button>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">

                        </form>
                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-primary save-button waves-effect waves-light" data-i18n="integration_smtp_save">Save</button>
                    </div>
                </div>

            </div>
        </div>
    </div>


</div>


<div class="modal fade text-left" id="smtp-modal" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">

        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="smtp-modal-title" data-i18n="integration_smtp_conn_result">SMTP connection result</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">

                <div class="row">
                    <div class="col-md-9">
                        <input type="text" name="recipient_mail" class="form-control" value="" placeholder="Email Address">
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-primary pull-right btn-execute-test" data-i18n="integration_smtp_send_email">Send Email</button>
                    </div>
                </div>

                <div class="row pt-2">
                    <div class="col-md-12 smtp-result-space" style="min-height: 300px; overflow: auto;">
                        &nbsp;
                    </div>
                </div>


            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger waves-effect waves-light" data-dismiss="modal" data-i18n="integration_smtp_close">Close</button>
            </div>

        </div>

    </div>
</div>


<?php

require_once "includes/include_footer.php";

?>


<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
<script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>