<?php

$kiw['module'] = "Policy -> Dynamic Bandwidth";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="policy_dynamic_bandwith_title">Bandwidth</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="policy_dynamic_bandwith_subtitle">
                                Manage dynamic bandwidth
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

                        <button type="button" class="btn btn-primary waves-effect waves-light create-btn-bandwidth pull-right" data-toggle="modal" data-target="#inlineForm" data-i18n="policy_dynamic_bandwith_add">Add Dynamic Bandwidth</button>

                        <div class="table-responsive">
                            <table id="itemlist" class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="policy_dynamic_bandwith_tno">NO</th>
                                        <th data-i18n="policy_dynamic_bandwith_tapplied">APPLIED TO</th>
                                        <th data-i18n="policy_dynamic_bandwith_tzone">ZONE NAME</th>
                                        <th data-i18n="policy_dynamic_bandwith_tusers">NUMBER OF USERS</th>
                                        <th data-i18n="policy_dynamic_bandwith_tlimit">REACH USER LIMIT</th>
                                        <th data-i18n="policy_dynamic_bandwith_tpriority">PRIORITY</th>
                                        <th data-i18n="policy_dynamic_bandwith_tdownload">DOWNLOAD CAPPED SPEED(MBPS)</th>
                                        <th data-i18n="policy_dynamic_bandwith_tupload">UPLOAD CAPPED SPEED(MBPS)</th>
                                        <th data-i18n="policy_dynamic_bandwith_taction">ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <th data-i18n="policy_dynamic_bandwith_loading">
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
                <h4 class="modal-title" id="myModalLabel33" data-i18n="policy_dynamic_bandwith_add_edit">Add or Edit Dynamic Bandwidth</h4>

                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="create-form" action="#">
                
                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                <div class="modal-body">


                    <div class="alert alert-info" data-i18n="policy_dynamic_bandwith_applied">
                        Applied To
                    </div>

                    <div class="form-group">
                        <label data-i18n="policy_dynamic_bandwith_label_applied">Applied To: </label>
                        <select name="applied_to" id="applied_to" class="select2 form-control change-provider">
                            <option value="all" data-i18n="policy_dynamic_bandwith_applied_all">All</option>
                            <option value="zone" data-i18n="policy_dynamic_bandwith_applied_zone">Zone</option>
                            <option value="users" data-i18n="policy_dynamic_bandwith_applied_users">Number of Users</option>
                        </select>
                    </div>

                    <div class="form-group users provider-input">
                        <label data-i18n="policy_dynamic_bandwith_user">No. Of User: </label>
                        <input type="text" name="at_user" id="at_user" value="" class="form-control">
                    </div>

                    <div class="form-group zone provider-input">
                        <label data-i18n="policy_dynamic_bandwith_zone">Zone: </label>

                        <select name="at_zone" id="at_zone" class="select2 form-control" data-style="btn-default" tabindex="-98">
                            <option value="none" data-i18n="policy_dynamic_bandwith_zone_none">None</option>
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


                    <div class="alert alert-info" data-i18n="policy_dynamic_bandwith_configuration">
                        Configuration
                    </div>


                    <div class="form-group">
                        <label data-i18n="policy_dynamic_bandwith_priority">Priority: </label> <span class="text-danger">*</span>
                        <input type="text" name="priority" id="priority" value="" class="form-control" placeholder="Integer eg: 1" required>
                    </div>

                    <div class="form-group">
                        <label data-i18n="policy_dynamic_bandwith_download">Download Speed (Mbps): </label> <span class="text-danger">*</span>
                        <input type="text" name="download_speed" id="download_speed" value="" class="form-control" placeholder="eg: 1024" required>
                    </div>

                    <div class="form-group">
                        <label data-i18n="policy_dynamic_bandwith_upload">Upload Speed (Mbps): </label> <span class="text-danger">*</span>
                        <input type="text" name="upload_speed" id="upload_speed" value="" class="form-control" placeholder="eg: 1024" required>
                    </div>

                    <div class="form-group zone provider-input">
                        <label data-i18n="policy_dynamic_bandwith_limit">Reach User Limit: </label>
                        <input type="text" name="k_trigger" id="k_trigger" value="" class="form-control">
                    </div>

                </div>

                <div class="modal-footer">

                    <input type="hidden" id="reference" name="reference" value="">
                    <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="policy_dynamic_bandwith_cancel">Cancel</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-create" data-i18n="policy_dynamic_bandwith_create">Create</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-update" data-i18n="policy_dynamic_bandwith_update">Update</button>

                </div>

            </form>
        </div>
    </div>
</div>



<?php
require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";
?>