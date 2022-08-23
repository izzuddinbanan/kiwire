<?php

$kiw['module'] = "Report -> User Dwell Time";
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
require_once "includes/include_report.php";

$kiw_db = Database::obtain();

?>


<script src="/app-assets/vendors/js/charts/echarts/echarts.min.js"></script>


<div class="content-wrapper">

    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">

                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="insight_deviceinfo_title">Custom Report</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item" data-i18n="insight_deviceinfo_subtitle">
                                generate custom report based on preference
                            </li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="row" style="display: none;">
        <div class="col-12 mb-1">
            <button id="filter-btn-front" class="float-right btn btn-icon btn-primary btn-xs fa fa-filter"></button>
        </div>
    </div>

    <div class="card" id="filter-form">
        <div class="card-body col-12">
            <form class="form form-vertical">
                <div class="form-body">

                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="" data-i18n="">Select main module</label>

                                <select name="main_module" id="main_module" class="form-control">
                                    <option value="0">-- Select --</option>
                                    <option value="Account">Account</option>
                                    <option value="Login">Login</option>
                                    <option value="Bandwidth">Bandwidth</option>
                                    <option value="Controller">Controller</option>
                                    <option value="Impression">Impression</option>
                                    <option value="Campaign">Campaign</option>
                                    <option value="Delivery Log">Delivery Log</option>
                                    <option value="Insight">Insight</option>
                                </select>

                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Select sub module</label>

                                <select name="sub_account" id="sub_account" class="form-control submodule">
                                    <option value='0'>-- Select --</option>
                                    <option value='1'>Summary</option>
                                    <option value='2'>Expiry</option>
                                    <option value='3'>Creator</option>
                                    <option value='4'>Voucher Activation</option>
                                    <option value='5'>Voucher Availability</option>
                                    <option value='6'>Creation Summary</option>
                                </select>

                                <select name="sub_login" id="sub_login" class="form-control submodule" style="display: none;">
                                    <option value='0'>-- Select --</option>
                                    <option value='7'>Active Session</option>
                                    <option value='8'>Login Record</option>
                                    <option value='9'>Login Summary</option>
                                    <option value='10'>Login Frequency</option>
                                    <option value='11'>Login Frequency by Profile</option>
                                    <option value='12'>Login Device Frequency</option>
                                    <option value='13'>Login Error</option>
                                    <option value='14'>Session Concurrency</option>
                                    <option value='15'>Dwell Time Summary</option>
                                    <option value='16'>Dwell Time by Profile</option>
                                    <option value='17'>Top Account by Login</option>
                                    <option value='18'>Return Account Summary</option>
                                </select>

                                <select name="sub_bandwidth" id="sub_bandwidth" class="form-control submodule" style="display: none;">
                                    <option value='0'>-- Select --</option>
                                    <option value='19'>Summary</option>
                                    <option value='20'>Usage Per Account</option>
                                    <option value='21'>History Top Account</option>
                                    <option value='22'>Current Top Account</option>
                                    <option value='23'>Bandwidth vs Login</option>
                                </select>

                                <select name="sub_controller" id="sub_controller" class="form-control submodule" style="display: none;">
                                    <option value='0'>-- Select --</option>
                                    <option value='24'>Controller Bandwidth</option>
                                    <option value='25'>Controller Report</option>
                                </select>

                                <select name="sub_impression" id="sub_impression" class="form-control submodule" style="display: none;">
                                    <option value='0'>-- Select --</option>
                                    <option value='26'>Summary</option>
                                </select>

                                <select name="sub_campaign" id="sub_campaign" class="form-control submodule" style="display: none;">
                                    <option value='0'>-- Select --</option>
                                    <option value='27'>Impression Summary</option>
                                    <option value='28'>Click Engangement</option>
                                    <option value='29'>Offline Summary</option>
                                    <option value='30'>Survey Response</option>
                                </select>

                                <select name="sub_delivery_log" id="sub_delivery_log" class="form-control submodule" style="display: none;">
                                    <option value='0'>-- Select --</option>
                                    <option value='31'>SMS Send Report</option>
                                    <option value='32'>Email Send Report</option>
                                    <option value='33'>PMS Transaction</option>
                                </select>

                                <select name="sub_insight" id="sub_insight" class="form-control submodule" style="display: none;">
                                    <option value='0'>-- Select --</option>
                                    <option value='34'>User Device Info</option>
                                    <option value='35'>Registration Data</option>
                                    <option value='36'>Social Networks Analytics</option>
                                    <option value='37'>Social Network Data</option>
                                </select>

                            </div>
                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-6 date_from" id="date_from" style="display: none;">
                            <div class="form-group" style="position:relative; left:auto; display:block;">
                                <label for="startdate" data-i18n="data_from">Date From</label>
                                <input type="text" class="form-control format-picker" name="startdate" id="startdate" value='<?= report_date_view(report_date_start("", 2)) ?>'>
                            </div>
                        </div>

                        <div class="col-md-6 date_until" id="date_until" style="display: none;">
                            <div class="form-group" style="position:relative; left:auto; display:block;">
                                <label for="enddate" data-i18n="data_until">Date Until</label>
                                <input type="text" class="form-control format-picker" name="enddate" id="enddate" value='<?= report_date_view(report_date_start("", 1)) ?>'>
                            </div>
                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group" id="username" style="display: none;">
                                <label for="username" data-i18n="username">Username: </label>
                                <input type="text" placeholder="" name="username" id="username" value="" class="form-control" />
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group" id="mac_address" style="display: none;">
                                <label for="mac_address" data-i18n="mac_address">MAC Address: </label>
                                <input type="text" placeholder="EG. 30:AA:BD:12:EE:09" name="mac_address" id="mac_address" value="" class="form-control" />
                            </div>
                        </div>

                    </div>


                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group" id="ip_address" style="display: none;">
                                <label for="ip_address" data-i18n="ip_address">IP Address: </label>
                                <input type="text" placeholder="EG. 10.5.12.245" name="ip_address" id="ip_address" value="" class="form-control" />
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group" id="nas_id" style="display: none;">
                                <label for="controller" data-i18n="controller">NAS Identity: </label>
                                <input type="text" placeholder="" name="controller" id="controller" value="" class="form-control" />
                            </div>
                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-6">
                            <?php if ($_SESSION['access_level'] == "superuser") { ?>

                                <div class="form-group" id="tenant" style="display: none;">

                                    <label for="tenant_id" data-i18n="tenant_id">Tenant </label>
                                    <select name="tenant_id" id="tenant_id" class="form-control">
                                        <option value="">All Tenant</option>

                                        <?php

                                        if (!empty($_SESSION['tenant_allowed'])) {


                                            $kiw_tenants = explode(",", $_SESSION['tenant_allowed']);

                                            foreach ($kiw_tenants as $kiw_tenant) {

                                                echo "<option value='{$kiw_tenant}'>{$kiw_tenant}</option>";
                                            }
                                        } else {

                                            $kiw_tenants = $kiw_db->fetch_array("SELECT tenant_id FROM kiwire_clouds");

                                            foreach ($kiw_tenants as $kiw_tenant) {

                                                echo "<option value='{$kiw_tenant['tenant_id']}'>{$kiw_tenant['tenant_id']}</option>";
                                            }
                                        }

                                        ?>

                                    </select>

                                </div>

                            <?php } ?>

                        </div>

                        <div class="col-md-6">
                            <div class="form-group" id="data_type" style="display: none;">
                                <label for="data_type" data-i18n="data_type">Data Type: </label>
                                <select class="select2 form-control">
                                    <option value="login" data-i18n="type_login">Login</option>
                                    <option value="logout" data-i18n="type_logout">Logout</option>
                                </select>
                            </div>
                        </div>

                    </div>


                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group" id="profile_name" style="display: none;">
                                <label data-i18n="data_profile">Profile Name</label>
                                <select class="select2 form-control" name="profile" id="profile">
                                    <option value="">All</option>

                                    <?php

                                    $kiw_row = $kiw_db->fetch_array("SELECT name FROM kiwire_profiles WHERE tenant_id = '{$tenant_id}'");

                                    foreach ($kiw_row as $record) {

                                        echo "<option value='{$record['name']}'>{$record['name']}</option> \n";
                                    }
                                    ?>

                                </select>
                            </div>
                        </div>

                        <!-- <div class="col-md-6">
                            <div class="form-group" id="zone" style="display: none;">
                                <label data-i18n="data_zone">Zone</label>
                                <?= report_project_select($kiw_db, $kiw_cache, $_SESSION['tenant_id']); ?>
                            </div>
                        </div> -->

                    </div>

                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group" id="type" style="display: none;">
                                <label data-i18n="data_zone">Type</label>
                                <select class="form-control">
                                    <option value='username'>Username</option>
                                    <option value='mac_address'>MAC Address</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group" id="zone" style="display: none;">
                                <label data-i18n="data_zone">Zone</label>
                                <?= report_project_select($kiw_db, $kiw_cache, $_SESSION['tenant_id']); ?>
                            </div>
                        </div>

                    </div>


                    <div class="col-12 float-right">
                        <button type="button" value='Seleted option' id="filter-data" class="btn btn-primary round mr-1 mb-1 waves-effect waves-light pull-right" data-i18n="login_dwell_sum_modal_filter">Filter</button>
                        <!-- <button type="reset" class="btn btn-danger round mr-1 mb-1 waves-effect waves-light pull-right" data-dismiss="modal" data-i18n="login_dwell_sum_modal_cancel">Cancel</button> -->
                    </div>


                </div>
            </form>
        </div>
    </div>



    <div id='showresult' style="display:none;">

       <div class="content-body">

            <section id="report_table" class="card">
                <div class="card-content">
                    <div class="card-body">
                        <div class="card-text">
                            <div class="table-responsive">
                                <table class="table table-hover table-data" id="example">
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


</div>


<script>
    var access_user = '<?= $_SESSION['access_level'] ?>';
</script>



<?php

require_once "includes/include_report_footer.php";
require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";

?>