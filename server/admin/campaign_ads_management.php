<?php

$kiw['module'] = "Campaign -> Ads Management";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Ads Management</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                Manages ads, media
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

                        <button type="button" class="btn btn-primary waves-effect waves-light create-btn-ads pull-right mb-25" data-toggle="modal" data-target="#inlineForm" data-i18n="button_add_ads">Add Ads</button>

                        <div class="table-responsive">
                            <table id="itemlist" class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="thead_no">No</th>
                                        <th data-i18n="thead_name">Name</th>
                                        <th data-i18n="thead_remarks">Remarks</th>
                                        <th data-i18n="thead_media">Media Type</th>
                                        <th data-i18n="thead_date">Added Date</th>
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


<!-- Modal -->
<div class="modal fade text-left" id="inlineForm" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg"  role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel33" data-i18n="modal_1_title">Add or Edit Ads</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="create-form" action="#" enctype="application/x-www-form-urlencoded">

                <div class="modal-body">

                    <label style="padding: 10px;" data-i18n="modal_1_label_ads_name">Ads Name: </label> <span class="text-danger">*</span>
                    <div class="form-group">
                        <input type="text" name="adsname" id="adsname" value="" class="form-control" placeholder="eg: Ads 1" required>
                    </div>

                    <label style="padding: 10px;" data-i18n="modal_1_label_ads_message">Message: </label>
                    <div class="form-group">
                        <input type="text" name="msg" id="msg" value="" class="form-control" placeholder="messages">
                        <label style="font-size: smaller; padding: 10px;" data-i18n="modal_1_label_message_label">Leave blank to disable message, limit 140 characters</label>
                    </div>

                    <label style="padding: 10px;" data-i18n="modal_1_label_remark">Remark: </label>
                    <div class="form-group">
                        <input type="text" name="remark" id="remark" value="" class="form-control" placeholder="description">
                    </div>

                    <label style="padding: 10px;" data-i18n="modal_1_label_media">Media Type: </label>
                    <div class="form-group">
                        <select name="type" id="type" class="select2 form-control change-provider">
                            <option value="img" data-i18n="modal_1_label_media_option_image">Image</option>
                            <option value="vid" data-i18n="modal_1_label_media_option_video">Video</option>
                            <option value="youtube" data-i18n="modal_1_label_media_option_youtube">YouTube Video link</option>
                            <option value="msg" data-i18n="modal_1_label_media_option_message">Message</option>
                            <option value="json" data-i18n="modal_1_label_media_option_json">JSON Server</option>
                        </select>
                    </div>


                    <div class="col-12 img vid youtube json provider-input">
                        <div class="form-group row">
                            <label style="padding: 10px;" data-i18n="modal_1_label_captcha">Captcha: </label>
                            <input type="text" name="captcha_txt" id="captcha_txt" value="" class="form-control">
                        </div>
                    </div>

                    <div class="col-12 img vid youtube provider-input">
                        <div class="form-group row">
                            <label style="padding: 10px;" data-i18n="modal_1_label_link">Link: </label>
                            <input type="text" name="link" id="link" value="" class="form-control">
                        </div>
                    </div>

                    <div class="col-12 json provider-input">
                        <div class="form-group row">
                            <label style="padding: 10px;" data-i18n="modal_1_label_json_url">JSON URL: </label>
                            <input type="text" name="json_url" id="json_url" value="" class="form-control">
                        </div>
                    </div>

                    <div class="col-12 json provider-input">
                        <div class="form-group row">
                            <label style="padding: 10px;" data-i18n="modal_1_label_json_path">JSON Path: </label>
                            <input type="text" name="json_path" id="json_path" value="" class="form-control">
                        </div>
                    </div>

                    <div class="form-group json provider-input">
                        <div class="custom-control custom-switch">
                            <label style="padding: 10px;" data-i18n="modal_1_label_random">Random: </label>
                            <input type="checkbox" class="custom-control-input" name="random" id="random" value="y" class="toggle" />
                            <label class="custom-control-label" for="random"></label>
                        </div>
                    </div>

                    <div class="col-12 json provider-input">
                        <div class="form-group row">
                            <label style="padding: 10px;" data-i18n="modal_1_label_max_ads">Max Number of Ads: </label>
                            <input type="text" name="ads_max_no" id="ads_max_no" value="" class="form-control">
                        </div>
                    </div>

                    <div class="col-12 json provider-input">
                        <div class="form-group row">
                            <label style="padding: 10px;" data-i18n="modal_1_label_mapping">Mapping: </label>
                            <textarea class="form-control" name="mapping" id="mapping" cols="30" rows="5"></textarea>
                        </div>
                    </div>

                    <div class="img vid provider-input">
                        <label for="">
                            <p style="display: inline-block;"data-i18n="modal_1_label_supported_files">Upload file:</p>
                            <br>&nbsp;
                            <p style="display: inline-block;" data-i18n="modal_1_label_supported_files_p1">Supported files are; </p>
                            <strong style="display: inline-block;" data-i18n="modal_1_label_supported_files_image">Image: </strong>
                            <p style="display: inline-block;" data-i18n="modal_1_label_supported_files_p2">JPG and PNG </p>
                            <strong style="display: inline-block;" data-i18n="modal_1_label_supported_files_video">Video: </strong>
                            <p style="display: inline-block;" data-i18n="modal_1_label_supported_files_p3">MP4</p>
                        </label>
                    </div>

                    <div class="col-12 img vid provider-input">
                        <div class="form-group row">
                            <label style="padding: 10px;" data-i18n="modal_1_label_desktop">Desktop: </label>
                            <div class="custom-file">
                              <input type="file" name="fn_desktop" id="fn_desktop" data-maxfilesize="2000000" class="custom-file-input">
                              <label class="custom-file-label" for="fn_desktop" data-i18n="modal_1_label_dekstop_label">Choose desktop file</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 img vid provider-input">
                        <div class="form-group row">
                            <label style="padding: 10px;" data-i18n="modal_1_label_tablet">Tablet: </label>
                            <div class="custom-file">
                              <input type="file" name="fn_tablet" id="fn_tablet" data-maxfilesize="2000000" class="custom-file-input">
                              <label class="custom-file-label" for="fn_tablet" data-i18n="modal_1_label_tablet_label">Choose tablet file</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 img vid provider-input">
                        <div class="form-group row">
                            <label style="padding: 10px;" data-i18n="modal_1_label_mobile">Mobile: </label>
                            <div class="custom-file">
                                <input type="file" name="fn_phone" id="fn_phone" data-maxfilesize="2000000"
                                       class="custom-file-input">
                                <label class="custom-file-label" for="fn_phone" data-i18n="modal_1_label_mobile_label">Choose mobile file</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 img vid provider-input">
                        <div class="row">
                            <div class="col-4 my-2 current-file fn_desktop" style="min-height: 200px; display: none;"></div>
                            <div class="col-4 my-2 current-file fn_tablet" style="min-height: 200px; display: none;"></div>
                            <div class="col-4 my-2 current-file fn_phone" style="min-height: 200px; display: none;"></div>
                        </div>
                    </div>


                    <label style="padding: 10px; margin-top: 15px;" data-i18n="modal_1_label_viewport">View Port Mode: </label>
                    <div class="form-group">
                        <select name="viewport" id="viewport" class="select2 form-control" tabindex="-98">
                            <option value="fill" data-i18n="modal_1_label_viewport_fill">Fill</option>
                            <option value="fit" data-i18n="modal_1_label_viewport_fit">Fit</option>
                            <option value="stretch" data-i18n="modal_1_label_viewport_stretch">Stretch</option>
                            <option value="center" data-i18n="modal_1_label_viewport_center">Center</option>
                        </select>
                    </div>

                </div>

                <div class="modal-footer">

                    <input type="hidden" id="reference" name="reference" value="">
                    <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="modal_1_footer_button_cancel">Cancel</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-create" data-i18n="modal_1_footer_button_create">Create</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-update" data-i18n="modal_1_footer_button_update">Update</button>

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
