<?php

$kiw['module'] = "Configuration -> Global";
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


$kiw_config = @file_get_contents(dirname(__FILE__, 2) . "/custom/system_setting.json");

$kiw_config = json_decode($kiw_config, true);


$kiw_smtp = @file_get_contents(dirname(__FILE__, 2) . "/custom/system_smtp.json");

$kiw_smtp = json_decode($kiw_smtp, true);


if (empty($kiw_config['timezone'])) $kiw_config['timezone'] = "Asia/Kuala_Lumpur";


$kiw_tzs = DateTimeZone::listIdentifiers();


?>

<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">System Settings</h2>
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
                                            <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" aria-controls="general" role="tab" aria-selected="true" data-i18n="form_general">GENERAL</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="cron-tab" data-toggle="tab" href="#cron" aria-controls="cron" role="tab" aria-selected="false" data-i18n="form_cron">CRON TIMING</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="smtp-tab" data-toggle="tab" href="#smtp" aria-controls="smtp" role="tab" aria-selected="false" data-i18n="form_smtp">SMTP</a>
                                        </li>
                                    </ul>

                                    <br>

                                    <div class="tab-content">
                                        <div class="tab-pane active" id="general" aria-labelledby="general-tab" role="tabpanel">

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <span data-i18n="general_free_profile">Accounting Interim Interval for Free Profile</span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="freeprofile_interim" id="freeprofile_interim" value="<?= $kiw_config['freeprofile_interim']; ?>" class="form-control col-11" />
                                                        <label for="freeprofile_interim" style="padding:10px;" data-i18n="general_free_profile_secs">Seconds</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <span data-i18n="general_non_free_profile">Accounting Interim Interval for Non-free Profile</span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="paidprofile_interim" id="paidprofile_interim" value="<?= $kiw_config['paidprofile_interim']; ?>" class="form-control col-11" />
                                                        <label for="paidprofile_interim" style="padding:10px;" data-i18n="general_non_free_profile_secs">Seconds</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <span data-i18n="general_backup">How Long to Keep Log Files / Backup</span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="keep_log_data" id="keep_log_data" value="<?= $kiw_config['keep_log_data']; ?>" class="form-control col-11" />
                                                        <label for="keep_log_data" style="padding:10px;" data-i18n="general_backup_days">Days</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <span data-i18n="general_url">URL for Captive Network Trigger</span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="system_url" id="system_url" placeholder="Example: http://google.com" value="<?= $kiw_config['system_url']; ?>" class="form-control col-11" />
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <span data-i18n="general_tenant_via_ip">Use IP Address as Tenant</span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <select name="tenant_via_ip" id="tenant_via_ip" class="form-control col-11">
                                                            <option value="no" <?= $kiw_config['tenant_via_ip'] == "no" ? "selected" : "" ?>>No</option>
                                                            <option value="yes" <?= $kiw_config['tenant_via_ip'] == "yes" ? "selected" : "" ?>>Yes</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <span data-i18n="general_timezone">Default Time Zone</span>
                                                    </div>
                                                    <div class="col-md-8">

                                                        <select name="timezone" id="timezone" class="select2 form-control col-11">

                                                            <?php foreach ($kiw_tzs as $kiw_tz){ ?>

                                                                <option value="<?= $kiw_tz ?>" <?= ($kiw_tz == $kiw_config['timezone']) ? "selected" : "" ?>><?= $kiw_tz ?></option>

                                                            <?php } ?>

                                                        </select>

                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <span data-i18n="general_number_service_worker">Number of <?= sync_brand_decrypt(SYNC_PRODUCT) ?> Service Worker</span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="service_worker" id="service_worker" placeholder="" value="<?= $kiw_config['service_worker']; ?>" class="form-control col-11" />
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <span data-i18n="general_number_integration_worker">Number of <?= sync_brand_decrypt(SYNC_PRODUCT) ?> Integration Worker</span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="integration_worker" id="integration_worker" placeholder="" value="<?= $kiw_config['integration_worker']; ?>" class="form-control col-11" />
                                                    </div>
                                                </div>
                                            </div>


                                        </div>


                                        <div class="tab-pane" id="cron" aria-labelledby="cron-tab" role="tabpanel">

                                            <div class="alert alert-warning pt-2 alert-validation-msg" role="alert">
                                                <ol>
                                                    <li class="mb-1" data-i18n="cron_warning_1">Please don't change anything here unless necessary.</li>
                                                    <li class="mb-1" data-i18n="cron_warning_2">Please be noted that all date and time in this section are in UTC timezone.</li>
                                                    <li class="mb-1">
                                                        <p data-i18n="cron_warning_3_1" style="display: inline-block;">Value format is </p>

                                                        <span style="font-weight: bold; display: inline-block;" data-i18n="cron_warning_3_2">HOUR:MINUTE.</span>

                                                        <p style="display: inline-block;" data-i18n="cron_warning_3_3"> Example, 00:00 to execute check at 12:00AM UTC time.</p>
                                                    </li>
                                                </ol>
                                            </div>

                                            <br><br>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <span data-i18n="cron_auto_update">Update Check</span> <span class="text-danger">*</span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="update_check" id="update_check" value="<?= $kiw_config['update_check'] ?>" class="form-control col-11" required />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <span data-i18n="cron_lastvisit">Campaign Check for Last Visit</span> <span class="text-danger">*</span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="campaign_check" id="campaign_check" value="<?= $kiw_config['campaign_check'] ?>" class="form-control col-11" required />
                                                    </div>
                                                </div>
                                            </div>
                                            

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <span data-i18n="cron_backup_db">Run Backup Database</span> <span class="text-danger">*</span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="backup_db" id="backup_db" value="<?= $kiw_config['backup_db'] ?>" class="form-control col-11" required />
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <span data-i18n="cron_device_monitor">Device Monitoring Check</span> <span class="text-danger">*</span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="device_monitor" id="device_monitor" value="<?= $kiw_config['device_monitor'] ?>" class="form-control col-11" required />
                                                        <label for="device_monitor" style="padding:10px;" data-i18n="cron_device_monitor_label">In minutes [ example 1, 5 or 10 ]</label>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <span data-i18n="cron_notification_interval">Notification Interval</span> <span class="text-danger">*</span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="notification_interval" id="notification_interval" value="<?= $kiw_config['notification_interval'] ?>" class="form-control col-11" required />
                                                        <label for="notification_interval" style="padding:10px;" data-i18n="cron_notification_interval_label">In minutes [ example 1, 5 or 10 ]</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <span data-i18n="cron_stats_admin">Generate Statistics Report to Administrator</span> <span class="text-danger">*</span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="statistics_admin" id="statistics_admin" value="<?= $kiw_config['statistics_admin'] ?>" class="form-control col-11" required />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <span data-i18n="cron_stats_user">Generate Statistics Report to User</span> <span class="text-danger">*</span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="statistics_user" id="statistics_user" value="<?= $kiw_config['statistics_user'] ?>" class="form-control col-11" required/>
                                                    </div>
                                                </div>
                                            </div>



                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <span data-i18n="cron_reset_daily">Profile Reset - Daily</span> <span class="text-danger">*</span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="reset_daily" id="reset_daily" value="<?= $kiw_config['reset_daily'] ?>" class="form-control col-11" required/>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <span data-i18n="cron_reset_weekly">Profile Reset - Weekly</span> <span class="text-danger">*</span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="reset_weekly" id="reset_weekly" value="<?= $kiw_config['reset_weekly'] ?>" class="form-control col-11" required/>
                                                        <label for="reset_weekly" style="padding:10px;" data-i18n="cron_reset_weekly_label">On Weekend</label>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <span data-i18n="cron_reset_monthly">Profile Reset - Monthly</span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="reset_monthly" id="reset_monthly" value="<?= $kiw_config['reset_monthly'] ?>" class="form-control col-11" required/>
                                                        <label for="reset_monthly" style="padding:10px;" data-i18n="cron_reset_monthly_label">On Month End</label>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <span data-i18n="cron_reset_yearly">Profile Reset - Yearly</span> <span class="text-danger">*</span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="reset_yearly" id="reset_yearly" value="<?= $kiw_config['reset_yearly'] ?>" class="form-control col-11" required/>
                                                        <label for="reset_yearly" style="padding:10px;" data-i18n="cron_reset_yearly_label">On 31 Dec</label>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="tab-pane" id="smtp" aria-labelledby="smtp-tab" role="tabpanel">


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <span data-i18n="smtp_host">SMTP Host</span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="host" id="host" value="<?= $kiw_smtp['host'] ?>" class="form-control col-11" placeholder="mail.domain.com" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <span data-i18n="smtp_port">SMTP Port</span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="port" id="port" value="<?= $kiw_smtp['port'] ?>" class="form-control col-11" placeholder="eg: 25" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <span data-i18n="smtp_authentication">Authentication</span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <select name="auth" id="auth" class="form-control col-11">
                                                            <option value="none" <?= ($kiw_smtp['auth'] == "none" ? "selected" : "") ?>>None</option>
                                                            <option value="tls" <?= ($kiw_smtp['auth'] == "tls" ? "selected" : "") ?>>Yes: TLS</option>
                                                            <option value="ssl" <?= ($kiw_smtp['auth'] == "ssl" ? "selected" : "") ?>>Yes: SSL</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <span data-i18n="smtp_username">Username</span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="user" id="user" value="<?= $kiw_smtp['user'] ?>" class="form-control col-11" placeholder="Ussername" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <span data-i18n="smtp_password">Password</span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="password" name="password" id="password" value="<?= $kiw_smtp['password'] ?>" class="form-control col-11" placeholder="Uppercase/lowercase/numeric/special character" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <span data-i18n="smtp_from_email">From Email Address</span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="from_email" id="from_email" value="<?= $kiw_smtp['from_email'] ?>" class="form-control col-11" placeholder="email@address.com" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <span data-i18n="smtp_from_name">From Name</span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="from_name" id="from_name" value="<?= $kiw_smtp['from_name'] ?>" class="form-control col-11" placeholder="eg: admin/etc.." />
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <span data-i18n="smtp_notification">Send Notification To</span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="notification" id="notification" value="<?= $kiw_smtp['notification'] ?>" class="form-control col-11" placeholder="eg: email1@address.com,email2@address.com,etc.." />
                                                        <label for="notification" style="padding:10px;" data-i18n="smtp_host_label">Split using semi-colon</label>
                                                    </div>
                                                </div>
                                            </div>


                                        </div>

                                    </div>

                                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                                    
                                </form>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-primary save-button waves-effect waves-light" data-i18n="btn_save">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require_once "includes/include_footer.php"; ?>