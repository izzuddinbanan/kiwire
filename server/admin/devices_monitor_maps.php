<?php

$kiw['module'] = "Device -> Monitoring -> Maps";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Monitoring Maps</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                List of maps
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

                      <button type="button" class="btn btn-primary waves-effect waves-light create-btn-mib pull-right mb-25" data-toggle="modal" data-target="#inlineForm" data-i18n="btn_add_map">Add Map</button>

                        <div class="table-responsive">
                            <table id="itemlist" class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="thead_no">No</th>
                                        <th data-i18n="thead_map_name">Map Name</th>
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
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel33" data-i18n="modal_1_title">Add or Edit Map</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="create-form" action="#">
                <div class="modal-body">

                    <label data-i18n="modal_1_map_file">Map File: </label>
                    <div class="form-group">
                        <div class="custom-file">
                            <input type="file" name="mapfile" class="custom-file-input" id="mapfile">
                            <input type="hidden" name="mapfile_base64" id="mapfile_base64" />
                            <input type="hidden" name="mapfile_name" id="mapfile_name" />
                            <input type="hidden" name="mapfile_type" id="mapfile_type" />
                            <label class="custom-file-label" for="mapfile" data-i18n="map_file_choose_file">Choose file</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <img id="mapfile_preview" style="max-width:100%; max-height:250px; margin-bottom:25px;" />
                        </div>
                    </div>

                    <label data-i18n="modal_1_map_name">Map Name: </label>
                    <div class="form-group">
                        <input type="text" name="name" id="name" value="" class="form-control" required>
                    </div>

                    <label data-i18n="modal_1_description">Description: </label>
                    <div class="form-group">
                        <textarea type="text" name="description" id="description" rows="5" cols="80" class="form-control"></textarea>
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

<?php
require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";
?>
