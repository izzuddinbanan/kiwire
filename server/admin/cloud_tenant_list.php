<?php

$kiw['module'] = "Cloud -> Manage Client";
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

?>

<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Tenant Management</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                Manage cloud tenant
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

                        <button type="button" class="btn btn-primary waves-effect waves-light import-btn-tenant" data-toggle="modal" data-target="#import-modal" style="display:none;" data-i18n="btn_import_tenant">Import Tenant</button>
                        <button type="button" class="btn btn-md btn-primary waves-effect waves-light create-btn-tenant pull-right" data-toggle="modal" data-target="#inlineForm" data-i18n="btn_add_tenant">Add Tenant</button>

                        <div class="table-responsive">
                            <table class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="thead_no">No</th>
                                        <th data-i18n="thead_tenantID">Tenant Id</th>
                                        <th data-i18n="thead_name">Tenant Name</th>
                                        <th data-i18n="thead_adminID">Admin Id</th>
                                        <th data-i18n="thead_expiry">Expiry Date</th>
                                        <th data-i18n="thead_action">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <th>
                                    <td colspan="8" data-i18n="tbody_loading">Loading...</td>
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
                <h4 class="modal-title" id="myModalLabel33" data-i18n="modal_1_title">Add or Edit Tenant</h4>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="create-form" action="#">

                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-6">
                            <label data-i18n="modal_1_label_tenantID">Tenant ID: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="tenant_id" id="tenant_id" class="form-control" placeholder="tenant name" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_label_clientName">Client Name: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="client_name" id="client_name" value="" class="form-control" placeholder="eg: Synchroweb" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_label_hostname">Hostname: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="ip_address" id="ip_address" value="" class="form-control" placeholder="eg: https://synchroweb.com" required>
                            </div>

                        </div>

                        <div class="col-md-6">
                            <label data-i18n="modal_1_label_adminID">Admin ID: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="admin_id" id="admin_id" value="" class="form-control" placeholder="eg: admin" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_label_adminPass">Admin Password: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="password" name="admin_pass" id="admin_pass" value="" class="form-control" placeholder="Uppercase/lowercase/numeric/special character" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_label_adminEmail">Admin Email Address: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="email" name="admin_email" id="admin_email" value="" class="form-control" placeholder="eg: admin@gmail.com" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_label_license">License Key: </label>
                            <div class="form-group">
                                <input type="text" name="lkey" id="lkey" value="" class="form-control">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label  data-i18n="modal_label_simultaneous_use">Simultaneous Use:  </label>
                            <div class="form-group">
                                <input type="text" id="simultaneous" name="simultaneous" class="form-control" placeholder="eg: 500" value="">
                                <div style="font-size: smaller; padding: 10px;" class=""  data-i18n="modal_simultaneous_concurrent">
                                    Concurrent user, default 500
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <label data-i18n="modal_1_label_email">Send email to the admin: </label>
                                    <input type="checkbox" class="custom-control-input" name="send_email" id="send_email" value="y" class="toggle" />
                                    <label class="custom-control-label" for="send_email"></label>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>


                <div class="modal-footer">

                    <input type="hidden" name="reference" value="">

                    <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="modal_1_footer_btn_cancel">Cancel</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-create" data-i18n="modal_1_footer_btn_create">Create</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-update" data-i18n="modal_1_footer_btn_update">Update</button>

                </div>

            </form>

        </div>
    </div>
</div>


<div class="modal fade text-left" id="import-modal" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <form class="import-file" action="">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel33" data-i18n="modal_2_title">Import Tenant</h4>
                    <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div style="margin-bottom: 10px;">

                        <span class="" data-i18n="modal_2_span_notice">Only file that using our template will be processed.</span> <br>

                        <a href="/user/templates/import_tenant.csv" download>
                            <span class="flang-form_3_desc_2" data-i18n="modal_2_span_download">Click here to download sample template.</span>
                        </a>

                    </div>

                    <input type="file" name="csv-file" accept=".csv" />

                    <div class="progress" style="display: none; margin-top: 25px;" id="import-progress">

                        <div class="progress-bar progress-bar-primary progress-bar-striped active" role="progressbar" aria-valuenow="82" aria-valuemin="0" aria-valuemax="100" style="width: 100%;">

                            <span class="sr-only"></span>

                        </div>

                    </div>

                    <div id="import-status" style="display: none; margin-top: 25px;">&nbsp;</div>

                </div>

                <div class="modal-footer">

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
