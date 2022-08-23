<?php

$kiw['module'] = "Integration -> API";
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

<style>
    .ui-timepicker-container{ 
        z-index:1151 !important; 
    }
</style>

<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="integration_api_title">API</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="integration_api_subtitle">
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

                        <button type="button" class="btn btn-primary waves-effect waves-light create-btn-apikey pull-right" data-toggle="modal" data-target="#inlineForm"  data-i18n="integration_api_add">Add Authentication Key</button>

                        <div class="table-responsive">
                            <table id="itemlist" class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th  data-i18n="integration_api_no">No</th>
                                        <th  data-i18n="integration_api_auth_key">Authentication Key</th>
                                        <th  data-i18n="integration_api_permission">Permission</th>
                                        <th  data-i18n="integration_api_role">Role</th>
                                        <th  data-i18n="integration_api_action">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <th  data-i18n="integration_api_loading">
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
                    <h4 class="modal-title" id="myModalLabel33"  data-i18n="integration_api_add_edit">Add or Edit Authentication Key</h4>
                    <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <label  data-i18n="integration_api_enable">Enable: </label>
                            <input type="checkbox" class="custom-control-input" name="enabled" id="enabled" value="y" class="toggle" />
                            <label class="custom-control-label" for="enabled"></label>
                        </div>
                    </div>

                        <!-- <div class="form-group">
                            <div class="custom-control custom-switch">
                                <label data-i18n="">24 hours: </label>
                                <input type="checkbox" class="custom-control-input" name="is_24" id="is_24" value="1" class="toggle" />
                                <label class="custom-control-label" for="is_24"></label>
                            </div>
                        </div>

                    <div class="time">
                        <label data-i18n="">Start Time: </label>
                        <div class="form-group">
                            <input type="text" name="start_time" id="start_time" value="" class="form-control datetime">
                        </div>
                    </div>

                    <div class="time">
                        <label data-i18n="">Stop Time: </label>
                        <div class="form-group">
                            <input type="text" name="stop_time" id="stop_time" value=""  class="form-control datetime">
                        </div>
                    </div> -->

                    

                    <label  data-i18n="integration_api_permission2">Permission: </label>
                    <div class="form-group">
                        <select name="permission" id="permission" class="select2 form-control" data-for="permission" data-style="btn-default" tabindex="-98">
                            <option value="r"  data-i18n="integration_api_read">Read</option>
                            <option value="w"  data-i18n="integration_api_write">Write</option>
                            <option value="rw"  data-i18n="integration_api_readwrite">Read & Write</option>
                        </select>
                    </div>


                    <label  data-i18n="integration_api_role2">Role: </label>
                    <div class="form-group">
                        <fieldset class="form-group">
                            <select name="groupname" id="groupname" class="select2 form-control" data-style="btn-default">

                                <?php

                                $kiw_row = $kiw_db->fetch_array("SELECT DISTINCT(`groupname`) FROM `kiwire_admin_group` WHERE `tenant_id` = '{$tenant_id}'");

                                foreach ($kiw_row as $record) {

                                    echo "<option value='{$record['groupname']}'> " . ucfirst($record['groupname']) . "</option>";
                                }

                                ?>

                            </select>
                        </fieldset>
                    </div>

                    <label  data-i18n="integration_api_auth_key2">Authentication Key: </label> <span class="text-danger">*</span>
                    <div class="form-group">
                        <input type="text" name=api_key id=api_key value="" class="form-control" required>
                    </div>
                    <div class="">
                        <button type="button" onclick="genKey()" class="btn <?= (strlen($kiw_row['api_key']) > 0 ? 'btn-danger' : 'btn-primary') ?> waves-effect waves-light"><?= (strlen($kiw_row['api_key']) > 0 ? "<span class='reset_key_button'>Reset Key</span>" : "<span class='c-generate_key_button' >Generate Key</span>") ?></button>
                        <button type="button" class="btn btn-primary waves-effect waves-light flang-c-copy_key_button" id="copyAuthKey" name="copyAuthKey"  data-i18n="integration_api_copy_key">Copy Key</button>
                    </div><br>


                    <div class="modal-footer">

                        <input type="hidden" id="reference" name="reference" value="">
                        <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal"  data-i18n="integration_api_cancel">Cancel</button>
                        <button type="button" class="btn btn-primary round waves-effect waves-light btn-create"  data-i18n="integration_api_create">Create</button>
                        <button type="button" class="btn btn-primary round waves-effect waves-light btn-update"  data-i18n="integration_api_update">Update</button>

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

<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
<script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>