<?php

$kiw['module'] = "Account -> Profile";
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


$kiw_profiles = $kiw_db->fetch_array("SELECT name FROM kiwire_profiles WHERE tenant_id = '{$_SESSION['tenant_id']}'");


$kiw_profile_actions['delete'] = "Delete All Accounts";


foreach ($kiw_profiles as $kiw_profile) {

    $kiw_profile_actions['actionto*' . $kiw_profile['name']] = "Change Profile To [ {$kiw_profile['name']} ]";

}


?>


<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Profile</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                Manage profile / packages for your users
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
                        <button type="button" class="btn btn-primary waves-effect waves-light pull-right  create-btn-profile" data-i18n="button_add_profile">
                            Add Profile
                        </button>

                        <div class="table-responsive">
                            <table class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="table_header_no">No</th>
                                        <th data-i18n="table_header_name">Name</th>
                                        <th data-i18n="table_header_price">Price</th>
                                        <th data-i18n="table_header_type">Type</th>
                                        <th data-i18n="table_header_minute">Minute</th>
                                        <th data-i18n="table_header_speed_up">Speed Up (Kb/s)</th>
                                        <th data-i18n="table_header_speed_down">Speed Down (Kb/s)</th>
                                        <th data-i18n="table_header_action">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th  data-i18n="table_body_loading">Loading...</th>
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


<div class="modal fade text-left" id="inlineForm" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel33"  data-i18n="modal_title">Add or Edit Profile</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="create-form" action="#">
                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-6">
                            <label data-i18n="modal_label_profile_name">Profile Name: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" id="name" name="name" class="form-control" value="" placeholder="eg: 1 week plan" required>
                                <span style="display: none; padding-top: 5px;" id="name-available">&nbsp;</span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_label_profile_type">Profile Type: </label>
                            <div class="form-group">
                                <select name="type" id="type" class="select2 form-control">
                                    <option value="countdown"  data-i18n="modal_form_option_countdown">Countdown</option>
                                    <option value="expiration" data-i18n="modal_form_option_expiration">Expiration</option>
                                    <option value="free" data-i18n="modal_form_option_free">Free</option>
                                    <option value="pay-per-use" data-i18n="modal_form_option_pay_as_use">Pay As You Use</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_label_minutes">Minutes: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" id="minutes" name="minutes" class="form-control" value="" placeholder="eg: 180" required>
                                <div style="font-size: smaller; padding: 10px;" class=""  data-i18n="modal_label_minutes_example">
                                    Example: 120 for 2 hour, default 60 minutes
                                </div>
                            </div>
                        </div>
                        
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_label_transfer_quota">Transfer Quota in MB: </label>
                            <div class="form-group">
                                <input type="text" id="vol_limit" name="vol_limit" class="form-control" placeholder="eg: 10240" value="">
                                <div style="font-size: smaller; padding: 10px;" class="" data-i18n="modal_label_quota_example">
                                    0 for unlimited quota
                                </div>
                            </div>
                        </div>
                        
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_label_price">Price: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" id="price" name="price" class="form-control" value="" placeholder="30.00" required>
                                <div style="font-size: smaller; padding: 10px;" class="" data-i18n="modal_label_price_example">
                                    Price for this profile
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_label_advanced_profile">Advanced Profile: </label>
                            <div class="form-group">
                                <fieldset class="form-group">
                                    <select class="select2 form-control" name="advance" id="advance">
                                        <option value="" data-i18n="modal_advanced_profile_option_none">None</option>

                                        <?php

                                        foreach ($kiw_profiles as $record) {

                                            echo "<option value='{$record['name']}'> {$record['name']} </option> \n";

                                        }

                                        ?>

                                    </select>
                                </fieldset>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_label_change_profile">Change profile once reached quota (in percent): </label>
                            <div class="form-group">
                                <input type="text" id="quota_trigger" name="quota_trigger" class="form-control" value="" placeholder="eg: 40%">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_label_integration">Integration: </label>
                            <div class="form-group">
                                <select name="integration" id="integration" class="select2 form-control" data-style="btn-default">
                                    <option value="int"  data-i18n="modal_integration_option_internal">Internal</option>
                                    <option value="bc" data-i18n="modal_integration_option_business">Business Center / Kiosk</option>
                                    <option value="pms" data-i18n="modal_integration_option_property">Property Management System</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_label_idle_timeout">Idle Timeout (Minute): </label>
                            <div class="form-group">
                                <input type="text" id="iddle" name="iddle" class="form-control" value="" placeholder="eg: 30">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label  data-i18n="modal_label_simultaneous_use">Simultaneous Use: </label>
                            <div class="form-group">
                                <input type="text" id="simultaneous" name="simultaneous" class="form-control" value="" placeholder="eg: 100">
                                <div style="font-size: smaller; padding: 10px;" class=""  data-i18n="modal_simultaneous_concurrent">
                                    Concurrent user
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label  data-i18n="modal_label_max_down">Max Download Bandwidth (Kb/s): </label>
                            <div class="form-group">
                                <input type="text" id="bwdown" name="bwdown" class="form-control" value="" placeholder="eg: 1024">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_label_max_up">Max Upload Bandwidth (Kb/s): </label>
                            <div class="form-group">
                                <input type="text" id="bwup" name="bwup" class="form-control" value="" placeholder="eg: 1024">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_label_min_down">Min Download Bandwidth (Kb/s): </label>
                            <div class="form-group">
                                <input type="text" id="min_down" name="min_down" class="form-control" value="" placeholder="eg: 512">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label  data-i18n="modal_label_min_up">Min Upload Bandwidth (Kb/s): </label>
                            <div class="form-group">
                                <input type="text" id="min_up" name="min_up" class="form-control" value="" placeholder="eg: 512">
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <label  data-i18n="modal_label_custom_attribute">[Optional] Custom Attribute (JSON): </label>
                            <div class="form-group">
                                <textarea name="attribute_custom" class="form-control" id="attribute_custom" rows="3"></textarea>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="grace-user">
                                <label  data-i18n="modal_label_delete_unverified">[Optional] Delete Unverified User After ( Minute ): </label>
                                <div class="form-group">
                                    <input type="text" id="grace" name="grace" class="form-control" value="">
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">

                        <input type="hidden" id="id" name="id" value="">

                        <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal"  data-i18n="modal_button_cancel">Cancel</button>
                        <button type="button" class="btn btn-primary round waves-effect waves-light btn-create" data-i18n="modal_button_create">Create</button>
                        <button type="button" class="btn btn-primary round waves-effect waves-light btn-update"  data-i18n="modal_button_update">Update</button>

                    </div>
                </div>
                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
            </form>
        </div>
    </div>
</div>


<script>

    var profile_deletion = JSON.parse('<?= json_encode($kiw_profile_actions)  ?>');

</script>


<?php

require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";

?>
