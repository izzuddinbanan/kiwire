<?php

$kiw['module'] = "BPanel -> Setting";
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


$kiw_row = $kiw_db->query_first("SELECT * FROM  kiwire_bpanel_template WHERE tenant_id = '{$tenant_id}' LIMIT 1");

$kiw_profiles = $kiw_db->fetch_array("SELECT * FROM  kiwire_profiles WHERE tenant_id = '{$tenant_id}' AND price > 0");
$kiw_pages = $kiw_db->fetch_array("SELECT * FROM  kiwire_login_pages WHERE purpose = 'landing' AND tenant_id = '{$tenant_id}'");


if (empty($kiw_row)) {

    $kiw_db->query("INSERT INTO kiwire_bpanel_template(tenant_id) VALUE('$tenant_id')");
}

$selected_profile = json_decode($kiw_row['profile']);

?>


<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="policy_user_panel_title">Buy Profile Panel</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="policy_user_panel_subtitle">
                                User manage to buy profile
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
                                                        <span data-i18n="policy_user_panel_username">Landing Page</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <select class="form-control select2" id="page" name="page">
                                                            <?php 
                                                                foreach($kiw_pages as $page){ ?>
                                                                    <option value="<?= $page['unique_id'] ?>" <?= $page['unique_id'] ==  $kiw_row['page'] ? 'selected' : '' ?> > <?= $page['page_name'] ?> </option>

                                                                <?php }

                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-3">
                                                        <span data-i18n="policy_user_panel_username">Complete Page</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <select class="form-control select2" id="page_complete" name="page_complete">
                                                            <?php 
                                                                foreach($kiw_pages as $page){ ?>
                                                                    <option value="<?= $page['unique_id'] ?>" <?= $page['unique_id'] ==  $kiw_row['page_complete'] ? 'selected' : '' ?> > <?= $page['page_name'] ?> </option>

                                                                <?php }

                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-3">
                                                        <span data-i18n="policy_user_panel_password">Profile to Sell</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <select class="form-control select2" id="profile" name="profile[]" multiple>
                                                            <?php 
                                                                foreach($kiw_profiles as $profile){ ?>
                                                                    <option value="<?= $profile['name'] ?>" <?= in_array($profile['name'], $selected_profile) ? 'selected' : '' ?> > <?= $profile['name'] ?> </option>

                                                                <?php }

                                                            ?>
                                                        </select>    
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