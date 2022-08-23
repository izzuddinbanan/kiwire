<?php

$kiw['module'] = "Device -> Monitoring -> Rules";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Device Monitoring - Alert Rules</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                List of rules
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

                      <button type="button" class="btn btn-primary waves-effect waves-light create-btn-rules pull-right" data-toggle="modal" data-target="#inlineForm" data-i18n="btn_add_rules">Add Rules</button>
                      <button type="button" class="btn btn-primary waves-effect waves-light import-btn-rules pull-right mr-1" data-toggle="modal" data-target="#import-modal" style="display:none;" data-i18n="btn_import_rules">Import Rules</button>

                        <div class="table-responsive">
                            <table id="itemlist" class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="thead_no">No</th>
                                        <th data-i18n="thead_name">Name</th>
                                        <th data-i18n="thead_desc">Description</th>
                                        <th data-i18n="thead_last_update">Last Updated</th>
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


<!-- Modal -->
<div class="modal fade text-left" id="inlineForm" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel33" data-i18n="modal_1_title">Add or Edit Rules</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="create-form" action="#">

                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                
                <div class="modal-body">

                    <label data-i18n="modal_1_name">Name: </label> <span class="text-danger">*</span>
                    <div class="form-group">
                        <input type="text" name="rules_name" id="rules_name" value="" class="form-control" required>
                    </div>

                    <label data-i18n="modal_1_mib">MIB: </label>
                    <div class="form-group">

                      <select name="mib" id="mib" class="select2 form-control">
                          <option value="NULL" data-i18n="mib_opt_none">None</option>
                          <?php $temp_opt = $kiw_db->fetch_array("SELECT `mib_name` FROM `kiwire_nms_mib` WHERE `tenant_id`='$tenant_id' "); ?>
                          <?php foreach ($temp_opt as $indexkey => $e_to): ?>
                              <option value="<?= $e_to['mib_name'] ?>"><?= $e_to['mib_name'] ?></option>
                          <?php endforeach; ?>
                      </select>

                    </div>

                    <label data-i18n="modal_1_description">Description: </label> <span class="text-danger">*</span>
                    <div class="form-group">
                        <input type="text" name="description" id="description" value="" class="form-control" required>
                    </div>

                    <label data-i18n="modal_1_warning_cpu">CPU (Warning): </label> <span class="text-danger">*</span>
                    <div class="form-group">
                        <input type="number" name="warning_cpu" id="warning_cpu" value="" class="form-control" required>
                    </div>

                    <label data-i18n="modal_1_critical_cpu">CPU (Critical): </label> <span class="text-danger">*</span>
                    <div class="form-group">
                        <input type="number" name="critical_cpu" id="critical_cpu" value="" class="form-control" required>
                    </div>

                    <label data-i18n="modal_1_warning_disk">Disk (Warning): </label> <span class="text-danger">*</span>
                    <div class="form-group">
                        <input type="number" name="warning_disk" id="warning_disk" value="" class="form-control" required>
                    </div>

                    <label data-i18n="modal_1_critical_disk">Disk (Critical): </label> <span class="text-danger">*</span>
                    <div class="form-group">
                        <input type="number" name="critical_disk" id="critical_disk" value="" class="form-control" required>
                    </div>

                    <label data-i18n="modal_1_warning_memory">Memory (Warning): </label> <span class="text-danger">*</span>
                    <div class="form-group">
                        <input type="number" name="warning_memory" id="warning_memory" value="" class="form-control" required>
                    </div>

                    <label data-i18n="modal_1_critical_memory">Memory (Critical): </label> <span class="text-danger">*</span>
                    <div class="form-group">
                        <input type="number" name="critical_memory" id="critical_memory" value="" class="form-control" required>
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

<!-- Import Modal -->
<div class="modal fade text-left" id="import-modal" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <form class="import-file" action="">

                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">

                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel33" data-i18n="modal_2_title">Import Rules</h4>
                    <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div style="margin-bottom: 10px;"><span class="" data-i18n="modal_2_span_1">Only file that using our template will be processed.</span> <a href="../custom/rules_import_template.csv"><span class="flang-form_3_desc_2" data-i18n="modal_2_span_2">Click here to download sample template.</span></a></div>
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
                    <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="modal_2_footer_btn_cancel">Cancel</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-import" id="exitBtn" onclick="importNASCSV()" data-i18n="modal_2_footer_btn_import">Import</button>

                </div>

            </form>
        </div>
    </div>
</div>



<?php
require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";
?>
