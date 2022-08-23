<?php

$kiw['module'] = "Integration -> Social";
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


$kiw_fields = @file_get_contents(dirname(__FILE__, 2) . "/custom/{$_SESSION['tenant_id']}/data-mapping.json");

if (empty($kiw_fields)) {

  $kiw_fields = @file_get_contents(dirname(__FILE__, 2) . "/user/templates/kiwire-data-mapping.json");
}

if (!empty($kiw_fields)) $kiw_fields = json_decode($kiw_fields, true);

$kiw_count = 1;


$kiw_row = $kiw_db->query_first("SELECT * FROM kiwire_int_social WHERE tenant_id = '$tenant_id' LIMIT 1");

if (empty($kiw_row)) {

  $kiw_db->query("INSERT INTO kiwire_int_social(tenant_id) VALUE('{$tenant_id}')");
}

$kiw_row['data'] = explode(",", $kiw_row['data']);

?>

<div class="content-wrapper">
  <div class="content-header row">
    <div class="content-header-left col-12 mb-2">
      <div class="row breadcrumbs-top">
        <div class="col-12">
          <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="integration_social_title">Social Network</h2>
          <div class="breadcrumb-wrapper">
            <ol class="breadcrumb">
              <li class="breadcrumb-item active" data-i18n="integration_social_subtitle">
                Manage social network connection
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
                <button type="button" class="btn btn-info waves-effect waves-light" data-i18n="integration_social_guide" data-toggle="modal" data-target="#modal-guide">Guide</button>&nbsp;
                <button type="button" class="btn btn-primary save-button waves-effect waves-light" data-i18n="integration_social_save">Save</button>
              </div>
              <div class="card-body">
                <form id="update-form" class="form-horizontal" method="post">

                  <br><br><br>

                  <div class="tab-content">
                    <div class="tab-pane active" id="general" aria-labelledby="general-tab" role="tabpanel">


                      <div class="col-12">
                        <div class="form-group row">
                          <div class="col-md-2">
                            <span data-i18n="integration_social_enable_fb">Enable Facebook Login</span>
                          </div>
                          <div class="col-md-10">
                            <div class="custom-control custom-switch custom-control-inline col-md-8">
                              <input type="checkbox" class="custom-control-input" name=facebook_en id=facebook_en <?= ($kiw_row['facebook_en'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                              <label class="custom-control-label" for="facebook_en"></label>
                            </div>
                          </div>

                        </div>
                      </div>

                      <div class="col-12">
                        <div class="form-group row">
                          <div class="col-md-2">
                            <span data-i18n="integration_social_fb_page">Facebook Page</span>
                          </div>
                          <div class="col-md-10">
                            <input type="text" name=facebook_page id=facebook_page value="<?= $kiw_row['facebook_page']; ?>" class="form-control" />
                            <span style="padding:10px;" data-i18n="integration_social_fb_page_desc">Facebook page, eg : https://www.facebook.com/xxxxx it will be xxxxx</span>
                          </div>
                        </div>
                      </div>

                      <div class="col-12">
                        <div class="form-group row">
                          <div class="col-md-2">
                            <span data-i18n="integration_social_enable_twitter">Enable Twitter Login</span>
                          </div>
                          <div class="col-md-10">
                            <div class="custom-control custom-switch custom-control-inline col-md-8">
                              <input type="checkbox" class="custom-control-input" name=twitter_en id=twitter_en <?= ($kiw_row['twitter_en'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                              <label class="custom-control-label" for="twitter_en"></label>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="col-12">
                        <div class="form-group row">
                          <div class="col-md-2">
                            <span data-i18n="integration_social_twitter_page">Twitter Page</span>
                          </div>
                          <div class="col-md-10">
                            <input type="text" name=twitter_page id=twitter_page value="<?= $kiw_row['twitter_page']; ?>" class="form-control" />
                          </div>
                        </div>
                      </div>

                      <div class="col-12">
                        <div class="form-group row">
                          <div class="col-md-2">
                            <span data-i18n="integration_social_enable_insta">Enable Instagram Login</span>
                          </div>
                          <div class="col-md-10">
                            <div class="custom-control custom-switch custom-control-inline">
                              <input type="checkbox" class="custom-control-input" name=instagram_en id=instagram_en <?= ($kiw_row['instagram_en'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                              <label class="custom-control-label" for="instagram_en"></label>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="col-12">
                        <div class="form-group row">
                          <div class="col-md-2">
                            <span data-i18n="integration_social_enable_kakao">Enable KakaoTalk Login</span>
                          </div>
                          <div class="col-md-10">
                            <div class="custom-control custom-switch custom-control-inline">
                              <input type="checkbox" class="custom-control-input" name=kakao_en id=kakao_en <?= ($kiw_row['kakao_en'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                              <label class="custom-control-label" for="kakao_en"></label>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="col-12">
                        <div class="form-group row">
                          <div class="col-md-2">
                            <span data-i18n="integration_social_enable_vk">Enable VK Login</span>
                          </div>
                          <div class="col-md-10">
                            <div class="custom-control custom-switch custom-control-inline">
                              <input type="checkbox" class="custom-control-input" name=vk_en id=vk_en <?= ($kiw_row['vk_en'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                              <label class="custom-control-label" for="vk_en"></label>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="col-12">
                        <div class="form-group row">
                          <div class="col-md-2">
                            <span data-i18n="integration_social_enable_line">Enable Line Login</span>
                          </div>
                          <div class="col-md-10">
                            <div class="custom-control custom-switch custom-control-inline">
                              <input type="checkbox" class="custom-control-input" name=line_en id=line_en <?= ($kiw_row['line_en'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                              <label class="custom-control-label" for="line_en"></label>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="col-12">
                        <div class="form-group row">
                          <div class="col-md-2">
                            <span data-i18n="integration_social_enable_zalo">Enable Zalo Login</span>
                          </div>
                          <div class="col-md-10">
                            <div class="custom-control custom-switch custom-control-inline">
                              <input type="checkbox" class="custom-control-input" name=zalo_en id=zalo_en <?= ($kiw_row['zalo_en'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                              <label class="custom-control-label" for="zalo_en"></label>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="col-12">
                        <div class="form-group row">
                          <div class="col-md-2">
                            <span data-i18n="integration_social_enable_wechat">Enable WeChat Login</span>
                          </div>
                          <div class="col-md-10">
                            <div class="custom-control custom-switch custom-control-inline">
                              <input type="checkbox" class="custom-control-input" name=wechat_en id=wechat_en <?= ($kiw_row['wechat_en'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                              <label class="custom-control-label" for="wechat_en"></label>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="col-12">
                        <div class="form-group row">
                          <div class="col-md-2">
                            <span data-i18n="integration_social_enable_linkedin">Enable LinkedIn Login</span>
                          </div>
                          <div class="col-md-10">
                            <div class="custom-control custom-switch custom-control-inline">
                              <input type="checkbox" class="custom-control-input" name=linkedin_en id=linkedin_en <?= ($kiw_row['linkedin_en'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                              <label class="custom-control-label" for="linkedin_en"></label>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="col-12">
                        <div class="form-group row">
                          <div class="col-md-2">
                            <span data-i18n="integration_social_link">Link With Profile</span>
                          </div>
                          <div class="col-md-10">
                            <select name="profile" id="profile" class="select2 form-control" data-style="btn-default" tabindex="-98">
                              <option value="none" data-i18n="integration_social_none">None</option>
                              <?
                              $sql = "select * from kiwire_profiles where tenant_id = '{$tenant_id}' group by name";
                              $rows = $kiw_db->fetch_array($sql);
                              foreach ($rows as $record) {
                                $selected = "";
                                if ($record['name'] == $kiw_row['profile']) $selected = 'selected="selected"';
                                echo "<option value =\"$record[name]\" $selected> $record[name]</option> \n";
                              }
                              ?>

                            </select>

                          </div>
                        </div>
                      </div>

                      <div class="col-12">
                        <div class="form-group row">
                          <div class="col-md-2">
                            <span data-i18n="integration_social_zone_restriction">Zone Restriction</span>
                          </div>
                          <div class="col-md-10">
                            <select name="allowed_zone" id="allowed_zone" class="select2 form-control" data-style="btn-default" tabindex="-98">
                              <option value="none" data-i18n="integration_social_none2">None</option>
                              <?
                              $sql = "select * from kiwire_allowed_zone where tenant_id = '{$tenant_id}' group by name order by name";
                              $rows = $kiw_db->fetch_array($sql);
                              foreach ($rows as $record) {
                                $selected = "";
                                if ($record['name'] == $kiw_row['allowed_zone']) $selected = 'selected="selected"';
                                echo "<option value =\"$record[name]\" $selected> $record[name]</option> \n";
                              }
                              ?>
                            </select>


                          </div>
                        </div>

                      </div>

                      <div class="col-12">
                        <div class="form-group row">
                          <div class="col-md-2">
                            <span data-i18n="integration_social_add_field">Additional Fields</span>
                          </div>
                          <div class="col-10">

                            <select name="data[]" id="data" class="select2 form-control" multiple="multiple">

                              <?php foreach ($kiw_fields as $kiw_field) { ?>

                                <div data-field-info="<?= $kiw_field['field'] ?>">

                                  <?php if ($kiw_field['display'] != "[empty]") { ?>

                                    <option value="<?= $kiw_field['variable'] ?>" <?= (in_array($kiw_field['variable'], $kiw_row['data']) ? "selected" : "") ?>> <?= $kiw_field['display'] ?></option>

                                  <?php } ?>

                                </div>

                                <?php $kiw_count++; ?>

                              <?php } ?>

                            </select>

                          </div>
                        </div>
                      </div>

                    </div>

                  </div>

                  <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                  <input type="hidden" name="update" value="true" />

                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>




<!-- Modal -->
<div class="modal fade" id="modal-guide" tabindex="-1" role="dialog" aria-labelledby="modal-guideLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Help</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <ol>
          <li><b>Switch on</b> "Social Network" toggle button.</li>
          <li>Click <b>Save</b> button to store the record.</li>
          <li><b>Download and Copy </b> script below to your device.</li>
        </ol>
        <hr width="80%">
        <ul>
          <li>Mikrotik <button class="btn btn-sm btn-success btn-script-download" data-dev="Mikrotik"><i class="fa fa-download"></i> Download </button></li>
        </ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<?php require_once "includes/include_footer.php"; ?>


