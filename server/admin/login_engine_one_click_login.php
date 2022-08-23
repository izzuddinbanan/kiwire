<?php

$kiw['module'] = "Login Engine -> Sign up -> One Click";
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


$kiw_row = $kiw_db->query_first("SELECT * FROM kiwire_one_click_login WHERE tenant_id = '{$tenant_id}' LIMIT 1");

if (empty($kiw_row)) $kiw_db->query("INSERT INTO kiwire_one_click_login(tenant_id) VALUE('$tenant_id')");


$kiw_row['data'] = explode(",", $kiw_row['data']);


?>

<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="login_engine_one_click_login_title">One Click Login</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="login_engine_one_click_login_subtitle">
                                Page for one click login
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

                                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">


                                    <br><br>
                                    <div class="col-12">
                                        <div class="form-group row">
                                            <div class="col-md-2">
                                                <span data-i18n="login_engine_one_click_login_enable">Enable</span>
                                            </div>
                                            <div class="col-md-10">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" class="custom-control-input" name="enabled" id="enabled" <?= ($kiw_row['enabled'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle"/>
                                                    <label class="custom-control-label" for="enabled"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-group row">
                                            <div class="col-md-2">
                                                <span data-i18n="login_engine_one_click_login_using">Login Using</span>
                                            </div>
                                            <div class="col-md-10">
                                                <select name="login_using_id" id="login_using_id" class="select2 form-control change-provider" data-style="btn-default">
                                                    <option value="MAC" <?= ($kiw_row['login_using_id'] == "MAC" ? "selected" : "") ?>><span data-i18n="login_engine_one_click_login_using_mac">MAC</span></option>
                                                    <option value="username" <?= ($kiw_row['login_using_id'] == "username" ? "selected" : "") ?>><span data-i18n="login_engine_one_click_login_using_username">Username</span></option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 username provider-input" style="display: <?= ($kiw_row['login_using_id'] == "username" ? "block" : "none") ?>;">
                                        <div class="form-group row">
                                            <div class="col-md-2">
                                                <span data-i18n="login_engine_one_click_login_subtitle" data-i18n="login_engine_one_click_login_uname">Username</span>
                                            </div>
                                            <div class="col-md-10">
                                                <input type="text" name="username" id="username" value="<?= $kiw_row['username']; ?>" class="form-control"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 MAC provider-input" style="display: <?= ($kiw_row['login_using_id'] == "MAC" ? "block" : "none") ?>;">
                                        <div class="form-group row">
                                            <div class="col-md-2">
                                                <span data-i18n="login_engine_one_click_login_link">Link With Profile</span>
                                            </div>
                                            <div class="col-md-10">
                                                <select name="profile" id="profile" class="select2 form-control" data-style="btn-default">
                                                    <option value="none" data-i18n="login_engine_one_click_login_none">None</option>
                                                    <?php

                                                    $rows = "SELECT name FROM kiwire_profiles WHERE tenant_id = '{$tenant_id}' GROUP BY name";
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
                                                <span data-i18n="login_engine_one_click_login_validity">Validity</span>
                                            </div>
                                            <div class="col-md-10">
                                                <input type="text" name="validity" id="validity" value="<?= $kiw_row['validity']; ?>" class="form-control"/>
                                                <div style="font-size: smaller; padding: 10px;" data-i18n="login_engine_one_click_login_days">days</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-group row">
                                            <div class="col-md-2">
                                                <span data-i18n="login_engine_one_click_login_zone_restriction">Zone Restriction</span>
                                            </div>
                                            <div class="col-md-10">
                                                <select name="allowed_zone" id="allowed_zone" class="select2 form-control" data-style="btn-default">
                                                    <option value="none" data-i18n="login_engine_one_click_login_none2">None</option>

                                                    <?php

                                                    $rows = "SELECT * FROM kiwire_allowed_zone WHERE tenant_id = '{$tenant_id}' GROUP BY name ORDER BY name";
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
                                                <span data-i18n="login_engine_one_click_login_add_field">Additional Fields</span>
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

                                </form>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-primary save-button waves-effect waves-light" data-i18n="login_engine_one_click_login_save">Save</button>
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

?>
