<?php

$kiw['module'] = "Cloud -> Custom Style";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="integration_api_title">Custom Style</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="integration_api_subtitle">
                                Allow different theme/style each tenant
                               
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

                        <button type="button" class="btn btn-primary waves-effect waves-light create-btn-apikey pull-right" data-toggle="modal" data-target="#inlineForm"  data-i18n="">Add </button>

                        <div class="table-responsive">
                            <table id="itemlist" class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th  data-i18n="thead_no">No</th>
                                        <th  data-i18n="thead_tenantID">Tenant ID</th>
                                        <th  data-i18n="integration_api_action">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <th  data-i18n="integration_api_loading">
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

            <form class="create-form" action="#">
                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel33"  data-i18n="">Add Setting for Custom Style </h4>
                    <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                   <label  data-i18n="integration_api_role2">Tenant: </label> <span class="text-danger">*</span>
                    <div class="form-group">
                        <fieldset class="form-group">
                            <select name="tenant" id="tenant" class="select2 form-control" data-style="btn-default">
                                <option value="all">ALL</option>
                                <?php

                                $kiw_row = $kiw_db->fetch_array("SELECT * FROM `kiwire_clouds` WHERE custom_style='n'");

                                foreach ($kiw_row as $record) {

                                    echo "<option value='{$record['tenant_id']}'> " . ucfirst($record['tenant_id']) . "</option>";
                                }

                                ?>

                            </select>
                        </fieldset>
                    </div>

                    <div class="modal-footer">

                        <input type="hidden" id="reference" name="reference" value="">
                        <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal"  data-i18n="integration_api_cancel">Cancel</button>
                        <button type="button" class="btn btn-primary round waves-effect waves-light btn-create"  data-i18n="integration_api_create">Create</button>
                        <button type="button" class="btn btn-primary round waves-effect waves-light btn-update"  data-i18n="integration_api_update">Update</button>

                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php

require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";

?>
