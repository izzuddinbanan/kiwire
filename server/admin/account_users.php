<?php

$kiw['module'] = "Account -> Account -> List";
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


<div class="content-wrapper">

    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Users Account</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                Manage user account to allow user access the internet
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- <div class="row">
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
                                        <label for="filter_username" data-i18n="filter_username">Username: </label>
                                        <input type="text" placeholder="Account username" name="filter_username" id="filter_username" value="" class="form-control" />
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
                                    <input type="text" name="filter_profile" id="filter_profile" value="" class="form-control" />
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group row">
                                <div class="col-md-4">
                                    <div class="form-group" style="position:relative; left:auto; display:block;">
                                        <label for="filter_expired_from" data-i18n="filter_expired_from">Expiry Date From</label>
                                        <input type="text" class="form-control format-picker" name="filter_expired_from" id="filter_expired_from" placeholder="DD-MM-YYYY" value=''>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group" style="position:relative; left:auto; display:block;">
                                        <label for="filter_expired_until" data-i18n="filter_expired_until">Expiry Date Until</label>
                                        <input type="text" class="form-control format-picker" name="filter_expired_until" id="filter_expired_until" placeholder="DD-MM-YYYY" value=''>
                                    </div>
                                </div>

                                <div class="col-md-4">

                                    <?php if ($_SESSION['access_level'] == "superuser") { ?>

                                        <div class="form-group">

                                            <label for="filter_tenant_id" data-i18n="filter_tenant_id">Tenant </label>
                                            <select name="filter_tenant_id" id="filter_tenant_id" class="form-control">
                                                <!-- <option value="<?= $_SESSION['tenant_id'] ?>"><?= $_SESSION['tenant_id'] ?></option> -->
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

                                                        // echo "<option value='{$kiw_tenant['tenant_id']}'>{$kiw_tenant['tenant_id']}</option>";
                                                    }
                                                }

                                                ?>

                                            </select>

                                        </div>

                                    <?php } ?>
                                </div>
                            </div>
                            <button type="submit" name='search' id='search' class="btn btn-primary waves-effect waves-light btn-search" data-i18n="account_user">Search</button>
                        </div>

                    </div>
                </div>
            </div>
        </section>

        <section id="css-classes" class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="card-text">

                        <button type="button" class="btn btn-primary waves-effect waves-light pull-right mb-75 create-btn-user" data-toggle="modal" data-target="#inlineForm" data-i18n="button_add_user">Add User
                        </button>
                        <button type="button" class="btn btn-primary waves-effect waves-light pull-right mr-1 import-btn-user" data-toggle="modal" data-target="#importForm" style="display:none;" data-i18n="button_import_user">Import User
                        </button>

                        <div class="table-responsive">
                            <table id="itemlist" class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="table_header_no">No</th>
                                        <th data-i18n="table_header_username">Username</th>
                                        <th data-i18n="table_header_fullname">Fullname</th>
                                        <th data-i18n="table_header_profile">Profile</th>
                                        <th data-i18n="table_header_status">Status</th>
                                        <th data-i18n="table_header_expiry">Expiry Date</th>
                                        <?php if ($_SESSION['access_level'] == "superuser") { ?>
                                            <th data-i18n="table_header_tenant">Tenant</th>
                                        <?php } ?>
                                        <th data-i18n="table_header_action">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <th data-i18n="table_body_loading">
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
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel33" data-i18n="modal_1_title">Add or Edit User</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="create-form" action="#" method="post">

                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">

                <div class="modal-body">
                    <div class="row">

                        <?php if ($_SESSION['access_level'] == "superuser") { ?>
                            <div class="col-md-12">
                                <label data-i18n="modal_1_label_tenant">Tenant ID:</label>
                                <div class="form-group">
                                    <fieldset class="form-group">
                                        <select class="select2 form-control" name="tenant_id" id="tenant_id">
                                            <?php

                                            if (!empty($_SESSION['tenant_allowed'])) {


                                                $kiw_tenants = explode(",", $_SESSION['tenant_allowed']);

                                                foreach ($kiw_tenants as $kiw_tenant) {

                                                    echo "<option value='{$kiw_tenant}'>{$kiw_tenant}</option>";
                                                }
                                            } else {

                                                $kiw_tenants = $kiw_db->fetch_array("SELECT tenant_id FROM kiwire_clouds");

                                                foreach ($kiw_tenants as $kiw_tenant) {


                                                    if($kiw_tenant['tenant_id'] === $_SESSION['tenant_id']) {

                                                        echo "<option selected='selected' value='{$kiw_tenant['tenant_id']}'>{$kiw_tenant['tenant_id']}</option>";

                                                    } else {

                                                        echo "<option value='{$kiw_tenant['tenant_id']}'>{$kiw_tenant['tenant_id']}</option>";

                                                    }

                                                    // echo "<option value='{$kiw_tenant['tenant_id']}'>{$kiw_tenant['tenant_id']}</option>";
                                                }
                                            }

                                            ?>
                                        </select>
                                    </fieldset>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="col-md-6">
                            <div class="form-group status">
                                <label data-i18n="modal_1_label_status">Status </label>
                                <fieldset class="form-group">
                                    <select name='status' id='status' class="select2 form-control" data-style="btn-default">
                                        <option selected="selected" value="active" data-i18n="modal_1_label_option_active">Active</option>
                                        <option value="suspend" data-i18n="modal_1_label_option_suspended">Suspended</option>
                                        <option value="expired" data-i18n="modal_1_label_option_expired">Expired</option>
                                    </select>
                                </fieldset>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group username">
                                <label data-i18n="modal_1_label_username">Username: </label> <span class="text-danger">*</span>
                                <div class="form-group">
                                    <input type="text" id="username" name="username" class="form-control" placeholder="Username" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_label_fullname">Fullname: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" id="fullname" name="fullname" class="form-control" placeholder="Fullname" required>
                            </div>
                        </div>
                            
                        <div class="col-md-6">
                            <label data-i18n="modal_1_label_password">Password: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="password" id="password" name="password" class="form-control" placeholder="Uppercase/lowercase/numeric/special character" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_label_email">Email: </label>
                            <div class="form-group">
                                <input type="text" id="email_address" name="email_address" class="form-control" placeholder="email@address.com">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_label_phone">Phone No: </label>
                            <div class="form-group">
                                <input type="text" id="phone_number" name="phone_number" class="form-control" placeholder="0123456789">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_label_mac">MAC Address: </label>
                            <div class="form-group">
                                <input type="text" id="allowed_mac" name="allowed_mac" class="form-control" placeholder="eg: 30:F7:72:63:84:0D">
                                <label class="label" style="font-size: smaller; padding: 10px;" data-i18n="modal_1_label_mac_label">Separated by comma </label>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_label_profile">Link to Profile: </label>
                            <div class="form-group">
                                <fieldset class="form-group">
                                    <select class="select2 form-control" name="profile_subs" id="profile_subs">

                                        <?php

                                        $kiw_row = $kiw_db->fetch_array("SELECT name FROM kiwire_profiles WHERE tenant_id = '{$tenant_id}'");

                                        foreach ($kiw_row as $record) {

                                            echo "<option value='{$record['name']}'>{$record['name']}</option> \n";
                                        }
                                        ?>

                                    </select>
                                </fieldset>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label data-i18n="modal_1_label_auth_module">Authentication Integration Module: </label>
                            <div class="form-group">
                                <fieldset class="form-group">
                                    <select name=integration id=integration class="select2 form-control" data-style="btn-default" tabindex="-98">
                                        <option value="int" data-i18n="modal_1_label_internal">Internal</option>
                                        <option value="pms" data-i18n="modal_1_label_pms">Integration: PMS</option>
                                        <option value="bc" data-i18n="modal_1_label_bc">Integration: Business Center</option>
                                        <option value="ms_ad" data-i18n="modal_1_label_ad">Integration: Active Directory</option>
                                        <option value="ldap" data-i18n="modal_1_label_ldap">Integration: LDAP</option>
                                    </select>
                                </fieldset>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_label_zone">Zone Restriction: </label>
                            <div class="form-group">
                                <fieldset class="form-group">
                                    <select class="select2 form-control" name="allowed_zone" id="allowed_zone" data-style="btn-default" tabindex="-98">

                                        <option value="none" data-i18n="modal_1_label_zone_option_1">None</option>

                                        <?php

                                        $kiw_row = $kiw_db->fetch_array("SELECT * FROM kiwire_allowed_zone WHERE tenant_id = '{$tenant_id}'");

                                        foreach ($kiw_row as $record) {

                                            echo "<option value='{$record['name']}'>{$record['name']}</option> \n";
                                        }

                                        ?>

                                    </select>
                                </fieldset>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group" style="position:relative; left:auto; display:block;">
                                <label for="modal_1_label_expiry_date" data-i18n="modal_1_label_expiry_date">Expiry Date: </label>
                                <input type="text" class="form-control format-picker" name="date_expiry" id="date_expiry" placeholder="MM-DD-YYYY" value=''>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_label_remark">Remark: </label>
                            <div class="form-group">
                                <input type="text" id="remark" name="remark" placeholder="remark" class="form-control">
                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <input type="hidden" id="reference" name="reference" value="">
                    <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="modal_1_button_cancel">Cancel
                    </button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-create" data-i18n="modal_1_button_create">Create
                    </button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-update" data-i18n="modal_1_button_update">Update
                    </button>

                </div>

            </form>
        </div>
    </div>
</div>


<!-- Modal User Details -->
<div class="modal fade text-left" id="viewUser" role="dialog">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel16" data-i18n="modal_2_title">User Information and Analytics</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" style="overflow: auto;">

                <div class="user-info">

                    <div class="row mb-50">
                        <div class="col-12  d-none d-md-block">
                            <div class="row text-center mx-0">
                                <div class="col-2 border d-flex align-items-between flex-column py-1 status-point">
                                    <p class="mb-50 text-uppercase" data-i18n="modal_2_status">Status</p>
                                    <p class="font-medium-5 text-bold-700 mb-50 user-status" data-i18n="modal_2_status_null"></p>
                                    <div>
                                        <!-- spinner here -->
                                    </div>
                                </div>
                                <div class="col-2 border-top border-bottom border-right d-flex align-items-between flex-column py-1">
                                    <p class="mb-50 text-uppercase" data-i18n="modal_2_integration">Integration</p>
                                    <p class="font-medium-5 text-bold-700 mb-50 user-integration" data-i18n="modal_2_integration_null">NULL</p>
                                </div>
                                <div class="col-2 border-top border-bottom border-right d-flex align-items-between flex-column py-1">
                                    <p class="mb-50 text-uppercase" data-i18n="modal_2_quota_used">Quota Used (Mb)</p>
                                    <p class="font-medium-5 text-bold-700 mb-50 user-current-quota" data-i18n="modal_2_quota_null">NULL</p>
                                </div>
                                <div class="col-2 border-top border-bottom border-right d-flex align-items-between flex-column py-1">
                                    <p class="mb-50 text-uppercase" data-i18n="modal_2_session_time">Session Time (D:H:M:S)</p>
                                    <p class="font-medium-5 text-bold-700 mb-50 user-current-session" data-i18n="modal_2_session_null">NULL</p>
                                </div>
                                <div class="col-2 border-top border-bottom border-right d-flex align-items-between flex-column py-1">
                                    <p class="mb-50 text-uppercase" data-i18n="modal_2_active_date">Activate Date</p>
                                    <p class="font-medium-5 text-capitalize text-bold-700 mb-50 user-activate" data-i18n="modal_2_active_null">NULL</p>
                                </div>
                                <div class="col-2 border-top border-bottom border-right d-flex align-items-between flex-column py-1">
                                    <p class="mb-50 text-uppercase" data-i18n="modal_2_expiry_date">Expiry Date</p>
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
                                        <div class="d-flex justify-content-between mb-25" style="margin-top:1em;">

                                            <div class="browser-info">
                                                <p class="mb-25">Remaining Quota</p>
                                                <h6 id="quota-remaining"></h6>
                                            </div>

                                        </div>
                                        <div id="quota-remaining-progress-bar" class="progress progress-bar-primary mb-2">
                                            <div id="quota-remaining-progress" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>

                                        <div class="d-flex justify-content-between mb-25" style="margin-top:3em;">

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


                    <div class="row">

                        <div class="col-sm-4">

                            <div class="card text-white">
                                <div class="card-body">

                                    <table class="table table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th colspan="5" style="text-transform: uppercase;" data-i18n="modal_2_thead_acc_info">Account Information</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td data-i18n="modal_2_tbody_username">Username</td>
                                                <td class="user-username" data-i18n="modal_2_tbody_username_null">NULL</td>
                                            </tr>

                                            <tr>
                                                <td data-i18n="modal_2_tbody_fullname">Full Name</td>
                                                <td class="user-fullname" data-i18n="modal_2_tbody_fullname_null">NULL</td>
                                            </tr>

                                            <tr>
                                                <td data-i18n="modal_2_tbody_email">Email Address</td>
                                                <td class="user-email-address" data-i18n="modal_2_tbody_email_null">NULL</td>
                                            </tr>

                                            <tr>
                                                <td data-i18n="modal_2_tbody_phone">Phone Number</td>
                                                <td class="user-phone-number" data-i18n="modal_2_tbody_phone_null">NULL</td>
                                            </tr>

                                            <?php


                                            // populate custom field

                                            foreach ($kiw_fields as $kiw_field) {

                                                if (!in_array($kiw_field['field'], array("fullname", "email_address", "phone_number", "")) &&  $kiw_field['display'] != "[empty]") {

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
                                                <th colspan="5" style="text-transform: uppercase;" data-i18n="modal_2_thead_profile_info">Profile Information</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td data-i18n="modal_2_td_subscribed">Subcribed</td>
                                                <td class="user-profile" data-i18n="modal_2_td_profile_null">NULL</td>
                                            </tr>
                                            <tr>
                                                <td data-i18n="modal_2_td_current">Current</td>
                                                <td class="user-current-profile" data-i18n="modal_2_td_current_null">NULL</td>
                                            </tr>
                                            <tr>
                                                <td data-i18n="modal_2_td_type">Type</td>
                                                <td class="user-profile-type" data-i18n="modal_2_td_type_null">NULL</td>
                                            </tr>
                                            <tr>
                                                <td data-i18n="modal_2_td_price">Price</td>
                                                <td class="user-profile-price" data-i18n="modal_2_td_price_null">NULL</td>
                                            </tr>
                                            <tr>
                                                <td data-i18n="modal_2_td_idle">Idle Time [ Minutes ]</td>
                                                <td class="user-profile-iddle" data-i18n="modal_2_td_idle_null">NULL</td>
                                            </tr>
                                            <tr>
                                                <td data-i18n="modal_2_td_simultaneous">Simultaneous Device</td>
                                                <td class="user-profile-simultaneous" data-i18n="modal_2_td_simultaneous_null">NULL</td>
                                            </tr>
                                            <tr>
                                                <td data-i18n="modal_2_td_downspeed">Download Speed [ Kbps ]</td>
                                                <td class="user-profile-download" data-i18n="modal_2_td_downspeed_null">NULL</td>
                                            </tr>
                                            <tr>
                                                <td data-i18n="modal_2_td_upspeed">Upload Speed [ Kbps ]</td>
                                                <td class="user-profile-upload" data-i18n="modal_2_td_upspeed_null">NULL</td>
                                            </tr>
                                            <tr>
                                                <td data-i18n="modal_2_td_limit">Quota Limit [ Megabyte ]</td>
                                                <td class="user-profile-quota" data-i18n="modal_2_td_limit_null">NULL</td>
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
                                                <th colspan="2" style="text-transform: uppercase;" data-i18n="modal_2_thead_registered_device">Registered Device</th>
                                            </tr>
                                        </thead>
                                        <tbody class="connected-devices">
                                            <tr>
                                                <td data-i18n="modal_2_td_mac_address">Mac Address</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                </div>
                            </div>

                        </div> -->
                    </div>
                    
                    <div class="row div-security">
                        <div class="col-12">
                            <div class="card" >
                                <div class="card-header">
                                    <h4 class="card-title">User Security Detected</h4>
                                    <div class="pull-right div-btn">
                                        
                                    </div>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered" id="security-data">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="text-transform: uppercase;">Date</th>
                                                <th style="text-transform: uppercase;">Severity</th>
                                                <th style="text-transform: uppercase;">Vulnerability Name</th>
                                            </tr>
                                        </thead>
                                        <tbody class="user-security-list">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
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
                                        <th style="text-transform: uppercase;" data-i18n="modal_2_thead_totaltime">Total Time (H:M:S)</th>
                                        <th style="text-transform: uppercase;" data-i18n="modal_2_thead_mac_address">MAC Address</th>
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
                <button class="btn btn-primary waves-effect waves-light btn-user-history"><span data-i18n="modal_2_footer_show_history">Show History</span></button>
            </div>

        </div>
    </div>
</div>


<div class="modal fade text-left" id="import_user" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <form class="import_account" action="">

                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel33" data-i18n="modal_3_title">Import User</h4>
                    <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="form-group mt-50">
                        <span class="" data-i18n="modal_3_body_only_our_template">Only file that using our template will be processed.</span>
                        <a href="/user/templates/import_user.csv">
                            <span data-i18n="modal_3_body_download_sample_template">Click here to download sample template.</span>
                        </a>
                    </div>


                    <?php if ($_SESSION['access_level'] == "superuser") { ?>
                        <label data-i18n="modal_3_label_tenant">Tenant ID:</label>
                        <div class="form-group">
                            <fieldset class="form-group">
                                <select class="select2 form-control" name="tenant_id_import" id="tenant_id_import">
                                    <?php

                                    if (!empty($_SESSION['tenant_allowed'])) {


                                        $kiw_tenants = explode(",", $_SESSION['tenant_allowed']);

                                        foreach ($kiw_tenants as $kiw_tenant) {

                                            echo "<option value='{$kiw_tenant}'>{$kiw_tenant}</option>";
                                        }
                                    } else {

                                        $kiw_tenants = $kiw_db->fetch_array("SELECT tenant_id FROM kiwire_clouds");

                                        foreach ($kiw_tenants as $kiw_tenant) {

                                            echo "<option value='{$kiw_tenant['tenant_id']}'>{$kiw_tenant['tenant_id']}</option>";
                                        }
                                    }

                                    ?>
                                </select>
                            </fieldset>
                        </div>
                    <?php } ?>

                    <div class="form-group">
                        <label data-i18n="modal_3_label_csv">CSV with account information: </label>
                        <div class="custom-file">
                            <input type="file" name="accounts_file" accept=".csv" class="custom-file-input" />
                            <label class="custom-file-label" for="logo" data-i18n="modal_3_label_choose_file">Choose file</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label data-i18n="modal_3_label_profile">Profile: </label>
                        <div class="form-group">
                            <select name="iprofile" id="iprofile" class="form-control">
                                <?php

                                $kiw_row = $kiw_db->fetch_array("SELECT name FROM kiwire_profiles WHERE tenant_id = '{$tenant_id}'");

                                foreach ($kiw_row as $record) {

                                    echo "<option value='{$record['name']}'>{$record['name']}</option> \n";
                                }

                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label data-i18n="modal_3_label_expiry">Expiry Date: </label> <span class="text-danger">*</span>
                        <div class="form-group">
                            <input type="date" id="iexpire" name="iexpire" class="form-control datepicker" required="">
                        </div>
                    </div>

                    <div class="form-group">
                        <label data-i18n="modal_3_label_integration">Integration: </label>
                        <div class="form-group">
                            <select name="iintegration" id="iintegration" class="form-control">
                                <option value="int" data-i18n="modal_3_integration_internal">Internal</option>
                                <option value="pms" data-i18n="modal_3_integration_pms">Integration: PMS</option>
                                <option value="ms_ad" data-i18n="modal_3_integration_ad">Integration: Active Directory</option>
                                <option value="ldap" data-i18n="modal_3_integration_ldap">Integration: LDAP</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label data-i18n="modal_3_label_zone">Zone Restriction: </label>
                        <div class="form-group">
                            <select name="izone" id="izone" class="form-control">
                                <option value="none" data-i18n="modal_3_zone_none">None</option>
                                <?php

                                $kiw_row = $kiw_db->fetch_array("SELECT * FROM kiwire_allowed_zone WHERE tenant_id = '{$tenant_id}'");

                                foreach ($kiw_row as $record) {

                                    echo "<option value='{$record['name']}'>{$record['name']}</option> \n";
                                }

                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label data-i18n="modal_3_label_status">Status: </label>
                        <div class="form-group">
                            <select name="istatus" id="istatus" class="form-control">
                                <option value="active" data-i18n="modal_3_status_active">Active</option>
                                <option value="suspend" data-i18n="modal_3_status_suspend">Suspend</option>
                            </select>
                        </div>
                    </div>

                    <input type="hidden" name="action" value="import_account" style="display: none;">

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-danger round waves-effect waves-light" data-dismiss="modal" data-i18n="modal_3_footer_button_cancel">Cancel</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-import" data-i18n="modal_3_footer_button_import">Import</button>
                </div>

            </form>

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
                                                    <label for="filter_username" data-i18n="filter_username">Username: </label>
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
                                                    <label for="filter_expired_from" data-i18n="filter_expired_from">Expiry Date From</label>
                                                    <input type="text" class="form-control format-picker" name="filter_expired_from" id="filter_expired_from" value=''>
                                                </div>


                                                <div class="form-group" style="position:relative; left:auto; display:block;">
                                                    <label for="filter_expired_until" data-i18n="filter_expired_until">Expiry Date Until</label>
                                                    <input type="text" class="form-control format-picker" name="filter_expired_until" id="filter_expired_until" value=''>
                                                </div>


                                                <!- ?php if ($_SESSION['access_level'] == "superuser") { ?-->

                                                    <!-- <div class="form-group">

                                                        <label for="filter_tenant_id" data-i18n="filter_tenant_id">Tenant </label>
                                                        <select name="filter_tenant_id" id="filter_tenant_id" class="form-control">
                                                            <option value="">All Tenant</option> -->
                                                            <!--?php

                                                            if (!empty($_SESSION['tenant_allowed'])) {


                                                                $kiw_tenants = explode(",", $_SESSION['tenant_allowed']);

                                                                foreach ($kiw_tenants as $kiw_tenant) {

                                                                    echo "<option value='{$kiw_tenant}'>{$kiw_tenant}</option>";
                                                                }
                                                            } else {

                                                                $kiw_tenants = $kiw_db->fetch_array("SELECT SQL_CACHE tenant_id FROM kiwire_clouds");

                                                                foreach ($kiw_tenants as $kiw_tenant) {

                                                                    echo "<option value='{$kiw_tenant['tenant_id']}'>{$kiw_tenant['tenant_id']}</option>";
                                                                }
                                                            }

                                                            ?-->

                                                        <!-- </select>

                                                    </div> -->

                                                <!--?php } ?-->


                                            <!-- </div>

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
        var max_column = 7
    <?php } else { ?>
        var max_column = 6
    <?php } ?>
</script>


<script src="/app-assets/vendors/js/charts/apexcharts.min.js"></script>
<script src="/assets/js/datejs/build/date.js"></script>


<link rel="stylesheet" href="/assets/css/bootstrap-datepicker.css">
<script src="/assets/js/bootstrap-datepicker.min.js"></script>