<?php


$kiw['module'] = "Policy -> Account Policy";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="policy_account_title">Account Policy</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="policy_account_subtitle">
                                Manage account policy
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

                        <button type="button" class="btn btn-primary waves-effect waves-light create-btn-account pull-right" data-toggle="modal" data-target="#inlineForm" data-i18n="policy_account_add">Add Account Policy</button>

                        <div class="table-responsive">
                            <table id="itemlist" class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="policy_account_tno">No</th>
                                        <th data-i18n="policy_account_tname">Name</th>
                                        <th data-i18n="policy_account_tfrequency">Frequency</th>
                                        <th data-i18n="policy_account_texecute">Execute</th>
                                        <th data-i18n="policy_account_tastatus">Account Status</th>
                                        <th data-i18n="policy_account_taint">Account Integration</th>
                                        <th data-i18n="policy_account_taccount">Account</th>
                                        <th data-i18n="policy_account_tstatus">Enabled</th>
                                        <th data-i18n="policy_account_taction">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <td colspan="8" data-i18n="policy_account_loading">Loading...</td>
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
                <h4 class="modal-title" id="myModalLabel33" data-i18n="policy_account_add_edit">Add or Edit account Policy</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="create-form" action="#">

                <div class="modal-body">

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <label data-i18n="policy_account_enable">Enable: </label>
                            <input type="checkbox" class="custom-control-input" name=status id=status <?= ($kiw_row['status '] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                            <label class="custom-control-label" for="status"></label>
                        </div>
                    </div>


                    <label data-i18n="policy_account_name">Name: </label> <span class="text-danger">*</span>
                    <div class="form-group">
                        <input type="text" name="name" id="name" value="" class="form-control" placeholder="Policy name" required>
                    </div>


                    <label data-i18n="policy_account_frequency">Frequency: </label>
                    <div class="form-group">

                        <select name="frequency" id="frequency" class="select2 form-control">
                            <option value="daily" data-i18n="policy_account_daily">Daily</option>
                            <option value="weekly" data-i18n="policy_account_weekly">Weekly</option>
                            <option value="monthly" data-i18n="policy_account_monthly">Monthly</option>
                            <option value="yearly" data-i18n="policy_account_yearly">Yearly</option>
                        </select>

                    </div>


                    <label data-i18n="policy_account_exacute">Execute: </label>
                    <div class="form-group">

                        <select name="exec_action" id="exec_action" class="select2 form-control change-exec-action">
                            <option value="delete_account" data-i18n="policy_account_delete_account">Delete Account</option>
                            <option value="update_status" data-i18n="policy_account_update_status">Update Status</option>
                            <option value="update_password" data-i18n="policy_account_update_password">Update Password </option>
                        </select>

                    </div>

                    <label data-i18n="policy_account_status">Status: </label>
                    <div class="form-group">

                        <select name="policy_status" id="policy_status" class="select2 form-control change-exec-action">
                            <option value="" data-i18n="policy_account_status_all">All</option>
                            <option value="active" data-i18n="policy_account_status_active">Active</option>
                            <option value="suspended" data-i18n="policy_account_status_suspended">Suspended</option>
                            <option value="expired" data-i18n="policy_account_status_expired">Expired</option>
                        </select>

                    </div>


                    <label for="policy_integration" data-i18n="policy_account_integration">Integration: </label>
                    <div class="form-group">

                        <select name="policy_integration" id="policy_integration" class="select2 form-control">
                            <option value="" data-i18n="policy_account_integration_all">All</option>
                            <option value="int" data-i18n="policy_account_integration_int">Internal</option>
                            <option value="pms" data-i18n="policy_account_integration_pms">PMS</option>
                            <option value="ms_ad" data-i18n="policy_account_integration_msad">Microsoft Active Directory</option>
                            <option value="ldap" data-i18n="policy_account_integration_ldap">LDAP</option>
                        </select>

                    </div>


                    <label data-i18n="policy_account_account">Account: </label>
                    <div class="form-group">
                        <input type="text" name="username" id="username" value="" placeholder="Account name to apply policy" class="form-control">
                    </div>

                    <div class="delete_account provider-input">
                        <label data-i18n="policy_account_change_to">Change to: </label>
                        <div class="form-group">
                            <input type="text" name="action_value" id="action_value" value="" class="form-control" >
                        </div>
                    </div>


                    <div class="modal-footer">

                        <input type="hidden" id="reference" name="reference" value="">
                        <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="policy_account_cancel">Cancel</button>
                        <button type="button" class="btn btn-primary round waves-effect waves-light btn-create" data-i18n="policy_account_create">Create</button>
                        <button type="button" class="btn btn-primary round waves-effect waves-light btn-update" data-i18n="policy_account_update">Update</button>

                    </div>
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