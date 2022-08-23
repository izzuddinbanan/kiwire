<?php

$kiw['module'] = "Configuration -> General";
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

$kiw_row = $kiw_db->query_first("SELECT * FROM kiwire_clouds WHERE kiwire_clouds.tenant_id = '$tenant_id' LIMIT 1");

if (empty($kiw_row)) {

    $kiw_db->query("INSERT INTO kiwire_clouds(tenant_id) VALUE('{$tenant_id}')");
}


?>

<style>
    .select2-container {
        width: 92% !important;
    }
</style>

<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Setting</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                Overall setting for system
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
                            <div class="card-header pull-right">
                                
                            </div>
                            <div class="card-body">
                                <form id="update-form" class="form-horizontal" method="post">

                                    <ul class="nav nav-tabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" aria-controls="general" role="tab" aria-selected="true" data-i18n="tab_general">GENERAL</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="voucher-tab" data-toggle="tab" href="#voucher" aria-controls="voucher" role="tab" aria-selected="false" data-i18n="tab_voucher">VOUCHER</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="topup-tab" data-toggle="tab" href="#topup" aria-controls="topup" role="tab" aria-selected="false" data-i18n="tab_topup">TOPUP</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="campaign-tab" data-toggle="tab" href="#campaign" aria-controls="campaign" role="tab" aria-selected="false" data-i18n="tab_campaign">CAMPAIGN</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="timezone-tab" data-toggle="tab" href="#timezone" aria-controls="timezone" role="tab" aria-selected="false" data-i18n="tab_timezone">DATE & TIME ZONE</a>
                                        </li>
                                    </ul>

                                    <br>

                                    <div class="tab-content">
                                        <div class="tab-pane active" id="general" aria-labelledby="general-tab" role="tabpanel">

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                                        <span data-i18n="general_span_currency">Default Currency</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <input type="text" name="currency" id="currency" value="<? echo $kiw_row['currency']; ?>" class="form-control col-11" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                                        <span data-i18n="general_span_gst">GST / VAT Rate (%)</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <input type="text" name="gst_percentage" id="gst_percentage" value="<? echo $kiw_row['gst_percentage']; ?>" class="form-control col-11" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                                        <span data-i18n="general_span_metric">Data Transfer Reporting Metric</span>
                                                    </div>

                                                    <? if ($kiw_row['volume_metrics'] == "Mb") {
                                                        $select1 = "selected";
                                                    } else {
                                                        $select2 = "selected";
                                                    } ?>

                                                    <div class="col-md-9">
                                                        <select name="volume_metrics" id="volume_metrics" class="select2 form-control col-11" data-style="btn-default" tabindex="-98">
                                                            <option value="Mb" <? if (isset($select1)) {
                                                                                    echo $select1;
                                                                                } ?>>Mb
                                                            </option>
                                                            <option value="Gb" <? if (isset($select2)) {
                                                                                    echo $select2;
                                                                                } ?>>Gb
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                                        <span data-i18n="general_span_insight">Join Insight Network</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <div class="custom-control custom-switch custom-control-inline col-md-8">
                                                            <input type="checkbox" class="custom-control-input" name="insight_reporting" id="insight_reporting" <? if ($kiw_row['insight_reporting'] == "y") {
                                                                                                                                                                    echo 'checked="yes"';
                                                                                                                                                                } ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="insight_reporting"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                                        <span data-i18n="general_span_2factor_auth">2-Factors Authentication</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <div class="custom-control custom-switch custom-control-inline col-md-8">
                                                            <input type="checkbox" class="custom-control-input" name="require_mfactor" id="require_mfactor" <? if ($kiw_row['require_mfactor'] == "y") {
                                                                                                                                                                echo 'checked="yes"';
                                                                                                                                                            } ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="require_mfactor"></label>
                                                            <div class="col-md-13">
                                                                <div class="badge badge-danger badge-md ml-1" role="alert">
                                                                    <span style="font-size: 11px;"><strong data-i18n="general_span_for_mod">For Administrator only</strong></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                                        <span data-i18n="general_span_language">Default Language</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <select name="default_language" class="select2 form-control col-11" data-style="btn-default" tabindex="-98">
                                                            <?php

                                                            echo "<option value=\"en\" " . ($kiw_row['default_language'] == "en" ? "selected" : "") . ">English</option>";
                                                            echo "<option value=\"ch\" " . ($kiw_row['default_language'] == "ch" ? "selected" : "") . ">Chinese (Simplified)</option>";
                                                            echo "<option value=\"my\" " . ($kiw_row['default_language'] == "my" ? "selected" : "") . ">Bahasa Malaysia</option>";

                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                                        <span data-i18n="general_span_forgot_pass">Forgot Password Method</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <select name="forgot_password_method" id="forgot_password_method" class="select2 form-control col-11" data-style="btn-default" tabindex="-98">
                                                            <option value="email" <?= ($kiw_row['forgot_password_method'] == 'email' ? 'selected' : '') ?>>Email</option>
                                                            <option value="sms" <?= ($kiw_row['forgot_password_method'] == 'sms' ? 'selected' : '') ?>>SMS</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                                        <span data-i18n="general_span_forgot_pass_temp">Forgot Password Template</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <select name="forgot_password_template" id="forgot_password_template" class="select2 form-control col-11" data-style="btn-default" tabindex="-98">
                                                            <option value="" data-i18n="general_span_forgot_pass_temp_none">None</option>

                                                            <?php
                                                            $template = $kiw_db->fetch_array("SELECT type, name FROM kiwire_html_template WHERE tenant_id = '$tenant_id' AND (type = 'email' OR type = 'sms')");

                                                            foreach ($template as $line) {
                                                                echo "<option value='" . $line['name'] . "' " . ($kiw_row['forgot_password_template'] == $line['name'] ? "selected" : "") . ">" . $line['name'] . " [ " . ucfirst($line['type']) . " ]</option>\n";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                                        <span data-i18n="general_span_reset_account">Reset Account with Last Password Change</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <div class="custom-control custom-switch custom-control-inline col-md-8">
                                                            <input type="checkbox" class="custom-control-input" name="reset_account_with_date_password" id="reset_account_with_date_password" <? if ($kiw_row['reset_acc_and_date_password'] == "y") {
                                                                                                                                                        echo 'checked="yes"';
                                                                                                                                                    } ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="reset_account_with_date_password"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                                        <span data-i18n="general_span_nps">NPS Enable</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <div class="custom-control custom-switch custom-control-inline col-md-8">
                                                            <input type="checkbox" class="custom-control-input" name="nps_enabled" id="nps_enabled" <? if ($kiw_row['nps_enabled'] == "y") {
                                                                                                                                                        echo 'checked="yes"';
                                                                                                                                                    } ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="nps_enabled"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                                        <span data-i18n="general_span_nps_email">NPS Email Template</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <select id="nps_template" name="nps_template" class="select2 form-control col-11" data-style="btn-default" tabindex="-98">
                                                            <option value="" data-i18n="general_span_nps_email_none">None</option>
                                                            <?php
                                                            $template = $kiw_db->fetch_array("SELECT name FROM kiwire_html_template WHERE tenant_id = '$tenant_id' AND type = 'email'");

                                                            foreach ($template as $line) : ?>
                                                                <option value="<?= $line['name'] ?>" <?= ($kiw_row['nps_template'] == $line['name'] ? "selected" : "") ?>>
                                                                    <?= $line['name'] ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                                        <span data-i18n="general_span_ask_user_webpush">Ask User for Web Push</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <div class="custom-control custom-switch custom-control-inline">
                                                            <input type="checkbox" class="custom-control-input" name="ask_web_push" id="ask_web_push" <? if ($kiw_row['ask_web_push'] == "y") {
                                                                                                                                                            echo 'checked="yes"';
                                                                                                                                                        } ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="ask_web_push"></label>

                                                            <div class="col-md-13">
                                                                <div class="badge badge-danger badge-md ml-1" role="alert">
                                                                    <span style="font-size: 11px;"><strong data-i18n="general_span_ask_user_webpush_label">Only work if connection is HTTPS and end-user is using Android or Windows (Chrome or Firefox)</strong></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>





                                        </div>

                                        <div class="tab-pane" id="voucher" aria-labelledby="voucher-tab" role="tabpanel">

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                                        <span data-i18n="voucher_span_prefix">Default Prefix</span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="voucher_prefix" id="voucher_prefix" value="<? echo $kiw_row['voucher_prefix']; ?>" class="form-control col-11" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                                        <span data-i18n="voucher_span_voucher_format">Voucher Format</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <select name="voucher_engine" id="voucher_engine" class="select2 form-control col-11 change-device-type" data-style="btn-default" tabindex="-98">
                                                            <option value="serial" <?= ($kiw_row['voucher_engine'] == 'serial' ? 'selected' : '') ?> data-i18n="voucher_span_voucher_format_serial">Number in Sequence</option>
                                                            <option value="uuid" <?= ($kiw_row['voucher_engine'] == 'uuid' ? 'selected' : '') ?> data-i18n="voucher_span_voucher_format_uuid">Unique ID</option>
                                                            <option value="random" <?= ($kiw_row['voucher_engine'] == 'random' ? 'selected' : '') ?> data-i18n="voucher_span_voucher_format_random">Random [0-9a-zA-Z]</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                                        <span data-i18n="voucher_span_template">Voucher Template</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <select name="voucher_template" id="voucher_template" class="form-control col-11" data-style="btn-default" tabindex="-98">
                                                            <?php
                                                            $prepaid_templates = $kiw_db->fetch_array("SELECT id, name FROM kiwire_html_template WHERE type='voucher' AND tenant_id='$tenant_id' ");
                                                            ?>

                                                            <option value="0" data-i18n="voucher_span_template_none">None</option>

                                                            <?php foreach ($prepaid_templates as $pt) : $selected = $pt['id'] == $kiw_row['voucher_template'] ? "selected" : "" ?>
                                                                <option value="<?= $pt['id'] ?>" <?= $selected ?>><?= $pt['name'] ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 random provider-input">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                                        <span data-i18n="voucher_span_ambiguous">Prevent Ambiguous Character</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <div class="custom-control custom-switch custom-control-inline col-md-8">
                                                            <input type="checkbox" class="custom-control-input" name="voucher_avoid_ambiguous" id="voucher_avoid_ambiguous" <? if ($kiw_row['voucher_avoid_ambiguous'] == "y") {
                                                                                                                                                                                echo 'checked="yes"';
                                                                                                                                                                            } ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="voucher_avoid_ambiguous"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                                        <span data-i18n="voucher_span_random_length">Random Engine Length Limit</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <input type="text" name=voucher_limit id=voucher_limit value="<? echo $kiw_row['voucher_limit']; ?>" class="form-control col-11" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-3"></div>

                                                    <div class="col-md-9">
                                                        <div class="badge badge-danger badge-md" role="alert">
                                                            <span style="font-size: 11px;"><strong data-i18n="voucher_span_random_length_label">Warning : Too short length voucher username will have conflict and smaller availability.</strong></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="tab-pane" id="topup" aria-labelledby="topup-tab" role="tabpanel">


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                                        <span data-i18n="voucher_span_prefix">Default Prefix</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <input type="text" name="topup_prefix" id="topup_prefix" value="<? echo $kiw_row['topup_prefix']; ?>" class="form-control col-11" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                                        <span data-i18n="general_span_allow_topup_voucher">Allow Topup Voucher</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <div class="custom-control custom-switch custom-control-inline">
                                                            <input type="checkbox" class="custom-control-input" name="allow_topup_voucher" id="allow_topup_voucher" <? if (in_array("voucher", explode(",", $kiw_row['allow_topup_to']))) {
                                                                                                                                                                        echo 'checked="yes"';
                                                                                                                                                                    } ?> value="1" class="toggle" />
                                                            <label class="custom-control-label" for="allow_topup_voucher"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                                        <span data-i18n="general_span_allow_topup_account">Allow Topup Account / User</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <div class="custom-control custom-switch custom-control-inline">
                                                            <input type="checkbox" class="custom-control-input" name="allow_topup_account" id="allow_topup_account" <? if (in_array("account", explode(",", $kiw_row['allow_topup_to']))) {
                                                                                                                                                                        echo 'checked="yes"';
                                                                                                                                                                    } ?> value="1" class="toggle" />
                                                            <label class="custom-control-label" for="allow_topup_account"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                                        <span data-i18n="general_span_carry_forward_topup">Carry Forward Topup</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <div class="custom-control custom-switch custom-control-inline">
                                                            <input type="checkbox" class="custom-control-input" name="carry_forward_topup" id="carry_forward_topup" <? if ($kiw_row['carry_forward_topup'] == "y") {
                                                                                                                                                                        echo 'checked="yes"';
                                                                                                                                                                    } ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="carry_forward_topup"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>


                                        <div class="tab-pane" id="campaign" aria-labelledby="campaign-tab" role="tabpanel">

                                            <div class="tab-pane" id="campaign" aria-labelledby="campaign-tab" role="tabpanel">
                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="col-xs-12 col-sm-12 col-md-3">
                                                            <span data-i18n="campaign_span_waiting_time">Campaign Waiting Time</span>
                                                        </div>
                                                        <div class="col-md-9">
                                                            <input type="text" name='campaign_wait_second' id='campaign_wait_second' value="<? echo $kiw_row['campaign_wait_second']; ?>" class="form-control col-11" />
                                                            <span style="font-size: smaller;" class="flang-c-field_3_note" data-i18n="campaign_span_waiting_time_secs">Seconds</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                                        <span data-i18n="campaign_span_ads">Multiple Ads</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <div class="custom-control custom-switch custom-control-inline col-md-8">
                                                            <input type="checkbox" class="custom-control-input" name="campaign_multi_ads" id="campaign_multi_ads" <? if ($kiw_row['campaign_multi_ads'] == "y") {
                                                                                                                                                                        echo 'checked="yes"';
                                                                                                                                                                    } ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="campaign_multi_ads"></label>
                                                        </div>
                                                        <div>
                                                            <span style="font-size: smaller;" class="flang-c-field_3_note" data-i18n="campaign_span_ads_label">NOTE: If disable multiple ads, only random campaign will be displayed</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                                        <span data-i18n="campaign_span_autoplay">Autoplay Video</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <div class="custom-control custom-switch custom-control-inline col-md-8">
                                                            <input type="checkbox" class="custom-control-input" name="campaign_autoplay" id="campaign_autoplay" <? if ($kiw_row['campaign_autoplay'] == "y") {
                                                                                                                                                                    echo 'checked="yes"';
                                                                                                                                                                } ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="campaign_autoplay"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                                        <span data-i18n="campaign_span_cookie">Super Cookies</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <div class="custom-control custom-switch custom-control-inline col-md-8">
                                                            <input type="checkbox" class="custom-control-input" name="campaign_cookies" id="campaign_cookies" <? if ($kiw_row['campaign_cookies'] == "y") {
                                                                                                                                                                    echo 'checked="yes"';
                                                                                                                                                                } ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="campaign_cookies"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                                        <span data-i18n="campaign_span_verification">Require Verification</span>
                                                    </div>
                                                    <div class="col-md-1">
                                                        <div class="custom-control custom-switch custom-control-inline col-md-8">
                                                            <input type="checkbox" class="custom-control-input" name="campaign_require_verification" id="campaign_require_verification" <? if ($kiw_row['campaign_require_verification'] == "y") {
                                                                                                                                                                                            echo 'checked="yes"';
                                                                                                                                                                                        } ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="campaign_require_verification"></label>
                                                                            
                                                            
                                                        </div>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div class="badge badge-danger badge-md" role="alert">
                                                            <span style="font-size: 11px;"><strong data-i18n="campaign_span_verification_label">If turn ON, all campaign required verification before published</strong></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>


                                        <div class="tab-pane" id="timezone" aria-labelledby="timezone-tab" role="tabpanel">

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                                        <span data-i18n="timezone_span_timezone">Time Zone</span>
                                                    </div>

                                                    <?php $rowl = DateTimeZone::listIdentifiers(); ?>

                                                    <div class="col-md-9">
                                                        <select name="timezone" id="timezone" class="select2 form-control col-11" data-style="btn-default" tabindex="-98">
                                                            <?php
                                                            foreach ($rowl as $timezone) {
                                                                echo "<option value='{$timezone}'" . ($timezone == $kiw_row['timezone'] ? "selected" : "") . ">$timezone</option>\n";
                                                            }

                                                            unset($rowl);
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                                        <span data-i18n="date_format_span_date_format">Date Format</span>
                                                    </div>

                                                    <?php $rowl = DateTimeZone::listIdentifiers(); ?>

                                                    <div class="col-md-9">
                                                        <select name="date_format" id="date_format" class="select2 form-control col-11" data-style="btn-default">
                                                            <option value="d-M-Y" data-i18n="date_format_YYYY-MM-DD" <?= "d-M-Y" == $kiw_row['date_format'] ? "selected" : "" ?> >d-M-Y [eg: 27-Sep-2021]</option>
                                                            <option value="d-m-Y" data-i18n="date_format_DD-MM-YYYY" <?= "d-m-Y" == $kiw_row['date_format'] ? "selected" : "" ?>>d-m-Y [eg: 27-09-2021]</option>
                                                            <option value="d-m-y" data-i18n="date_format_MM-DD-YYYY" <?= "d-m-y" == $kiw_row['date_format'] ? "selected" : "" ?>>d-m-y [eg: 27-09-21]</option>
                                                            <option value="d/m/y" data-i18n="date_format_YYYY/MM/DD" <?= "d/m/y" == $kiw_row['date_format'] ? "selected" : "" ?>>d/m/y [eg: 27/09/21]</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                    
                                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                                    <input type="hidden" name="update" value="true" />

                                </form>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-primary save-button waves-effect waves-light" data-i18n="btn_save">Save </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require_once "includes/include_footer.php"; ?>