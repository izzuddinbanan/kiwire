<?php

$kiw['module'] = "Integration -> Zapier";
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

$kiw_row = $kiw_db->query_first("SELECT * FROM kiwire_zapier_data WHERE tenant_id = '{$tenant_id}' LIMIT 1");

if (empty($kiw_row)) $kiw_db->query("INSERT INTO kiwire_zapier_data(tenant_id) VALUE('{$tenant_id}')");

?>

<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="integration_zapier_title">Zapier</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="integration_zapier_subtitle">
                                Authentication for zapier
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
                                        <button type="button" class="btn btn-primary pull-right save-button waves-effect waves-light" data-i18n="integration_zapier_save">Save</button>

                                        <br><br><br>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="text-right col-md-3">
                                                    <span data-i18n="integration_zapier_enable">Enable</span>
                                                </div>
                                                <div class="col-md-7">
                                                    <div class="custom-control custom-switch custom-control-inline">
                                                        <input type="checkbox" class="custom-control-input" name="enabled" id="enabled" <?= ($kiw_row['enabled'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                        <label class="custom-control-label" for="enabled"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="text-right col-md-3">
                                                    <span data-i18n="integration_zapier_public_url">Public URL</span>
                                                </div>
                                                <div class="col-md-7"><input type="text" name="api_id" id="api_id" value="<?= $kiw_row['api_id']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="text-right col-md-3">
                                                    <span data-i18n="integration_zapier_auth_key">Authentication Key</span>
                                                </div>
                                                <div class="col-md-7"><input type="text" name="authkey" id="authkey" value="<?= $kiw_row['api_key']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                          <hr>
                                            <div class="form-group row">
                                                <div class="text-right col-md-3"></div>
                                                <div class="col-md-7">
                                                    <button type="button" class="btn btn-success waves-effect waves-light btn-generate-key" data-i18n="integration_zapier_generate_key">Generate Key</button>
                                                    <button type="button" class="btn btn-warning waves-effect waves-light flang-c-copy_key_button" id="copyAuthKey" name="copyAuthKey" data-i18n="integration_zapier_copy_key">Copy Key</button>
                                                </div>
                                            </div>
                                        </div>

                                    </form>

                                </div>
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

<script src="/assets/js/jquery-copytoclipboard.js"></script>
