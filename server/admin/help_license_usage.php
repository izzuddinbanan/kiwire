<?php

$kiw['module'] = "Help -> License Usage";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="help_license_title">License Usage</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="help_license_subtitle">
                                View current number of NAS and licensing
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
                                        <th data-i18n="help_license_no">No</th>
                                        <th data-i18n="help_license_cust_name">Customer name</th>
                                        <th data-i18n="help_license_type">License Type</th>
                                        <th data-i18n="help_license_status">Status</th>
                                        <th data-i18n="help_license_expiry_date">Expiry Date</th>
                                        <th data-i18n="help_license_allowed_controller">Allowed No. Of Controller</th>
                                        <th data-i18n="help_license_current_controller">Current No. Of Controller</th>
                                        <th data-i18n="help_license_percentage">Percentage (%)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody></tbody>
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
require_once "includes/include_datatable.php";

?>

<script src="/assets/js/datejs/build/date.js"></script>

