<?php

$kiw['module'] = "Account -> Topup Code";
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


?>


<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Topup Code</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                Generate and manage topup code
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
                        <button type="button" class="btn btn-primary waves-effect waves-light pull-right create-btn-profile" data-i18n="button_add_profile">
                            Generate Topup Code
                        </button>

                        <div class="table-responsive">
                            <table class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="table_header_no">No</th>
                                        <th data-i18n="table_header_price">Price</th>
                                        <th data-i18n="table_header_name">Code</th>
                                        <th data-i18n="table_header_quota">Quota</th>
                                        <th data-i18n="table_header_time">Time</th>
                                        <th data-i18n="table_header_status">Status</th>
                                        <th data-i18n="table_header_account">Account</th>
                                        <th data-i18n="table_header_activated_date">Activated Date</th>
                                        <th data-i18n="table_header_action">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="6" data-i18n="table_body_loading">Loading...</td>
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
                <h4 class="modal-title" id="myModalLabel33" data-i18n="modal_title">Add or Edit Topup Details</h4>

                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="create-form" action="#">

                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label data-i18n="modal_label">Prefix: </label> <span class="text-danger">*</span>
                            <?php
                            $kiw_topup_code = $kiw_db->query_first("SELECT * FROM kiwire_clouds WHERE tenant_id = '{$tenant_id}' LIMIT 1");
                            ?>

                            <div class="form-group">
                                <input type="text" id="prefix" name="prefix" class="form-control" value="<?= $kiw_topup_code['topup_prefix'] ?>" required>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <label data-i18n="modal_label_">Plan Name</label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" id="plan" name="plan" class="form-control" value="" placeholder="eg: 1 Hr Plan" required autocomplete="off">
                            </div>
                        </div>
                        
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_label_">Number of Code</label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="number" id="ncode" name="ncode" class="form-control" value="10"  required autocomplete="off">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_label_">Price</label>
                            <div class="form-group">
                                <input type="text" id="price" name="price" class="form-control" value="" placeholder="eg: 20.00" required autocomplete="off">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_label_">Code Length (minimum length:8)</label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="number" id="clength" name="clength" class="form-control" value="10" required min="8" autocomplete="off">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_label_">Additional transfer quota [ in MB ]:</label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="number" id="tquota" name="tquota" class="form-control" placeholder="eg: 10240" required autocomplete="off">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_label_">Additional minutes:</label>
                            <div class="form-group">
                                <input type="number" id="minutes" name="minutes" class="form-control" placeholder="eg: 60" autocomplete="off">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_label_">Expiry Date: </label>
                            <div class="form-group" style="position:relative; left:auto; display:block;">
                                <input type="text" class="form-control format-picker" name="date_expiry" id="date_expiry" placeholder="MM-DD-YYYY" value=''>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_label_">Remark</label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" id="remark" name="remark" class="form-control" value="" placeholder="remark" required autocomplete="off">
                            </div>
                        </div>



                        <!-- <label data-i18n="modal_label_max_down">Max Download Bandwidth (Kb/s): </label>
                        <div class="form-group">
                            <input type="text" id="bwdown" name="bwdown" class="form-control" value="">
                        </div>

                        <label data-i18n="modal_label_max_up">Max Upload Bandwidth (Kb/s): </label>
                        <div class="form-group">
                            <input type="text" id="bwup" name="bwup" class="form-control" value="">
                        </div>

                        <label data-i18n="modal_label_min_down">Min Download Bandwidth (Kb/s): </label>
                        <div class="form-group">
                            <input type="text" id="min_down" name="min_down" class="form-control" value="">
                        </div>

                        <label data-i18n="modal_label_min_up">Min Upload Bandwidth (Kb/s): </label>
                        <div class="form-group">
                            <input type="text" id="min_up" name="min_up" class="form-control" value="">
                        </div> -->
                    </div>

                    <div class="modal-footer">

                        <input type="hidden" id="id" name="id" value="">
                        <input type="hidden" id="username" name="username" value="">

                        <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="modal_button_cancel">Cancel</button>
                        <button type="button" class="btn btn-primary round waves-effect waves-light btn-create" data-i18n="modal_button_create">Generate</button>

                    </div>

                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal User Details -->
<div class="modal fade text-left" id="viewTopup" role="dialog">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel16" data-i18n="modal_2_title">Topup Code Information and Analytics</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" style="overflow: auto;">

                <div class="user-info">

                    <div class="row mb-80">
                        <div class="col-12">
                            <div class="row text-center mx-0">
                                <div class="col-2 border d-flex align-items-between flex-column py-1">
                                    <p class="mb-50 text-uppercase" data-i18n="modal_2_status">Status</p>
                                    <p class="font-medium-5 text-bold-700 mb-50 user-status" data-i18n="modal_2_status_null">NULL</p>
                                </div>
                                <!-- <div class="col-2 border-top border-bottom border-right d-flex align-items-between flex-column py-1">
                                    <p class="mb-50 text-uppercase" data-i18n="modal_2_integration">Creator</p>
                                    <p class="font-medium-5 text-bold-700 mb-50 user-creator" data-i18n="modal_2_integration_null">NULL</p>
                                </div> -->
                                <div class="col-2 border-top border-bottom border-right d-flex align-items-between flex-column py-1">
                                    <p class="mb-50 text-uppercase" data-i18n="modal_2_integration">User</p>
                                    <p class="font-medium-5 text-bold-700 mb-50 user-username" data-i18n="modal_2_integration_null">NULL</p>
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
                                    <p class="mb-50 text-uppercase" data-i18n="modal_2_session">Created Date</p>
                                    <p class="font-medium-5 text-bold-700 mb-50 user-date-create" data-i18n="modal_2_session_null">NULL</p>
                                </div>
                                <div class="col-2 border-top border-bottom border-right d-flex align-items-between flex-column py-1">
                                    <p class="mb-50 text-uppercase" data-i18n="modal_2_active">Activate Date</p>
                                    <p class="font-medium-5 text-capitalize text-bold-700 mb-50 user-activate" data-i18n="modal_2_active_null">NULL</p>
                                </div>
                            </div>
                        </div>
                    </div><br><br>


                    <div class="row">

                        <div class="col-sm-4">
                            <div class="card text-white">
                                <div class="card-body">

                                    <table class="table table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th colspan="2" style="text-transform: uppercase;" data-i18n="modal_2_thead_profile_info">Topup Information</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td data-i18n="modal_2_tbody_subscribed">Price</td>
                                                <td class="topup-price" data-i18n="modal_2_tbody_price_null">NULL</td>
                                            </tr>
                                            <tr>
                                                <td data-i18n="modal_2_tbody_subscribed">Topup Code</td>
                                                <td class="topup-code" data-i18n="modal_2_tbody_code_null">NULL</td>
                                            </tr>
                                            <tr>
                                                <td data-i18n="modal_2_tbody_quota">Quota Limit(MB)</td>
                                                <td class="topup-quota" data-i18n="modal_2_tbody_quota_null">NULL</td>
                                            </tr>
                                            <tr>
                                                <td data-i18n="modal_2_tbody_time">Time Limit (Minutes)</td>
                                                <td class="topup-time" data-i18n="modal_2_tbody_time_null">NULL</td>
                                            </tr>
                                            <tr>
                                                <td data-i18n="modal_2_tbody_create">Date Create</td>
                                                <td class="topup-date-create" data-i18n="modal_2_tbody_create_null">NULL</td>
                                            </tr>
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
                                                <th colspan="2" style="text-transform: uppercase;" data-i18n="modal_2_thead_profile_info">User Information</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td data-i18n="modal_2_tbody_username">Username</td>
                                                <td class="topup-username" data-i18n="modal_2_tbody_username_null">NULL</td>
                                            </tr>
                                            <tr>
                                                <td data-i18n="modal_2_tbody_current">Full Name</td>
                                                <td class="topup-fullname" data-i18n="modal_2_tbody_fullname_null">NULL</td>
                                            </tr>
                                            <tr>
                                                <td data-i18n="modal_2_tbody_email">Email Address</td>
                                                <td class="topup-email" data-i18n="modal_2_tbody_type_email">NULL</td>
                                            </tr>
                                            <tr>
                                                <td data-i18n="modal_2_tbody_phone">Phone No</td>
                                                <td class="topup-phone" data-i18n="modal_2_tbody_phone">NULL</td>
                                            </tr>
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
                <!-- <button class="btn btn-primary waves-effect waves-light btn-user-history"><span data-i18n="modal_2_show_history">Show History</span></button> -->
            </div>

        </div>
    </div>
</div>



<?php

require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";

?>