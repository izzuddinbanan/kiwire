<?php

$kiw['module'] = "Login Engine -> Template Engine";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase"  data-i18n="login_engine_template_title">Template</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="login_engine_template_subtitle">
                                Create or design template for user
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

                        <button type="button"
                                class="btn btn-primary waves-effect waves-light create-btn-template pull-right"
                                data-toggle="modal" data-target="#inlineForm"  data-i18n="login_engine_template_add">Add Template
                        </button>

                        <div class="table-responsive">
                            <table id="itemlist" class="table table-hover table-data">
                                <thead>
                                <tr class="text-uppercase">
                                    <th data-i18n="login_engine_template_tno">No</th>
                                    <th data-i18n="login_engine_template_tname">Name</th>
                                    <th data-i18n="login_engine_template_ttype">Template Type</th>
                                    <th data-i18n="login_engine_template_tsubject">Subject</th>
                                    <th data-i18n="login_engine_template_tupdate">Last Update</th>
                                    <th data-i18n="login_engine_template_taction">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <th data-i18n="login_engine_template_loading">
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
    <div class="modal-dialog modal-dialog-centered modal-lg" style="min-width: 90%;">
        <div class="modal-content" style="min-width: 90%;">

            <form class="create-form" action="#">

                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel33"  data-i18n="login_engine_template_add_edit">Add or Edit Template</h4>

                    <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-8">

                            <label data-i18n="login_engine_template_name">Template Name: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="name" id="name" value="" class="form-control" required>
                            </div>

                            <label data-i18n="login_engine_template_subject">Subject: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="subject" id="subject" value="" class="form-control" required>
                            </div>

                            <label  data-i18n="login_engine_template_type">Template Type: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <select name="type" id="type" class="select2 form-control change-provider" required>
                                    <option value="email" data-i18n="login_engine_template_email">Email</option>
                                    <option value="sms" data-i18n="login_engine_template_sms">SMS</option>
                                    <option value="voucher" data-i18n="login_engine_template_voucher">Voucher</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="editor"  data-i18n="login_engine_template_editor">Editor: </label>
                                <textarea name="editor" id="editor"></textarea>
                            </div>

                        </div>
                        <div class="col-md-4">

                            <div class="col-12 var-for-email type-select">
                                <div class="row">
                                    <label data-i18n="login_engine_template_avail_var">Available Variables: </label>
                                    <div class="col-md-12" style="font-size: smaller;">

                                        <span style="margin-top: 5px;" data-i18n="login_engine_template_email_verify">Email sign up template:</span>
                                        <ul style="margin-top: 5px;">
                                            <li>{{fullname}}</li>
                                            <li>{username}}</li>
                                            <li>{{tenant_id}}</li>
                                            <li>{{password}}</li>
                                            <li>{{verification_link}}</li>
                                            <li>{{verify_button}}</li>
                                        </ul>

                                        <span style="margin-top: 5px;" data-i18n="login_engine_template_sponsored_verify">Sponsored verification template:</span>
                                        <ul style="margin-top: 5px;">
                                            <li>{{fullname}}</li>
                                            <li>{username}}</li>
                                            <li>{{tenant_id}}</li>
                                            <li>{{password}}</li>
                                            <li>{{verification_link}}</li>
                                        </ul>

                                    </div>
                                </div>
                            </div>


                            <div class="col-12 var-for-sms type-select" style="display: none;">
                                <div class="row">
                                    <label data-i18n="login_engine_template_label_avail_var">Available Variable: </label>
                                    <div class="col-md-12" style="font-size: smaller;">

                                        <span style="margin-top: 5px;" data-i18n="login_engine_template_sms_variables">SMS template:</span>
                                        <ul style="margin-top: 5px;">
                                            <li>{{fullname}}</li>
                                            <li>{username}}</li>
                                            <li>{{phone_number}}</li>
                                        </ul>

                                    </div>
                                </div>
                            </div>


                            <div class="col-12 var-for-voucher type-select" style="display: none;">
                                <div class="row">
                                    <label data-i18n="login_engine_template_label_avail_var2">Available Variable: </label>
                                    <div class="col-md-12" style="font-size: smaller;">
                                        <span style="margin-top: 5px;" data-i18n="login_engine_template_voucher_variables">Voucher print template:</span>
                                        <ul style="margin-top: 5px;">
                                            <li>{{logo}}</li>
                                            <li>{{date_expiry}}</li>
                                            <li>{{username}}</li>
                                            <li>{{password}}</li>
                                            <li>{{remark}}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>



                    <div class="modal-footer">

                        <input type="hidden" id="reference" name="reference" value="">

                        <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="login_engine_template_cancel">Cancel</button>
                        <button type="button" class="btn btn-primary round waves-effect waves-light btn-create" data-i18n="login_engine_template_create">Create</button>
                        <button type="button" class="btn btn-primary round waves-effect waves-light btn-update" data-i18n="login_engine_template_update">Update</button>

                    </div>
                </div>
                <input type="hidden" class="token" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
            </form>
        </div>
    </div>
</div>


<?php

require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";

?>

