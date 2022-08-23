<?php

$kiw['module'] = "Report -> Top Current Bandwidth User";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="bandwidth_current_top_account_title">Current Top Account</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="bandwidth_current_top_account_subtitle">
                                Information on current top account
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

    <div class="content-body">


        <!-- <section id="css-classes" class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="card-text">
                        <div class="col-12">
                            <div class="form-group row">
                                <h6 class="text-bold-500" data-i18n="report_account_expiry_search">Date Search :</h6>
                            </div>
                        </div>
                        <div class="col-12">

                            <div class="form-group row">

                                <div class="col-md-6">
                                    <div class="form-group"> -->
                                        <!-- <label for="zone" data-i18n="data_zone">Zone</label> -->
                                        <!-- <?= report_project_select($kiw_db, $kiw_cache, $_SESSION['tenant_id']); ?>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <button type="submit" name='search' id='search' class="btn btn-primary waves-effect waves-light btn-search" data-i18n="bandwidth_account_usage_search">Search</button>
                                    </div>
                                </div>

                            </div>

                        </div>
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
                            <div class="col-12">

                                <div class="form-group row">

                                    <div class="col-md-6" id="zone-div" style="display:block">
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

                                    <div class="col-md-6" style="padding-top:20px;">
                                        <div class="form-group">
                                            <button type="submit" name='search' id='search' class="btn btn-primary waves-effect waves-light btn-search" data-i18n="bandwidth_account_usage_search">Search</button>
                                        </div>
                                    </div>

                                </div>

                            </div>
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
                            <table class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="bandwidth_current_top_account_no">No</th>
                                        <th data-i18n="bandwidth_current_top_account_login">Login Time</th>
                                        <th data-i18n="bandwidth_current_top_account_username">Username</th>
                                        <th data-i18n="bandwidth_current_top_account_usage_time">Usage Time</th>
                                        <th data-i18n="bandwidth_current_top_account_mac_address">Mac Address</th>
                                        <th data-i18n="bandwidth_current_top_account_ipaddress">IP Address</th>
                                        <th><span data-i18n="bandwidth_current_top_account_download">Download</span> ( <?= $_SESSION['metrics'] ?> )</th>
                                        <th><span data-i18n="bandwidth_current_top_account_upload">Upload</span> ( <?= $_SESSION['metrics'] ?> )</th>
                                        <th><span data-i18n="bandwidth_current_top_account_average_speed">Average Speed</span> ( <?= $_SESSION['metrics'] ?>ps )</th>
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
                                                    <label for="zone" data-i18n="data_zone">Zone</label>
                                                    <?= report_project_select($kiw_db, $kiw_cache, $_SESSION['tenant_id']); ?>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <button type="button" id="filter-data" class="btn btn-primary round mr-1 mb-1 waves-effect waves-light pull-right" data-i18n="bandwidth_current_top_account_modal_filter">Filter</button>
                                                <button type="reset" class="btn btn-danger round mr-1 mb-1 waves-effect waves-light pull-right" data-dismiss="modal" data-i18n="bandwidth_current_top_account_modal_cancel">Cancel</button>
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


<?php

require_once "includes/include_footer.php";
require_once "includes/include_report_footer.php";
require_once "includes/include_datatable.php";

?>
<script src="/assets/js/datejs/build/date.js"></script>