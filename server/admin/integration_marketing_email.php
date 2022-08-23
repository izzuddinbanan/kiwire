<?php

$kiw['module'] = "Integration -> Mail";
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


$kiw_row = $kiw_db->query_first("SELECT * FROM kiwire_int_marketing_email WHERE tenant_id = '{$tenant_id}' LIMIT 1");

if (empty($kiw_row)) $kiw_db->query("INSERT INTO kiwire_int_marketing_email(tenant_id) VALUE('{$tenant_id}')");


?>

<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="integration_marketing_email_title">Marketing Email</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="integration_marketing_email_subtitle">
                                Manage cloud mail
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


                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-2">
                                                    <span data-i18n="integration_marketing_email_enable">Enable</span>
                                                </div>
                                                <div class="col-md-10">
                                                    <div class="custom-control custom-switch custom-control-inline">
                                                        <input type="checkbox" class="custom-control-input" name=mailchimp_en id=mailchimp_en <?= ($kiw_row['mailchimp_en'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                        <label class="custom-control-label" for="mailchimp_en"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-2">
                                                    <span data-i18n="integration_marketing_email_mailchimp_key">Mailchimp API key</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-10"><input type="text" name=mailchimp_api id=mailchimp_api value="<?= $kiw_row['mailchimp_api']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-2">
                                                    <span data-i18n="integration_marketing_email_mailchimp_id">Mailchimp List ID</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-10">
                                                    <input type="text" name=mailchimp_lid id=mailchimp_lid value="<?= $kiw_row['mailchimp_lid']; ?>" class="form-control" required>
                                                    <span style="font-size: smaller;padding: 10px;" data-i18n="integration_marketing_email_enter">Enter list and find Settings->List name & defaults. Then you will find List ID eg : 306d39834c</span>
                                                </div>
                                            </div>
                                        </div>

                                        <hr><br>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-2">
                                                    <span data-i18n="integration_marketing_email_enable2">Enable</span>
                                                </div>
                                                <div class="col-md-10">
                                                    <div class="custom-control custom-switch custom-control-inline">
                                                        <input type="checkbox" class="custom-control-input" name=madmini_en id=madmini_en <?= ($kiw_row['madmini_en'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                        <label class="custom-control-label" for="madmini_en"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-2">
                                                    <span data-i18n="integration_marketing_email_madmimi_uname">MadMimi Email/Username</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-10">
                                                    <input type="text" name=madmini_email id=madmini_email value="<?= $kiw_row['madmini_email']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-2">
                                                    <span data-i18n="integration_marketing_email_madmimi_key">Madmimi API Key</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-10">
                                                    <input type="text" name=madmini_api id=madmini_api value="<?= $kiw_row['madmini_api']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-2">
                                                    <span data-i18n="integration_marketing_email_madmimi_list">Madmimi List</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-10">
                                                    <input type="text" name=madmini_list id=madmini_list value="<?= $kiw_row['madmini_list']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <input type="hidden" name="update" value="true" />
                                        <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                                    </form>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-primary waves-effect waves-light save-button" data-i18n="integration_marketing_email_save">Save</button>
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
