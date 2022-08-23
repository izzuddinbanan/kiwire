<?php

$kiw['module'] = "Tools -> Bypass Device";
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


$kiw_temp = "SELECT unique_id, description, device_ip FROM kiwire_controller WHERE vendor = 'mikrotik' AND tenant_id = '{$_SESSION['tenant_id']}'";

$kiw_temp = $kiw_db->fetch_array($kiw_temp);


?>


<div class="content-wrapper">

    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Bypass User Device</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                bypass a specific device from authentication without produce a report
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <section id="css-classes" class="card">
        <div class="card-content">
            <div class="card-body">
                <div class="card-text">


                    <div class="row">
                        <div class="col-md-3">
                        <label for="filter_status" data-i18n="filter_status">Controller: </label>
                        <select name="filter_nas" id="filter_nas" class="select2 form-control" data-style="btn-default" tabindex="-94">
                        <?php

                        foreach ($kiw_temp as $kiw_nas) {

                            echo "<option value='{$kiw_nas['unique_id']}'>{$kiw_nas['device_ip']} [ {$kiw_nas['unique_id']} ]</option>\n";

                        }

                        ?>
                        </select>
                        </div>
                        <div class="col-md-3">
                        <label for="filter_status" data-i18n="filter_status">Type: </label>
                        <select name="filter_status" id="filter_status" class="select2 form-control" data-style="btn-default" tabindex="-98">
                            <option value="bound">Bound</option>
                        </select>
                        </div>

                        <div class="col-md-2">
                            <br>
                            <button type="submit" name='search' id='btn-get-list' class="btn btn-primary waves-effect waves-light btn-search" data-i18n="bandwidth_account_usage_search">Search</button>

                        </div>

                    </div>

                </div>
            </div>
        </div>
    </section>

    <div class="row">
        <div class="col-12 mb-1">
            <button id="btn-get-data" class="float-right btn btn-icon btn-primary btn-xs fa fa-arrow-down"></button>
            <button id="btn-add-data" class="float-right btn btn-icon btn-primary btn-xs fa fa-plus mr-1"></button>
        </div>
    </div>

    <div class="content-body">
        <section id="css-classes" class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="card-text">

                        <div class="table-responsive">
                            <table class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="table_header_no">No</th>
                                        <th data-i18n="table_header_username">MAC Address</th>
                                        <th data-i18n="table_header_fullname">IP Address</th>
                                        <th data-i18n="table_header_speed">Speed ( in Megabytes / second )</th>
                                        <th data-i18n="table_header_profile">Remark</th>
                                        <th data-i18n="table_header_action">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td colspan="6" class="text-center" data-i18n="table_body_loading">
                                        [ Please select a controller ]
                                    </td>
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

<div class="modal fade text-left" id="create-bypass" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel33" data-i18n="modal_1_title">Add or Edit User Device</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="create-form" action="#" method="post">

                <div class="modal-body">
                    <span style="color: red;">** Please enable port 8728</span>
                    <div class="form-group username">
                        <label for="mac_address" data-i18n="modal_1_label_mac_address">MAC Address: </label>
                        <div class="form-group">
                            <input type="text" id="mac_address" name="mac_address" class="form-control" required>
                        </div>
                    </div>

                    <label for="ip_address" data-i18n="modal_1_label_ip_address">IP Address: </label>
                    <div class="form-group">
                        <input type="text" id="ip_address" name="ip_address" class="form-control" required>
                    </div>

                    <label for="speed" data-i18n="modal_1_label_speed">Speed ( in Megabytes / second ): </label>
                    <div class="form-group">
                        <input type="number" id="speed" name="speed" class="form-control" value="0" required>
                    </div>

                    <label for="nas" data-i18n="modal_1_label_nas">Controller:</label>
                    <fieldset class="form-group">
                        <select name="nas[]" id="nas" class="select2 form-control" multiple data-style="btn-default">
                            <?php

                            foreach ($kiw_temp as $kiw_nas) {

                                echo "<option value='{$kiw_nas['unique_id']}'>{$kiw_nas['device_ip']} [ {$kiw_nas['unique_id']} ]</option>\n";

                            }

                            ?>
                        </select>
                    </fieldset>

                    <label for="remark" data-i18n="modal_1_label_remark">Remark: </label>
                    <div class="form-group">
                        <input type="text" id="remark" name="remark" class="form-control">
                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="modal_1_button_cancel">Cancel</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-create" data-i18n="modal_1_button_create">Create</button>

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