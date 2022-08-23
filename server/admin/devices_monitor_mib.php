<?php

$kiw['module'] = "Device -> Monitoring -> MIB";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Management Information Base (MIB)</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                List of MIB settings
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

                      <button type="button" class="btn btn-primary waves-effect waves-light create-btn-mib pull-right mb-25" data-toggle="modal" data-target="#inlineForm" data-i18n="btn_add_MIB">Add MIB</button>
                      <button type="button" class="btn btn-primary waves-effect waves-light import-btn-mib pull-right mr-1 mb-25" data-toggle="modal" data-target="#import-mib" style="display:none;" data-i18n="btn_import_MIB">Import MIB</button>

                        <div class="table-responsive">
                            <table id="itemlist" class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="thead_no">No</th>
                                        <th data-i18n="thead_mib">MIB</th>
                                        <th data-i18n="thead_desc">Description</th>
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
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel33" data-i18n="modal_1_title">Add or Edit MIB</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="create-form" action="#">

                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                <div class="modal-body">

                    <div class="row">

                        <div class="col-md-12">
                            <label data-i18n="modal_1_title">MIB Name: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="mib_name" id="mib_name" value="" class="form-control" placeholder="Your MIB Name" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_description">Description: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="description" id="description" value="" class="form-control" placeholder="remark" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_system_name">System Name: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="system_name" id="system_name" value="" class="form-control" placeholder="system name" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_dev_loc">Device Location: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="dev_loc" id="dev_loc" value="" class="form-control" placeholder="eg: Malaysia" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_cpu_load">CPU Load: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="cpu_load" id="cpu_load" value="" class="form-control" placeholder="" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_memory_total">Total Memory: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="memory_total" id="memory_total" value="" class="form-control" placeholder="" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_memory_used">Memory Used: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="memory_used" id="memory_used" value="" class="form-control" placeholder="" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_disk_total">Disk Size: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="disk_total" id="disk_total" value="" class="form-control" placeholder="" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_disk_used">Disk Used: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="disk_used" id="disk_used" value="" class="form-control" placeholder="" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_device_count">Connected Device: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="device_count" id="device_count" value="" class="form-control" placeholder="" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_input_vol">Input Volume: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="input_vol" id="input_vol" value="" class="form-control" placeholder="" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_output_vol">Output Volume: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="output_vol" id="output_vol" value="" class="form-control" placeholder="" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_uptime">System Up Time: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="uptime" id="uptime" value="" class="form-control" placeholder="" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_if_desc">Interface Desc.: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="if_desc" id="if_desc" value="" class="form-control" placeholder="" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_if_total">Total Interface: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="if_total" id="if_total" value="" class="form-control" placeholder="" required>
                            </div>
                        </div>
                            
                        <div class="col-md-6">
                            <label data-i18n="modal_1_if_status">Interface Status: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="if_status" id="if_status" value="" class="form-control" placeholder="" required>
                            </div>
                        </div>
                        
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_if_speed">Interface Number to Monitor for Bandwidth and Speed: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="if_speed" id="if_speed" value="" class="form-control" placeholder="" required>
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

<!-- Import Modal -->
<div class="modal fade text-left" id="import-modal" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <form class="import-file" action="">

                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">

                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel33" data-i18n="modal_2_title">Import MIB</h4>
                    <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div style="margin-bottom: 10px;"><span class="" data-i18n="modal_2_span_1">Only file that using our template will be processed.</span> <a href="../custom/mib_import_template.csv"><span class="flang-form_3_desc_2" data-i18n="modal_2_span_2">Click here to download sample template.</span></a></div>
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
