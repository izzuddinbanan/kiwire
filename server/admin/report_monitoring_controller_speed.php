<?php

$kiw['module'] = "Report -> Monitoring -> Controller Speed";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="monitoring_speed_title">Controller Speed (via SNMP)</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="monitoring_speed_subtitle">
                                Information on controller speed (via SNMP)
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">



        <div class="content-body">

            <section id="css-classes" class="card">
                <div class="card-content">
                    <div class="card-body">
                        <div class="card-text">
                            <div class="col-12">
                                <div class="form-group row">
                                    <!-- <h6 class="text-bold-500">Controller :</h6> -->
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group row">


                                    <div class="col-md-3 position-relative has-icon-left">
                                        <input type="text" class="form-control format-picker" name="startdate" id="startdate" value='<?= report_date_view(report_date_start("", 2)) ?>'>
                                        <div class="form-control-position">
                                            <i class="feather icon-calendar"></i>
                                        </div>
                                    </div>

                                    <span data-i18n="monitoring_speed_to">to</span>

                                    <div class="col-md-3 position-relative has-icon-left">
                                        <input type="text" class="form-control format-picker" name="enddate" id="enddate" value='<?= report_date_view(report_date_start("", 1)) ?>'>
                                        <div class="form-control-position">
                                            <i class="feather icon-calendar"></i>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <select name="controller" id="controller" class="select2 form-control" data-style="btn-default" tabindex="-98">
                                            <option value="none" data-i18n="monitoring_speed_none">None</option>
                                            <?
                                            $sql = "select * from kiwire_controller where tenant_id = '{$tenant_id}' group by unique_id order by unique_id";
                                            $rows = $kiw_db->fetch_array($sql);
                                            foreach ($rows as $record) {
                                                $selected = "";
                                                if ($record['unique_id'] == $kiw_row['unique_id']) $selected = 'selected="selected"';
                                                echo "<option value =\"$record[unique_id]\" $selected> $record[unique_id]</option> \n";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <button type="submit" name='search' id='search' class="btn btn-primary waves-effect waves-light btn-search" data-i18n="monitoring_speed_search">Search</button>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Line Chart -->
            <section id="report_graph" class="card">


                <div class="row">
                    <div class="col-12">
                        <div class="card-content">
                            <div class="card-body">
                                <div id="data-chart"></div>
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
                                            <th data-i18n="monitoring_speed_no">No</th>
                                            <th data-i18n="monitoring_speed_date">Date</th>
                                            <th data-i18n="monitoring_speed_speed">Speed (MB)</th>
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
</div>



<?php
require_once "includes/include_footer.php";
require_once "includes/include_report_footer.php";
require_once "includes/include_datatable.php";
?>