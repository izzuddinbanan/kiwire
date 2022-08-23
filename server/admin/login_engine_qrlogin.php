<?php

$kiw['module'] = "Login Engine -> QR Login";
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

$kiw_row = $kiw_db->query_first("SELECT * FROM kiwire_qr WHERE tenant_id = '{$tenant_id}' LIMIT 1");

if (empty($kiw_row)) $kiw_db->query("INSERT INTO kiwire_qr(tenant_id) VALUE('$tenant_id')");


?>


<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="login_engine_qrlogin_title">QR Login</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="login_engine_qrlogin_subtitle">
                                Manage QR login setting
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
                                <div class="tab-content">

                                    <form class="update-form">

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-2">
                                                    <span data-i18n="login_engine_qrlogin_enable">Enable</span>
                                                </div>
                                                <div class="col-md-10">
                                                    <div class="custom-control custom-switch custom-control-inline">
                                                        <input type="checkbox" class="custom-control-input" name="enabled" id="enabled" <?= ($kiw_row['enabled'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                        <label class="custom-control-label" for="enabled"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-2">
                                                    <span data-i18n="login_engine_qrlogin_link">Link With Profile</span>
                                                </div>
                                                <div class="col-md-10">
                                                    <select name="profile" id="profile" class="select2 form-control" data-style="btn-default" tabindex="-98">
                                                        <option value="none" data-i18n="login_engine_qrlogin_none_link">None</option>
                                                        <?
                                                        $sql = "select * from kiwire_profiles where tenant_id = '{$tenant_id}' group by name";
                                                        $rows = $kiw_db->fetch_array($sql);
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
                                                    <span data-i18n="login_engine_qrlogin_validity">Validity</span>
                                                </div>
                                                <div class="col-md-10">
                                                    <input type="text" name="validity" id="validity" value="<?= $kiw_row['validity']; ?>" class="form-control" />
                                                    <div style="font-size: smaller; padding: 10px;" data-i18n="login_engine_qrlogin_days">Days</div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-2">
                                                    <span data-i18n="login_engine_qrlogin_zone_restriction">Zone Restriction</span>
                                                </div>
                                                <div class="col-md-10">
                                                    <select name="allowed_zone" id="allowed_zone" class="select2 form-control" data-style="btn-default" tabindex="-98">
                                                        <option value="none" data-i18n="login_engine_qrlogin_none_zone">None</option>
                                                        <?
                                                        $sql = "select * from kiwire_allowed_zone where tenant_id = '{$tenant_id}' group by name order by name";
                                                        $rows = $kiw_db->fetch_array($sql);
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

                                        <input type="hidden" name="update" value="true" />
                                        <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">

                                    </form>

                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-primary save-button waves-effect waves-light" data-i18n="login_engine_qrlogin_save">Save</button>
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
