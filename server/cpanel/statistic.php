<?php
http_response_code(1);
exit();
$kiw_page = "Statistics";

require_once "includes/include_general.php";
require_once "includes/include_session.php";
require_once "includes/include_header.php";
require_once "includes/include_nav.php";

?>

<style>
    .btn-filter {
        float: right;
        margin: 4px
    }

    .btn-close {
        float: right;
        margin: 5px;
    }

    .modal-body {
        height: 50vh;
        overflow-y: auto;
    }
</style>


<div class="be-content">
    <div class="main-content container-fluid">

        <div class="row">
            <div class="col-md-12">

                <div class="col-md-12">
                    <div class="panel panel-default panel-border-color panel-border-color-primary">
                        <div class="panel-heading panel-heading-divider">Statistic</div>
                        <div class="panel-body">


                            <!-- <form action="#" method=""> -->

                            <div class="row" style="margin-bottom:10px;">

                                <div style="margin-left:101em;">
                                    <!-- <button class="btn btn-primary btn-sm" name="searchdate" value="true">Submit</button> -->
                                    <button id="filter-btn" class="btn btn-primary fa fa-filter">Filter</button>
                                </div>

                                <!-- <div class="col-md-3">
                                        <select class="form-control input-xs" id="interval" name="interval">
                                            <option value="monthly">Monthly</option>
                                            <option value="daily">Daily</option>
                                            <option value="hourly">Hourly</option>
                                        </select>
                                    </div> -->

                                <!-- <div class="col-md-3" data-selected="daily" style="display:none">
                                        <div data-min-view="2" data-date-format="yyyy-mm-dd" class="input-group date datetimepicker" style="padding:0">
                                            <input size="16" type="text" class="form-control input-xs" id="datepicker-from" name="input_datepicker-from" value="" placeholder="From">
                                            <span class="input-group-addon btn btn-primary"><i class="icon-th mdi mdi-calendar"></i></span>
                                        </div>
                                    </div>

                                    <div class="col-md-3" data-selected="daily" style="display:none">
                                        <div data-min-view="2" data-date-format="yyyy-mm-dd" class="input-group date datetimepicker" style="padding:0">
                                            <input size="16" type="text" class="form-control input-xs" id="datepicker-to" name="input_datepicker-to" value="" placeholder="To">
                                            <span class="input-group-addon btn btn-primary"><i class="icon-th mdi mdi-calendar"></i></span>
                                        </div>
                                    </div>

                                    <div class="col-md-3" data-selected="hourly" style="display:none">
                                        <div data-min-view="2" data-date-format="yyyy-mm-dd" class="input-group date datetimepicker" style="padding:0">
                                            <input size="16" type="text" class="form-control input-xs" id="datepicker-hourly" name="input_datepicker-hourly" value="" placeholder="Select Date">
                                            <span class="input-group-addon btn btn-primary"><i class="icon-th mdi mdi-calendar"></i></span>
                                        </div>
                                    </div> -->

                            </div>

                            <!-- <div style="margin-left:100em;"> -->
                            <!-- <button class="btn btn-primary btn-sm" name="searchdate" value="true">Submit</button> -->
                            <!-- <button id="filter-btn" class="btn btn-primary btn-sm">Filter</button>
                                </div> -->

                            <!-- </form> -->

                            <table id="table1" class="table table-condensed table-hover table-bordered table-striped table-data">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Month/Date</th>
                                        <th>Total User Login</th>
                                        <th>Total Time <br>(H:M:S)</th>
                                        <th>Total Traffic Use <br>(GB)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="5" style="text-align: center;">No Data Available</td>
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


<div id="filter-modal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Filter</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <form class="edit-form" action="#">

                <div class="modal-body">

                    <label>Interval</label>
                    <select name="interval" id="interval" class="form-control input-xs">
                        <option value="monthly">Monthly</option>
                        <option value="daily">Daily</option>
                        <option value="hourly">Hourly</option>
                    </select>
                    <br />

                    <div class="col-12 date-start" id="date-start" style="position:relative; left:auto; display:none;">
                        <label for="start_date" id="date-label">Date Start</label>
                        <div class="form-group">
                            <input type="text" name="start_date" id="start_date" value="" class="form-control input-xs" required>
                        </div>
                    </div>


                    <div class="col-12 date-end" id="date-end" style="position:relative; left:auto; display:none;">
                        <label for="end_date">Date End</label>
                        <div class="form-group">
                            <input type="text" name="end_date" id="end_date" value="" class="form-control input-xs" required>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">

                    <input type="hidden" id="reference" name="reference" value="">
                    <button type="button" class="btn btn-danger btn-close" data-dismiss="modal">Close</button>
                    <button type="button" id="filter-data" class="btn btn-primary btn-filter">Filter</button>

                </div>
            </form>
        </div>
    </div>
</div>


<?php

require_once "includes/include_footer.php";

?>