<?php

$kiw['module'] = "Configuration -> White Label";
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


?>

<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">White Labelling</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="whitelabel_subtitle">
                                and basic configuration
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
                                <!-- <button type="button" class="btn btn-primary save-button waves-effect waves-light" data-i18n="btn_save">Save</button> -->
                            </div>
                            <div class="card-body">
                                <form id="update-form" class="form-horizontal" method="post">
                                    <div class="tab-content"><br><br><br>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <span data-i18n="sync_product">Product</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="sync_product" id="sync_product" value="<?= SYNC_PRODUCT ?>" class="form-control" />
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <span data-i18n="sync_copyright">Copyright</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="sync_copyright" id="sync_copyright" value="<?= SYNC_COPYRIGHT ?>=" class="form-control" />
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <span data-i18n="sync_logo_big">Logo Big</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="sync_logo_big" id="sync_logo_big" value="<?= SYNC_LOGO_BIG ?>" class="form-control" />
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <span data-i18n="sync_logo_small">Logo Small</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="sync_logo_small" id="sync_logo_small" value="<?= SYNC_LOGO_SMALL ?>" class="form-control" />
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <span data-i18n="sync_icon">Title</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="sync_icon" id="sync_icon" value="<?= SYNC_ICON ?>" class="form-control" />
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <span data-i18n="sync_title">Title</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="sync_title" id="sync_title" value="<?= SYNC_TITLE ?>" class="form-control" />
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <span data-i18n="sync_help_support">Help Support</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="sync_help_support" id="sync_help_support" value="<?= SYNC_HELP_SUPPORT ?>=" class="form-control" />
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <span data-i18n="sync_expired_error_msg">Expired Error Msg</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="sync_expired_error_msg" id="sync_expired_error_msg" value="<?= SYNC_EXPIRED_ERROR_MSG ?>=" class="form-control" />
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <span data-i18n="sync_max_trial_days">Max Trial Days</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="sync_max_trial_days" id="sync_max_trial_days" value="<?= SYNC_MAX_TRIAL_DAYS ?>" class="form-control" />
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <span data-i18n="sync_max_trial_devices">Max Trial Devices</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="sync_max_trial_devices" id="sync_max_trial_devices" value="<?= SYNC_MAX_TRIAL_DEVICES ?>" class="form-control" />
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <span data-i18n="sync_social_url">Social URL</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="sync_social_url" id="sync_social_url" value="<?= SYNC_SOCIAL_URL ?>" class="form-control" />
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <span data-i18n="sync_qr_url">QR URL</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="sync_qr_url" id="sync_qr_url" value="<?= SYNC_QR_URL ?>" class="form-control" />
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <span data-i18n="sync_doc_url">Doc URL</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="sync_doc_url" id="sync_doc_url" value="<?= SYNC_DOC_URL ?>" class="form-control" />
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <span data-i18n="sync_db1_host">DB1 Host</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="sync_db1_host" id="sync_db1_host" value="<?= SYNC_DB1_HOST ?>" class="form-control" />
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <span data-i18n="sync_db1_port">DB1 Port</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="sync_db1_port" id="sync_db1_port" value="<?= SYNC_DB1_PORT ?>" class="form-control" />
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <span data-i18n="sync_db1_user">DB1 User</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="sync_db1_user" id="sync_db1_user" value="<?= SYNC_DB1_USER ?>" class="form-control" />
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <span data-i18n="sync_db1_pass">DB1 Password</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="sync_db1_pass" id="sync_db1_pass" value="<?= $_SESSION['access_level'] = "superuser" ? SYNC_DB1_PASSWORD : '' ?>" class="form-control" disabled/>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <span data-i18n="sync_db1_db">DB1 Database</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="sync_db1_db" id="sync_db1_db" value="<?= SYNC_DB1_DATABASE ?>" class="form-control" />
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <span data-i18n="sync_db2_host">DB2 Host</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="sync_db2_host" id="sync_db2_host" value="<?= SYNC_DB2_HOST ?>" class="form-control" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <span data-i18n="sync_db2_port">DB2 Port</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="sync_db2_port" id="sync_db2_port" value="<?= SYNC_DB2_PORT ?>" class="form-control" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <span data-i18n="sync_db2_user">DB2 User</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="sync_db2_user" id="sync_db2_user" value="<?= SYNC_DB2_USER ?>" class="form-control" />
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <span data-i18n="sync_db2_pass">DB2 Password</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="sync_db2_pass" id="sync_db2_pass" value="<?=  $_SESSION['access_level'] = "superuser" ? SYNC_DB2_PASSWORD : ''  ?>" class="form-control" disabled/>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <span data-i18n="sync_db2_db">DB2 Database</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="sync_db2_db" id="sync_db2_db" value="<?= SYNC_DB2_DATABASE ?>" class="form-control" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <span data-i18n="sync_redis_host">Redis Host</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="sync_redis_host" id="sync_redis_host" value="<?= SYNC_REDIS_HOST ?>" class="form-control" />
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <span data-i18n="sync_redis_port">Redis Port</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="sync_redis_port" id="sync_redis_port" value="<?= SYNC_REDIS_PORT ?>" class="form-control" />
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <span data-i18n="sync_coa_host">CoA Sender Host</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="sync_coa_host" id="sync_coa_host" value="<?= SYNC_COA_HOST ?>" class="form-control" />
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <span data-i18n="sync_coa_port">CoA Sender Port</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="sync_coa_port" id="sync_coa_port" value="<?= SYNC_COA_PORT ?>" class="form-control" />
                                                </div>
                                            </div>
                                        </div>


                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

</div>

<?php require_once "includes/include_footer.php"; ?>
