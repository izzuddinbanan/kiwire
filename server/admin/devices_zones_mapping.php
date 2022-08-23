<?php

$kiw['module'] = "Device -> Zone";
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

?>


<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Zones Mapping</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                Manage zone
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
                <div class="card-body">
                    <div class="card-text">

                        <button type="button" class="btn btn-primary waves-effect waves-light create-btn-zone pull-right mb-25 ml-1" data-toggle="modal" data-target="#inlineForm" data-i18n="btn_add_one">Add Zone</button>
                        <button type="button" class="btn btn-primary waves-effect waves-light import-btn-zone pull-right mb-25" data-toggle="modal" data-target="#import-modal" style="display:none;" data-i18n="btn_import_zone">Import Zone</button>

                        <div class="table-responsive">
                            <table id="itemlist" class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="thead_no">No</th>
                                        <th data-i18n="thead_name">Name</th>
                                        <th data-i18n="thead_link_user">Link To User</th>
                                        <th data-i18n="thead_limit">Limit Session</th>
                                        <th data-i18n="thead_priority">Priority</th>
                                        <th data-i18n="thead_journey">Journey</th>
                                        <th data-i18n="thead_status">Status</th>
                                        <th data-i18n="thead_action">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                  <th data-i18n="tbody_loading">
                                    Loading...
                                  </th>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </div>
</div>


<div class="modal fade text-left" id="inlineForm" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel33" data-i18n="modal_1_title">Add or Edit Zone</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="create-form" action="#">

                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">

                <div class="modal-body">

                    <div class="row">

                        <div class="col-md-12">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <label data-i18n="modal_1_enable">Enable: </label>
                                        <input type="checkbox" class="custom-control-input" name="status" id="status" <?= ($kiw_row['status'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                    <label class="custom-control-label" for="status"></label>
                                </div>
                            </div>
                        </div>
                    
                        <div class="col-md-6">
                            <label data-i18n="modal_1_zone_name">Zone Name: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="name" id="name" value="" class="form-control" pattern="[a-zA-Z0-9\s\-\_]+" title="Special characters are not allowed." placeholder="eg: Demo" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_auto_login">Link To User: </label>
                            <div class="form-group">
                                <input type="text" name="auto_login" id="auto_login" value="" placeholder="username" class="form-control">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_simultaneous">Limit Connected User: </label>
                            <div class="form-group">
                                <input type="number" name="simultaneous" id="simultaneous" value="" placeholder="integer" class="form-control" />
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_priority">Priority: </label>
                            <div class="form-group">
                                <input type="number" name="priority" id="priority" value="" class="form-control" placeholder="integer" />
                                <label style="font-size: smaller; padding: 10px;" data-i18n="modal_1_priority_label">Highest number will be detected first</label>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_force_profile">Force Profile: </label>
                            <div class="form-group">

                                <select name="force_profile" id="force_profile" class="select2 form-control">
                                    <option value="" data-i18n="force_profile_opt_none">None</option>
                                    <?php

                                    $kiw_temp = "SELECT name FROM kiwire_profiles WHERE tenant_id = '{$tenant_id}' LIMIT 100";
                                    $rows = $kiw_db->fetch_array($kiw_temp);
                                    
                                    foreach ($rows as $record) {
                                        echo "<option value =\"$record[name]\" $selected> $record[name]</option> \n";
                                    }
                                    
                                    ?>
                                </select>
                                
                            </div>
                        </div>
                    
                        <div class="col-md-6">
                            <label data-i18n="modal_1_force_zone">Force Zone Restriction: </label>
                            <div class="form-group">

                                <select name="force_zone" id="force_zone" class="select2 form-control">
                                    <option value="" data-i18n="force_zone_opt_none">None</option>
                                    <?php

                                    $kiw_temp = "SELECT * FROM kiwire_allowed_zone WHERE tenant_id = '{$tenant_id}' LIMIT 100";
                                    $rows = $kiw_db->fetch_array($kiw_temp);

                                    foreach ($rows as $record) {
                                        echo "<option value =\"$record[name]\" $selected> $record[name]</option> \n";
                                    }

                                    ?>
                                </select>

                            </div>
                        </div>

                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_label_profile">Journey: </label>
                            <div class="form-group">
                                <fieldset class="form-group">
                                    <select class="select2 form-control" name="journey" id="journey">

                                        <?php

                                        $kiw_row = $kiw_db->fetch_array("SELECT journey_name FROM kiwire_login_journey WHERE tenant_id = '{$tenant_id}' LIMIT 100");

                                        foreach ($kiw_row as $record) {

                                            echo "<option value='{$record['journey_name']}'>{$record['journey_name']}</option> \n";
                                        }
                                        ?>

                                    </select>
                                </fieldset>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <input type="hidden" id="reference" name="reference" value="">
                    <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="modal_1_footer_btn_cancel">Cancel</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-create" data-i18n="modal_1_footer_btn_create">Create</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-update" data-i18n="modal_1_footer_btn_update">Update</button>

                </div>

            </form>

        </div>
    </div>
</div>



<div class="modal fade text-left" id="rules-modal" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content" style="height: 600px;">

            <div class="modal-header">
                <h4 class="modal-title" id="rules-modal-title" data-i18n="modal_2_title">Zone Rules</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" style="overflow: auto;">

                <table class="table table-bordered rules-list">

                    <thead class="thead-dark">
                        <tr>
                            <th data-i18n="modal_2_thead_ip">IP Address</th>
                            <th data-i18n="modal_2_thead_ipv6">IPv6 Address</th>
                            <th data-i18n="modal_2_thead_vlan">VLAN</th>
                            <th data-i18n="modal_2_thead_ssid">SSID</th>
                            <th data-i18n="modal_2_thead_id">Controller ID</th>
                            <th data-i18n="modal_2_thead_zone">Controller Zone</th>
                            <th data-i18n="modal_2_thead_action">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>


            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary waves-effect waves-light btn-rules-add" data-i18n="modal_2_footer_btn_new_row">Add New Row</button>
                <button type="button" class="btn btn-primary waves-effect waves-light btn-rules-close" data-dismiss="modal" data-i18n="modal_2_footer_btn_close">Close</button>
            </div>

        </div>
    </div>
</div>


<!-- Import Modal -->
<div class="modal fade text-left" id="import-modal" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <form class="import-file" action="">

                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel33" data-i18n="modal_3_title">Import Zone</h4>
                    <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div style="margin-bottom: 10px;"><span class="" data-i18n="modal_3_span_1">Only file that using our template will be processed.</span> <a href="../custom/zone_import_template.csv"><span class="flang-form_3_desc_2" data-i18n="modal_3_span_2">Click here to download sample template.</span></a></div>
                    <input type="file" name="csv-file" accept=".csv" />
                    <div class="progress" style="display: none; margin-top: 25px;" id="import-progress">
                        <div class="progress-bar progress-bar-primary progress-bar-striped active" role="progressbar" aria-valuenow="82" aria-valuemin="0" aria-valuemax="100" style="width: 100%;">
                            <span class="sr-only"></span>
                        </div>
                    </div>
                    <div id="import-status" style="display: none; margin-top: 25px;">&nbsp;</div>
                </div>

                <div class="modal-footer">

                    <input type="hidden" id="reference" name="reference" value="">
                    <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="modal_3_footer_btn_cancel">Cancel</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-import" id="exitBtn" onclick="importNASCSV()" data-i18n="modal_3_footer_btn_import">Import</button>

                </div>

            </form>
        </div>
    </div>
</div>



<?php
require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";
?>
