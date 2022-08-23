<?php

$kiw['module'] = "Cloud -> Access Level";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Superuser Role</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                Manage role for superuser
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

                        <button type="button" class="btn btn-primary waves-effect waves-light btn-add pull-right" data-toggle="modal" data-i18n="button_add_role">Add Role</button>

                        <div class="table-responsive">
                            <table class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="thead_no">no</th>
                                        <th data-i18n="thead_role">role</th>
                                        <th data-i18n="thead_action">action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <th data-i18n="tbody_loading">
                                        Loading..
                                    </th>
                                </tbody>
                            </table>
                        </div>

                        <div class="modal fade text-left" id="inlineForm" role="dialog">
                            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h4 class="modal-title" id="myModalLabel33" data-i18n="modal_1_title">Add or Edit Role</h4>

                                        <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>

                                    <form class="create-form" action="#">

                                        <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                                        <div class="modal-body">

                                            <div class="col-md-12">
                                                <span data-i18n="modal_1_form_name">Role Name:</span>  <span class="text-danger">*</span>

                                                <div class="form-group">
                                                    <input type="text" id="groupname" name="groupname" class="form-control" placeholder="eg: Operator" required>
                                                </div>
                                            </div>

                                            <div class="col-md-12 mb-5">
                                                <span data-i18n="modal_1_form_access">Allow Access:</span>

                                                <button type="button" class="btn btn-primary pull-right mr-2 btn-select-all" data-i18n="modal_1_form_access_btn_select">Select All</button>
                                                <button type="button" class="btn btn-primary pull-right mr-2 btn-clear-all" data-i18n="modal_1_form_access_btn_clear">Clear All</button>

                                                <div class="col-md-5 pull-right mr-2">
                                                    <input type="text" class="form-control filter-text" placeholder="Filter">
                                                </div>

                                            </div>

                                            <div class="col-md-12 p-1 border role-list" style="max-height: 300px; min-height: 300px; overflow: auto;">


                                                <?php


                                                $list_group_mod = $kiw_db->fetch_array("SELECT SQL_CACHE DISTINCT(mod_group) AS ccount FROM kiwire_moduleid");


                                                foreach ($list_group_mod as $group_mod) {


                                                    $kiw_group_name = str_replace(" ", "_", $group_mod['ccount']);

                                                    $kiw_row = $kiw_db->fetch_array("SELECT SQL_CACHE * FROM kiwire_moduleid WHERE mod_group = '{$group_mod['ccount']}'");


                                                    echo "<div class='custom-control custom-checkbox' style='margin-top: 10px; margin-bottom: 5px;'><input type='checkbox' class='Select-All custom-control-input' data-section='{$kiw_group_name}' name='{$group_mod['ccount']}' id='{$group_mod['ccount']}' value='{$group_mod['ccount']}'><label style='text-transform: uppercase; font-size: larger;' for='{$group_mod['ccount']}' class='custom-control-label'>{$group_mod['ccount']}</label></div>\n";

                                                    foreach ($kiw_row as $value) {


                                                        $kiw_id = preg_replace("/[^a-zA-Z0-9]+/", "", $value['moduleid']);

                                                        echo "<div class='custom-control custom-checkbox'><input type='checkbox' class='Section-{$kiw_group_name} custom-control-input' name='modules[]' id='{$kiw_id}' value='{$value['moduleid']}'><label for='{$kiw_id}' class='custom-control-label'>{$value['moduleid']}</label></div>\n";


                                                    }


                                                }

                                                ?>


                                            </div>

                                        </div>

                                        <div class="modal-footer">

                                            <input type="hidden" id="reference" name="reference" value="">
                                            <button type="button" class="btn btn-danger round waves-effect waves-light btn-cancel" data-dismiss="modal" data-i18n="modal_1_footer_btn_cancel">Cancel
                                            </button>
                                            <button type="button" class="btn btn-primary round waves-effect waves-light btn-create" data-i18n="modal_1_footer_btn_create">
                                                Create
                                            </button>

                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php

                                                require_once "includes/include_footer.php";
                                                require_once "includes/include_datatable.php";

?>