<?php

$kiw['module'] = "Cloud -> Manage Superuser";
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


?>

<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Superuser Management</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                List and manage superuser
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

                        <button type="button" class="btn btn-primary waves-effect waves-light create-btn-superuser pull-right" data-toggle="modal" data-target="#inlineForm" data-i18n="button_add_superuser">Add Superuser</button>

                        <div class="table-responsive">
                            <table class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="thead_no">No</th>
                                        <th data-i18n="thead_id">Superadmin ID</th>
                                        <th data-i18n="thead_fullname">Full Name</th>
                                        <th data-i18n="thead_cloud">Tenant</th>
                                        <th data-i18n="thead_permission">Permission</th>
                                        <th data-i18n="thead_lastlogin">Last Login</th>
                                        <th data-i18n="thead_action">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <th data-i18n="tbody_loading">
                                        Loading..
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
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">

            <form class="create-form" action="#">

                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel33" data-i18n="modal_1_title">Add or Edit Superuser</h4>
                    <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-12">
                            <label data-i18n="modal_1_label_tenantid">Default Tenant ID: </label> <span class="text-danger">*</span>
                            <div class="form-group">

                                <select name="tenant_default" id="tenant_default" class="select2 form-control" data-style="btn-default" tabindex="-98">
                                    <?php

                                    $rows = "SELECT tenant_id FROM kiwire_clouds";
                                    $rows = $kiw_db->fetch_array($rows);

                                    foreach ($rows as $record) {


                                        if($record['tenant_id'] === $_SESSION['tenant_id']) {

                                            echo "<option selected='selected' value='{$record['tenant_id']}'>{$record['tenant_id']}</option>";

                                        } else {

                                            echo "<option value='{$record['tenant_id']}'>{$record['tenant_id']}</option>";

                                        }

                                        // echo "<option value='{$record['tenant_id']}'> {$record['tenant_id']}</option> \n";

                                    }
                                    
                                    ?>

                                </select>

                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_label_username">Username: </label>  <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="username" id="username" class="form-control" placeholder="Username" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_label_password">Password: </label>  <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="password" name="password" id="password" class="form-control" placeholder="Uppercase/lowercase/numeric/special character" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_label_fullname">Full Name: </label>  <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="fullname" id="fullname" class="form-control" placeholder="Fullname" required>
                            </div>
                        </div>
                            
                        <div class="col-md-6">
                            <label data-i18n="modal_1_label_email">Email Address: </label>  <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="email" name="email" id="email" class="form-control" placeholder="email@address.com" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label data-i18n="modal_1_label_permission">Permission: </label>
                            <div class="form-group">
                                <select name="permission" id="permission" class="form-control select2">
                                    <option value="rw" data-i18n="modal_1_label_permission_opt_rw">Read &amp; Write</option>
                                    <option value="r" data-i18n="modal_1_label_permission_opt_r">Read Only</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_label_role">Role: </label>
                            <div class="form-group">
                                <select name="groupname" id="groupname" class="form-control select2">
                                <?php

                                $kiw_temp = "SELECT DISTINCT(groupname) FROM kiwire_admin_group WHERE tenant_id = 'superuser'";
                                $kiw_temp = $kiw_db->fetch_array($kiw_temp);
                                
                                foreach ($kiw_temp as $role) {
                                    
                                    echo "<option value='{$role['groupname']}'>{$role['groupname']}</option>\n";
                                    
                                }
                                
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <label data-i18n="modal_1_label_alertmsg">Send Alert Message: </label>
                                    <input type="checkbox" class="custom-control-input" name="monitor" id="monitor" value="y" class="toggle" />
                                    <label class="custom-control-label" for="monitor"></label>
                                </div>
                            </div>
                        </div>
                        
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <label data-i18n="modal_1_label_2factor">Required 2-factors auth: </label>
                                    <input type="checkbox" class="custom-control-input" name="2-factors" id="2-factors" value="y" class="toggle" />
                                    <label class="custom-control-label" for="2-factors"></label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <label data-i18n="modal_1_label_emailadmin">Send email to the admin: </label>
                                    <input type="checkbox" class="custom-control-input" name="send_email" id="send_email" value="y" class="toggle" />
                                    <label class="custom-control-label" for="send_email"></label>
                                </div>
                            </div>
                        </div>
                        
                    
                        <div class="col-md-6">
                            <label>
                                <span data-i18n="modal_1_label_tenant_access_1">Tenant Access:</span> <br><br>
                                <span data-i18n="modal_1_label_tenant_access_2">Restrict this admin to the below selected tenant;</span>
                            </label>
                            <div style="height: 90px; overflow-y: auto; padding-left: 5px;">
                                
                                <?php

                                $kiw_clouds = $kiw_db->fetch_array("SELECT tenant_id FROM kiwire_clouds");

                                foreach ($kiw_clouds as $kiw_cloud) {

                                    ?>

                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" name="tenants[]" id="<?= $kiw_cloud['tenant_id']; ?>" value="<?= $kiw_cloud['tenant_id']; ?>"/>
                                        <label class="custom-control-label" for="<?= $kiw_cloud['tenant_id']; ?>"><?= $kiw_cloud['tenant_id']; ?></label>
                                    </div>

                                    <?php

                                }

                                ?>

                            </div>
                        </div>

                        
                        
                    </div>

                </div>

                <div class="modal-footer">

                    <input type="hidden" id="reference" name="reference" value="">
                    <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="modal_1_footer_button_cancel">Cancel</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-create" data-i18n="modal_1_footer_button_create">Create</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-update" data-i18n="modal_1_footer_button_update">Update</button>

                </div>

            </form>
        </div>
    </div>
</div>

<?php

                                                                                                                                                                                                                                                    require_once "includes/include_footer.php";
                                                                                                                                                                                                                                                    require_once "includes/include_datatable.php";

?>
