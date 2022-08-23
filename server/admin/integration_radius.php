<?php

$kiw['module'] = "Integration -> Realm";
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

$kiw_row = $kiw_db->query_first("SELECT * FROM  kiwire_int_radius WHERE tenant_id = '{$tenant_id}' LIMIT 1");

?>
<style>
    .ui-timepicker-container{ 
        z-index:1151 !important; 
    }
</style>

<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="integration_radius_title">Radius</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="integration_radius_subtitle">
                                Authentication with external radius
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <section id="css-classes" class="card">
            <div class="card-content">
                <div class="card-header pull-right">
                    <button type="button" onclick="$('#import-modal').modal()" class="btn btn-primary waves-effect waves-light import-btn-radius mr-1" style="display:none;" data-i18n="integration_radius_import">Import Radius</button>
                    <button type="button" class="btn btn-primary waves-effect waves-light create-btn-radius" data-toggle="modal" data-target="#inlineForm" data-i18n="integration_radius_add">Add Radius</button>
                </div>
                <div class="card-body">
                    <div class="card-text">

                        <div class="table-responsive">
                            <table id="itemlist" class="table table-hover table-data">
                                <thead>
                                    <tr>
                                        <th data-i18n="integration_radius_no">NO</th>
                                        <th data-i18n="integration_radius_domain">DOMAIN</th>
                                        <th data-i18n="integration_radius_host">HOST</th>
                                        <th data-i18n="integration_radius_nas_id">NAS ID</th>
                                        <th data-i18n="integration_radius_method">METHOD</th>
                                        <th data-i18n="integration_radius_profile">PROFILE</th>
                                        <th data-i18n="integration_radius_status">STATUS</th>
                                        <th data-i18n="integration_radius_action">ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- Modal -->
<div class="modal fade text-left" id="inlineForm" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">

            <form class="create-form" action="#">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel33" data-i18n="integration_radius_add_edit">Add or Edit Radius</h4>
                    <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-2">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <label data-i18n="integration_radius_enable">Enable: </label>
                                    <input type="checkbox" class="custom-control-input" name="enabled" id="enabled" value="y" class="toggle" />
                                    <label class="custom-control-label" for="enabled"></label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <label data-i18n="integration_radius_use_domain">Use Domain: </label>
                                    <input type="checkbox" class="custom-control-input" name="use_domain" id="use_domain" value="y" class="toggle" />
                                    <label class="custom-control-label" for="use_domain"></label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12"></div>

                        <!-- <div class="col-md-2">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <label data-i18n="">24 hours: </label>
                                    <input type="checkbox" class="custom-control-input" name="is_24" id="is_24" value="1" class="toggle" />
                                    <label class="custom-control-label" for="is_24"></label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12"></div>

                        <div class="col-md-6 time">
                            <label data-i18n="">Start Time: </label>
                            <div class="form-group">
                                <input type="text" name="start_time" id="start_time" value="" class="form-control datetime">
                            </div>
                        </div>

                        <div class="col-md-6 time">
                            <label data-i18n="">Stop Time: </label>
                            <div class="form-group">
                                <input type="text" name="stop_time" id="stop_time" value=""  class="form-control datetime">
                            </div>
                        </div> -->

                        <div class="col-md-6">
                            <label data-i18n="integration_radius_domain2">Domain: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="domain" id="domain" value="" class="form-control" placeholder="eg: test.domain.com" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="integration_radius_host2">Host: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="host" id="host" value="" class="form-control" placeholder="Your host" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="integration_radius_secret">Secret: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="secret" id="secret" value="" class="form-control" placeholder="password" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="integration_radius_nas_identifier">NAS Identifier: </label>
                            <div class="form-group">
                                <input type="text" name="nasid" id="nasid" value="" class="form-control">
                            </div>
                        </div>
                        
                        
                        
                        <div class="col-md-6">
                            <label data-i18n="integration_radius_link">Link Profile: </label>
                            <div class="form-group">
                                <select name="forward_profile" id="forward_profile" class="select2 form-control">
                                    <option value="link" data-i18n="integration_radius_link_local">Link with local profile</option>
                                    <option value="create" data-i18n="integration_radius_carry">Carry forward from host</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="profile-space">
                                <label data-i18n="integration_radius_local2">Local Profile</label>
                                <div class="form-group">
                                    <select name="profile" id="profile" class="select2 form-control">
                                        <option value="none" data-i18n="integration_radius_none">None</option>
                                        <?php
                                        $kiw_temps = $kiw_db->fetch_array("SELECT name FROM kiwire_profiles WHERE tenant_id = '{$_SESSION['tenant_id']}'");
                                        foreach ($kiw_temps as $kiw_temp) {
                                            echo "<option value='{$kiw_temp['name']}'>{$kiw_temp['name']}</option>\n";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        
                        <div class="col-md-6">
                            <label data-i18n="integration_radius_validity">Validity (Days): </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="validity" id="validity" value="" class="form-control" placeholder="eg: 365" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="integration_radius_keyword">Keyword: </label>
                            <div class="form-group">
                                <input type="text" name="keyword_str" id="keyword_str" value="" class="form-control">
                            </div>
                        </div>
                        
                        
                        <div class="col-md-6">
                            <label data-i18n="integration_radius_data_type">Data Type: </label>
                            <div class="form-group">
                                <select name="data_type" id="data_type" class="select2 form-control" data-style="btn-default" tabindex="-98">
                                    <option value="int" data-i18n="integration_radius_int">Integer</option>
                                    <option value="string" data-i18n="integration_radius_string">String</option>
                                    <option value="addr" data-i18n="integration_radius_ip_addr">IP Address</option>>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="integration_radius_zone_restriction">Zone Restriction: </label>
                            <div class="form-group">
                                <select name="allowed_zone" id="allowed_zone" class="select2 form-control" data-style="btn-default" tabindex="-98">
                                    <option value="none" data-i18n="integration_radius_none2">None</option>
                                    <?php
                                    $rows = "SELECT * FROM kiwire_allowed_zone WHERE tenant_id = '{$tenant_id}'";
                                    $rows = $kiw_db->fetch_array($rows);
                                    foreach ($rows as $record) {
                                        echo "<option value ='{$record['name']}'>{$record['name']}</option> \n";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <input type="hidden" id="reference" name="reference" value="">
                    <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="integration_radius_cancel">Cancel</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-create" data-i18n="integration_radius_create">Create</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-update" data-i18n="integration_radius_update">Update</button>
                </div>

                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">

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