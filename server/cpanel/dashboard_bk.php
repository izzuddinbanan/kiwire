<?php

$kiw_page = "Dashboard";

require_once "includes/include_general.php";
require_once "includes/include_session.php";
require_once "includes/include_header.php";
require_once "includes/include_nav.php";

?>

<div class="be-content">
    <div class="main-content container-fluid">

        <div class="row">
            <div class="col-md-12">

                <div class="widget widget-fullwidth be-loading">
                    <div class="widget-chart-container" style="padding: 15px;">
                        <br>
                        <div class="widget-chart-info">
                            <ul class="chart-legend-horizontal">
                                <li><span data-color="main-chart-color1"></span> Download (MB)</li>
                                <li><span data-color="main-chart-color2"></span> Upload (MB)</li>
                            </ul>
                        </div>
                        <div class="widget-counter-group widget-counter-group-right">
                            <div class="counter counter-big">
                                <div class="value">Daily Upload / Download Usage</div>
                                <div class="desc">For the past one month</div>
                            </div>
                        </div>
                        <div id="main-chart" style="height: 260px;"></div>
                    </div>
                    <div class="be-spinner">
                        <svg width="40px" height="40px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg">
                            <circle fill="none" stroke-width="4" stroke-linecap="round" cx="33" cy="33" r="30" class="circle"></circle>
                        </svg>
                    </div>
                </div>

            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-md-6">

                <div class="panel panel-default">
                    <div class="panel-heading">Login Activities</div>
                    <div class="panel-body">

                        <ul class="user-timeline user-timeline-compact">
                            <li class="latest">
                                <div class="user-timeline-title">No record</div>
                                <div class="user-timeline-description">2020-12-04 15:22:53</div>
                            </li>

                        </ul>
                    </div>
                </div>

            </div>
            <div class="col-md-6">

                <div class="panel panel-default panel-table">
                    <div class="panel-heading">
                        <div class="title">Invoices</div>
                    </div>
                    <div class="panel-body table-responsive">
                        <table class="table table-striped table-borderless">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Profile</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody class="no-border-x">


                                <tr>
                                    <td>1</td>
                                    <td>04/09/2019</td>
                                    <td>Sop</td>
                                    <td>0.00</td>
                                    <td>Unpaid</td>
                                </tr>


                                <tr>
                                    <td>2</td>
                                    <td>22/10/2020</td>
                                    <td>Cme-installer-5g</td>
                                    <td>0.00</td>
                                    <td>Unpaid</td>
                                </tr>


                            </tbody>
                        </table>
                    </div>

                </div>

            </div>
            <div class="col-md-6">

                <div class="panel panel-default panel-table">

                    <div class="panel-heading">
                        <div class="title">Last Login Information</div>
                    </div>
                    <div class="panel-body table-responsive">
                        <table class="table table-striped table-borderless">
                            <thead>
                                <tr>
                                    <th>Device Type</th>
                                    <th>Brand</th>
                                </tr>
                            </thead>
                            <tbody class="no-border-x">

                                <tr>
                                    <td>
                                    </td>
                                    <td>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>

                </div>

            </div>
        </div>

    </div>

<?php

require_once "includes/include_footer.php";

?>

