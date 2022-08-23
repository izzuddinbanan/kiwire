<?php

$kiw['module'] = "Policy -> Password";
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

$kiw_row = $kiw_db->query_first("SELECT * FROM  kiwire_policies WHERE tenant_id = '{$tenant_id}' LIMIT 1");

if (empty($kiw_row)) $kiw_db->query("INSERT INTO kiwire_policies(tenant_id) VALUE('$tenant_id')");

?>


<style>
    @media only screen and (min-width: 567px){
        .margin-pc{
            margin-left: 250px !important;
            margin-right: 250px !important;
        }
        
    }

    @media only screen and (max-width: 567px){
        .margin-pc{
            margin-left: -6px;
            margin-right: 10px;
        }
    }
</style>
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="policy_password_title">Password policy</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="policy_password_subtitle">
                                Manage password setting
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
                                    <div class="tab-content">

                                        <div class="row margin-pc mt-2">

                                            <div class="col-10 mb-2">
                                                <span data-i18n="policy_password_enable">Enable Password Policy</span>
                                            </div>
                                            <div class="col-2 mb-2">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" class="custom-control-input" name="password_policy" id="password_policy" <?= ($kiw_row['password_policy'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                    <label class="custom-control-label" for="password_policy"></label>
                                                </div>
                                            </div>

                                            <div class="col-10 mb-2">
                                                <span data-i18n="policy_password_min_char">Minimum 8 characters</span>
                                            </div>
                                            <div class="col-2 mb-2">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" class="custom-control-input" name="password_character" id="password_character" <?= ($kiw_row['password_character'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                    <label class="custom-control-label" for="password_character"></label>
                                                </div>
                                            </div>

                                            <div class="col-10 mb-2">
                                                <span data-i18n="policy_password_contain_alphabet">Contain at least 1 alphabet</span>
                                            </div>
                                            <div class="col-2 mb-2">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" class="custom-control-input" name="password_alphabet" id="password_alphabet" <?= ($kiw_row['password_alphabet'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                    <label class="custom-control-label" for="password_alphabet"></label>
                                                </div>
                                            </div>

                                            <div class="col-10 mb-2">
                                                <span data-i18n="policy_password_contain_numeral">Contain at least 1 numeral</span>
                                            </div>
                                            <div class="col-2 mb-2">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" class="custom-control-input" name="password_numeral" id="password_numeral" <?= ($kiw_row['password_numeral'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                    <label class="custom-control-label" for="password_numeral"></label>
                                                </div>
                                            </div>

                                            <div class="col-10 mb-2">
                                                <span data-i18n="policy_password_contain_symbol">Contain at least 1 symbol</span>
                                            </div>
                                            <div class="col-2 mb-2">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" class="custom-control-input" name="password_symbol" id="password_symbol" <?= ($kiw_row['password_symbol'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                    <label class="custom-control-label" for="password_symbol"></label>
                                                </div>
                                            </div>


                                            <div class="col-10 mb-2">
                                                <span data-i18n="policy_password_must_changed" data-i18n="policy_password_must_changed">Must be changed at least every 90 days</span>
                                            </div>
                                            <div class="col-2 mb-2">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" class="custom-control-input" name="password_days" id="password_days" <?= ($kiw_row['password_days'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                    <label class="custom-control-label" for="password_days"></label>
                                                </div>
                                            </div>

                                            <div class="col-10 mb-2">
                                                <span data-i18n="policy_password_cannot_reuse">Cannot reuse the last 3 passwords</span>
                                            </div>
                                            <div class="col-2 mb-2">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" class="custom-control-input" name="password_reused" id="password_reused" <?= ($kiw_row['password_reused'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                    <label class="custom-control-label" for="password_reused"></label>
                                                </div>
                                            </div>

                                            <div class="col-10 mb-2">
                                                <span data-i18n="policy_password_lock_user">Lock user after maximum of 6 failed attempts</span>
                                            </div>
                                            <div class="col-2 mb-2">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" class="custom-control-input" name="password_attempts" id="password_attempts" <?= ($kiw_row['password_attempts'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                    <label class="custom-control-label" for="password_attempts"></label>
                                                </div>
                                            </div>

                                            <div class="col-10 mb-2">
                                                <span data-i18n="policy_password_change_after_login">Must be changed upon first login</span>
                                            </div>
                                            <div class="col-2 mb-2">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" class="custom-control-input" name="password_first_login" id="password_first_login" <?= ($kiw_row['password_first_login'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                    <label class="custom-control-label" for="password_first_login"></label>
                                                </div>
                                            </div>

                                            <div class="col-10 mb-2">
                                                <span data-i18n="policy_password_not_same">Not be the same as the username</span>
                                            </div>
                                            <div class="col-2 mb-2">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" class="custom-control-input" name="password_same" id="password_same" <?= ($kiw_row['password_same'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                    <label class="custom-control-label" for="password_same"></label>
                                                </div>
                                            </div>

                                            <div class="col-10 mb-2">
                                                <span data-i18n="policy_password_login_wifi">Login user to wifi once password changed</span>
                                            </div>
                                            <div class="col-2 mb-2">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" class="custom-control-input" name="auto_login" id="auto_login" <?= ($kiw_row['auto_login'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                    <label class="custom-control-label" for="auto_login"></label>
                                                </div>
                                            </div>


                                            <div class="col-8 mb-2">
                                                <span data-i18n="policy_password_pass_page">Change password page</span>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4 mb-2">
                                                <select name="change_passpage" id="change_passpage" class="select2 form-control" data-style="btn-default" tabindex="">
                                                    <option value="" data-i18n="policy_password_none">None</option>
                                                    <?php

                                                    $pages = $kiw_db->fetch_array("SELECT unique_id,page_name FROM kiwire_login_pages WHERE tenant_id = '{$tenant_id}'");

                                                    foreach ($pages as $page) {

                                                        ?>

                                                        <option value="<?= $page['unique_id'] ?>" <?= ($kiw_row['change_passpage'] == $page['unique_id'] ? "selected" : "") ?>><?= $page['page_name'] ?></option>

                                                    <?php

                                                    }

                                                    ?>
                                                </select>
                                            </div>
                                        </div>



                                    </div>
                                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                                </form>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-primary save-button waves-effect waves-light" data-i18n="policy_password_save">Save</button>
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
