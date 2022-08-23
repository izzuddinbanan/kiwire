<?php


$kiw['module'] = "CPanel -> Verify Device";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="policy_account_title">Approve User Device</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="policy_account_subtitle">
                                List of user device to be registered
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

                        <div class="table-responsive">
                            <table id="itemlist" class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="policy_account_tno">No</th>
                                        <th data-i18n="policy_account_tname">Registered To</th>
                                        <th data-i18n="policy_account_tfrequency">MAC Address</th>
                                        <th data-i18n="policy_account_texecute">Device Type</th>
                                        <th data-i18n="policy_account_tastatus">Device Brand</th>
                                        <th data-i18n="policy_account_taint">Device Model</th>
                                        <th data-i18n="policy_account_taccount">Device OS</th>
                                        <th data-i18n="policy_account_tstatus">Status</th>
                                        <th data-i18n="policy_account_taction">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <td colspan="9" data-i18n="policy_account_loading">Loading...</td>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </div>
</div>


<div class="modal fade text-left" id="device-detail" role="dialog">
    <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel16" data-i18n="modal_2_title">Device Information</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" style="overflow: auto;">

                <div class="col-md-12">

                    <div class="card text-white">
                        <div class="card-body">

                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th colspan="5" style="text-transform: uppercase;" data-i18n="modal_2_thead_acc_info">Detail</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td data-i18n="modal_2_tbody_registered_to">Registered to</td>
                                        <td class="registered_to" data-i18n="modal_2_tbody_registered_to_null">-</td>
                                    </tr>

                                    <tr>
                                        <td data-i18n="modal_2_tbody_mac_address">MAC Address</td>
                                        <td class="mac_address" data-i18n="modal_2_tbody_mac_address_null">-</td>
                                    </tr>

                                    <tr>
                                        <td data-i18n="modal_2_tbody_device_type">Device Type</td>
                                        <td class="device_type" data-i18n="modal_2_tbody_device_type_">-</td>
                                    </tr>

                                    <tr>
                                        <td data-i18n="modal_2_tbody_device_brand">Device Brand</td>
                                        <td class="device_brand" data-i18n="modal_2_tbody_device_brand_null">-</td>
                                    </tr>

                                    <tr>
                                        <td data-i18n="modal_2_tbody_device_model">Device Model</td>
                                        <td class="device_model" data-i18n="modal_2_tbody_device_model_null">-</td>
                                    </tr>

                                    <tr>
                                        <td data-i18n="modal_2_tbody_device_os">Device OS</td>
                                        <td class="device_os" data-i18n="modal_2_tbody_device_os_null">-</td>
                                    </tr>

                                    <tr>
                                        <td data-i18n="modal_2_tbody_device_os">Status</td>
                                        <td class="status" data-i18n="modal_2_tbody_device_os_null">-</td>
                                    </tr>

                               
                                </tbody>
                            </table>

                        </div>
                    </div>


                </div>


            </div>

            <div class="modal-footer">
                <input type="hidden" id="reference" name="reference" value="">
                <button type="button" class="btn btn-danger round waves-effect waves-light btn-cancel" data-dismiss="modal" data-i18n="modal_1_footer_button_cancel">Close</button>
            </div>

        </div>
    </div>
</div>

<?php
require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";
?>