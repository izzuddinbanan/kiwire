<?php

$kiw['module'] = "Cloud -> API";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="cloud_api_title">API</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="cloud_api_subtitle">
                                API connectivity with 3rd party, let other access and manage our system
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

                        <button type="button" class="btn btn-primary waves-effect waves-light create-btn-apikey pull-right" data-toggle="modal" data-target="#inlineForm" data-i18n="cloud_api_add_auth_key">Add Authentication Key</button>

                        <div class="table-responsive">
                            <table id="itemlist" class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="cloud_api_no">No</th>
                                        <th data-i18n="cloud_api_auth_key">Authentication Key</th>
                                        <th data-i18n="cloud_api_permission">Permission</th>
                                        <th data-i18n="cloud_api_role">Role</th>
                                        <th data-i18n="cloud_api_action">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <th>
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

            <form class="create-form" action="#">

                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel33" data-i18n="cloud_api_modal_label">Add or Edit Authentication Key</h4>
                    <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <label data-i18n="cloud_api_modal_enable">Enable: </label>
                            <input type="checkbox" class="custom-control-input" name="enabled" id="enabled" value="y" class="toggle" />
                            <label class="custom-control-label" for="enabled"></label>
                        </div>
                    </div>

                    <label data-i18n="cloud_api_modal_permission">Permission: </label>  <span class="text-danger">*</span> 
                    <div class="form-group">
                        <select name="permission" id="permission" class="select2 form-control" data-for="permission" data-style="btn-default">
                            <option value="r" data-i18n="cloud_api_modal_read">Read</option>
                            <option value="w" data-i18n="cloud_api_modal_write">Write</option>
                            <option value="rw" data-i18n="cloud_api_modal_readwrite">Read & Write</option>
                        </select>
                    </div>


                    <label data-i18n="cloud_api_modal_role">Role: </label>  <span class="text-danger">*</span>
                    <div class="form-group">
                        <fieldset class="form-group">
                            <select name="groupname" id="groupname" class="select2 form-control" data-style="btn-default">

                                <?php

                                $kiw_row = $kiw_db->fetch_array("SELECT DISTINCT(`groupname`) FROM `kiwire_admin_group` WHERE `tenant_id` = 'superuser'");

                                foreach ($kiw_row as $record) {

                                    echo "<option value='{$record['groupname']}'> " . ucfirst($record['groupname']) . "</option>";
                                }

                                ?>

                            </select>
                        </fieldset>
                    </div>

                    <label data-i18n="cloud_api_modal_auth_key">Authentication Key: </label> <span class="text-danger">*</span>
                    <div class="form-group">
                        <input type="text" name=api_key id=api_key value="" class="form-control" required>
                    </div>
                    <div class="">
                        <button type="button" onclick="genKey()" class="btn <?= (strlen($kiw_row['api_key']) > 0 ? 'btn-danger' : 'btn-primary') ?> waves-effect waves-light"><?= (strlen($kiw_row['api_key']) > 0 ? "<span class='reset_key_button' data-i18n='cloud_api_modal_reset'>Reset Key</span>" : "<span data-i18n='cloud_api_modal_generate'>Generate Key</span>") ?></button>
                        <button type="button" class="btn btn-primary waves-effect waves-light" id="copyAuthKey" name="copyAuthKey" data-i18n='cloud_api_modal_copy'>Copy Key</button>
                    </div><br>


                    <div class="modal-footer">

                        <input type="hidden" id="reference" name="reference" value="">
                        <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-i18n='cloud_api_modal_cancel' data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary round waves-effect waves-light btn-create" data-i18n='cloud_api_modal_create'>Create</button>
                        <button type="button" class="btn btn-primary round waves-effect waves-light btn-update" data-i18n='cloud_api_modal_update'>Update</button>

                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php

require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";

?>

<script src="/assets/js/jquery-copytoclipboard.js"></script>