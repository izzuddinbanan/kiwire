<?php

$kiw['module'] = "Integration -> HSS";
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

$kiw_count = 1;


$kiw_row = $kiw_db->query_first("SELECT * FROM kiwire_int_hss WHERE tenant_id = '$tenant_id' LIMIT 1");

if (empty($kiw_row)) {

    $kiw_db->query("INSERT INTO kiwire_int_hss(tenant_id) VALUE('{$tenant_id}')");

}

?>

<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="integration_hss">HSS</h2>
                    <div class="breadcrumb-wrapper">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active" data-i18n="integration_hss_title">
                            Manage HSS Integration
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
                            </div>
                            
                            <div class="card-body">

                                <form id="update-form" class="form-horizontal" method="post">

                                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                                    
                                    <br><br><br>
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="general" aria-labelledby="general-tab" role="tabpanel">
                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_status_hss">Enable</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <div class="custom-control custom-switch custom-control-inline col-md-8">
                                                            <input type="checkbox" class="custom-control-input" name=hss_status id=hss_status <?= ($kiw_row['status'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="hss_status"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="">24 Hours</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <div class="custom-control custom-switch custom-control-inline">
                                                            <input type="checkbox" class="custom-control-input" name="is_24" id="is_24" <?= ($kiw_row['is_24_hour'] == "1") ? 'checked' : '' ?> value="1" class="toggle" />
                                                            <label class="custom-control-label" for="is_24"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 time" style="display: <?= $kiw_row['is_24_hour'] ? 'none' : 'block' ?>">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="">Start Time</span> 
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" name=start_time id=start_time value="<?= $kiw_row['start_time']; ?>" class="form-control datetime"  />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 time" style="display: <?= $kiw_row['is_24_hour'] ? 'none' : 'block' ?>">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="">Stop Time</span> 
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" name=stop_time id=stop_time value="<?= $kiw_row['stop_time']; ?>" class="form-control datetime"  />
                                                    </div>
                                                </div>
                                            </div> -->

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_hss_user">Username</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" name=hss_user id=hss_user value="<?= $kiw_row['username']; ?>" class="form-control" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_hss_password">Password</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="password" name=hss_password id=hss_password value="<?= $kiw_row['password']; ?>" class="form-control" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_hss_password">HSS Server URL</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" name=hss_url id=hss_url value="<?= $kiw_row['hss_server_url']; ?>" class="form-control" placeholder="e.g http://10.100.137.50:8001"/>
                                                    </div>
                                                </div>
                                            </div> 
                                            <hr>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_hss_last_test">Last Test</span>
                                                        &nbsp;
                                                    </div>
                                                    <div class="col-md-10">
                                                        <?php 

                                                        if(empty($kiw_row['last_test'])) $kiw_row['last_test'] = "N/A";
                                                        else {

                                                            $kiw_row['last_test'] = sync_tolocaltime($kiw_row['last_test'], $_SESSION["timezone"]);

                                                            if(!empty($kiw_row['last_test_status'])) {
                                                                $kiw_row['last_test'] .= " <span class='badge ". (strtolower($kiw_row['last_test_status']) == "success" ? "badge-success" : "badge-danger") ."'>{$kiw_row['last_test_status']}</span></code>";

                                                            }
                                                        }
                                                        ?>
                                                        <label><?= $kiw_row['last_test']; ?></label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="text-right col-md-3">
                                                        <span data-i18n="integration_hss_last_test">&nbsp;</span>
                                                        &nbsp;
                                                    </div>
                                                    <div class="col-md-7">
                                                        <button type="button" class="btn btn-warning test-button waves-effect waves-light" data-i18n="integration_hss_test">Test Connect HSS </button>&nbsp;

                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <input type="hidden" name="update" value="true" />
                                </form>
                
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-primary save-button waves-effect waves-light" data-i18n="integration_social_save">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require_once "includes/include_footer.php"; ?>

<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
<script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>

<script>
</script>
