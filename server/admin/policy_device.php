<?php

$kiw['module'] = "Policy -> Device Policy";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="policy_device_title">Device Policy</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="policy_device_subtitle">
                                Manage device policy
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

                        <button type="button" class="btn btn-primary waves-effect waves-light create-btn-device pull-right" data-toggle="modal" data-target="#inlineForm" data-i18n="policy_device_add">Add Device Policy</button>

                        <div class="table-responsive">
                            <table id="itemlist" class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="policy_device_tno">No</th>
                                        <th data-i18n="policy_device_tname">Name</th>
                                        <th data-i18n="policy_device_tzone">Zone</th>
                                        <th data-i18n="policy_device_ttype">Type</th>
                                        <th data-i18n="policy_device_tvalue">Value</th>
                                        <th data-i18n="policy_device_trestriction">Restriction</th>
                                        <th data-i18n="policy_device_tpriority">Priority</th>
                                        <th data-i18n="policy_device_tstatus">Status</th>
                                        <th data-i18n="policy_device_taction">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <td colspan="8" data-i18n="policy_device_loading">Loading...</td>
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
                <h4 class="modal-title" id="myModalLabel33" data-i18n="policy_device_add_edit">Add or Edit Device Policy</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="create-form" action="#">
                <div class="modal-body">

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <label data-i18n="policy_device_enable">Enable: </label>
                            <input type="checkbox" class="custom-control-input" name=status id=status <?= ($kiw_row['status '] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                            <label class="custom-control-label" for="status"></label>
                        </div>
                    </div>

                    <label data-i18n="policy_device_name">Name: </label> <span class="text-danger">*</span>
                    <div class="form-group">
                        <input type="text" name="name" id="name" value="" class="form-control" placeholder="Policy name" required>
                    </div>

                    <label data-i18n="policy_device_zone">Zone: </label>
                    <div class="form-group">

                        <select name="zone" id="zone" class="select2 form-control" data-style="btn-default" tabindex="-98">
                            <option value="none" data-i18n="policy_device_zone_none">None</option>
                            <?php

                            $rows = "SELECT * FROM kiwire_allowed_zone WHERE tenant_id = '{$tenant_id}' GROUP BY name ORDER BY name";
                            $rows = $kiw_db->fetch_array($rows);

                            foreach ($rows as $record) {

                                $selected = "";

                                if ($record['name'] == $kiw_row['allowed_zone']) $selected = 'selected="selected"';

                                echo "<option value =\"$record[name]\" $selected> $record[name]</option> \n";
                            }

                            ?>
                        </select>

                    </div>

                    <label data-i18n="policy_device_type">Type: </label>
                    <div class="form-group">

                        <select name="type" id="type" class="select2 form-control">
                            <option value="Brand" data-i18n="policy_device_brand">Brand</option>
                            <option value="Type" data-i18n="policy_device_option_type">Type</option>
                            <option value="Model" data-i18n="policy_device_model">Model</option>
                            <option value="OS" data-i18n="policy_device_operating_system">Operating System</option>
                        </select>

                    </div>

                    <label data-i18n="policy_device_value">Value: </label> <span class="text-danger">*</span>
                    <div class="form-group">
                        <input type="text" name="value" id="value" value="" class="form-control" placeholder="policy value" required>
                    </div>

                    <label data-i18n="policy_device_restriction">Restriction: </label>
                    <div class="form-group">

                        <select name="profile" id="profile" class="select2 form-control">
                            <option value="[blocked]" <? ($profile == "[blocked]" ? "selected" : "") ?> data-i18n="policy_device_block"> Block this device</option>
                            <?php
                            $user_l = "SELECT DISTINCT name FROM kiwire_profiles WHERE tenant_id = '$tenant_id'";
                            $user_l = $kiw_db->fetch_array($user_l);
                            foreach ($user_l as $user) {
                                echo "<option value = '" . $user['name'] . "' " . ($profile == $user['name'] ? "selected" : "") . ">Profile: " . $user['name'] . "</option>";
                            }
                            ?>
                        </select>

                    </div>

                    <label data-i18n="policy_device_priority">Priority: </label> <span class="text-danger">*</span>
                    <div class="form-group">
                        <input type="text" name="priority" id="priority" value="" class="form-control" placeholder="Integer eg:1" required>
                    </div>

                </div>

                <div class="modal-footer">

                    <input type="hidden" id="reference" name="reference" value="">
                    <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="policy_device_cancel">Cancel</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-create" data-i18n="policy_device_create">Create</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-update" data-i18n="policy_device_update">Update</button>

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