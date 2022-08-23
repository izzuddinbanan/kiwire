<?php

$kiw['module'] = "Integration -> PMS";
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

$kiw_row = $kiw_db->query_first("SELECT * FROM  kiwire_int_pms WHERE tenant_id = '{$tenant_id}' LIMIT 1");

if (empty($kiw_row)) $kiw_db->query("INSERT INTO kiwire_int_pms(tenant_id) VALUE('{$tenant_id}')");



?>


<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="integration_pms_title">PMS</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="integration_pms_subtitle">
                                Property management interface
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
                            <div class="card-header">
                                <button type="button" class="btn btn-primary waves-effect waves-light create-btn-vip" style="display:none;" data-i18n="integration_pms_add_vip_code">Add VIP Code</button>

                            </div>
                            <div class="card-body">

                                <form action="" method="post" class="update-form">



                                    <ul class="nav nav-tabs" role="tablist">

                                        <li class="nav-item">
                                            <a class="nav-link active" id="main-tab" data-toggle="tab" href="#main" aria-controls="main" role="tab" aria-selected="true" data-i18n="integration_pms_main">MAIN</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="vip_code-tab" data-toggle="tab" href="#vip_code" aria-controls="vip_code" role="tab" aria-selected="false" data-i18n="integration_pms_vip_code">VIP CODE</a>
                                        </li>

                                    </ul>

                                    <br><br>

                                    <div class="tab-content">

                                        <div class="tab-pane active" id="main" aria-labelledby="main-tab" role="tabpanel">


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_pms_enable">Enable</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <div class="custom-control custom-switch custom-control-inline">
                                                            <input type="checkbox" class="custom-control-input" name=enabled id=enabled <?= ($kiw_row['enabled'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="enabled"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
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
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_pms_type">PMS Type</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <select class="select2 form-control change-provider" name="pms_type" id="pms_type" data-style="btn-default">
                                                            <optgroup label="API">
                                                                <option value="json" <?= ($kiw_row['pms_type'] == "json" ? "selected" : ""); ?>>JSON</option>
                                                            </optgroup>
                                                            <optgroup label="Fidelio">
                                                                <option value="opera" <?= ($kiw_row['pms_type'] == "opera" ? "selected" : ""); ?>>Oracle Opera</option>
                                                                <option value="infor" <?= ($kiw_row['pms_type'] == "infor" ? "selected" : ""); ?>>Infor HMS</option>
                                                                <option value="oasis" <?= ($kiw_row['pms_type'] == "oasis" ? "selected" : ""); ?>>Oasis</option>
                                                            </optgroup>
                                                            <optgroup label="Cloud">
                                                                <option value="idb" <?= ($kiw_row['pms_type'] == "idb" ? "selected" : ""); ?>>IDB</option>
                                                            </optgroup>
                                                            <optgroup label="POS">
                                                                <option value="ezee" <?= ($kiw_row['pms_type'] == "ezee" ? "selected" : ""); ?>>eZee</option>
                                                            </optgroup>
                                                            <optgroup label="Socket">
                                                                <option value="winpac" <?= ($kiw_row['pms_type'] == "winpac" ? "selected" : ""); ?>>JDS : Winpac</option>
                                                                <option value="hospital" <?= ($kiw_row['pms_type'] == "hospital" ? "selected" : ""); ?>>FCS : Hospitality</option>
                                                                <option value="pmsi" <?= ($kiw_row['pms_type'] == "pmsi" ? "selected" : ""); ?>>3CJ : PMSI</option>
                                                            </optgroup>
                                                            <optgroup label="Others">
                                                                <option value="rhealta" <?= ($kiw_row['pms_type'] == "rhealta" ? "selected" : ""); ?>>Rhealta</option>
                                                            </optgroup>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12 ezee-remove">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_pms_host">PMS Host</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" name="pms_host" id="pms_host" value="<?= $kiw_row['pms_host']; ?>" class="form-control">
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12 idb-remove">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_pms_port">PMS Port</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" name="pms_port" id="pms_port" value="<?= $kiw_row['pms_port']; ?>" class="form-control">
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12 idb-fields">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_pms_project">PMS Project ID</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" name="pms_project" id="pms_project" value="<?= $kiw_row['pms_project']; ?>" class="form-control">
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12 idb-fields ezee-fields">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_pms_token">PMS Token</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" name="pms_token" id="pms_token" value="<?= $kiw_row['pms_token']; ?>" class="form-control">
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_pms_guest_pass_setting">Guest Password Setting</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <select name="pass_mode" id="pass_mode" class="select2 form-control" data-style="btn-default">
                                                            <option value="0" <?= ($kiw_row['pass_mode'] == "0" ? "selected" : "") ?>><span data-i18n="integration_pms_predefine_pass">Use Predefine Password</span></option>
                                                            <option value="1" <?= ($kiw_row['pass_mode'] == "1" ? "selected" : "") ?>><span data-i18n="integration_pms_no_pass">Room Number As Password</span></option>
                                                            <option value="2" <?= ($kiw_row['pass_mode'] == "2" ? "selected" : "") ?>><span data-i18n="integration_pms_firstname">Guest First Name</span></option>
                                                            <option value="3" <?= ($kiw_row['pass_mode'] == "3" ? "selected" : "") ?>><span data-i18n="integration_pms_lname">Guest Last Name</span></option>
                                                            <option value="4" <?= ($kiw_row['pass_mode'] == "4" ? "selected" : "") ?>> <span data-i18n="integration_pms_fullname">Guest Full Name</span></option>
                                                            <option value="5" <?= ($kiw_row['pass_mode'] == "5" ? "selected" : "") ?>> <span data-i18n="integration_pms_generated">System Generated Password</span></option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12" id="pass_first_login" <?= ($kiw_row['pass_mode'] == "2" || $kiw_row['pass_mode'] == "3" || $kiw_row['pass_mode'] == "4"  ? "style='display:block'" : "style='display:none'") ?>>
                                            <!-- <div class="col-12" id="pass_first_login" style="display:none;"> -->
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_pms_use_first_login_only">Use on First Login Only</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <li class="d-inline-block mr-2">
                                                            <fieldset>
                                                                <div class="vs-checkbox-con vs-checkbox-primary">
                                                                    <input type="checkbox" name="use_first_login" id="use_first_login" <?= ($kiw_row['use_first_login_only']  == "y") ? 'checked value="true"' : '' ?> value="y">
                                                                    <span class="vs-checkbox vs-checkbox-lg">
                                                                        <span class="vs-checkbox--check">
                                                                            <i class="vs-icon feather icon-check"></i>
                                                                        </span>
                                                                    </span>
                                                                </div>
                                                            </fieldset>
                                                        </li>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_pms_predefine_pass2">Predefine Password</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" name="pass_predefined" id="pass_predefined" value="<?= $kiw_row['pass_predefined']; ?>" class="form-control" />
                                                        <div style="font-size: smaller; padding: 10px;" data-i18n="integration_pms_if">If password setting set to predefine</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_pms_match">Password match percentage</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" name="pass_percentage" id="pass_percentage" value="<?= $kiw_row['pass_percentage']; ?>" class="form-control" />
                                                        <div style="font-size: smaller; padding: 10px;" data-i18n="integration_pms_lower">Lower value will allow more flexible password match</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_pms_zone_restriction">Zone Restriction</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <select name="allowed_zone" id="allowed_zone" class="select2 form-control" data-style="btn-default">
                                                            <option value="none" data-i18n="integration_pms_none">None</option>
                                                            <?php

                                                            $rows = $kiw_db->fetch_array("SELECT SQL_CACHE * FROM kiwire_allowed_zone WHERE tenant_id = '{$tenant_id}' GROUP BY name ORDER BY name");

                                                            foreach ($rows as $record) {

                                                                echo "<option value ='{$record['name']}' " . ($kiw_row['zone_allowed'] == $record['name'] ? "selected" : "") . "> {$record['name']}</option>";
                                                            }

                                                            ?>
                                                        </select>

                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_pms_profile_rules">VIP Field</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <select name="vip_match" id="vip_match" class="select2 form-control">
                                                            <option value="">None</option>
                                                            <option value="room_no" <?= ($kiw_row['vip_match'] == "room_no" ? "selected" : "") ?>>Room Number</option>
                                                            <option value="guest_id" <?= ($kiw_row['vip_match'] == "guest_id" ? "selected" : "") ?>>Guest ID</option>
                                                            <option value="guest_vip" <?= ($kiw_row['vip_match'] == "guest_vip" ? "selected" : "") ?>>VIP Code</option>
                                                            <option value="language" <?= ($kiw_row['vip_match'] == "language" ? "selected" : "") ?>>Language</option>
                                                            <option value="a0" <?= ($kiw_row['vip_match'] == "a0" ? "selected" : "") ?>>Field A0</option>
                                                            <option value="a1" <?= ($kiw_row['vip_match'] == "a1" ? "selected" : "") ?>>Field A1</option>
                                                            <option value="a2" <?= ($kiw_row['vip_match'] == "a2" ? "selected" : "") ?>>Field A2</option>
                                                            <option value="a3" <?= ($kiw_row['vip_match'] == "a3" ? "selected" : "") ?>>Field A3</option>
                                                            <option value="a4" <?= ($kiw_row['vip_match'] == "a4" ? "selected" : "") ?>>Field A4</option>
                                                            <option value="a5" <?= ($kiw_row['vip_match'] == "a5" ? "selected" : "") ?>>Field A5</option>
                                                            <option value="a6" <?= ($kiw_row['vip_match'] == "a6" ? "selected" : "") ?>>Field A6</option>
                                                            <option value="a7" <?= ($kiw_row['vip_match'] == "a7" ? "selected" : "") ?>>Field A7</option>
                                                            <option value="a8" <?= ($kiw_row['vip_match'] == "a8" ? "selected" : "") ?>>Field A8</option>
                                                            <option value="a9" <?= ($kiw_row['vip_match'] == "a9" ? "selected" : "") ?>>Field A9</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12 pms-credential">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_pms_credential_string" id="label_name">Credential String</span> <span class="text-danger">*</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" name="credential_string" id="credential_string" value="<?= $kiw_row['credential_string']; ?>" class="form-control">
                                                        <div style="font-size: smaller; padding: 10px;" data-i18n="integration_pms_required">Required if using remote agent</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 pms-keys">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_pms-keys">Keys</span> <span class="text-danger">*</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="password" name="pms_keys" id="pms_keys" value="" class="form-control">
                                                        <div style="font-size: smaller; padding: 10px;" data-i18n="integration_pms_required">Required if using remote agent</div>
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
                                                        <button type="button" class="btn btn-warning btn-db-swap">Database Synchronization</button>
                                                    </div>
                                                </div>
                                            </div>


                                        </div>


                                        <div class="tab-pane" id="vip_code" aria-labelledby="vip_code-tab" role="tabpanel">

                                            <div class="table-responsive">
                                                <table id="itemlist" class="table table-hover table-data">
                                                    <thead>
                                                        <tr class="text-uppercase">
                                                            <th data-i18n="integration_pms_no">No</th>
                                                            <th data-i18n="integration_pms_vip_code2">VIP Code</th>
                                                            <th data-i18n="integration_pms_overiding_profile">Overiding Profile</th>
                                                            <th data-i18n="integration_pms_overiding_price">Overiding Price (MYR)</th>
                                                            <th data-i18n="integration_pms_action">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <th data-i18n="integration_pms_loading">
                                                            Loading..
                                                        </th>
                                                    </tbody>
                                                </table>
                                            </div>

                                        </div>
                                    </div>
                                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                                </form>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-primary save-button waves-effect waves-light" data-i18n="integration_pms_save">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>


<div class="modal fade text-left" id="inlineForm" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel33" data-i18n="integration_pms_add_edit">Add or Edit VIP Code</h4>

                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="create-form" action="#">

                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">

                <div class="modal-body">

                    <label data-i18n="integration_pms_vip_code3">VIP Code: </label> <span class="text-danger">*</span>
                    <div class="form-group">
                        <input type="text" name="code" id="code" value="" class="form-control" placeholder="code" required>
                    </div>

                    <label data-i18n="integration_pms_overiding_profile2">Overiding Profile: </label>
                    <div class="form-group">

                        <select name="profile" id="profile" class="select2 form-control" data-style="btn-default">
                            <option value=""></option>
                            <?php

                            $row = $kiw_db->fetch_array("SELECT DISTINCT(name) AS profile FROM kiwire_profiles WHERE tenant_id = '{$tenant_id}'");

                            foreach ($row as $profile) {

                                echo "<option value='{$profile['profile']}'>{$profile['profile']}</option>";
                            }

                            ?>
                        </select>
                    </div>

                    <label data-i18n="integration_pms_overiding_price2">Overiding Price (MYR): </label>
                    <div class="form-group">
                        <input type="text" name="price" id="price" value="" placeholder="eg: 100.00" class="form-control">
                    </div>

                </div>

                <div class="modal-footer">

                    <input type="hidden" id="reference" name="reference" value="">
                    <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="integration_pms_cancel">Cancel</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-create" data-i18n="integration_pms_create">Create</button>

                </div>
            </form>
        </div>
    </div>
</div>

<?php

require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";

?>

<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
<script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>