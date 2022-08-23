<?php

$kiw['module'] = "Campaign -> Coupon Creation";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Coupon Management</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                Manage coupon
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

                        <button type="button" class="btn btn-primary waves-effect waves-light create-btn-coupon pull-right mb-25" data-toggle="modal" data-target="#inlineForm" data-i18n="button_add_coupon">Add Coupon</button>

                        <div class="table-responsive">
                            <table id="itemlist" class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="thead_no">No</th>
                                        <th data-i18n="thead_title">Title</th>
                                        <th data-i18n="thead_details">Details</th>
                                        <th data-i18n="thead_code">Code</th>
                                        <th data-i18n="thead_price">Price</th>
                                        <th data-i18n="thead_expired">Expired Date</th>
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
                <h4 class="modal-title" id="myModalLabel33" data-i18n="modal_1_title">Add or Edit Coupon</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="create-form" action="#">
                <div class="modal-body">

                    <label data-i18n="modal_1_label_title">Title: </label>
                    <div class="form-group">
                        <input type="text" name="title" id="title" value="" class="form-control" required>
                    </div>

                    <label data-i18n="modal_1_label_coupon_img">Coupon Image: </label>
                    <div class="custom-file">
                        <input type="file" name="img_name" id="img_name" class="custom-file-input">
                        <label class="custom-file-label" for="logo" data-i18n="modal_1_coupon_img_choose_img">Choose Image</label>
                        <span style="font-size: smaller; padding: 10px;" class="flang-c-field_3_note" data-i18n="modal_1_coupon_img_choose_img_span">Supported files are .jpg and .png. Size should be under 1 Mb</span>
                    </div>

                    <label data-i18n="modal_1_label_current_img">Current Image: </label>
                    <div class="form-group">
                        <div class="col-md-9" id="currentImage">
                            &nbsp;
                        </div>
                    </div>

                    <label data-i18n="modal_1_label_details">Details: </label>
                    <div class="form-group">
                        <input type="text" name="details" id="details" value="" class="form-control" required>
                    </div>

                    <label data-i18n="modal_1_label_addtnl_info">Additional Information: </label>
                    <div class="form-group">
                        <input type="text" name="additional_info" id="additional_info" value="" class="form-control">
                    </div>

                    <label data-i18n="modal_1_label_price">Price: </label>
                    <div class="form-group">
                        <input type="number" min="0" step="0.01" id="price" name="price" class="form-control">
                    </div>

                    <label data-i18n="modal_1_label_expired">Expired Date: </label>
                    <div class="form-group">
                        <input type="text" name="date_expired" id="date_expired" class="form-control datepick" required>
                    </div>

                    <label data-i18n="modal_1_label_method">Code Method: </label>
                    <div class="form-group">
                        <select name="code_method" id="code_method" class="select2 form-control change-provider">
                            <option value="ran" data-i18n="modal_1_method_ran">Random</option>
                            <option value="pre" data-i18n="modal_1_method_pre">Pre Generate</option>
                        </select>
                    </div>

                    <div class="col-12 pre provider-input">
                        <div class="form-group row">
                            <label data-i18n="modal_1_label_code">Code: </label>
                            <input type="text" name="code" id="code" class="form-control">
                            <button type="button" class="btn btn-primary pull-right mr-2 btn-generate-code" style="margin-top: 7px;" data-i18n="modal_1_code_generate">Generate Code</button>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">

                    <input type="hidden" id="reference" name="reference" value="">
                    <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="modal_1_footer_button_cancel">Cancel</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-create" data-i18n="modal_1_footer_button_create">Create</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-update" data-i18n="modal_1_footer_button_update">Update</button>

                </div>

            </form>
        </div>
    </div>
</div>



<?php
require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";
?>

<link rel="stylesheet" href="/assets/css/bootstrap-datepicker.css">
<script src="/assets/js/bootstrap-datepicker.min.js"></script>
