<?php

$kiw['module'] = "Campaign -> Company Apps";
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

$kiw_row = $kiw_db->query_first("SELECT * FROM kiwire_campaign_apps WHERE tenant_id = '{$tenant_id}' LIMIT 1");

if (empty($kiw_row)){

    $kiw_db->query("INSERT INTO kiwire_campaign_apps (tenant_id) VALUE ('{$_SESSION['tenant_id']}')");

}


?>


<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Smart Banner</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                Banner for mobile apps
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

                                <form class="update-form" enctype="multipart/form-data">

                                    <ul class="nav nav-tabs" role="tablist">

                                        <li class="nav-item">
                                            <a class="nav-link active" id="main-tab" data-toggle="tab" href="#main" aria-controls="main" role="tab" aria-selected="true" data-i18n="form_title">APPS INFORMATION</a>
                                        </li>

                                    </ul>

                                    <br><br>
                                    <div class="tab-content">

                                        <div class="tab-pane active" id="main" aria-labelledby="main-tab" role="tabpanel">

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-3">
                                                        <span data-i18n="form_span_enable">Enable</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <div class="custom-control custom-switch custom-control-inline">
                                                            <input type="checkbox" class="custom-control-input" name="status" id="status" <?= ($kiw_row['status'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="status"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-3">
                                                        <span data-i18n="form_span_upload_logo">Upload Logo</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                      <div class="custom-file">
                                                          <input type="file" name="logo" class="custom-file-input" id="logo" onchange="showImage(this);" data-maxfilesize="1000000">
                                                          <label class="custom-file-label" for="logo" data-i18n="form_choose_file">Choose file</label>
                                                      </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-3">
                                                        <span data-i18n="form_span_current_logo">Current Logo</span>
                                                    </div>
                                                    <div class="col-md-9" id="current_logo_label">

                                                        <?php

                                                        $image_avail = false;

                                                        foreach (array("png", "jpeg", "jpg", "gif") as $ext){

                                                            if (file_exists( dirname(__FILE__, 2) . "/custom/{$tenant_id}/images/banner-logo-{$tenant_id}.{$ext}") == true){

                                                                $image_avail = true;

                                                                echo "<img id='banner_logo' src='/custom/{$tenant_id}/images/banner-logo-{$tenant_id}.{$ext}' style='max-width: 300px; max-height: 300px;' />";

                                                                break;

                                                            }

                                                        }

                                                        if ($image_avail == false){

                                                            echo "<img id='banner_logo' src='' style='max-width: 300px; max-height: 300px;' />";
                                                            echo "<span class='badge badge-warning badge-md badge-not-upload' style='font-size: 11px;'>No logo has been uploaded</span>";

                                                        }


                                                        ?>

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-3">
                                                        <span data-i18n="form_span_title">Title</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <input type="text" name="app_title" id="app_title" value="<? echo $kiw_row['app_title']; ?>" class="form-control col-11" placeholder="eg: Download Apps"/>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-3">
                                                        <span data-i18n="form_span_author">Author</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <input type="text" name="app_author" id="app_author" value="<? echo $kiw_row['app_author']; ?>" class="form-control col-11" placeholder="eg: Synchroweb"/>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-3">
                                                        <span data-i18n="form_span_price">Price</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <input type="text" name="app_price" id="app_price" value="<? echo $kiw_row['app_price']; ?>" class="form-control col-11"/>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-3">
                                                        <span data-i18n="form_span_playstore">Google Playstore Url</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <input type="text" name="app_playstore_url" id="app_playstore_url" value="<? echo $kiw_row['app_playstore_url']; ?>" class="form-control col-11" placeholder="eg: https://play.google.com/store/apps/details?id=<package_name>"/>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-3">
                                                        <span data-i18n="form_span_appstore">Apple App Store Url</span>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <input type="text" name="app_appstore_url" id="app_appstore_url" value="<? echo $kiw_row['app_appstore_url']; ?>" class="form-control col-11" placeholder="eg: http://apps.apple.com/<country>/app/<app–name>/id<store-ID>"/>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                    </div>

                                    <input type="hidden" name="update" value="true"/>
                                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                                </form>
                            </div>
                            <div class="card-footer">
                               <button type="button" class="btn btn-primary save-button waves-effect waves-light" data-i18n="button_save">Save</button>
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
