<?php

$kiw['module'] = "Integration -> SMS";
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


$kiw_row = $kiw_db->query_first("SELECT * FROM  kiwire_int_sms WHERE tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");

if (empty($kiw_row)) $kiw_db->query("INSERT INTO  kiwire_int_sms(tenant_id) VALUE('{$_SESSION['tenant_id']}')");


$kiw_row['data'] = explode(",", $kiw_row['data']);


?>


<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="integration_sms_title">SMS</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="integration_sms_subtitle">
                                Connect to SMS gateway
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

                            <div class="card-header">
                               
                                <button type="button" class="btn btn-primary waves-effect waves-light create-btn-prefix" style="display:none;" data-toggle="modal" data-target="#inlineForm" data-i18n="integration_sms_add_prefix">
                                    Add Prefix
                                </button>
                            </div>

                            <div class="card-body">

                                <form class="update-form">

                                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                                    <ul class="nav nav-tabs" role="tablist">

                                        <li class="nav-item">
                                            <a class="nav-link active" id="basic-tab" data-toggle="tab" href="#basic" aria-controls="basic" role="tab" aria-selected="true" data-i18n="integration_sms_basic">BASIC</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="prefix-tab" data-toggle="tab" href="#prefix" aria-controls="prefix" role="tab" aria-selected="false" data-i18n="integration_sms_prefix">PREFIX</a>
                                        </li>

                                    </ul>

                                    <br><br>

                                    <div class="tab-content">
                                        <div class="tab-pane active" id="basic" aria-labelledby="basic-tab" role="tabpanel">

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_sms_enable">Enable</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <div class="custom-control custom-switch custom-control-inline">
                                                            <input type="checkbox" class="custom-control-input" name="enabled" id="enabled" <?= ($kiw_row['enabled'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="enabled"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
<!-- 
                                            <div class="col-12">
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
                                                        <span data-i18n="integration_sms_cloud_operator">Cloud SMS Operator</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <select name="operator" class="select2 form-control change-provider" data-for="sms-test" data-style="btn-default">
                                                            <option value="twilio" <?= (($kiw_row['operator'] == "twilio") ? " selected" : ""); ?>>
                                                                Twilio
                                                            </option>
                                                            <option value="synsms" <?= (($kiw_row['operator'] == "synsms") ? " selected" : ""); ?>>
                                                                Synchroweb
                                                            </option>
                                                            <option value="generic" <?= (($kiw_row['operator'] == "generic") ? " selected" : ""); ?>>
                                                                Generic
                                                            </option>
                                                            <option value="genusis" <?= (($kiw_row['operator'] == "genusis") ? " selected" : ""); ?>>
                                                                Genusis
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12 twillio provider-input">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_sms_twilio_phone">Twilio Phone No</span>
                                                    </div>
                                                    <div class="col-md-10">

                                                        <input type="text" name="twilio_no" id="twilio_no" value="<?= $kiw_row['twilio_no']; ?>" class="form-control">

                                                        <div style="font-size: smaller; padding: 10px;" class="flang-c-field_3_note" data-i18n="integration_sms_enter_twilio_no">Enter Twilio assign no, under
                                                            Product Phone no @ twilio.com. eg : +13603206925
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 twillio provider-input">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_sms_twilio_sid">Twilio SID</span>
                                                    </div>
                                                    <div class="col-md-10">

                                                        <input type="text" name="twilio_sid" id="twilio_sid" value="<?= $kiw_row['twilio_sid']; ?>" class="form-control">

                                                        <div style="font-size: smaller; padding: 10px;" data-i18n="integration_sms_enter_twilio_sid">Enter Twilio SID , under
                                                            Account @ twilio.com
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 twillio provider-input">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_sms_twilio_pass">Twilio Password</span>
                                                    </div>
                                                    <div class="col-md-10">

                                                        <input type="text" name="twilio_token" id="twilio_token" value="<?= $kiw_row['twilio_token']; ?>" class="form-control">

                                                        <div style="font-size: smaller; padding: 10px;" data-i18n="integration_sms_enter_twilio_pass">Enter Twilio Password , under
                                                            Account @ twilio.com
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12 provider-input synsms">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_sms_api_key">API Key</span>
                                                    </div>
                                                    <div class="col-md-10">

                                                        <input type="text" name="syn_key" id="syn_key" value="<?= $kiw_row['syn_key']; ?>" class="form-control">

                                                        <div style="font-size: smaller; padding: 10px;" data-i18n="integration_sms_enter_api_key">
                                                            API key provided by Synchroweb
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12 provider-input genusis">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_sms_genusis_url">Genusis URL</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" name="g_url" id="g_url" value="<?= $kiw_row['g_url']; ?>" class="form-control">
                                                        <div style="font-size: smaller; padding: 10px;" data-i18n="integration_sms_genusis_url_label">
                                                            Genusis URL to post SMS request
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 provider-input genusis">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_sms_genusis_clientid">Client ID</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" name="g_clientid" id="g_clientid" value="<?= $kiw_row['g_clientid']; ?>" class="form-control">
                                                        <div style="font-size: smaller; padding: 10px;" data-i18n="integration_sms_genusis_clientid_label">
                                                            Genusis Client ID
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 provider-input genusis">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_sms_genusis_username">Username</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" name="g_username" id="g_username" value="<?= $kiw_row['g_username']; ?>" class="form-control">
                                                        <div style="font-size: smaller; padding: 10px;" data-i18n="integration_sms_genusis_username_label">
                                                            Genusis Username
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 provider-input genusis">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_sms_genusis_key">Private Key</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="password" name="g_key" id="g_key" value="<?= $kiw_row['g_key']; ?>" class="form-control">
                                                        <div style="font-size: smaller; padding: 10px;" data-i18n="integration_sms_genusis_key_label">
                                                            Private key provided by Genusis [ not your password ]
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12 provider-input generic">
                                                <div class="form-group row">

                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_sms_request">Request Method</span>
                                                    </div>

                                                    <div class="col-md-10">
                                                        <select name="u_method" id="" class="select2 form-control" data-style="btn-default" tabindex="-98">
                                                            <option value="get" <?php echo ($kiw_row['u_method'] == "get" ? "selected" : ""); ?>>
                                                                GET
                                                            </option>
                                                            <option value="post" <?php echo ($kiw_row['u_method'] == "post" ? "selected" : ""); ?>>
                                                                POST
                                                            </option>
                                                        </select>
                                                    </div>

                                                </div>
                                            </div>

                                            <div class="col-12 provider-input generic">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_sms_url">Full URI</span>
                                                    </div>
                                                    <div class="col-md-10">

                                                        <input type="text" name="u_uri" id="u_uri" value="<?= $kiw_row['u_uri']; ?>" class="form-control">

                                                        <div style="font-size: smaller; padding: 10px;" data-i18n="integration_sms_url_to_send">
                                                            URI to send SMS
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 provider-input generic">
                                                <div class="form-group row">

                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_sms_variable">Variable Name for Message</span>
                                                    </div>

                                                    <div class="col-md-10">

                                                        <input type="text" name="u_message" id="u_message" value="<?= $kiw_row['u_message']; ?>" class="form-control">

                                                        <div style="font-size: smaller; padding: 10px;" data-i18n="integration_sms_variable_name">Variable name for message
                                                        </div>

                                                    </div>

                                                </div>
                                            </div>

                                            <div class="col-12 provider-input generic">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_sms_var_phone">Variable Name for Phone Number</span>
                                                    </div>
                                                    <div class="col-md-10">

                                                        <input type="text" name="u_phoneno" id="u_phoneno" value="<?= $kiw_row['u_phoneno']; ?>" class="form-control">

                                                        <div style="font-size: smaller; padding: 10px;">
                                                            <span data-i18n="integration_sms_var_name_phone">Variable name for phone number to send the message to</span>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 provider-input generic">
                                                <div class="form-group row">

                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_sms_headers">Headers</span>
                                                    </div>

                                                    <div class="col-md-10">

                                                        <input type="text" name="u_header" id="u_header" value="<?= $kiw_row['u_header']; ?>" class="form-control">

                                                        <div style="font-size: smaller; padding: 10px;" data-i18n="integration_sms_custom_header">
                                                            Custom header for request
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>

                                            <input type="hidden" name="update" value="true" />

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_sms_mode">Mode</span>
                                                    </div>

                                                    <div class="col-md-10">

                                                        <select name="mode" class="select2 form-control" data-for="sms-test" data-style="btn-default">

                                                            <option value="1" <?= (($kiw_row['mode'] == "1") ? " selected" : ""); ?>>
                                                                <span data-i18n="integration_sms_otp_register">Send OTP to complete registration</span>
                                                            </option>
                                                            <option value="2" <?= (($kiw_row['mode'] == "2") ? " selected" : ""); ?>>
                                                                <span data-i18n="integration_sms_otp_login">Send OTP every login</span>
                                                            </option>
                                                            <option value="3" <?= (($kiw_row['mode'] == "3") ? " selected" : ""); ?>>
                                                                <span data-i18n="integration_sms_pass_register">Send user password once registered</span>
                                                            </option>

                                                        </select>

                                                    </div>

                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">

                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_sms_content">SMS Content</span>
                                                    </div>

                                                    <div class="col-md-10">


                                                        <select name="sms_text" id="sms_text" class="select2 form-control" data-style="btn-default" tabindex="-98">
                                                            <option value="none" data-i18n="integration_sms_none">None</option>
                                                            <?php

                                                            $rows = "SELECT * FROM kiwire_html_template WHERE type = 'sms' AND tenant_id = '{$tenant_id}'";
                                                            $rows = $kiw_db->fetch_array($rows);

                                                            foreach ($rows as $record) {


                                                                $selected = "";

                                                                if ($record['name'] == $kiw_row['sms_text']) $selected = 'selected="selected"';

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
                                                        <span data-i18n="integration_sms_link">Link With Profile</span>
                                                    </div>

                                                    <div class="col-md-10">

                                                        <select name="profile" id="profile" class="select2 form-control" data-style="btn-default" tabindex="-98">
                                                            <option value="none" data-i18n="integration_sms_none2">None</option>
                                                            <?php

                                                            $rows = "SELECT * FROM kiwire_profiles WHERE tenant_id = '{$tenant_id}'";
                                                            $rows = $kiw_db->fetch_array($rows);

                                                            foreach ($rows as $record) {


                                                                $selected = "";

                                                                if ($record['name'] == $kiw_row['profile']) $selected = 'selected="selected"';

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
                                                        <span data-i18n="integration_sms_validity">Validity</span> <span class="text-danger">*</span>
                                                    </div>
                                                    <div class="col-md-10">

                                                        <input type="text" name="validity" id="validity" value="<?= $kiw_row['validity']; ?>" class="form-control" required>
                                                        <div style="font-size: smaller; padding: 10px;" data-i18n="integration_sms_days">Days</div>

                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_sms_once_registered">Once Registered</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <select name="after_register" id="after_register" class="select2 form-control" data-style="btn-default" tabindex="-98">
                                                            <?php $temp_list = array("internet" => "Login User to Wifi", "journey" => "Follow Journey if Possible"); ?>
                                                            <?php foreach ($temp_list as $tl_key => $tl_val) : $selected = $tl_key == $kiw_row['after_register'] ? "selected" : ""; ?>
                                                                <option value="<?= $tl_key ?>" <?= $selected ?>><?= $tl_val ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_sms_zone_restriction">Zone Restriction</span>
                                                    </div>
                                                    <div class="col-md-10">

                                                        <select name="allowed_zone" id="allowed_zone" class="select2 form-control" data-style="btn-default">
                                                            <option value="none" data-i18n="integration_sms_none3">None</option>
                                                            <?php

                                                            $rows = "SELECT * FROM kiwire_allowed_zone WHERE tenant_id = '{$tenant_id}' GROUP BY `name` ORDER BY `name`";
                                                            $rows = $kiw_db->fetch_array($rows);

                                                            foreach ($rows as $record) {


                                                                $selected = "";

                                                                if ($record['name'] == $kiw_row['allowed_zone']) $selected = 'selected="selected"';

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
                                                        <span data-i18n="integration_sms_prefix_no">Prefix Phone No With +Sign</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <div class="custom-control custom-switch custom-control-inline">
                                                            <input type="checkbox" class="custom-control-input" name="prefix_phoneno" id="prefix_phoneno" <?= ($kiw_row['prefix_phoneno'] == "y") ? 'checked="yes"' : '' ?>value="y" class="toggle" />
                                                            <label class="custom-control-label" for="prefix_phoneno"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_sms_add_field">Additional Fields</span>
                                                    </div>
                                                    <div class="col-10">

                                                        <select name="data[]" id="data" class="select2 form-control" multiple="multiple">
                                                            <?php foreach ($kiw_fields as $kiw_field) { ?>

                                                                <div data-field-info="<?= $kiw_field['field'] ?>">
                                                                    <?php if ($kiw_field['display'] != "[empty]") { ?>
                                                                        <option value="<?= $kiw_field['variable'] ?>" <?= (in_array($kiw_field['variable'], $kiw_row['data']) ? "selected" : "") ?>> <?= $kiw_field['display'] ?></option>
                                                                    <?php } ?>
                                                                </div>

                                                            <?php $kiw_count++;
                                                            } ?>
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
                                                        <button type="button" onclick="$('#test-modal').modal({show:true});" class="btn btn-warning waves-effect waves-light" data-i18n="integration_sms_test">
                                                            Test Send SMS
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <?php if (false) : ?>

                                                <div class="col-12">
                                                    <div class="form-group row">

                                                        <div class="text-right col-md-3">
                                                            <span data-i18n="integration_sms_cloud_acc">Synchroweb Cloud SMS Account No</span>
                                                        </div>

                                                        <div class="col-md-7">

                                                            <input type="text" name="syn_account" id="syn_account" value="<?= $kiw_row['syn_account']; ?>" class="form-control">

                                                            <div style="font-size: smaller; padding: 10px;" data-i18n="integration_sms_enter_acc_no">Enter Synchroweb Cloud SMS
                                                                account no
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="text-right col-md-3">
                                                            <span data-i18n="integration_sms_cloud_key">Synchroweb Cloud SMS Key</span>
                                                        </div>
                                                        <div class="col-md-7">
                                                            <input type="text" name="syn_key" id="syn_key" value="<?= $kiw_row['syn_key']; ?>" class="form-control">
                                                            <div style="font-size: smaller; padding: 10px;" data-i18n="integration_sms_enter_cloud_key">Enter Synchroweb Cloud SMS
                                                                assign account key
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                        </div>

                                        <div class="tab-pane" id="prefix" aria-labelledby="prefix-tab" role="tabpanel">

                                            <div class="table-responsive">
                                                <table id="itemlist" class="table table-hover table-data">
                                                    <thead>
                                                        <tr class="text-uppercase">
                                                            <th data-i18n="integration_sms_no">No</th>
                                                            <th data-i18n="integration_sms_country">Country</th>
                                                            <th data-i18n="integration_sms_prefix2">Prefix</th>
                                                            <th data-i18n="integration_sms_action">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <th data-i18n="integration_sms_loading">
                                                            Loading..
                                                        </th>
                                                    </tbody>
                                                </table>
                                            </div>

                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-primary save-button waves-effect waves-light" data-i18n="integration_sms_save">Save </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>


<!-- Modal -->
<div class="modal fade text-left" id="inlineForm" role="dialog">

    <div class="modal-dialog modal-dialog-centered" role="document">

        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel33" data-i18n="integration_sms_add_edit">Add or Edit Prefix</h4>

                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="create-form" action="#">

                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                <div class="modal-body">

                    <label data-i18n="integration_sms_country2">Country: </label> <span class="text-danger">*</span>
                    <div class="form-group">
                        <input type="text" name="country" id="country" value="" class="form-control" placeholder="eg: Malaysia" required>
                    </div>

                    <label data-i18n="integration_sms_prefix3">Prefix: </label> <span class="text-danger">*</span>
                    <div class="form-group">
                        <input type="text" name="prefix" id="prefix" value="" class="form-control" placeholder="eg: +60" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="integration_sms_cancel">Cancel
                    </button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-create" data-i18n="integration_sms_create">Create
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>


<div id="test-modal" class="modal fade in" role="dialog" aria-labelledby="form-modal" aria-hidden="true">
    <form id="test-form" class="form-horizontal" role="form">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel" data-i18n="integration_sms_send_test">Send Test SMS</h4>

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <label data-i18n="integration_sms_send_test_label">Please provide a phone number to send the test message: </label>

                    <div class="form-group">
                        <input type="text" class="form-control " name="smsto" value="" placeholder="" data-for="sms-test">
                    </div>

                    <div class="row">
                        <div class="col-12 sms-respond-space" style="height: 50px; overflow: auto;">
                            &nbsp;
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger waves-effect waves-light" data-dismiss="modal" data-i18n="integration_sms_close">
                            Close
                        </button>
                        <button type="button" class="btn btn-primary waves-effect waves-light btn-send-test" data-i18n="integration_sms_send">
                            Send
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>


<?php

require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";

?>



<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
<script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>