<?php

$kiw['module'] = "Login Engine -> Additional Field";
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

$kiw_count = 1;

?>



<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="login_engine_additional_field_title">Additional Field</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="login_engine_additional_field_subtitle">
                                Create new additional field
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-content mt-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="itemlist" class="table table-hover table-data">
                                <thead>
                                    <tr>
                                        <th data-i18n="login_engine_additional_field_no">NO</th>
                                        <th data-i18n="login_engine_additional_field_variable">VARIABLE</th>
                                        <th data-i18n="login_engine_additional_field_display">DISPLAY</th>
                                        <th data-i18n="login_engine_additional_field_required">REQUIRED?</th>
                                        <th data-i18n="login_engine_additional_field_action">ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php foreach ($kiw_fields as $kiw_field) { ?>

                                        <tr data-field-info="<?= $kiw_field['field'] ?>">
                                            <td><?= $kiw_count ?></td>
                                            <td class="variable-<?= $kiw_field['field'] ?>" data-field-info="variable-<?= $kiw_field['field'] ?>"><?= $kiw_field['variable'] ?></td>
                                            <td class="display-<?= $kiw_field['field'] ?>" data-field-info="display-<?= $kiw_field['field'] ?>"><?= $kiw_field['display'] ?></td>
                                            <td class="required-<?= $kiw_field['field'] ?>" data-field-info="required-<?= $kiw_field['field'] ?>"><?= ($kiw_field['required'] == "Yes" ? "Yes" : "No") ?></td>
                                            <td><button type="button" data-field-id="<?= $kiw_field['field'] ?>" class="btn btn-icon btn-success btn-xs mr-1 fa fa-pencil btn-update-field"></button> <? if ($kiw_field['variable'] != 'fullname' && $kiw_field['variable'] != 'email_address' && $kiw_field['variable'] != 'phone_number' && $kiw_field['variable'] != 'gender' && $kiw_field['variable'] != 'age_group' && $kiw_field['variable'] != 'location' && $kiw_field['variable'] != 'birthday') { ?> <button type="button" data-field-id="<?= $kiw_field['field'] ?>" class="btn btn-icon btn-danger btn-xs mr-1 fa fa-times btn-delete-field"></button><?php } ?></td>
                                        </tr>

                                    <?php $kiw_count++;

                                    }?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>



<!-- Modal -->
<div class="modal fade text-left" id="inlineForm" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <form class="create-form" action="#">

                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel33">Add/Edit Field</h4>

                    <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <label>Variable: </label>
                    <div class="form-group">
                        <input type="text" name="variable" id="variable" value="" class="form-control">
                    </div>

                    <label>Display: </label>
                    <div class="form-group">
                        <input type="text" name="display" id="display" value="" class="form-control">
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <label>Required: </label>
                            <input type="checkbox" class="custom-control-input" name="required" id="required" value="y" class="toggle" />
                            <label class="custom-control-label" for="required"></label>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-update">Update</button>

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
