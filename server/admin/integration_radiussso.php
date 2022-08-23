<?php

$kiw['module'] = "Integration -> Radius SSO";
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

$kiw_row = $kiw_db->query_first("SELECT * FROM kiwire_sso WHERE tenant_id = '{$tenant_id}' LIMIT 1");

if (empty($kiw_row)) $kiw_db->query("INSERT INTO kiwire_sso(tenant_id) VALUE('{$tenant_id}')");

?>

<form id="update-form" class="form-horizontal" method="post">
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-12 mb-2">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="integration_radiussso_title">Radius SSO</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item active" data-i18n="integration_radiussso_subtitle">
                                    Send accounting data to external Radius Server for Single Sign-On
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
                                                <button type="button" class="btn btn-primary pull-right save-button round waves-effect waves-light" data-i18n="integration_radiussso_save">Save</button>

                                                <br><br><br>

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="text-right col-md-3">
                                                            <span data-i18n="integration_radiussso_enabled">Enabled</span>
                                                        </div>
                                                        <div class="col-md-7">
                                                            <div class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" class="custom-control-input" name=enabled id=enabled <?= ($kiw_row['enabled'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                                <label class="custom-control-label" for="enabled"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="text-right col-md-3">
                                                            <span data-i18n="integration_radiussso_server">SSO Server</span>
                                                        </div>
                                                        <div class="col-md-7"><input type="text" name=sso_server id=sso_server value="<?= $kiw_row['sso_server']; ?>" class="form-control" />
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="text-right col-md-3">
                                                            <span data-i18n="integration_radiussso_port">SSO Port</span>
                                                        </div>
                                                        <div class="col-md-7"><input type="text" name=sso_port id=sso_port value="<?= $kiw_row['sso_port']; ?>" class="form-control" />
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="text-right col-md-3">
                                                            <span data-i18n="integration_radiussso_secret">SSO Secret</span>
                                                        </div>
                                                        <div class="col-md-7"><input type="text" name=sso_secret id=sso_secret value="<?= $kiw_row['sso_secret']; ?>" class="form-control" />
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="text-right col-md-3">
                                                            <span data-i18n="integration_radiussso_simultaneous_request">Simultaneous Request</span>
                                                        </div>
                                                        <div class="col-md-7"><input type="text" name=sso_simul id=sso_simul value="<?= $kiw_row['sso_simul']; ?>" class="form-control" />
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="text-right col-md-3">
                                                            <span data-i18n="integration_radiussso_request_timeout">Request Timeout (Seconds)</span>
                                                        </div>
                                                        <div class="col-md-7"><input type="text" name=sso_timeout id=sso_timeout value="<?= $kiw_row['sso_timeout']; ?>" class="form-control" />
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="text-right col-md-3">
                                                            <span data-i18n="integration_radiussso_retry">Retry</span>
                                                        </div>
                                                        <div class="col-md-7"><input type="text" name=sso_retry id=sso_retry value="<?= $kiw_row['sso_retry']; ?>" class="form-control" />
                                                        </div>
                                                    </div>
                                                </div>

                                                <hr><br>

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="text-right col-md-3">
                                                            <span data-i18n="integration_radiussso_acct_id">Acct-Session-Id</span>
                                                        </div>
                                                        <div class="col-md-7">
                                                            <div class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" class="custom-control-input" name=acctsessionid id=acctsessionid <?= ($kiw_row['acctsessionid'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                                <label class="custom-control-label" for="acctsessionid"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="text-right col-md-3">
                                                            <span data-i18n="integration_radiussso_uname">User-Name</span>
                                                        </div>
                                                        <div class="col-md-7">
                                                            <div class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" class="custom-control-input" name=username id=username <?= ($kiw_row['username'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                                <label class="custom-control-label" for="username"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="text-right col-md-3">
                                                            <span data-i18n="integration_radiussso_nas_ip">NAS-IP-Address</span>
                                                        </div>
                                                        <div class="col-md-7">
                                                            <div class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" class="custom-control-input" name=nasipaddress id=nasipaddress <?= ($kiw_row['nasipaddress'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                                <label class="custom-control-label" for="nasipaddress"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="text-right col-md-3">
                                                            <span data-i18n="integration_radiussso_nas_port_id">NAS-Port-Id</span>
                                                        </div>
                                                        <div class="col-md-7">
                                                            <div class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" class="custom-control-input" name=nasportid id=nasportid <?= ($kiw_row['nasportid'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                                <label class="custom-control-label" for="nasportid"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="text-right col-md-3">
                                                            <span data-i18n="integration_radiussso_nas_port_type">NAS-Port-Type</span>
                                                        </div>
                                                        <div class="col-md-7">
                                                            <div class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" class="custom-control-input" name=nasporttype id=nasporttype <?= ($kiw_row['nasporttype'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                                <label class="custom-control-label" for="nasporttype"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="text-right col-md-3">
                                                            <span data-i18n="integration_radiussso_acct_time">Acct-Session-Time</span>
                                                        </div>
                                                        <div class="col-md-7">
                                                            <div class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" class="custom-control-input" name=acctsessiontime id=acctsessiontime <?= ($kiw_row['acctsessiontime'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                                <label class="custom-control-label" for="acctsessiontime"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="text-right col-md-3">
                                                            <span data-i18n="integration_radiussso_acct_octets">Acct-Input-Octets</span>
                                                        </div>
                                                        <div class="col-md-7">
                                                            <div class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" class="custom-control-input" name=acctinputoctets id=acctinputoctets <?= ($kiw_row['acctinputoctets'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                                <label class="custom-control-label" for="acctinputoctets"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
 
                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="text-right col-md-3">
                                                            <span data-i18n="integration_radiussso_acct_octets2">Acct-Input-Octets</span>
                                                        </div>
                                                        <div class="col-md-7">
                                                            <div class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" class="custom-control-input" name=acctoutputoctets id=acctoutputoctets <?= ($kiw_row['acctoutputoctets'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                                <label class="custom-control-label" for="acctoutputoctets"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="text-right col-md-3">
                                                            <span data-i18n="integration_radiussso_called_id">Called-Station-Id</span>
                                                        </div>
                                                        <div class="col-md-7">
                                                            <div class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" class="custom-control-input" name=calledstationid id=calledstationid <?= ($kiw_row['calledstationid'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                                <label class="custom-control-label" for="calledstationid"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="text-right col-md-3">
                                                            <span data-i18n="integration_radiussso_calling_id">Calling-Station-Id</span>
                                                        </div>
                                                        <div class="col-md-7">
                                                            <div class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" class="custom-control-input" name=callingstationid id=callingstationid <?= ($kiw_row['callingstationid'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                                <label class="custom-control-label" for="callingstationid"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="text-right col-md-3">
                                                            <span data-i18n="integration_radiussso_acct_cause">Acct-Terminate-Cause</span>
                                                        </div>
                                                        <div class="col-md-7">
                                                            <div class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" class="custom-control-input" name=acctterminatecause id=acctterminatecause <?= ($kiw_row['acctterminatecause'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                                <label class="custom-control-label" for="acctterminatecause"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="text-right col-md-3">
                                                            <span data-i18n="integration_radiussso_framed_addr">Framed-IP-Address</span>
                                                        </div>
                                                        <div class="col-md-7">
                                                            <div class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" class="custom-control-input" name=framedipaddress id=framedipaddress <?= ($kiw_row['framedipaddress'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                                <label class="custom-control-label" for="framedipaddress"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <input type="hidden" name="update" value="true" />

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


<?php

require_once "includes/include_footer.php";

?>