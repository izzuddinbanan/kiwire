<?php

$kiw['module'] = "Help -> Services";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="help_services_title">System / Services Status</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="help_services_subtitle">
                                Critical system services health report
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
                <div class="card-header">
                </div>
                <div class="card-content">
                    <div class="card-body">

                        <div class="row">
                            <div class="col-sm-6 col-md-4">
                                <div class="card text-white bg-gradient-primary">
                                    <div class="card-content d-flex">
                                        <div class="card-body">
                                            <h5 class="text-white" id="current-cpu-usage" style="font-size: xx-large; font-weight: bolder;" >0</h5>
                                            <h4 class="text-white" data-i18n="help_services_cpu_load">CPU LOAD</h4>
                                            <p class="card-text" style="font-size: smaller;" data-i18n="help_services_past_min">FOR THE PAST 1, 5 AND 15 MINUTES</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <div class="card text-white bg-gradient-primary">
                                    <div class="card-content d-flex">
                                        <div class="card-body">
                                            <h5 class="text-white" id="current-memory-usage" style="font-size: xx-large; font-weight: bolder;">0</h5>
                                            <h4 class="text-white" data-i18n="help_services_memory">MEMORY USAGE</h4>
                                            <p class="card-text" style="font-size: smaller;" data-i18n="help_services_percentage1">IN PERCENTAGE</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <div class="card text-white bg-gradient-primary">
                                    <div class="card-content d-flex">
                                        <div class="card-body">
                                            <h5 class="text-white" id="current-disk-usage" style="font-size: xx-large; font-weight: bolder;">0</h5>
                                            <h4 class="text-white" data-i18n="help_services_disk">DISK USAGE</h4>
                                            <p class="card-text" style="font-size: smaller;" data-i18n="help_services_percentage2">IN PERCENTAGE</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-header mb-1">
                    <div class="heading-elements">
                        <span data-i18n="help_services_last_update">Last update on</span> <span class="last-update"><?= date("Y-m-d | H : i") ?></span>
                    </div>
                </div>

                <div class="card-content">
                    <div class="card-body">

                        <table class="table table-bordered service-list">

                            <thead>
                                <tr class="thead-dark">
                                    <th data-i18n="help_services_service_name">Service Name</th>
                                    <th data-i18n="help_services_status">Status</th>
                                    <th data-i18n="help_services_last_detected">Last Detected</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr class="service-mysqld">
                                    <td data-i18n="help_services_mariadb">MariaDB</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="service-redis">
                                    <td data-i18n="help_services_redis">Redis</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="service-nginx">
                                    <td data-i18n="help_services_nginx">Nginx</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="service-php-fpm">
                                    <td data-i18n="help_services_php-fpm">PHP-FPM</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="service-kiwire_service">
                                    <td data-i18n="help_serv_kiwire_service"><?= sync_brand_decrypt(SYNC_PRODUCT) ?> Service</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="service-kiwire_integration">
                                    <td data-i18n="help_serv_kiwire_integration"><?= sync_brand_decrypt(SYNC_PRODUCT) ?> Integration</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="service-kiwire_replication">
                                    <td data-i18n="help_services_replication">Replication Services [ Backup Server ]</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>

                        </table>

                    </div>
                </div>

            </div>
        </div>
    </div>


</div>


<?php

require_once "includes/include_footer.php";

?>

<script src="/assets/js/jquery-copytoclipboard.js"></script>

