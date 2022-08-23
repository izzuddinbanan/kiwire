<?php

$kiw['module'] = "Integration -> LBS";
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

$kiw_row = $kiw_db->query_first("SELECT * FROM kiwire_omaya WHERE tenant_id = '{$tenant_id}' LIMIT 1");

if (empty($kiw_row)) $kiw_db->query("INSERT INTO kiwire_omaya(tenant_id) VALUE('{$tenant_id}')");

?>

<form id="update-form" class="form-horizontal" method="post">
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-12 mb-2">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="integration_lbs_title">LBS</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item active" data-i18n="integration_lbs_subtitle">
                                    Manage connection to LBS service
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
                                        <div class="tab-pane active" id="general" aria-labelledby="general-tab" role="tabpanel">
                                            <form id="update-form" class="form-horizontal" method="post">
                                                <button type="button" class="btn btn-primary pull-right save-button waves-effect waves-light" data-i18n="integration_lbs_save">Save</button>

                                                <br><br><br>

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="text-right col-md-3">
                                                            <span data-i18n="integration_lbs_enable">Enable</span>
                                                        </div>
                                                        <div class="col-md-7">
                                                            <div class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" class="custom-control-input" name=status id=status <?= ($kiw_row['status'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                                <label class="custom-control-label" for="status"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="text-right col-md-3">
                                                            <span data-i18n="integration_lbs_url">URL Path</span>
                                                        </div>
                                                        <div class="col-md-7"><input type="text" name=api_id id=api_id value="<?= $kiw_row['api_id']; ?>" class="form-control required" />
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="text-right col-md-3">
                                                            <span data-i18n="integration_lbs_secret_key">Secret Key</span>
                                                        </div>
                                                        <div class="col-md-7"><input type="text" name="api_secret" id="api_secret" value="<?= $kiw_row['api_secret']; ?>" class="form-control required" />
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                  <hr>
                                                    <div class="form-group row">
                                                        <div class="text-right col-md-3"></div>
                                                        <div class="col-md-7">
                                                            <a href="javascript:void(0)" onclick="checkOmaya()" id="test-omaya" class="btn btn-warning waves-effect waves-light" data-i18n="integration_lbs_test">Test</a>
                                                        </div>
                                                    </div>
                                                </div>

                                                <input type="hidden" name="update" id="update" value="true" />

                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</form>

<div id="form-modal" class="modal fade in" tabindex="-1" role="dialog" aria-labelledby="form-modal" aria-hidden="true" style="display: none; padding-left: 0px;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModalLabel" data-i18n="integration_lbs_test_omaya_connection">Test Omaya Connection</h4>
            </div>

            <div class="modal-body">
                <div class="panel-body">
                    <div class="row">
                        <label class="col-sm-4 control-label" data-i18n="integration_lbs_link">Link</label>
                        <span id="link" class="col-sm-8">1&nbsp;</span>
                    </div>

                    <div class="row">
                        <label class="col-sm-4 control-label" data-i18n="integration_lbs_key">Key</label>
                        <span id="key" class="col-md-8">1</span>
                    </div>

                    <div class="row">
                        <label class="col-sm-4 control-label" data-i18n="integration_lbs_client">Client</label>
                        <span id="client" class="col-sm-8">1&nbsp;</span>
                    </div>

                    <div class="row">
                        <label class="col-sm-4 control-label" data-i18n="integration_lbs_expired">Expired</label>
                        <span id="expired" class="col-md-8">1&nbsp;</span>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal" data-i18n="integration_lbs_close">Close</button>
            </div>
        </div>
    </div>
</div>


<?php

require_once "includes/include_footer.php";

?>
