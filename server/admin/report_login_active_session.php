<?php


$kiw['module'] = "Report -> Who is Online";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="login_active_session_title">Active Session</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="login_active_session_subtitle">
                                Listing of current connected user
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

                        <div class="col-12 float-right">
                            <div class="custom-control custom-switch custom-control-inline">
                                <input type="checkbox" class="custom-control-input" name="customSwitch1" id="customSwitch1" value="mini_column" checked />
                                <label class="custom-control-label" for="customSwitch1">
                                </label>
                                <span class="switch-label">Column</span>
                            </div>
                        </div>

                        <div class="table-responsive">

                            <table id="table-data" class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">

                                        <th data-i18n="login_active_session_no">NO</th>
                                        <th data-i18n="login_active_session_action">ACTION</th>
                                        <th data-i18n="login_active_session_login_time">LOGIN TIME</th>
                                        <th data-i18n="login_active_session_username">USERNAME</th>
                                        <th data-i18n="login_active_session_mac_address">MAC ADDR</th>
                                        <th data-i18n="login_active_session_ip_address">IP ADDRESS</th>
                                        <th data-i18n="login_active_session_ipv6_address">IPv6 ADDRESS</th>
                                        <th><span data-i18n="login_active_session_upload">UPLOAD</span> (<?= strtoupper($_SESSION['metrics']) ?>)</th>
                                        <th><span data-i18n="login_active_session_download">DOWNLOAD</span> (<?= strtoupper($_SESSION['metrics']) ?>)</th>
                                        <th data-i18n="login_active_session_zone">ZONE</th>
                                        <th data-i18n="login_active_session_nas_id">NAS ID</th>
                                        <th><span data-i18n="login_active_session_average_speed">AVERAGE SPEED</span>( <?= ($_SESSION['metrics']) ?>ps )</th>
                                        <th data-i18n="login_active_session_class">CLASS</th>
                                        <th data-i18n="login_active_session_brand">BRAND</th>
                                        <th data-i18n="login_active_session_model">MODEL</th>
                                        <th data-i18n="login_active_session_os">OS</th>

                                        <?php if ($_SESSION['access_level'] == "superuser") { ?>
                                            <th data-i18n="login_active_session_tenant">TENANT</th>
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
</div>


<script>
    var access_user = '<?= $_SESSION['access_level'] ?>';
</script>


<?php
require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";
?>