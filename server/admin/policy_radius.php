<?php

$kiw['module'] = "Policy -> Radius";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="policy_radius_title">Radius Attribute</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="policy_radius_subtitle">
                                Manage additional radius attribute reply to a profile
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

                        <button type="button" class="btn btn-primary round waves-effect waves-light create-btn-radiusattr pull-right" data-toggle="modal" data-target="#inlineForm" data-i18n="policy_radius_add">Add New Radius</button>

                        <div class="table-responsive">
                            <table id="itemlist" class="table table-hover table-data">
                                <thead>
                                    <tr>
                                        <th data-i18n="policy_radius_tno">NO</th>
                                        <th data-i18n="policy_radius_tprofile">PROFILE</th>
                                        <th data-i18n="policy_radius_tattribute">ATTRIBUTE</th>
                                        <th data-i18n="policy_radius_tremark">REMARK</th>
                                        <th data-i18n="policy_radius_taction">ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
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
                <h4 class="modal-title" id="myModalLabel33" data-i18n="policy_radius_add_edit">Add/Edit Radius</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="create-form" action="#">
                <div class="modal-body">

                    <label data-i18n="policy_radius_profile">Profile: </label>
                    <div class="form-group">

                        <select name="plan" id="plan" class="select2 form-control" data-style="btn-default" tabindex="-98">
                            <option value="none" data-i18n="policy_radius_none">None</option>
                            <?
                            $sql = "select name from kiwire_profiles where tenant_id = '{$tenant_id}' group by name";
                            $rows = $kiw_db->fetch_array($sql);
                            foreach ($rows as $record) {
                                $selected = "";
                                if ($record['name'] == $profile) {
                                    $selected = 'selected="selected"';
                                }
                                echo "<option value =\"$record[name]\" $selected> $record[name]</option> \n";
                            }
                            ?>
                        </select>

                    </div>

                    <label data-i18n="policy_radius_attribute">Attribute: </label>
                    <div class="form-group">
                        <input type="text" name="attribute" id="attribute" value="" class="form-control" required>
                    </div>

                    <label>Profile: </label>
                    <div class="form-group">
                        <fieldset class="form-group">
                            <select name="op" id="op" class="select2 form-control" data-style="btn-default" tabindex="-98">
                                <option value=":="> := </option>
                                <option value="="> = </option>
                                <option value="=="> == </option>
                                <option value="+="> += </option>
                                <option value="!="> != </option>
                            </select>
                        </fieldset>
                    </div>

                    <label data-i18n="policy_radius_value">Value: </label>
                    <div class="form-group">
                        <input type="text" name="value" id="value" value="" class="form-control" required>
                    </div>

                    <label data-i18n="policy_radius_remark">Remark: </label>
                    <div class="form-group">
                        <input type="text" name="remark" id="remark" value="" class="form-control">
                    </div>

                </div>

                <div class="modal-footer">

                    <input type="hidden" id="reference" name="reference" value="">
                    <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="policy_radius_cancel">Cancel</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-create" data-i18n="policy_radius_create">Create</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-update" data-i18n="policy_radius_update">Update</button>

                </div>

            </form>
        </div>
    </div>
</div>


<?php
require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";
?>