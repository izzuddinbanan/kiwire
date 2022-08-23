<?php

$kiw['module'] = "Report -> Login History";
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



<div class="content-wrapper">

    <div class="content-header row">
        <div class="content-header-left col-md-9 col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-11">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="login_logins_record_title">Login Record</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="login_logins_record_subtitle">
                                Listing of previous connected user
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- <div class="row">
        <div class="col-12 mb-1">
            <button id="filter-btn" class="float-right btn btn-icon btn-primary btn-xs fa fa-filter"></button>
        </div>
    </div> -->

    <!-- <section id="css-classes" class="card">
        <div class="card-content">
            <div class="card-body">
                <div class="card-text">
                    <div class="col-12">
                        <div class="form-group row">
                            <h6 class="text-bold-500" data-i18n="report_account_expiry_search">Filter :</h6>
                        </div>
                    </div>

                    <div class="row">

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="type" data-i18n="data_type">Date Type: </label>
                                <select name=type id="type" class="select2 form-control">
                                    <option value="login" data-i18n="type_login">Login</option>
                                    <option value="logout" data-i18n="type_logout">Logout</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group" style="position:relative; left:auto; display:block;">
                                <label for="startdate" data-i18n="data_from">Date From</label>
                                <input type="text" class="form-control format-picker" name="startdate" id="startdate" value='<?= report_date_view(report_date_start("", 10)) ?>'>
                            </div>

                        </div>

                        <div class="col-md-4">
                            <div class="form-group" style="position:relative; left:auto; display:block;">
                                <label for="enddate" data-i18n="data_until">Date Until</label>
                                <input type="text" class="form-control format-picker" name="enddate" id="enddate" value='<?= report_date_view(report_date_start("", 1)) ?>'>
                            </div>
                        </div>


                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="username" data-i18n="username">Username: </label>
                                <input type="text" placeholder="" name="username" id="username" value="" class="form-control" />
                            </div>
                        </div>


                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="mac_address" data-i18n="mac_address">MAC Address: </label>
                                <input type="text" placeholder="EG. 30:AA:BD:12:EE:09" name="mac_address" id="mac_address" value="" class="form-control" />
                            </div>
                        </div>


                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="ip_address" data-i18n="ip_address">IP Address: </label>
                                <input type="text" placeholder="EG. 10.5.12.245" name="ip_address" id="ip_address" value="" class="form-control" />
                            </div>
                        </div>


                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="controller" data-i18n="controller">NAS Identity: </label>
                                <input type="text" placeholder="" name="controller" id="controller" value="" class="form-control" />
                            </div>

                        </div>


                        <div class="col-md-4">

                            <?php if ($_SESSION['access_level'] == "superuser") { ?>

                                <div class="form-group">

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

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="profile" data-i18n="profile">Profile: </label>
                                <select name="profile" id="profile" class="form-control">
                                    <option value="">All Profile</option>
                                    <?php

                                    $kiw_profile = $kiw_db->fetch_array("SELECT * FROM kiwire_profiles WHERE tenant_id = '{$_SESSION['tenant_id']}'");

                                    foreach ($kiw_profile as $kiw_profiles) {

                                        echo "<option value='{$kiw_profiles['name']}'>{$kiw_profiles['name']}</option>";
                                    } ?>
                                </select>
                            </div>
                        </div>


                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="project" data-i18n="project">Project: </label>
                                <select name="project" id="project" class="form-control">
                                    <option value="">All Project</option>
                                    <?php

                                    $kiw_project = $kiw_db->fetch_array("SELECT * FROM kiwire_project WHERE tenant_id = '{$_SESSION['tenant_id']}'");

                                    foreach ($kiw_project as $kiw_projects) {

                                        echo "<option value='{$kiw_projects['name']}'>{$kiw_projects['name']}</option>";
                                    } ?>
                                </select>
                            </div>
                        </div>

                    </div>
                    <button type="submit" name='search' id='search' class="btn btn-primary waves-effect waves-light btn-search" data-i18n="bandwidth_account_usage_search">Search</button>

                </div>
            </div>
        </div>
    </section> -->


    <section class="custom-radio">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="card-text">
                        <div class="col-12">
                            <div class="form-group row">
                                <h6 class="text-bold-500" data-i18n="report_account_expiry_search">Filter :</h6>
                            </div>
                        </div>

                        <div class="row">

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="type" data-i18n="data_type">Date Type: </label>
                                    <select name=type id="type" class="select2 form-control">
                                        <option value="login" data-i18n="type_login">Login</option>
                                        <option value="logout" data-i18n="type_logout">Logout</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group" style="position:relative; left:auto; display:block;">
                                    <label for="startdate" data-i18n="data_from">Date From</label>
                                    <input type="text" class="form-control format-picker" name="startdate" id="startdate" value='<?= report_date_view(report_date_start("", 2)) ?>'>
                                </div>

                            </div>

                            <div class="col-md-4">
                                <div class="form-group" style="position:relative; left:auto; display:block;">
                                    <label for="enddate" data-i18n="data_until">Date Until</label>
                                    <input type="text" class="form-control format-picker" name="enddate" id="enddate" value='<?= report_date_view(report_date_start("", 1)) ?>'>
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="username" data-i18n="username">Username: </label>
                                    <input type="text" placeholder="" name="username" id="username" value="" class="form-control" />
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="mac_address" data-i18n="mac_address">MAC Address: </label>
                                    <input type="text" placeholder="EG. 30:AA:BD:12:EE:09" name="mac_address" id="mac_address" value="" class="form-control" />
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="ip_address" data-i18n="ip_address">IP Address: </label>
                                    <input type="text" placeholder="EG. 10.5.12.245" name="ip_address" id="ip_address" value="" class="form-control" />
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="controller" data-i18n="controller">NAS Identity: </label>
                                    <input type="text" placeholder="" name="controller" id="controller" value="" class="form-control" />
                                </div>

                            </div>


                            <div class="col-md-4">

                                <?php if ($_SESSION['access_level'] == "superuser") { ?>

                                    <div class="form-group">

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

                                                    echo "<option value='{$kiw_tenant['tenant_id']}' ". ($kiw_tenant['tenant_id'] == $_SESSION['tenant_id'] ? "selected" : "") .">{$kiw_tenant['tenant_id']}</option>";
                                                }
                                            }

                                            ?>

                                        </select>

                                    </div>

                                <?php } ?>

                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="profile" data-i18n="profile">Profile: </label>
                                    <select name="profile" id="profile" class="form-control">
                                        <option value="">All Profile</option>
                                        <?php

                                        $kiw_profile = $kiw_db->fetch_array("SELECT * FROM kiwire_profiles WHERE tenant_id = '{$_SESSION['tenant_id']}'");

                                        foreach ($kiw_profile as $kiw_profiles) {

                                            echo "<option value='{$kiw_profiles['name']}'>{$kiw_profiles['name']}</option>";
                                        } ?>
                                        <!-- <option value="login" data-i18n="type_login">Login</option>
                                    <option value="logout" data-i18n="type_logout">Logout</option> -->
                                    </select>
                                </div>
                            </div>


                            <div class="col-md-4" id="zone-div" style="display:block">
                                <div class="form-group">
                                    <form method="post">
                                        <ul class="list-unstyled mb-0">
                                            <li class="d-inline-block mr-2">
                                                <fieldset>
                                                    <label for="zone" data-i18n="data_zone">Filter by:</label>
                                                </fieldset>
                                            </li>
                                            <li class="d-inline-block mr-2">
                                                <fieldset>
                                                    <div class="custom-control custom-radio">
                                                        <input type="radio" class="custom-control-input" name="customRadio" id="customRadio1" value="Zone" checked>
                                                        <label class="custom-control-label" for="customRadio1">Zone</label>
                                                    </div>
                                                </fieldset>
                                            </li>
                                            <li class="d-inline-block mr-2">
                                                <fieldset>
                                                    <div class="custom-control custom-radio">
                                                        <input type="radio" class="custom-control-input" name="customRadio" id="customRadio2" value="Project">
                                                        <label class="custom-control-label" for="customRadio2">Project</label>
                                                    </div>
                                                </fieldset>
                                            </li>
                                        </ul>

                                        <div class="zone" style="display:block;">

                                            <?= report_project_select($kiw_db, $kiw_cache, $_SESSION['tenant_id']); ?>

                                        </div>

                                        <div class="project" style="display:none;">

                                            <?= report_project_option($kiw_db, $kiw_cache, $_SESSION['tenant_id']); ?>

                                        </div>

                                    </form>

                                    <!--?= report_project_select($kiw_db, $kiw_cache, $_SESSION['tenant_id']); ?-->
                                </div>
                            </div>

                        </div>
                        <button type="submit" name='search' id='search' class="btn btn-primary waves-effect waves-light btn-search" data-i18n="bandwidth_account_usage_search">Search</button>

                    </div>
                </div>
            </div>
        </div>
    </section>


    <section id="report_table" class="card">
        <div class="card-content">
            <div class="card-body">
                <div class="card-text">
                    <div class="table-responsive">

                        <?php
                        $accessLevel = $_SESSION['access_level'];
                        ?>

                        <table class="table table-hover table-data">
                            <thead>
                                <tr class="text-uppercase">

                                    <th data-i18n="login_logins_record_no">No</th>
                                    <th data-i18n="login_logins_record_login_datetime">login date/time</th>
                                    <th data-i18n="login_logins_record_logout_datetime">logout date/time</th>
                                    <th data-i18n="login_logins_record_username">username</th>
                                    <th data-i18n="login_logins_record_mac_address">mac addr</th>
                                    <th data-i18n="login_logins_record_ip_address">ip address</th>
                                    <th data-i18n="login_logins_record_ipv6_address">ipv6 address</th>
                                    <th data-i18n="login_logins_record_zone">zone</th>
                                    <th data-i18n="login_logins_record_controller">nas id</th>
                                    <th data-i18n="login_logins_record_reason">reason</th>
                                    <th data-i18n="login_logins_record_total_time">total time</th>
                                    <th><span data-i18n="login_logins_record_average_speed">average speed </span>( <?= $_SESSION['metrics'] ?>ps )</th>
                                    <th><span data-i18n="login_logins_record_traffic_used">traffic used </span>( <?= $_SESSION['metrics'] ?> )</th>
                                    <th data-i18n="login_logins_record_profile">profile</th>
                                    <th data-i18n="login_logins_record_class">class</th>
                                    <th data-i18n="login_logins_record_brand">brand</th>
                                    <th data-i18n="login_logins_record_model">model</th>

                                    <?php if ($_SESSION['access_level'] == "superuser") { ?>
                                        <th data-i18n="login_logins_record_tenant">tenant</th>
                                    <?php } ?>

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


<!-- <div class="modal fade text-left" id="filter_modal" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="mythememodal">Filter</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">

                        <div class="card-content">
                            <div class="card-body">

                                <form class="form form-vertical">
                                    <div class="form-body">
                                        <div class="row">

                                            <div class="col-12">

                                                <div class="form-group">
                                                    <label for="type" data-i18n="data_type">Date Type: </label>
                                                    <select name=type id="type" class="select2 form-control">
                                                        <option value="login" data-i18n="type_login">Login</option>
                                                        <option value="logout" data-i18n="type_logout">Logout</option>
                                                    </select>
                                                </div>

                                                <div class="form-group" style="position:relative; left:auto; display:block;">
                                                    <label for="startdate" data-i18n="data_from">Date From</label>
                                                    <input type="text" class="form-control format-picker" name="startdate" id="startdate" value='<?= report_date_view(report_date_start("", 2)) ?>'>
                                                </div>

                                                <div class="form-group" style="position:relative; left:auto; display:block;">
                                                    <label for="enddate" data-i18n="data_until">Date Until</label>
                                                    <input type="text" class="form-control format-picker" name="enddate" id="enddate" value='<?= report_date_view(report_date_start("", 1)) ?>'>
                                                </div>

                                                <div class="form-group">
                                                    <label for="username" data-i18n="username">Username: </label>
                                                    <input type="text" placeholder="" name="username" id="username" value="" class="form-control" />
                                                </div>

                                                <div class="form-group">
                                                    <label for="mac_address" data-i18n="mac_address">MAC Address: </label>
                                                    <input type="text" placeholder="EG. 30:AA:BD:12:EE:09" name="mac_address" id="mac_address" value="" class="form-control" />
                                                </div>

                                                <div class="form-group">
                                                    <label for="ip_address" data-i18n="ip_address">IP Address: </label>
                                                    <input type="text" placeholder="EG. 10.5.12.245" name="ip_address" id="ip_address" value="" class="form-control" />
                                                </div>

                                                <div class="form-group">
                                                    <label for="controller" data-i18n="controller">NAS Identity: </label>
                                                    <input type="text" placeholder="" name="controller" id="controller" value="" class="form-control" />
                                                </div>


                                                <!-?php if ($_SESSION['access_level'] == "superuser") { ?->

                                                    <div class="form-group">

                                                        <label for="tenant_id" data-i18n="tenant_id">Tenant </label>
                                                        <select name="tenant_id" id="tenant_id" class="form-control">
                                                            <option value="">All Tenant</option>
                                                            <!-?php

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

                                                <!-?php } ?>


                                            </div>

                                            <div class="col-12">
                                                <button type="button" id="filter-data" class="btn btn-primary round mr-1 mb-1 waves-effect waves-light pull-right">Filter</button>
                                                <button type="reset" class="btn btn-danger round mr-1 mb-1 waves-effect waves-light pull-right" data-dismiss="modal">Cancel</button>
                                            </div>

                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div> -->



<script>
    var access_user = '<?= $_SESSION['access_level'] ?>';
</script>


<?php

require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";


?>

<script src="/assets/js/datejs/build/date.js"></script>