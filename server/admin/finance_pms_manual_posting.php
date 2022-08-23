<?php

$kiw['module'] = "Finance -> Manual Posting";
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


$kiw_accounts = $kiw_db->fetch_array("SELECT username,fullname FROM kiwire_account_auth WHERE tenant_id = '{$_SESSION['tenant_id']}' AND status = 'active' AND integration = 'pms' LIMIT 5000");


?>

<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Manual
                        Posting</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                Post charges manualy to room or User ID
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

                                            <br><br><br>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-3">
                                                        <span data-i18n="integration">Post To Integration</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <select class="select2 form-control" name="type" id="type"
                                                                data-style="btn-default" tabindex="-98">
                                                            <option value="pms" data-i18n="integration_opt_pms">PMS
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-3">
                                                        <span data-i18n="post_to">Post to</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <select name="room_no" id="room_no" class="select2 form-control">

                                                            <?php if (count($kiw_accounts) > 0) { ?>

                                                                <?php foreach ($kiw_accounts as $kiw_account): ?>
                                                                    <option value="<?= $kiw_account['username'] ?>"><?= $kiw_account['username'] ?> - <?= $kiw_account['fullname'] ?></option>
                                                                <?php endforeach; ?>

                                                            <?php } else { ?>

                                                                <option value="">No room checked in</option>

                                                            <?php } ?>

                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-3">
                                                        <span data-i18n="charge_amount">Charge Amount</span> <span class="text-danger">*</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <input type="text" name="charge_amount" id="charge_amount" value="" class="form-control" placeholder="eg: 50.00" required>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-3">
                                                        <span data-i18n="remark">Remark</span> <span class="text-danger">*</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <input type="text" name="remark" id="remark" class="form-control" placeholder="description" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                                        </form>

                                    </div>

                                </div>
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

<?php
require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";
?>
