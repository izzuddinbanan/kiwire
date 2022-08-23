<?php

$kiw['module'] = "Configuration -> Administrator";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Administrator</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                Manage administrator
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

                        <button type="button" class="btn btn-primary waves-effect waves-light create-btn-admin pull-right" data-i18n="btn_add_admin">Add Administrator</button>

                        <div class="table-responsive">
                            <table id="itemlist" class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="thead_no">no</th>
                                        <th data-i18n="thead_username">username</th>
                                        <th data-i18n="thead_fullname">fullname</th>
                                        <th data-i18n="thead_email">email</th>
                                        <th data-i18n="thead_role">role</th>
                                        <th data-i18n="thead_permission">permission</th>
                                        <th data-i18n="thead_alert">receive alert email</th>
                                        <!-- <th data-i18n="thead_credit">balance credit (MYR)</th> -->
                                        <th data-i18n="thead_login">last login</th>
                                        <th data-i18n="thead_action">action</th>
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


<div class="modal fade text-left" id="inlineForm" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel33" data-i18n="modal_1_title">Add or Edit Administrator</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="create-form" action="#">

                <div class="modal-body">
                    <div class="row">
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_label_username">Username: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" id="username" name="username" class="form-control" placeholder="eg: admin" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_label_password">Password: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="password" id="password" name="password" class="form-control" placeholder="Uppercase/lowercase/numeric/special character" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_label_fullname">Fullname: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" id="fullname" name="fullname" class="form-control" placeholder="eg: Administrator" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_label_email">Email: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="email" id="email" name="email" class="form-control" placeholder="email@address.com" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_label_role">Role: </label>
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
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_label_permission">Permission: </label>
                            <div class="form-group">
                                <fieldset class="form-group">
                                    <select class="select2 form-control" name="permission" id="permission" data-style="btn-default">
                                        <option value='r' data-i18n="modal_1_label_permission_r">Read</option>
                                        <option value='w' data-i18n="modal_1_label_permission_w">Write</option>
                                        <option value='rw' data-i18n="modal_1_label_permission_rw">Read + Write</option>
                                    </select>
                                </fieldset>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <label data-i18n="modal_1_label_receive_alert">Receive Alert Email: </label>
                                    <input type="checkbox" class="custom-control-input" name=monitor id=monitor value="monitor" class="toggle" />
                                    <label class="custom-control-label" for="monitor"></label>
                                </div>
                            </div>
                        </div>
                    
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <label data-i18n="modal_1_label_email_admin">Send email to the admin: </label>
                                    <input type="checkbox" class="custom-control-input" name="send_email" id="send_email" value="y" class="toggle" />
                                    <label class="custom-control-label" for="send_email"></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="modal-footer">
                    
                    <input type="hidden" id="reference" name="reference" value="">
                    <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="modal_1_footer_btn_cancel">Cancel</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-create" data-i18n="modal_1_footer_btn_create">Create</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-update" data-i18n="modal_1_footer_btn_update">Update</button>

                </div>

                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">

            </form>
        </div>
    </div>
</div>


<div class="modal fade text-left" id="topupForm" role="dialog">

    <div class="modal-dialog modal-dialog-centered" role="document">

        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel33" data-i18n="modal_2_title">Top-up Balance for Administrator</h4>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="topup-form" action="#">

                <div class="modal-body">

                    <label data-i18n="modal_2_label_credit">Credit (MYR): </label>
                    <div class="form-group">
                        <input type="number" step="0.01" name="balance_credit" id="balance_credit" value="0.00" class="form-control" placeholder="" required>
                    </div>

                </div>

                <div class="modal-footer">

                    <input type="hidden" id="id" name="id" value="" />
                    <button type="button" class="btn btn-danger round waves-effect waves-light flang-form_cancel_button" data-dismiss="modal" data-i18n="modal_2_footer_btn_cancel">Cancel</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-update-topup" data-i18n="modal_2_footer_btn_update">Update</button>

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
