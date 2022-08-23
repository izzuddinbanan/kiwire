<?php

$kiw['module'] = "CPanel -> Setting";
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


$kiw_row = $kiw_db->query_first("SELECT * FROM  kiwire_cpanel_template WHERE tenant_id = '{$tenant_id}' LIMIT 1");


if (empty($kiw_row)) {

    $kiw_db->query("INSERT INTO kiwire_cpanel_template(tenant_id) VALUE('$tenant_id')");
}

?>


<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="policy_user_panel_title">User Panel</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="policy_user_panel_subtitle">
                                User self-manage account site
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

                                    <br><br><br><br>

                                    <div class="tab-content">

                                        <div class="tab-pane active" id="main" aria-labelledby="main-tab" role="tabpanel">   
                                            
                                        
                                            <!-- <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="text-right col-md-3">
                                                        <span data-i18n="policy_user_panel_login_type">Login Type</span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="login_type" id="login_type" value="<?= $kiw_row['login_type'] ?>" class="form-control" />
                                                    </div>
                                                </div>
                                            </div> -->


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-3">
                                                        <span data-i18n="policy_user_panel_username">Label Username</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <input type="text" name="username" id="username" value="<?= $kiw_row['label_username'] ?>" class="form-control" />
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-3">
                                                        <span data-i18n="policy_user_panel_password">Label Password</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <input type="text" name="password" id="password" value="<?= $kiw_row['label_password'] ?>" class="form-control" />
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-3">
                                                        <span data-i18n="policy_user_panel_tenant">Label Tenant</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <input type="text" name="tenant" id="tenant" value="<?= $kiw_row['label_tenant'] ?>" class="form-control" />
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-3">
                                                        <span data-i18n="policy_user_panel_welcome">Label Welcome</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <input type="text" name="welcome" id="welcome" value="<?= $kiw_row['label_welcome'] ?>" class="form-control" />
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-3">
                                                        <span data-i18n="policy_user_panel_tittle">Label Title</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <input type="text" name="title" id="title" value="<?= $kiw_row['label_title'] ?>" class="form-control" />
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-3">
                                                        <span data-i18n="policy_user_panel_logout">Label Logout</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <input type="text" name="logout" id="logout" value="<?= $kiw_row['label_logout'] ?>" class="form-control" />
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-3">
                                                        <span data-i18n="policy_user_panel_wrong_credential">Label Wrong Credential</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <input type="text" name="wrong_credential" id="wrong_credential" value="<?= $kiw_row['label_wrong_credential'] ?>" class="form-control" />
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-3">
                                                        <span data-i18n="policy_user_panel_history_month">Number of Month for History</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <input type="text" name="history_month" id="history_month" value="<?= $kiw_row['history_month'] ?>" class="form-control" />
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">

                                                    <div class="col-md-3">
                                                        <span data-i18n="policy_user_panel_enable">Page Enable</span>
                                                    </div>

                                                    <div class="col-md-9">
                                                        <div class="custom-control custom-switch custom-control-inline">
                                                            <input type="checkbox" class="custom-control-input" name="enabled" id="enabled" <?= ($kiw_row['enabled'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="enabled"></label>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">

                                                    <div class="col-md-3">
                                                        <span data-i18n="policy_user_panel_dashboard">Dashboard</span>
                                                    </div>

                                                    <div class="col-md-9">
                                                        <div class="custom-control custom-switch custom-control-inline">
                                                            <input type="checkbox" class="custom-control-input" name="dashboard" id="dashboard" <?= ($kiw_row['dashboard'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="dashboard"></label>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">

                                                    <div class="col-md-3">
                                                        <span data-i18n="policy_user_panel_information">Information</span>
                                                    </div>

                                                    <div class="col-md-9">
                                                        <div class="custom-control custom-switch custom-control-inline">
                                                            <input type="checkbox" class="custom-control-input" name="information" id="information" <?= ($kiw_row['information'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="information"></label>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">

                                                    <div class="col-md-3">
                                                        <span data-i18n="policy_user_panel_profile">Profile</span> <br>
                                                    </div>

                                                    <div class="col-md-9">
                                                        <div class="custom-control custom-switch custom-control-inline">
                                                            <input type="checkbox" class="custom-control-input" name="profile" id="profile" <?= ($kiw_row['profile'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="profile"></label>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>


                                            <!-- <div class="col-12">
                                                <div class="form-group row">

                                                    <div class="text-right col-md-3">
                                                        <span data-i18n="policy_user_panel_usage">Usage / Statistics</span>
                                                    </div>

                                                    <div class="col-md-8">
                                                        <div class="custom-control custom-switch custom-control-inline">
                                                            <input type="checkbox" class="custom-control-input" name="statistics" id="statistics" <?= ($kiw_row['statistics'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="statistics"></label>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div> -->


                                            <div class="col-12">
                                                <div class="form-group row">

                                                    <div class="col-md-3">
                                                        <span data-i18n="policy_user_panel_history">History</span> <br>
                                                    </div>

                                                    <div class="col-md-9">
                                                        <div class="custom-control custom-switch custom-control-inline">
                                                            <input type="checkbox" class="custom-control-input" name="history" id="history" <?= ($kiw_row['history'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="history"></label>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">

                                                    <div class="col-md-3">
                                                        <span data-i18n="policy_user_panel_recharge">Topup</span> <br>
                                                    </div>

                                                    <div class="col-md-9">
                                                        <div class="custom-control custom-switch custom-control-inline">
                                                            <input type="checkbox" class="custom-control-input" name="recharge" id="recharge" <?= ($kiw_row['recharge'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="recharge"></label>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">

                                                    <div class="col-md-3">
                                                        <span data-i18n="policy_user_panel_auto_login">Auto Login</span> <br>
                                                    </div>

                                                    <div class="col-md-9">
                                                        <div class="custom-control custom-switch custom-control-inline">
                                                            <input type="checkbox" class="custom-control-input" name="auto_login" id="auto_login" <?= ($kiw_row['login'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="auto_login"></label>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">

                                                    <div class="col-md-3">
                                                        <span data-i18n="policy_user_panel_generate_voucher">Generate Voucher</span> <br>
                                                    </div>

                                                    <div class="col-md-9">
                                                        <div class="custom-control custom-switch custom-control-inline">
                                                            <input type="checkbox" class="custom-control-input" name="generate_voucher" id="generate_voucher" <?= ($kiw_row['voucher'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="generate_voucher"></label>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>


                                            <!-- <div class="col-12">
                                                <div class="form-group row">

                                                    <div class="text-right col-md-3">
                                                        <span data-i18n="policy_user_panel_register_device">Register Device</span>
                                                    </div>

                                                    <div class="col-md-8">
                                                        <div class="custom-control custom-switch custom-control-inline">
                                                            <input type="checkbox" class="custom-control-input" name="register" id="register" <?= ($kiw_row['register'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="register"></label>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div> -->


                                            <div class="col-12">
                                                <div class="form-group row">

                                                    <div class="col-md-3">
                                                        <span data-i18n="policy_user_panel_allow_inactive">Allow Inactive</span>
                                                    </div>

                                                    <div class="col-md-9">
                                                        <div class="custom-control custom-switch custom-control-inline">
                                                            <input type="checkbox" class="custom-control-input" name="allow_inactive" id="allow_inactive" <?= ($kiw_row['allow_inactive'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="allow_inactive"></label>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                   
                                </form>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-primary save-button waves-effect waves-light" data-i18n="policy_user_panel_save">Save</button>
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