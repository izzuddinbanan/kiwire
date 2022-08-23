<?php

$kiw['module'] = "Login Engine -> Sign up -> Sponsor";
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


$kiw_row = $kiw_db->query_first("SELECT * FROM kiwire_signup_visitor WHERE tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");

if (empty($kiw_row)) {

    $kiw_db->query("INSERT INTO kiwire_signup_visitor(tenant_id, enabled) VALUE('{$_SESSION['tenant_id']}', 'n')");

}


$kiw_row['data'] = explode(",", $kiw_row['data']);

?>


<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="login_engine_sponsor_signup_title">Sponsor Sign Up</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="login_engine_sponsor_signup_subtitle">
                                Sign up page for visitor
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

                                <form class="update-form">

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-2">
                                                    <span data-i18n="login_engine_sponsor_signup_enable">Enable</span>
                                                </div>
                                                <div class="col-md-10">
                                                    <div class="custom-control custom-switch custom-control-inline">
                                                        <input type="checkbox" class="custom-control-input" name="enabled" id="enabled" <?= ($kiw_row['enabled'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                        <label class="custom-control-label" for="enabled"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-2">
                                                    <span data-i18n="login_engine_sponsor_signup_temp_tosponsor">Template [ to Sponsor ]</span>
                                                </div>
                                                <div class="col-md-10">

                                                    <?php $row_temp = $kiw_db->fetch_array("SELECT id,name FROM kiwire_html_template WHERE type = 'email' AND tenant_id = '{$_SESSION['tenant_id']}'"); ?>

                                                    <select name="verification_content" id="verification_content" class="select2 form-control">

                                                        <option value="none" data-i18n="login_engine_sponsor_signup_none_temps">None</option>

                                                        <?php foreach ($row_temp as $each_temp) : ?>

                                                            <option value="<?= $each_temp['name'] ?>" <?= $kiw_row['verification_content'] == $each_temp['name'] ? "selected" : "" ?>>
                                                                <?= $each_temp['name'] ?>
                                                            </option>

                                                        <?php endforeach; ?>

                                                    </select>

                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-2">
                                                    <span data-i18n="login_engine_sponsor_signup_temp_touser">Template [ to User ]</span>
                                                </div>
                                                <div class="col-md-10">

                                                    <?php $row_temp = $kiw_db->fetch_array("SELECT id,name FROM kiwire_html_template WHERE type = 'sms' AND tenant_id = '{$_SESSION['tenant_id']}'"); ?>

                                                    <select name="confirmation_content" id="confirmation_content" class="select2 form-control">

                                                        <option value="none" data-i18n="login_engine_sponsor_signup_none_tempu">None</option>

                                                        <?php foreach ($row_temp as $each_temp) : ?>

                                                            <option value="<?= $each_temp['name'] ?>" <?= $kiw_row['confirmation_content'] == $each_temp['name'] ? "selected" : "" ?>>

                                                                <?= $each_temp['name'] ?>

                                                            </option>

                                                        <?php endforeach; ?>

                                                    </select>

                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-2">
                                                    <span data-i18n="login_engine_sponsor_signup_confirm_pages">Confirmed Page</span>
                                                </div>
                                                <div class="col-md-10">

                                                    <?php $row_temp = $kiw_db->fetch_array("SELECT id,unique_id,page_name FROM kiwire_login_pages WHERE tenant_id = '{$_SESSION['tenant_id']}'"); ?>

                                                    <select name="confirmed_page" id="confirmed_page" class="select2 form-control">

                                                        <option value="none" data-i18n="login_engine_sponsor_signup_none_confirm">None</option>

                                                        <?php foreach ($row_temp as $each_temp) : ?>

                                                            <option value="<?= $each_temp['unique_id'] ?>" <?= $kiw_row['confirmed_page'] == $each_temp['unique_id'] ? "selected" : "" ?>>

                                                                <?= $each_temp['page_name'] ?>

                                                            </option>

                                                        <?php endforeach; ?>

                                                    </select>

                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-2">
                                                    <span data-i18n="login_engine_sponsor_signup_inform_acc">Inform Account Verified via</span>
                                                </div>
                                                <div class="col-md-10">

                                                    <select name="send_notification" id="send_notification" class="select2 form-control change-provider" data-for="send_notification">
                                                        <option value="" data-i18n="login_engine_sponsor_signup_none_inform">None</option>
                                                        <option value="sms" selected><span data-i18n="login_engine_sponsor_signup_sms"> SMS</span></option>
                                                    </select>

                                                    <div class="sms provider-input" style="font-size: smaller; padding: 10px;" data-i18n="login_engine_sponsor_signup_inform_desc">Please make sure you have updated the SMS gateway</div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-2">
                                                    <span data-i18n="login_engine_sponsor_signup_allowed_domain">Allowed Sponsor E-mail Domain</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-10"><input type="text" name="domain" id="domain" value="<?= $kiw_row['domain']; ?>" class="form-control required" />
                                                    <div style="font-size: smaller; padding: 10px;" data-i18n="login_engine_sponsor_signup_allowed_desc">Eg: domain.com</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-2">
                                                    <span data-i18n="login_engine_sponsor_signup_link">Link With Profile</span>
                                                </div>
                                                <div class="col-md-10">
                                                    <select name="profile" id="profile" class="select2 form-control">
                                                        <option value="none" data-i18n="login_engine_sponsor_signup_none_link">None</option>
                                                        <?php

                                                        $rows = "SELECT name FROM kiwire_profiles WHERE tenant_id = '{$tenant_id}'";
                                                        $rows = $kiw_db->fetch_array($rows);

                                                        foreach ($rows as $record) {

                                                            $selected = "";

                                                            if ($record['name'] == $kiw_row['profile']) $selected = 'selected="selected"';

                                                            echo "<option value ='{$record['name']}' $selected>{$record['name']}</option> \n";

                                                        }

                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-2">
                                                    <span data-i18n="login_engine_sponsor_signup_validity">Validity</span>
                                                </div>
                                                <div class="col-md-10"><input type="text" name="validity" id="validity" value="<?= $kiw_row['validity']; ?>" class="form-control" />
                                                    <div style="font-size: smaller; padding: 10px;" data-i18n="login_engine_sponsor_signup_days">Days</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>



                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-2">
                                                    <span data-i18n="login_engine_sponsor_signup_prefix">Prefix</span>
                                                </div>
                                                <div class="col-md-10"><input type="text" name="prefix" id="prefix" value="<?= $kiw_row['prefix']; ?>" class="form-control" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-2">
                                                    <span data-i18n="login_engine_sponsor_signup_zone_restriction">Zone Restriction</span>
                                                </div>
                                                <div class="col-md-10">
                                                    <select name="allowed_zone" id="allowed_zone" class="select2 form-control" data-style="btn-default" tabindex="-98">
                                                        <option value="none" data-i18n="login_engine_sponsor_signup_none_zone">None</option>
                                                        <?php

                                                        $rows = "SELECT * FROM kiwire_allowed_zone WHERE tenant_id = '{$tenant_id}'";
                                                        $rows = $kiw_db->fetch_array($rows);

                                                        foreach ($rows as $record) {

                                                            $selected = "";

                                                            if ($record['name'] == $kiw_row['allowed_zone']) $selected = 'selected="selected"';

                                                            echo "<option value ='{$record['name']}' {$selected}> {$record['name']}</option> \n";

                                                        }

                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-2">
                                                    <span data-i18n="login_engine_sponsor_signup_add_field">Additional Fields</span>
                                                </div>
                                                <div class="col-10">

                                                    <select name="extra_data[]" id="extra_data" class="select2 form-control" multiple="multiple">

                                                        <?php foreach ($kiw_fields as $kiw_field) { ?>

                                                            <div data-field-info="<?= $kiw_field['field'] ?>">

                                                                <?php if ($kiw_field['display'] != "[empty]") { ?>

                                                                    <option value="<?= $kiw_field['variable'] ?>" <?= (in_array($kiw_field['variable'], $kiw_row['data']) ? "selected" : "") ?>> <?= $kiw_field['display'] ?></option>

                                                                <?php } ?>

                                                            </div>

                                                        <?php

                                                            $kiw_count++;

                                                        }

                                                        ?>
                                                    </select>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                                </form>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-primary save-button waves-effect waves-light" data-i18n="login_engine_sponsor_signup_save">Save</button>
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


