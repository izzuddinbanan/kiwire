<?php

$kiw['module'] = "Report -> Login Scanner";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="login_logins_record_title">Security</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="login_logins_record_subtitle">
                                Listing of IP scanned for security
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
                    <div class="col-12">
                        <div class="form-group row">
                            <h6 class="text-bold-500" data-i18n="report_account_expiry_search">Filter :</h6>
                        </div>
                    </div>

                    <div class="row">

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
                                <label for="ip_address" data-i18n="ip_address">IP Address: </label>
                                <input type="text" placeholder="EG. 10.5.12.245" name="ip_address" id="ip_address" value="" class="form-control" />
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="severity" data-i18n="">Severity: </label>
                                <select id="severity" class="select2 form-control" name="severity">
                                    <option value="">All</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
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


                    </div>
                    <button type="submit" name='search' id='search' class="btn btn-primary waves-effect waves-light btn-search" data-i18n="bandwidth_account_usage_search">Search</button>

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
                                    <th data-i18n="login_logins_record_username">Date</th>
                                    <th data-i18n="login_logins_record_total_time">severity</th>
                                    <th data-i18n="login_logins_record_login_datetime">tenant</th>
                                    <th data-i18n="login_logins_record_logout_datetime">username</th>
                                    <th data-i18n="login_logins_record_logout_datetime">Source User</th>
                                    <th data-i18n="login_logins_record_mac_address">level</th>
                                    <th data-i18n="login_logins_record_ip_address">ip address</th>
                                    <th data-i18n="login_logins_record_ipv6_address">Service</th>
                                    <th data-i18n="login_logins_record_controller">host</th>
                                    <th data-i18n="login_logins_record_reason">vulnerability name</th>
                                    <th data-i18n="login_logins_record_zone">action</th>

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

<script>
    var access_user = '<?= $_SESSION['access_level'] ?>';
</script>


<?php

require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";


?>

<script src="/assets/js/datejs/build/date.js"></script>