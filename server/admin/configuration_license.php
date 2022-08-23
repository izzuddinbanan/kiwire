<?php

$kiw['module'] = "Configuration -> License";
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


// get the cloud level license

$kiw_cloud_license_key = @file_get_contents(dirname(__FILE__, 2) . "/custom/{$tenant_id}/tenant.license");

if ($kiw_cloud_license_key) $kiw_cloud_license = sync_license_decode($kiw_cloud_license_key);


// get multitenant license if available

$kiw_multitenant_license_key = @file_get_contents(dirname(__FILE__, 2) . "/custom/cloud.license");

if ($kiw_multitenant_license_key) $kiw_multitenant_license = sync_license_decode($kiw_multitenant_license_key);


?>


    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-12 mb-2">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">License Settings</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item active" data-i18n="subtitle">
                                    Settings for license
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
                                    <button type="button" class="btn btn-primary save-button waves-effect waves-light" data-i18n="btn_save">
                                        Save
                                    </button>
                                </div>
                                <div class="card-body">
                                    <form id="update-form" class="form-horizontal" method="post">
                                        <ul class="nav nav-tabs" role="tablist">

                                            <li class="nav-item">
                                                <a class="nav-link active" id="license-tab" data-toggle="tab"
                                                   href="#license" aria-controls="license" role="tab"
                                                   aria-selected="true" data-i18n="form_cloud">CLOUD</a>
                                            </li>

                                            <?php if (($_SESSION['multi_tenant'] == false) || ($_SESSION['multi_tenant'] == true && $_SESSION['access_level'] == "superuser")) { ?>
                                                <li class="nav-item">
                                                    <a class="nav-link" id="multitenantlicense-tab" data-toggle="tab" href="#multitenantlicense" aria-controls="multitenantlicense" role="tab" aria-selected="false" data-i18n="form_multitenant">MULTI-TENANT</a>
                                                </li>
                                            <?php } ?>

                                        </ul>

                                        <br>

                                        <div class="tab-content">

                                            <div class="tab-pane active" id="license" aria-labelledby="license-tab"
                                                 role="tabpanel">

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="text-right col-md-3">
                                                            <span data-i18n="form_cloud_license">License Key</span>
                                                        </div>
                                                        <div class="col-md-8">

                                                            <input type="text" name="cloud_key" id="cloud_key"
                                                                   value="<?= $kiw_cloud_license_key ?>"
                                                                   class="form-control col-11">

                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="text-right col-md-3">
                                                            <span data-i18n="form_cloud_license_expiry">Support Expired On</span>
                                                        </div>
                                                        <div class="col-md-8">

                                                            <?php if ($kiw_cloud_license['expire_on'] > time()) { ?>

                                                                <span class="badge badge-success badge-md mr-1 mb-1"><?= date("d-m-Y", $kiw_cloud_license['expire_on']) ?></span>

                                                            <?php } else { ?>

                                                                <span class="badge badge-danger badge-md mr-1 mb-1"><?= date("d-m-Y", $kiw_cloud_license['expire_on']) ?></span>

                                                            <?php } ?>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <?php if (($_SESSION['multi_tenant'] == false) || ($_SESSION['multi_tenant'] == true && $_SESSION['access_level'] == "superuser")) { ?>

                                                <div class="tab-pane" id="multitenantlicense" aria-labelledby="multitenantlicense-tab" role="tabpanel">

                                                    <div class="col-12">
                                                        <div class="form-group row">
                                                            <div class="text-right col-md-3">
                                                                <span data-i18n="form_multitenant_key">Multi Tenant Key</span>
                                                            </div>
                                                            <div class="col-md-8">
                                                                <input type="text" name="multitenant_key" id="multitenant_key" value="<?= $kiw_multitenant_license_key ?>" class="form-control col-11"/>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-12">
                                                        <div class="form-group row">
                                                            <div class="text-right col-md-3">
                                                                <span data-i18n="form_multitenant_key_expiry">Support Expired On</span>
                                                            </div>
                                                            <div class="col-md-8">
                                                                <?php

                                                                    if ($kiw_multitenant_license['expire_on'] > time()) {

                                                                        echo '<span class="badge badge-success badge-md mr-1 mb-1">' . date("d-m-Y", $kiw_multitenant_license['expire_on']) . '</span>';

                                                                    } else {

                                                                        echo '<span class="badge badge-danger badge-md mr-1 mb-1">' . date("d-m-Y", $kiw_multitenant_license['expire_on']) . '</span>';

                                                                    }

                                                                ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            <?php } ?>

                                        </div>

                                        <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">

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