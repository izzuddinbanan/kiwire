<?php

$kiw['module'] = "Report -> Monitoring -> Controller Session";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="monitoring_top_controller_title">PMS Transactions</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="monitoring_top_controller_subtitle">
                                Transaction recorded from PMS system
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">

        <section id="report_table" class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="card-text">
                        <div class="table-responsive">
                            <table class="table table-hover table-data">

                                <thead>
                                <tr class="text-uppercase">
                                    <th data-i18n="">No</th>
                                    <th data-i18n="">Check In</th>
                                    <th data-i18n="">Check Out</th>
                                    <th data-i18n="">Room</th>
                                    <th data-i18n="">First Name</th>
                                    <th data-i18n="">Last Name</th>
                                    <th data-i18n="">VIP Code</th>
                                    <th data-i18n="">Status</th>
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


<?php

require_once "includes/include_footer.php";
require_once "includes/include_report_footer.php";
require_once "includes/include_datatable.php";

?>

