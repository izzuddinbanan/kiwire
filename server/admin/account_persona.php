<?php

$kiw['module'] = "Account -> Persona";
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


$kiw_fields = @file_get_contents(dirname(__FILE__, 2) . "/custom/{$_SESSION['tenant_id']}/data-mapping.json");

if (empty($kiw_fields)) {

    $kiw_fields = @file_get_contents(dirname(__FILE__, 2) . "/user/templates/kiwire-data-mapping.json");
}

if (!empty($kiw_fields)) $kiw_fields = json_decode($kiw_fields, true);



?>


<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Persona</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                Manage persona for your user
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
                    <button type="button" class="btn btn-primary waves-effect waves-light create-btn-persona" data-i18n="button_add_persona">Add
                        Persona
                    </button>
                </div>
                <div class="card-body">
                    <div class="card-text">
                        <div class="table-responsive">
                            <table class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="table_header_no">No</th>
                                        <th data-i18n="table_header_name">Name</th>
                                        <th data-i18n="table_header_created_date">Created Date</th>
                                        <th data-i18n="table_header_action">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td data-i18n="table_body_loading">Loading...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </div>
</div>


<!-- modal -->
<div class="modal fade text-left" id="inlineForm" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">

        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel33" data-i18n="modal_add_edit_persona">Add or Edit Persona</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" style="overflow: auto;">
                <form class="rule-form" id="rule-form" style="min-height: 300px; max-height: 500px;">
                    <label data-i18n="modal_label_persona_name">Persona Name: </label> <span class="text-danger">*</span>
                    <div class="form-group">
                        <input type="text" id="name" name="name" class="form-control" value="" required>
                    </div>

                    <label data-i18n="modal_label_rule">Rule: </label>
                    <table class="table rule-list">

                        <tbody class="field-list">

                            <tr class="field-data">
                                <td>
                                    <select class="form-control field" name="fields[]">
                                        <?php foreach ($kiw_fields as $kiw_field) { ?>
                                            <?php if ($kiw_field['display'] != "[empty]") { ?>
                                                <option value="<?= $kiw_field['field'] ?>"><?= $kiw_field['display'] ?></option>
                                            <?php } ?>
                                        <? } ?>
                                    </select>
                                </td>
                                <td>
                                    <select class="form-control operator" name="operators[]">
                                        <option value="is" data-i18n="modal_option_is">is</option>
                                        <option value="is_not" data-i18n="modal_option_is_not">is not</option>
                                        <option value="contain" data-i18n="modal_option_contain">contain</option>
                                        <option value="not_contain" data-i18n="modal_option_not_contain">not contain</option>
                                        <option value="larger_than" data-i18n="modal_option_larger">larger than</option>
                                        <option value="smaller_than" data-i18n="modal_option_smaller">smaller than</option>
                                        <option value="start_with" data-i18n="modal_option_start">start with</option>
                                        <option value="end_with" data-i18n="modal_option_end">end with</option>
                                    </select>
                                </td>
                                <td>
                                    <input class="form-control" type="text" name="values[]">
                                </td>
                            </tr>

                        </tbody>

                    </table>

            </div>

            <div class="modal-footer">

                <input type="hidden" id="reference" name="reference" value="">
                <button type="button" class="btn btn-danger waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="modal_button_cancel">Cancel</button>
                <button type="button" class="btn btn-primary waves-effect waves-light btn-add-rule" data-i18n="modal_button_add_rule">Add Rule</button>
                <button type="button" class="btn btn-primary waves-effect waves-light btn-create" data-i18n="modal_button_create">Create</button>

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