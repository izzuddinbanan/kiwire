<?php

$kiw['module'] = "Account -> Voucher -> List";
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

$kiw_fields = json_decode($kiw_fields, true);

if (!is_array($kiw_fields)) $kiw_fields = array();


?>
<style>
    .dataTables_filter {
        display: none !important;
    }
</style>

<div class="content-wrapper">

    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Voucher</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                Manage vouchers
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 
    <div class="row">
        <div class="col-12 mb-1">
            <button id="filter-btn" class="float-right btn btn-icon btn-primary btn-xs fa fa-filter"></button>
        </div>
    </div> -->


    <div class="content-body">

        <section id="css-classes" class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="card-text">
                        <div class="col-12">
                            <div class="form-group row">
                                <h6 class="text-bold-500" data-i18n="login_logins_error_date_search">FILTER :</h6>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="filter_username" data-i18n="filter_username">Code: </label>
                                        <input type="text" placeholder="Voucher Code" name="filter_username" id="filter_username" value="" class="form-control"  autocomplete="off" />
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
                                        <label for="filter_created_date" data-i18n="filter_created_date">Creation Date</label>
                                        <input type="text" class="form-control format-picker" name="filter_created_date" id="filter_created_date" value='' placeholder="DD-MM-YYYY">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group" style="position:relative; left:auto; display:block;">
                                        <label for="filter_expired_date" data-i18n="filter_expired_date">Expiry Date</label>
                                        <input type="text" class="form-control format-picker" name="filter_expired_date" id="filter_expired_date" value='' placeholder="DD-MM-YYYY">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group" style="position:relative; left:auto; display:block;">
                                        <label for="filter_remark" data-i18n="filter_remark">Remark</label>
                                        <input type="text" placeholder="voucher remark" name="filter_remark" id="filter_remark" value="" class="form-control" autocomplete="off" />
                                    </div>
                                </div>

                                <div class="col-md-4">

                                    <?php if ($_SESSION['access_level'] == "superuser") { ?>

                                        <div class="form-group">

                                            <label for="filter_tenant_id" data-i18n="filter_tenant_id">Tenant </label>
                                            <select name="filter_tenant_id" id="filter_tenant_id" class="form-control">
                                                <option value="">All Tenant</option>

                                                <?php

                                                if (!empty($_SESSION['tenant_allowed'])) {


                                                    $kiw_tenants = explode(",", $_SESSION['tenant_allowed']);

                                                    foreach ($kiw_tenants as $kiw_tenant) {

                                                        echo "<option value='{$kiw_tenant}'>{$kiw_tenant}</option>";
                                                    }
                                                } else {

                                                    $kiw_tenants = $kiw_db->fetch_array("SELECT SQL_CACHE tenant_id FROM kiwire_clouds");

                                                    foreach ($kiw_tenants as $kiw_tenant) {

                                                        if($kiw_tenant['tenant_id'] === $_SESSION['tenant_id']) {

                                                            echo "<option selected='selected' value='{$kiw_tenant['tenant_id']}'>{$kiw_tenant['tenant_id']}</option>";

                                                        } else {

                                                            echo "<option value='{$kiw_tenant['tenant_id']}'>{$kiw_tenant['tenant_id']}</option>";

                                                        }

                                                    }
                                                }

                                                ?>

                                            </select>

                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <button type="submit" name='search' id='search' class="btn btn-primary waves-effect waves-light btn-search" data-i18n="login_logins_error_search">Search</button>
                        </div>

                    </div>
                </div>
            </div>
        </section>

        <section id="css-classes" class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="card-text">

                        <button type="button" class="btn btn-primary waves-effect waves-light pull-right create-btn-voucher" data-toggle="modal" data-target="#inlineForm" data-i18n="button_add_voucher">Add Voucher</button>

                        <div class="table-responsive">
                            <table class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="table_thead_no">No</th>
                                        <th data-i18n="table_thead_code">Code</th>
                                        <th data-i18n="table_thead_status">Status</th>
                                        <th data-i18n="table_thead_profile">Profile</th>
                                        <th data-i18n="table_thead_price">Price</th>
                                        <th data-i18n="table_thead_creation_date">Creation Date</th>
                                        <th data-i18n="table_thead_expiry_date">Expiry Date</th>
                                        <th data-i18n="table_thead_remark">Remark</th>
                                        <?php if ($_SESSION['access_level'] == "superuser") { ?>
                                            <th data-i18n="table_header_tenant">Tenant</th>
                                        <?php } ?>
                                        <th data-i18n="table_thead_action">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <th data-i18n="table_tbody_loading">
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



<div class="modal fade text-left" id="inlineForm" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel33" data-i18n="modal_1_title">Add or Edit Voucher</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="create-form" action="#" method="post">

                <div class="modal-body">

                    <label data-i18n="modal_1_label_prefix">Prefix: </label> <span class="text-danger">*</span>

                    <?php

                    $kiw_voucher = $kiw_db->query_first("SELECT * FROM kiwire_clouds WHERE tenant_id = '{$tenant_id}' LIMIT 1");

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

                                $kiw_row = $kiw_db->fetch_array("SELECT name FROM kiwire_profiles WHERE tenant_id = '{$tenant_id}'");

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

                                $kiw_row = $kiw_db->fetch_array("SELECT * FROM kiwire_allowed_zone WHERE tenant_id = '{$tenant_id}'");

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
<div class="modal fade text-left" id="viewVoucher" role="dialog">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel16" data-i18n="modal_2_title">Voucher Information and Analytics</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" style="overflow: auto;">

                <div class="user-info">

                    <div class="row mb-50">
                        <div class="col-12 d-none d-md-block">
                            <div class="row text-center mx-0">
                                <div class="col-2 border d-flex align-items-between flex-column py-1">
                                    <p class="mb-50 text-uppercase" data-i18n="modal_2_status">Status</p>
                                    <p class="font-medium-5 text-bold-700 mb-50 user-status" data-i18n="modal_2_status_null">NULL</p>
                                </div>
                                <div class="col-2 border-top border-bottom border-right d-flex align-items-between flex-column py-1">
                                    <p class="mb-50 text-uppercase" data-i18n="modal_2_integration">Integration</p>
                                    <p class="font-medium-5 text-bold-700 mb-50 user-integration" data-i18n="modal_2_integration_null">NULL</p>
                                </div>
                                <div class="col-2 border-top border-bottom border-right d-flex align-items-between flex-column py-1">
                                    <p class="mb-50 text-uppercase" data-i18n="modal_2_quota">Quota Used (Mb)</p>
                                    <p class="font-medium-5 text-bold-700 mb-50 user-current-quota" data-i18n="modal_2_quota_null">NULL</p>
                                </div>
                                <div class="col-2 border-top border-bottom border-right d-flex align-items-between flex-column py-1">
                                    <p class="mb-50 text-uppercase" data-i18n="modal_2_session">Session Time (D:H:M:S)</p>
                                    <p class="font-medium-5 text-bold-700 mb-50 user-current-session" data-i18n="modal_2_session_null">NULL</p>
                                </div>
                                <div class="col-2 border-top border-bottom border-right d-flex align-items-between flex-column py-1">
                                    <p class="mb-50 text-uppercase" data-i18n="modal_2_active">Activate Date</p>
                                    <p class="font-medium-5 text-capitalize text-bold-700 mb-50 user-activate" data-i18n="modal_2_active_null">NULL</p>
                                </div>
                                <div class="col-2 border-top border-bottom border-right d-flex align-items-between flex-column py-1">
                                    <p class="mb-50 text-uppercase" data-i18n="modal_2_expiry">Expiry Date</p>
                                    <p class="font-medium-5 text-capitalize text-bold-700 mb-50 user-expiry" data-i18n="modal_2_expiry_null">NULL</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 d-sm-block  d-xs-block d-md-none ">

                            <div class="card">
                                
                                <div class="card-body">

                                    <div class="col-md-12">

                                        <table width="100%">
                                            <tr>
                                                <td><p class="mb-50 text-uppercase" data-i18n="modal_2_status">Status</p></td>
                                                <td width="1%">:</td>
                                                <td><p class="text-bold-700 user-status" data-i18n="modal_2_status_null">NULL</p></td>
                                            </tr>

                                            <tr>
                                                <td><p class="mb-50 text-uppercase" data-i18n="modal_2_integration">Integration</p></td>
                                                <td width="1%">:</td>
                                                <td><p class="text-bold-700 user-integration" data-i18n="modal_2_status_null">NULL</p></td>
                                            </tr>

                                            <tr>
                                                <td><p class="mb-50 text-uppercase" data-i18n="modal_2_quota">Quota Used (Mb)</p></td>
                                                <td width="1%">:</td>
                                                <td><p class="text-bold-700  user-current-quota" data-i18n="modal_2_status_null">NULL</p></td>
                                            </tr>

                                            <tr>
                                                <td><p class="mb-50 text-uppercase" data-i18n="modal_2_session">Session Time (D:H:M:S)</p></td>
                                                <td width="1%">:</td>
                                                <td><p class="text-bold-700 user-current-session" data-i18n="modal_2_session_null">NULL</p></td>
                                            </tr>

                                            <tr>
                                                <td><p class="mb-50 text-uppercase" data-i18n="modal_2_active">Activate Date</p></td>
                                                <td width="1%">:</td>
                                                <td><p class="text-bold-700 user-activate" data-i18n="modal_2_active_null">NULL</p></td>
                                            </tr>

                                            <tr>
                                                <td><p class="mb-50 text-uppercase" data-i18n="modal_2_expiry">Expiry Date</p></td>
                                                <td width="1%">:</td>
                                                <td><p class="text-bold-700 user-expiry" data-i18n="modal_2_expiry_null">NULL</p></td>
                                            </tr>

                                            
                                        </table>
                                        
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>


                    <div class="row">

                        <div class="col-sm-8">
                            <div class="card" style="min-height: 300px;">

                                <div class="card-header d-flex justify-content-between pb-0">
                                    <h4 class="card-title"></h4>
                                </div>

                                <div class="card-content">

                                    <div class="card-body">
                                        <div style="text-align:center;" id="line-chart"></div>
                                    </div>

                                </div>

                            </div>
                        </div>

                        <div class="col-md-4">

                            <div class="card" style="min-height: 300px;">
                                <div class="card-header">
                                    <h4 class="card-title">Account Usage</h4>
                                </div>
                                <div class="card-content">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-25">

                                            <div class="browser-info">
                                                <p class="mb-25">Remaining Quota</p>
                                                <h6 id="quota-remaining"></h6>
                                            </div>

                                        </div>
                                        <div id="quota-remaining-progress-bar" class="progress progress-bar-primary mb-2">
                                            <div id="quota-remaining-progress" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>

                                        <div class="d-flex justify-content-between mb-25">

                                            <div class="browser-info">
                                                <p class="mb-25">Remaining Time</p>
                                                <h6 id="time-remaining"></h6>
                                            </div>

                                        </div>
                                        <div id="time-remaining-progress-bar" class="progress progress-bar-primary mb-2">
                                            <div id="time-remaining-progress" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>


                    <!--                    <div class="row mb-50">-->
                    <!--                        <div class="col-12">-->
                    <!--                            <div class="row mx-0" style="min-height: 300px;">-->
                    <!--                                <div class="col-8 border d-flex"><div style="width: 100%;" id="user-chart-1"></div></div>-->
                    <!--                                <div class="col-4 border-top border-right border-bottom d-flex"><div style="width: 100%;" id="user-chart-2"></div></div>-->
                    <!--                            </div>-->
                    <!--                        </div>-->
                    <!--                    </div>-->


                    <div class="row">

                        <div class="col-sm-4">

                            <div class="card text-white">
                                <div class="card-body">

                                    <table class="table table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th colspan="2" style="text-transform: uppercase;" data-i18n="modal_2_thead_voucher_info">Voucher Information</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td data-i18n="modal_2_tbody_voucher_id">Voucher Id</td>
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


                        <div class="col-sm-4">

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


                        <!-- <div class="col-sm-4">

                            <div class="card text-white">
                                <div class="card-body">

                                    <table class="table table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th colspan="3" style="text-transform: uppercase;" data-i18n="modal_2_thead_registered_device">Registered Device</th>
                                            </tr>
                                        </thead>
                                        <tbody class="connected-devices">
                                            <tr>
                                                <td data-i18n="modal_2_tbody_mac_address">Mac Address</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                </div>
                            </div>

                        </div> -->
                    </div>

                </div>


                <div class="user-history" style="display: none;">

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

                </div>


            </div>

            <div class="modal-footer">
                <button class="btn btn-primary waves-effect waves-light btn-user-history"><span data-i18n="modal_2_show_history">Show History</span></button>
            </div>

        </div>
    </div>
</div>

<!-- <div class="modal fade text-left" id="filter_modal" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="mythememodal">Filter</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">

                        <div class="card-content">
                            <div class="card-body">

                                <form class="form form-vertical">
                                    <div class="form-body">
                                        <div class="row">

                                            <div class="col-12">

                                                <div class="form-group">
                                                    <label for="filter_username" data-i18n="filter_username">Code: </label>
                                                    <input type="text" placeholder="" name="filter_username" id="filter_username" value="" class="form-control" />
                                                </div>


                                                <div class="form-group">
                                                    <label for="filter_status" data-i18n="filter_status">Status: </label>
                                                    <select name="filter_status" id="filter_status" class="form-control">
                                                        <option value="">All Status</option>
                                                        <option value="active">Active</option>
                                                        <option value="suspend">Suspend</option>
                                                        <option value="expired">Expired</option>
                                                    </select>
                                                </div>


                                                <div class="form-group">
                                                    <label for="filter_profile" data-i18n="filter_profile">Profile: </label>
                                                    <input type="text" name="filter_profile" id="filter_profile" value="" class="form-control" />
                                                </div>


                                                <div class="form-group" style="position:relative; left:auto; display:block;">
                                                    <label for="filter_created_date" data-i18n="filter_created_date">Creation Date</label>
                                                    <input type="text" class="form-control format-picker" name="filter_created_date" id="filter_created_date" value=''>
                                                </div>

                                                <div class="form-group" style="position:relative; left:auto; display:block;">
                                                    <label for="filter_expired_date" data-i18n="filter_expired_date">Expiry Date</label>
                                                    <input type="text" class="form-control format-picker" name="filter_expired_date" id="filter_expired_date" value=''>
                                                </div>

                                            </div>

                                            <div class="col-12">
                                                <button type="button" id="filter-data" class="btn btn-primary round mr-1 mb-1 waves-effect waves-light pull-right">Filter</button>
                                                <button type="reset" class="btn btn-danger round mr-1 mb-1 waves-effect waves-light pull-right" data-dismiss="modal">Cancel</button>
                                            </div>

                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div> -->


<?php

require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";

?>

<script>
    <?php if ($_SESSION['access_level'] == "superuser") { ?>
        var max_column = 9
    <?php } else { ?>
        var max_column = 8
    <?php } ?>
</script>


<script src="/assets/js/datejs/build/date.js"></script>

<link rel="stylesheet" href="/assets/css/bootstrap-datepicker.css">
<script src="/assets/js/bootstrap-datepicker.min.js"></script>