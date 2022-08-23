<?php

$kiw['module'] = "Policy -> General";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;
$carry_forward = 0;

require_once 'includes/include_config.php';
require_once 'includes/include_session.php';
require_once 'includes/include_general.php';
require_once 'includes/include_header.php';
require_once 'includes/include_nav.php';
require_once 'includes/include_access.php';
require_once "includes/include_connection.php";

$kiw_db = Database::obtain();

$kiw_row = $kiw_db->query_first("SELECT * FROM  kiwire_policies WHERE tenant_id = '{$tenant_id}' LIMIT 1");

if (empty($kiw_row)) $kiw_db->query("INSERT INTO kiwire_policies(tenant_id) VALUE('$tenant_id')");


$kiw_pages = $kiw_db->fetch_array("SELECT page_name,unique_id FROM kiwire_login_pages WHERE tenant_id = '{$_SESSION['tenant_id']}'");

//check if license allow carry forward topup data
$kiw_license = $kiw_cache->get("LICENSE_CHECKED:{$tenant_id}"); //get license on cache

if (!in_array($kiw_license, array("yes", "invalid"))) {

    $kiw_license = @file_get_contents(dirname(__FILE__, 2) . "/custom/{$tenant_id}/tenant.license"); //get tenant license
    $kiw_license = sync_license_decode($kiw_license);

    if (empty($kiw_license)){

        $kiw_license = @file_get_contents(dirname(__FILE__, 2) . "/custom/cloud.license"); //get if multi-tenant
        $kiw_license = sync_license_decode($kiw_license);

        if(!empty($kiw_license)){

            if(isset($kiw_license['carry_forward']) && $kiw_license['carry_forward'] == true){

                $carry_forward = 1;
    
            }

        }

    }
    else{

        if(isset($kiw_license['carry_forward']) && $kiw_license['carry_forward'] == true){

            $carry_forward = 1;

        }

    }

}


?>

<style>
    @media only screen and (min-width: 567px){
        .margin-pc{
            margin-left: 300px !important;
            margin-right: 300px !important;
        }
    }
</style>
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="policy_config_title">Configuration</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="policy_config_subtitle">
                                Overall policy
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
                                    <ul class="nav nav-tabs" role="tablist">

                                        <li class="nav-item">
                                            <a class="nav-link active" id="main-tab" data-toggle="tab" href="#main" aria-controls="main" role="tab" aria-selected="true" data-i18n="policy_config_main">MAIN</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="macsecurity-tab" data-toggle="tab" href="#macsecurity" aria-controls="macsecurity" role="tab" aria-selected="false" data-i18n="policy_config_mac_security">MAC SECURITY</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="autologin-tab" data-toggle="tab" href="#autologin" aria-controls="autologin" role="tab" aria-selected="true" data-i18n="policy_config_autologin">AUTOLOGIN</a>
                                        </li>

                                    </ul>

                                    <br><br>

                                    <div class="tab-content">

                                        <div class="tab-pane active" id="main" aria-labelledby="main-tab" role="tabpanel">


                                            <div class="row margin-pc">

                                                <div class="col-10 mb-2">
                                                    <span data-i18n="policy_config_auto_disconnect">Auto disconnect user connected session when same user relogin </span>
                                                </div>
                                                <div class="col-2  mb-2">

                                                    <div class="custom-control custom-switch custom-control-inline">
                                                        <input type="checkbox" class="custom-control-input" name="kick_on_simultaneous" id="kick_on_simultaneous" <?= ($kiw_row['kick_on_simultaneous'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                        <label class="custom-control-label" for="kick_on_simultaneous"></label>
                                                    </div>
                                                </div>



                                                <div class="col-10 mb-2 ">
                                                    <span data-i18n="policy_config_suspend_users">Suspend users account when credit has been exhausted </span>
                                                </div>
                                                <div class="col-2 mb-2">
                                                    <div class="custom-control custom-switch custom-control-inline">
                                                        <input type="checkbox" class="custom-control-input" name="suspend_exhausted_account" id="suspend_exhausted_account" <?= ($kiw_row['suspend_exhausted_account'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                        <label class="custom-control-label" for="suspend_exhausted_account"></label>
                                                    </div>
                                                </div>


                                                <div class="col-10 mb-2">
                                                    <span data-i18n="policy_config_remember_user">Remember user credential for the next login</span> <br>
                                                    <div class="badge badge-danger badge-sm" role="alert">
                                                        <span style="font-size: 11px;" data-i18n="policy_config_user_cookies"><strong>User cookies must be enabled</strong></span>
                                                    </div>
                                                </div>
                                                <div class="col-2 mb-2">
                                                    <div class="custom-control custom-switch custom-control-inline">
                                                        <input type="checkbox" class="custom-control-input" name="remember_me" id="remember_me" <?= ($kiw_row['remember_me'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                        <label class="custom-control-label" for="remember_me"></label>
                                                    </div>
                                                </div>


                                                
                                                <div class="col-10 mb-2">
                                                    <span data-i18n="policy_config_required_2fa">Required 2 factor authentication</span> <br>
                                                    <div class="badge badge-danger badge-sm" role="alert">
                                                        <span style="font-size: 11px;" data-i18n="policy_config_sms_supported"><strong>Only SMS supported, please make sure SMS gateway is provided</strong></span>
                                                    </div>
                                                </div>
                                                <div class="col-2 mb-2">
                                                    <div class="custom-control custom-switch custom-control-inline">
                                                        <input type="checkbox" class="custom-control-input" name="two-factors" id="two-factors" <?= ($kiw_row['two-factors'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                        <label class="custom-control-label" for="two-factors"></label>
                                                    </div>
                                                </div>



                                                <div class="col-10 mb-2">
                                                    <span data-i18n="policy_config_required_captcha">Required captcha</span>
                                                </div>
                                                <div class="col-2 mb-2">
                                                    <div class="custom-control custom-switch custom-control-inline">
                                                        <input type="checkbox" class="custom-control-input" name="captcha" id="captcha" <?= ($kiw_row['captcha'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                        <label class="custom-control-label" for="captcha"></label>
                                                    </div>
                                                </div>



                                                <div class="col-10 mb-2">
                                                    <span data-i18n="policy_config_delete_unverified_acc">Delete unverified account ( for temporary access account, ie: email verification sign-up )</span>
                                                </div>
                                                <div class="col-2 mb-2">
                                                    <div class="custom-control custom-switch custom-control-inline">
                                                        <input type="checkbox" class="custom-control-input" name="delete_unverified" id="delete_unverified" <?= ($kiw_row['delete_unverified'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                        <label class="custom-control-label" for="delete_unverified"></label>
                                                    </div>
                                                </div>



                                                <?php if($carry_forward){ ?>
                                                <div class="col-10 mb-2">
                                                    <span data-i18n="policy_config_allow_carry_forward">Allow carry forward data</span>
                                                </div>
                                                <div class="col-2 mb-2">
                                                    <div class="custom-control custom-switch custom-control-inline">
                                                        <input type="checkbox" class="custom-control-input" name="allow_carry_forward" id="allow_carry_forward" <?= ($kiw_row['allow_carry_forward'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                        <label class="custom-control-label" for="allow_carry_forward"></label>
                                                    </div>
                                                </div>
                                                <?php } ?>


                                                <div class="col-10 mb-2">
                                                    <span data-i18n="">Security to block user if detected with high severity</span>
                                                </div>
                                                <div class="col-2 mb-2">
                                                    <div class="custom-control custom-switch custom-control-inline">
                                                        <input type="checkbox" class="custom-control-input" name="security_block" id="security_block" <?= ($kiw_row['security_block'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                        <label class="custom-control-label" for="security_block"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="tab-pane" id="macsecurity" aria-labelledby="macsecurity-tab" role="tabpanel">

                                            <div class="col-12">
                                                <div class="form-group row">

                                                    <div class="col-md-3">
                                                        <span data-i18n="policy_config_mac_register">MAC Auto Register</span>
                                                    </div>

                                                    <div class="col-md-9">
                                                        <div class="custom-control custom-switch custom-control-inline col-md-8">
                                                            <input type="checkbox" class="custom-control-input" name="mac_auto_register" id="mac_auto_register" <? if ($kiw_row['mac_auto_register'] == "y") { echo 'checked="yes"'; } ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="mac_auto_register"></label>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">

                                                    <div class="col-md-3">
                                                        <span data-i18n="policy_config_max_num">Maximum number of MAC Address to be registered per account</span>
                                                    </div>

                                                    <div class="col-md-9">
                                                        <input type="text" name="mac_max_register" id="mac_max_register" value="<? echo $kiw_row['mac_max_register']; ?>" class="form-control col-11" />
                                                    </div>

                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">

                                                    <div class="col-md-3">
                                                        <span data-i18n="policy_config_allowed">Allowed registered mac to login using registered Account</span>
                                                    </div>

                                                    <div class="col-md-9">
                                                        <div class="custom-control custom-switch custom-control-inline col-md-8">
                                                            <input type="checkbox" class="custom-control-input" name="mac_security" id="mac_security" <? if ($kiw_row['mac_security'] == "y") { echo 'checked="yes"'; } ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="mac_security"></label>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>


                                        <div class="tab-pane" id="autologin" aria-labelledby="autologin-tab" role="tabpanel">

                                            <div class="col-12">
                                                <div class="form-group row">

                                                    <div class="col-md-3">
                                                        <span data-i18n="policy_config_cookies_autologin">Cookies Auto Login</span>
                                                    </div>

                                                    <div class="col-md-9">
                                                        <div class="custom-control custom-switch custom-control-inline col-md-8">
                                                            <input type="checkbox" class="custom-control-input" name="cookies_login" id="cookies_login" <? if ($kiw_row['cookies_login'] == "y") { echo 'checked="yes"'; } ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="cookies_login"></label>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">

                                                    <div class="col-md-3">
                                                        <span data-i18n="policy_config_cookies_validity">Cookies Auto Login Days</span>
                                                    </div>

                                                    <div class="col-md-9">
                                                        <input type="text" name="cookies_login_validity" id="cookies_login_validity" value="<? echo $kiw_row['cookies_login_validity']; ?>" class="form-control col-11" />
                                                    </div>

                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">

                                                    <div class="col-md-3">
                                                        <span data-i18n="policy_config_mac_autologin">MAC Auto Login</span>
                                                    </div>

                                                    <div class="col-md-9">
                                                        <div class="custom-control custom-switch custom-control-inline col-md-8">
                                                            <input type="checkbox" class="custom-control-input" name="mac_auto_login" id="mac_auto_login" <? if ($kiw_row['mac_auto_login'] == "y") { echo 'checked="yes"'; } ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="mac_auto_login"></label>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">

                                                    <div class="col-md-3">
                                                        <span data-i18n="policy_config_mac_autologin_days">MAC Address Auto Login Days</span>
                                                    </div>

                                                    <div class="col-md-9">
                                                        <input type="text" name="mac_auto_login_days" id="mac_auto_login_days" value="<? echo $kiw_row['mac_auto_login_days']; ?>" class="form-control col-11" />
                                                    </div>

                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">

                                                    <div class="col-md-3">
                                                        <span data-i18n="policy_config_mac_auto_zone">Auto Login Only Same Zone</span>
                                                    </div>

                                                    <div class="col-md-9">
                                                        <div class="custom-control custom-switch custom-control-inline col-md-8">
                                                            <input type="checkbox" class="custom-control-input" name="mac_auto_same_zone" id="mac_auto_same_zone" <?= ($kiw_row['mac_auto_same_zone'] == "y") ? "checked=checked" : "" ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="mac_auto_same_zone"></label>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                                </form>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-primary save-button waves-effect waves-light" data-i18n="policy_config_save">Save</button>
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
