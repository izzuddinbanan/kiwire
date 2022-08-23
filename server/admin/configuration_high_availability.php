<?php

$kiw['module'] = "Configuration -> High Availability";
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


$kiw_temp = @file_get_contents(dirname(__FILE__, 2) . "/custom/ha_setting.json");

if (!empty($kiw_temp)) $kiw_temp = json_decode($kiw_temp, true);


$kiw_error = @file_get_contents(dirname(__FILE__, 2) . "/custom/ha_error.json");

$kiw_error = (int)$kiw_error;

if (empty($kiw_error)) $kiw_error = 0;


$kiw_status = @file_get_contents(dirname(__FILE__, 2) . "/custom/ha_status.json");

if (!empty($kiw_status)) $kiw_status = json_decode($kiw_status, true);



?>


    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-12 mb-2">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">High Availability</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item active" data-i18n="subtitle">
                                    Configure high availability
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">

            <div class="row">
                <div class="col-sm-12">
                    <div class="card overflow-hidden">
                        <div class="card-content">
                            <div class="card-body">

                                

                                <form id="update-form" class="form form-horizontal" method="post">

                                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                                    
                                    <div class="form-body">

                                        <div class="row mt-5">

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-2">
                                                        <span data-i18n="form_enable">Enable</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <div class="custom-control custom-switch custom-control-inline">
                                                            <input type="checkbox" class="custom-control-input" <?= ($kiw_temp['enabled'] == "y" ? "checked" : "") ?> name="enabled" id="enabled" value="y" class="toggle"/>
                                                            <label class="custom-control-label" for="enabled"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-2">
                                                        <span data-i18n="form_server_role">This Server Role</span> <span class="text-danger">*</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <select name="role" id="role" class="select2 form-control" data-style="btn-default">
                                                            <option value="master" <?= ($kiw_temp['role'] == "master" ? "selected" : "") ?> data-i18n="form_server_role_master">Main</option>
                                                            <option value="backup" <?= ($kiw_temp['role'] == "backup" ? "selected" : "") ?> data-i18n="form_server_role_slave">Backup</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-2">
                                                        <span data-i18n="form_master_IP">Main IP Address</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" name="master_ip_address" id="master_ip_address" value="<?= $kiw_temp['master_ip_address'] ?>" class="form-control"/>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-2">
                                                        <span data-i18n="form_master_key">Main Keys</span></div>
                                                    <div class="col-md-10">
                                                        <input type="text" class="form-control" name="master_key" id="master_key" value="<?= $kiw_temp['master_key'] ?>">
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-2">
                                                        <span data-i18n="form_backup_ip">Backup IP Address</span></div>
                                                    <div class="col-md-10">
                                                        <input type="text" name="backup_ip_address" id="backup_ip_address" value="<?= $kiw_temp['backup_ip_address'] ?>" class="form-control"/>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-2">
                                                        <span data-i18n="form_backup_key">Backup Keys</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" class="form-control" name="backup_key" id="backup_key" value="<?= $kiw_temp['backup_key'] ?>">
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-2">
                                                        <span data-i18n="form_time_interval">Time Interval</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" name="t_interval" id="t_interval" value="<?= $kiw_temp['t_interval'] ?>" class="form-control"/>
                                                        <span style="font-size: smaller; padding: 10px;" class="flang-c-field_3_note" data-i18n="form_time_interval_label">Minutes (Minimum 10)</span>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-2">
                                                        <span data-i18n="form_error_count">Error Count</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" name="e_count" id="e_count" value="<?= $kiw_error ?>" class="form-control" disabled />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-2">
                                                        <span data-i18n="form_last_run">Last Run</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" name="latest" id="latest" value="<?= (empty($kiw_status['latest']) ? "Never" : sync_tolocaltime(date("Y-m-d H:i:s", $kiw_status['latest']), $_SESSION['timezone'])) ?>" class="form-control" disabled />
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-3"></div>
                                                    <div class="col-md-9">
                                                        <a href="#" id="key_gen" class="btn btn-success" data-i18n="form_btn_generate_key">Generate Key</a>
                                                        <a href="#" id="revoke_key" class="btn btn-danger" data-i18n="form_btn_revoke_key">Revoke Key</a>
                                                        <a href="#" id="reset_error" class="btn btn-danger" data-i18n="form_btn_reset_error">Reset Error</a>
                                                        <a href="#" id="reset_server" class="btn btn-danger" style="display: none;" data-i18n="form_btn_reset_server">Reset Server</a>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                    </div>

                                </form>

                            </div>
                            <div class="card-footer"> 
                                <button type="button" class="btn btn-primary save-button waves-effect waves-light" data-i18n="btn_save">Save</button>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

<?php

require_once "includes/include_footer.php";

?>

<script src="/assets/js/jquery-copytoclipboard.js"></script>