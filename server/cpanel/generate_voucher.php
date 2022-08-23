<?php

$kiw_page = "Account Generate Voucher";

require_once "includes/include_general.php";
require_once "includes/include_session.php";
require_once "includes/include_header.php";
require_once "includes/include_nav.php";

require_once dirname(__FILE__, 2) . "/admin/includes/include_general.php";

$kiw_tenant = $_SESSION['cpanel']['tenant_id'];

?>


<!-- <div class="be-content">
    <div class="main-content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="col-md-12">
                    <div class="panel panel-default panel-border-color panel-border-color-primary">
                        <div class="panel-heading panel-heading-divider">Filter</div>
                        <div class="panel-body">

                            <div class="col-12">
                                <div class="form-group row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="filter_username" data-i18n="filter_username">Code: </label>
                                            <input type="text" placeholder="Voucher Code" name="filter_username" id="filter_username" value="" class="form-control" autocomplete="off" />
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="filter_status" data-i18n="filter_status">Status: </label>
                                            <select name="filter_status" id="filter_status" class="select2 form-control">
                                                <option value="">All Status</option>
                                                <option selected='selected' value="active">Active</option>
                                                <option value="suspend">Suspend</option>
                                                <option value="expired">Expired</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="filter_profile" data-i18n="filter_profile">Profile: </label>
                                        <input type="text" name="filter_profile" id="filter_profile" value="" class="form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group row">
                            
                                    <div class="col-md-4">
                                        <div class="form-group" style="position:relative; left:auto; display:block;">
                                            <label for="filter_remark" data-i18n="filter_remark">Remark</label>
                                            <input type="text" placeholder="voucher remark" name="filter_remark" id="filter_remark" value="" class="form-control" autocomplete="off" />
                                        </div>
                                    </div>

                             
                                </div>
                                <button type="submit" name='search' id='search' class="btn btn-primary waves-effect waves-light btn-search" data-i18n="login_logins_error_search">Search</button>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div> -->

<div class="be-content">
    <div class="main-content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="col-md-12">
                    <div class="panel panel-default panel-border-color panel-border-color-primary" style="overflow-x: auto;">

                        <div class="panel-heading panel-heading-divider">Manage Invitation Code
                            <button type="button" class="btn btn-primary waves-effect waves-light pull-right create-btn-voucher" data-toggle="modal" data-target="#inlineForm" data-i18n="button_add_voucher">Generate Code</button>
                        </div>

                        <div class="panel-body">
                            <div class="table-responsive">
                                <table id="table1" class="table responsive no-wrap table-condensed table-hover table-bordered table-striped table-data dtr-inline">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Invitation Code</th>
                                            <th>Status</th>
                                            <th>Profile</th>
                                            <th>Price</th>
                                            <th>Creation Date</th>
                                            <th>Expiry Date</th>
                                            <th>Remark</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>


<div class="modal fade text-left" id="inlineForm" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel33" data-i18n="modal_1_title">Add or Edit Invitation Code</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="create-form" action="#" method="post">

                <div class="modal-body">

                    <label data-i18n="modal_1_label_prefix">Prefix: </label> <span class="text-danger">*</span>

                    <?php

                    $kiw_voucher = $kiw_db->query_first("SELECT * FROM kiwire_clouds WHERE tenant_id = '{$kiw_tenant}' LIMIT 1");

                    ?>

                    <div class="form-group">
                        <input type="text" id="prefix" name="prefix" class="form-control" value="<?= $kiw_voucher['voucher_prefix'] ?>" required>
                    </div>

                    <label data-i18n="modal_1_label_quantity">Quantity: </label> <span class="text-danger">*</span>
                    <div class="form-group">
                        <input type="text" id="qty" name="qty" value="1" class="form-control" required>
                    </div>

                    <label data-i18n="modal_1_label_profile">Profile: </label>
                    <div class="form-group">
                        <fieldset class="form-group">
                            <select class="select2 form-control" name="plan" id="plan" data-style="btn-default" tabindex="-98">

                                <?php

                                $kiw_row = $kiw_db->fetch_array("SELECT name FROM kiwire_profiles WHERE tenant_id = '{$kiw_tenant}'");

                                foreach ($kiw_row as $record) {

                                    echo "<option value='{$record['name']}'> {$record['name']} </option> \n";
                                }

                                ?>

                            </select>
                        </fieldset>
                    </div>

                    <label data-i18n="modal_1_label_zone">Zone Restriction: </label>
                    <div class="form-group">
                        <fieldset class="form-group">
                            <select class="select2 form-control" name="zone" id="zone" data-style="btn-default" tabindex="-98">

                                <option value="none" data-i18n="modal_1_quantity_option_none">None</option>

                                <?php

                                $kiw_row = $kiw_db->fetch_array("SELECT * FROM kiwire_allowed_zone WHERE tenant_id = '{$kiw_tenant}'");

                                foreach ($kiw_row as $record) {

                                    echo "<option value='{$record['name']}'> {$record['name']} </option> \n";
                                }

                                ?>

                            </select>
                        </fieldset>
                    </div>

                    <div class="form-group" style="position:relative; left:auto; display:block;">
                        <label for="modal_1_label_expiry" data-i18n="modal_1_label_expiry">Expiry Date: </label>
                        <input type="text" class="form-control format-picker" name="date_expiry" id="date_expiry" value='' placeholder="DD-MM-YYYY">
                    </div>

                    <label data-i18n="modal_1_label_remark">Remark: </label>
                    <div class="form-group">
                        <input type="text" id="remark" name="remark" class="form-control" placeholder="description">
                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="modal_1_footer_button_cancel">Cancel</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-create" data-i18n="modal_1_footer_button_create">Create</button>

                </div>
                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
            </form>
        </div>
    </div>
</div>



<!-- Modal User Details -->
<div class="modal text-left" id="view-voucher" role="dialog">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel16" data-i18n="modal_2_title">Invitation Code Information and Analytics</h4>
                <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button> -->
            </div>

            <div class="modal-body" style="overflow: auto;">

                <div class="user-info">

                    <div class="row mb-50">
                        <div class="col-lg-12">
                            <div class="row text-center mx-0">
                                <div class="col-2 border d-flex align-items-between flex-column py-1">
                                    <p class="mb-50 text-uppercase" data-i18n="modal_2_status">Status</p>
                                    <p class="font-medium-5 text-bold-700 mb-50 user-status" style="font-weight:bold;" data-i18n="modal_2_status_null">NULL</p>
                                </div>
                                <div class="col-2 border-top border-bottom border-right d-flex align-items-between flex-column py-1">
                                    <p class="mb-50 text-uppercase" data-i18n="modal_2_integration">Integration</p>
                                    <p class="font-medium-5 text-bold-700 mb-50 user-integration" style="font-weight:bold;" data-i18n="modal_2_integration_null">NULL</p>
                                </div>
                                <div class="col-2 border-top border-bottom border-right d-flex align-items-between flex-column py-1">
                                    <p class="mb-50 text-uppercase" data-i18n="modal_2_quota">Quota Used (Mb)</p>
                                    <p class="font-medium-5 text-bold-700 mb-50 user-current-quota" style="font-weight:bold;" data-i18n="modal_2_quota_null">NULL</p>
                                </div>
                                <div class="col-2 border-top border-bottom border-right d-flex align-items-between flex-column py-1">
                                    <p class="mb-50 text-uppercase" data-i18n="modal_2_session">Session Time (D:H:M:S)</p>
                                    <p class="font-medium-5 text-bold-700 mb-50 user-current-session" style="font-weight:bold;" data-i18n="modal_2_session_null">NULL</p>
                                </div>
                                <div class="col-2 border-top border-bottom border-right d-flex align-items-between flex-column py-1">
                                    <p class="mb-50 text-uppercase" data-i18n="modal_2_active">Activate Date</p>
                                    <p class="font-medium-5 text-capitalize text-bold-700 mb-50 user-activate" style="font-weight:bold;" data-i18n="modal_2_active_null">NULL</p>
                                </div>
                                <div class="col-2 border-top border-bottom border-right d-flex align-items-between flex-column py-1">
                                    <p class="mb-50 text-uppercase" data-i18n="modal_2_expiry">Expiry Date</p>
                                    <p class="font-medium-5 text-capitalize text-bold-700 mb-50 user-expiry" style="font-weight:bold;" data-i18n="modal_2_expiry_null">NULL</p>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-12">

                            <div class="card text-white">
                                <div class="card-body">

                                    <table class="table table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th colspan="2" style="text-transform: uppercase;" data-i18n="modal_2_thead_voucher_info">Code Information</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td data-i18n="modal_2_tbody_voucher_id">Code Id</td>
                                                <td class="user-username" data-i18n="modal_2_tbody_voucher_null">NULL</td>
                                            </tr>

                                            <tr>
                                                <td data-i18n="modal_2_tbody_date_create">Creation Date</td>
                                                <td class="user-date_create" data-i18n="modal_2_tbody_voucher_null">NULL</td>
                                            </tr>

                                            <tr>
                                                <td data-i18n="modal_2_tbody_remark">Remark</td>
                                                <td class="user-remark" data-i18n="modal_2_tbody_voucher_null">NULL</td>
                                            </tr>


                                            <?php

                                            // populate custom field

                                            foreach ($kiw_fields as $kiw_field) {

                                                if ($kiw_field['display'] != "[empty]") {

                                                    echo "<tr><td>{$kiw_field['display']}</td><td class='user-fields user-field-{$kiw_field['field']}'></td></tr>\n";
                                                }
                                            }

                                            ?>

                                        </tbody>
                                    </table>

                                </div>
                            </div>

                        </div>
                    </div><br>

                    <div class="row">
                        <div class="col-md-12">

                            <div class="card text-white">
                                <div class="card-body">

                                    <table class="table table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th colspan="2" style="text-transform: uppercase;" data-i18n="modal_2_thead_profile_info">Profile Information</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td data-i18n="modal_2_tbody_subscribed">Subcribed</td>
                                                <td class="user-profile" data-i18n="modal_2_tbody_subscribed_null">NULL</td>
                                            </tr>
                                            <tr>
                                                <td data-i18n="modal_2_tbody_current">Current</td>
                                                <td class="user-current-profile" data-i18n="modal_2_tbody_current_null">NULL</td>
                                            </tr>
                                            <tr>
                                                <td data-i18n="modal_2_tbody_type">Type</td>
                                                <td class="user-profile-type" data-i18n="modal_2_tbody_type_null">NULL</td>
                                            </tr>
                                            <tr>
                                                <td data-i18n="modal_2_tbody_price">Price</td>
                                                <td class="user-profile-price" data-i18n="modal_2_tbody_price_null">NULL</td>
                                            </tr>
                                            <tr>
                                                <td data-i18n="modal_2_tbody_idle">Idle Time [ Minutes ]</td>
                                                <td class="user-profile-iddle" data-i18n="modal_2_tbody_idle_null">NULL</td>
                                            </tr>
                                            <tr>
                                                <td data-i18n="modal_2_tbody_simultaneous">Simultaneous Device</td>
                                                <td class="user-profile-simultaneous" data-i18n="modal_2_tbody_simultaneous_null">NULL</td>
                                            </tr>
                                            <tr>
                                                <td data-i18n="modal_2_tbody_downspeed">Download Speed [ Kbps ]</td>
                                                <td class="user-profile-download" data-i18n="modal_2_tbody_downspeed_null">NULL</td>
                                            </tr>
                                            <tr>
                                                <td data-i18n="modal_2_tbody_upspeed">Upload Speed [ Kbps ]</td>
                                                <td class="user-profile-upload" data-i18n="modal_2_tbody_upspeed_null">NULL</td>
                                            </tr>
                                            <tr>
                                                <td data-i18n="modal_2_tbody_quota">Quota Limit [ Megabyte ]</td>
                                                <td class="user-profile-quota" data-i18n="modal_2_tbody_quota_null">NULL</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                </div>
                            </div>

                        </div>
                    </div>


                </div>


                <!-- <div class="user-history" style="display: none;">

                    <div class="row">
                        <div class="col-12">

                            <table class="table table-bordered" id="table-data">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="text-transform: uppercase;" data-i18n="modal_2_thead_no">No</th>
                                        <th style="text-transform: uppercase;" data-i18n="modal_2_thead_login">Login</th>
                                        <th style="text-transform: uppercase;" data-i18n="modal_2_thead_logout">Logout</th>
                                        <th style="text-transform: uppercase;" data-i18n="modal_2_thead_time">Total Time (H:M:S)</th>
                                        <th style="text-transform: uppercase;" data-i18n="modal_2_thead_mac">MAC Address</th>
                                        <th style="text-transform: uppercase;" data-i18n="modal_2_thead_type">Type</th>
                                        <th style="text-transform: uppercase;" data-i18n="modal_2_thead_brand">Brand</th>
                                        <th style="text-transform: uppercase;" data-i18n="modal_2_thead_ip">IP Address</th>
                                        <th style="text-transform: uppercase;" data-i18n="modal_2_thead_ipv6">IPv6 Address</th>
                                        <th style="text-transform: uppercase;" data-i18n="modal_2_thead_volume">Volume (MB)</th>
                                        <th style="text-transform: uppercase;" data-i18n="modal_2_thead_dc_reason">Disconnect Reason</th>
                                    </tr>
                                </thead>
                                <tbody class="user-history-list">
                                </tbody>
                            </table>

                        </div>
                    </div>

                </div> -->

            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="modal_1_footer_button_cancel">Close</button>

            </div>


        </div>
    </div>
</div>


<?php

require_once "includes/include_footer.php";
require_once "../../server/admin/includes/include_datatable.php";

// require_once "../admin/includes/include_datatable.php";

?>


<script>
    var kiw_tenant = "<?php echo $_SESSION['cpanel']['tenant_id']; ?>"
</script>


<script src="../assets/js/datejs/build/date.js"></script>

<link rel="stylesheet" href="../assets/css/bootstrap-datepicker.css">
<script src="../assets/js/bootstrap-datepicker.min.js"></script>