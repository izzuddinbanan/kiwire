<?php

$kiw['module'] = "Help -> Software Update";
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


$kiw_temp = @file_get_contents(dirname(__FILE__, 3) . "/system/version.json");

$kiw_temp = json_decode($kiw_temp, true);


$kiw_temp['version']      = $kiw_temp['version'] ?: "3.0";
$kiw_temp['build_num']    = $kiw_temp['build_num'] ?: "1";
$kiw_temp['last_update']  = $kiw_temp['last_update'] ?: "Unknown";
$kiw_temp['last_status']  = $kiw_temp['last_status'] ?: "Unknown";



?>


<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="help_software_update_title">Software Update</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="help_software_update_subtitle">
                                Perform software update
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">

        <div class="col-12">

            <div class="card">

                <div class="card-content">
                    <div class="card-body">

                            <div class="table-responsive">

                                <table class="table table-hover table-data">
                                    <thead class="dark" style="background:lightgrey">
                                        <tr class="text-uppercase">
                                            <th data-i18n="thead_tenantID">Module</th>
                                            <th data-i18n="thead_name">Sub-Module</th>
                                            <th data-i18n="thead_adminID">Version</th>
                                            <th data-i18n="thead_expiry">Latest</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Administrator</td>
                                        </tr>
                                        <tr>
                                            <td>API</td>
                                        </tr>
                                        <tr>
                                            <td>Control Panel</td>
                                        </tr>
                                        <tr>
                                            <td>User</td>
                                        </tr>
                                        <tr>
                                            <td>Service</td>
                                        </tr>
                                    </tbody>

                                </table>
                            </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


<?php

require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";

?>

<script src="/assets/js/jquery-copytoclipboard.js"></script>