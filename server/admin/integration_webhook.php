<?php

$kiw['module'] = "Integration -> Web hook";
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

// $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_int_webhook WHERE kiwire_int_webhook.tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");
?>

<style>
    .ui-timepicker-container{ 
        z-index:1151 !important; 
    }
</style>

<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="integration_webhook_title">Web Hook</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="integration_webhook_subtitle">
                                Trigger your web service on event
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

                        <button type="button" class="btn btn-primary waves-effect waves-light create-btn-webhook pull-right" data-toggle="modal" data-target="#inlineForm" data-i18n="btn_add_webhook">Add Web Hook</button>

                        <div class="table-responsive">
                            <table id="itemlist" class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="thead_no">No</th>
                                        <th data-i18n="thead_name">Name</th>
                                        <th data-i18n="thead_event">Event</th>
                                        <th data-i18n="thead_url">URL</th>
                                        <th data-i18n="thead_remark">Remark</th>
                                        <th data-i18n="thead_status">Status</th>
                                        <th data-i18n="thead_action">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <th data-i18n="tbody_loading">
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
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">

            <form class="create-form" action="#">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel33" data-i18n="integration_webhook_add_edit">Add or Edit Web Hook</h4>
                    <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <label data-i18n="integration_webhook_enable">Enable: </label>
                                    <input type="checkbox" class="custom-control-input" name="status" id="status" value="y" class="toggle" />
                                    <label class="custom-control-label" for="status"></label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12"></div>

                        <!-- <div class="col-md-2">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <label data-i18n="">24 hours: </label>
                                    <input type="checkbox" class="custom-control-input" name="is_24" id="is_24" value="1" class="toggle" />
                                    <label class="custom-control-label" for="is_24"></label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12"></div>

                        <div class="col-md-6 time">
                            <label data-i18n="">Start Time: </label>
                            <div class="form-group">
                                <input type="text" name="start_time" id="start_time" value="" class="form-control datetime">
                            </div>
                        </div>

                        <div class="col-md-6 time">
                            <label data-i18n="">Stop Time: </label>
                            <div class="form-group">
                                <input type="text" name="stop_time" id="stop_time" value=""  class="form-control datetime">
                            </div>
                        </div> -->


                        <div class="col-md-12">
                            <label data-i18n="integration_webhook_name">Name: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="name" id="name" value="" class="form-control" placeholder="eg: Alert" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="integration_webhook_event">Event: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                
                                <select name="when" id="when" class="select2 form-control" required>
                                    <option value="login" data-i18n="integration_webhook_login">Login</option>
                                    <option value="logout" data-i18n="integration_webhook_logout">Logout</option>
                                </select>
                                
                            </div>
                        </div>
                        
                        
                        <div class="col-md-6">
                            <label data-i18n="integration_webhook_url">URL: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="url" id="url" value="" class="form-control" placeholder="test.domain.com" required>
                            </div>
                        </div>
                        
                        
                        <div class="col-md-6">
                            <label data-i18n="integration_webhook_method">Method: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <select name="method" id="method" class="select2 form-control" required>
                                    <option value="get" data-i18n="integration_webhook_get">Get</option>
                                    <option value="put" data-i18n="integration_webhook_put">Put</option>
                                    <option value="post" data-i18n="integration_webhook_post">Post</option>
                                    <option value="delete" data-i18n="integration_webhook_delete">Delete</option>
                                </select>
                            </div>
                        </div>
                    
                    
                        <div class="col-md-6">
                            <label data-i18n="integration_webhook_payload">Payload: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="payload" id="payload" value="" class="form-control" placeholder="message" required>
                            </div>
                        </div>
                        
                        
                        <div class="col-md-12">
                            <label data-i18n="integration_webhook_header">Header (separated by comma)</label>
                            <div class="form-group">
                                <textarea name="header" id="header" value="" class="form-control" rows="2" cols="9"></textarea>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">

                    <input type="hidden" id="reference" name="reference" value="">
                    <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="integration_webhook_cancel">Cancel</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-create" data-i18n="integration_webhook_create">Create</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-update" data-i18n="integration_webhook_update">Update</button>

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

<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
<script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>