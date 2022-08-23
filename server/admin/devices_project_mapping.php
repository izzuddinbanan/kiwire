<?php

$kiw['module'] = "Device -> Project";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Project Mapping</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                Manage project based on zones
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

                        <button type="button" class="btn btn-primary waves-effect waves-light create-btn-project pull-right" data-toggle="modal" data-target="#inlineForm" data-i18n="btn_add_project">Add Project</button>

                        <div class="table-responsive">
                            <table class="table table-hover table-data">
                                <thead>
                                <tr class="text-uppercase">
                                    <th data-i18n="thead_no">No</th>
                                    <th data-i18n="thead_name">Project Name</th>
                                    <th data-i18n="thead_action">Action</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
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
                <h4 class="modal-title" id="myModalLabel33" data-i18n="modal_1_title">Add or Edit Project</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="create-form" action="#">

                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                
                <div class="modal-body">

                    <label data-i18n="modal_1_name">Project Name: </label> <span class="text-danger">*</span>
                    <div class="form-group">
                        <input type="text" name="name" id="name" value="" class="form-control" placeholder="eg: Demo" required>
                    </div>

                    <label data-i18n="modal_1_permission">Permission Control: </label>
                    <div class="col-md-12 p-1 border role-list" style="max-height: 150px; min-height: 150px; overflow: auto;">

                        <?php


                        $kiw_row = $kiw_db->fetch_array("SELECT name FROM kiwire_zone WHERE tenant_id = '{$tenant_id}'");

                        foreach ($kiw_row as $value) {

                            echo "<div class='custom-control custom-checkbox'><input type='checkbox' class='custom-control-input' name='zones[]' id='{$value['name']}' value='{$value['name']}'><label for='{$value['name']}' class='custom-control-label'>{$value['name']}</label></div>";

                        }

                        ?>

                    </div>

                </div>

                <div class="modal-footer">

                    <input type="hidden" id="reference" name="reference" value="">

                    <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="modal_1_footer_btn_cancel">Cancel</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-create" data-i18n="modal_1_footer_btn_create">Create</button>

                </div>

            </form>
        </div>
    </div>
</div>


<?php

require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";

?>
