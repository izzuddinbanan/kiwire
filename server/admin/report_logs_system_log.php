<?php

$kiw['module'] = "Report -> System Log";
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
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="logs_system_log_title">System Log</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="logs_system_log_subtitle">

                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <section id="basic-tabs-components">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card overflow-hidden">
                        <div class="card-content">
                            <div class="card-body">
                                <form id="update-form" class="form-horizontal" method="post"><br><br>

                                    <div class="col-12">
                                        <div class="form-group row">
                                            <div class="text-right col-md-3">
                                                <span data-i18n="logs_system_log_system">Select a system log</span>
                                            </div>
                                            <div class="col-md-7">

                                                <select onchange="getLog(this.value);" class="form-control" data-style="btn-default" tabindex="-98">
                                                    <option value=""></option>

                                                    <?php
                                                    // $dir = "/var/log/";
                                                    // if (is_dir($dir)) {
                                                    //     if ($dh = opendir($dir)) {
                                                    //         while (($file = readdir($dh)) !== false) {
                                                    //             if (preg_match("/kiwire_syslog*/", $file)) {
                                                    //                 echo "<option value=\"$file\">" . ucfirst(substr($file, 7, (strlen($file) - 7))) . "</option> \n";
                                                    //             }
                                                    //         }
                                                    //         closedir($dh);
                                                    //     }
                                                    // }
                                                    ?>
                                                </select>

                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="result_log" class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="card-text">
                        <div class="">

                        </div>

                    </div>

                </div>
            </div>
        </section>
    </div>


</div>

<?php
require_once "includes/include_footer.php";
?>


<!-- 
<script>
    function getLog(log) {

        if (log) {

            $.get("ajax/report_ksyslog.php?action=get&type=full&xid=" + log, function(x) {
                $("#result").html(x);
                $("#result").css("display", "block");

            });

        }

    }
</script> -->