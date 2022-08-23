<?php

$kiw_page = "Dashboard";

require_once "includes/include_general.php";
require_once "includes/include_session.php";
require_once "includes/include_header.php";
require_once "includes/include_nav.php";


?>

<style>
    *,
    *::before,
    *::after {
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        box-sizing: border-box;
    }

    .text-center {
        text-align: center !important;
    }

    .mb-50,
    .my-50 {
        margin-bottom: .5rem !important;
        font-weight: bold;
    }

    .d-flex {
        display: -webkit-box !important;
        display: -webkit-flex !important;
        display: -moz-box !important;
        display: -ms-flexbox !important;
        display: flex !important;
    }

    .justify-content-between {
        -webkit-box-pack: justify !important;
        -webkit-justify-content: space-between !important;
        -moz-box-pack: justify !important;
        -ms-flex-pack: justify !important;
        justify-content: space-between !important;
    }

    .justify-content-center {
        -webkit-box-pack: center !important;
        -webkit-justify-content: center !important;
        -moz-box-pack: center !important;
        -ms-flex-pack: center !important;
        justify-content: center !important;
    }

    .font-small-3 {
        font-size: 1rem !important;
    }

    .font-large-1 {
        font-size: 1.3rem !important;
    }

    .font-medium-2 {
        font-size: 1.2rem !important;
    }

    .font-weight-bold {
        font-weight: 700 !important
    }

    .list-unstyled {
        padding-left: 0;

        list-style: none;
    }

    .bg-primary {
        background-color: #7367f0 !important;
    }

    .bg-warning {
        background-color: #ff9f43 !important;
    }

    .bg-danger {
        background-color: #ea5455 !important;
    }

    a.bg-primary:hover,
    a.bg-primary:focus,
    button.bg-primary:hover,
    button.bg-primary:focus {
        background-color: #4839eb !important;
    }

    a.bg-success:hover,
    a.bg-success:focus,
    button.bg-success:hover,
    button.bg-success:focus {
        background-color: #1f9d57 !important;
    }

    .align-middle {
        vertical-align: middle !important;
    }

    .text-muted {
        color: #b8c2cc !important
    }

    .icon-plus-square:before {
        content: "\e8af";
    }

    .icon-plus-circle:before {
        content: "\e8b0";
    }

    .icon-plus:before {
        content: "\e8b1";
    }

    .activity-timeline.timeline-left li .timeline-icon i {
        vertical-align: sub;
    }

    .activity-timeline.timeline-right {
        margin-right: 1.5rem;
        padding-right: 40px;

        border-right: 2px solid #dae1e7;
    }

    .activity-timeline.timeline-right li {
        position: relative;

        margin-bottom: 20px;

        text-align: right;
    }

    .activity-timeline.timeline-right li p {
        margin-bottom: 0;
    }

    .activity-timeline.timeline-right li .timeline-icon {
        position: absolute;
        top: 0;
        right: -4.3rem;

        padding: .6rem .7rem;

        color: #fff;
        border-radius: 50%;
    }

    .activity-timeline.timeline-right li .timeline-icon i {
        vertical-align: sub;
    }
</style>

<div class="be-content">
    <div class="main-content container-fluid">

        <div class="row">
            <div class="col-lg-5 col-md-6 col-12">
                <div class="panel panel-default panel-border-color panel-border-color-success">
                    <div class="panel-heading panel-heading-divider">Overall Usage</div>
                    <div class="panel-body">

                        <div class="card-content">
                            <div class="card-body pt-0">
                                <div class="row">

                                    <div class="col-sm-10 col-12 d-flex justify-content-center">
                                        <div id="support-tracker-chart"></div>
                                    </div>
                                </div>
                                <div class="chart-info d-flex justify-content-between">
                                    <div class="text-center">
                                        <p class="mb-50">Total Quota</p>
                                        <span class="font-large-1 total-quota"></span>
                                    </div>
                                    <div class="text-center">
                                        <p class="mb-50">Balance</p>
                                        <span class="font-large-1 balance-quota"></span>
                                    </div>
                                    <div class="text-center">
                                        <p class="mb-50">Remaining Time</p>
                                        <span class="font-large-1 remaining-time"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-lg-7 col-md-6 col-12">
                <div class="panel panel-default panel-border-color panel-border-color-success">
                    <div class="panel-heading panel-heading-divider">Monthly Usage</div>
                    <div class="panel-body">

                        <div class="card-content">
                            <div class="card-body">
                                <div style="text-align:center;" id="column-chart"></div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>


        <div class="row match-height">
            <div class="col-lg-5 col-md-6 col-12">
                <div class="panel panel-default panel-border-color panel-border-color-success">
                    <div class="panel-heading panel-heading-divider">Login Activities</div>
                    <div class="panel-body">

                        <div class="card-content">
                            <div class="card-body">
                                <ul class="user-timeline user-timeline-compact">

                                </ul>

                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- <div class="col-lg-7 col-md-6 col-12">
                <div class="panel panel-default panel-border-color panel-border-color-success">
                    <div class="panel-heading panel-heading-divider">Last Login Information</div>
                    <div class="panel-body">

                        <div class="card-content">
                            <div class="card-body">
                        

                            </div>
                        </div>

                    </div>
                </div>
            </div> -->
        </div>

    </div>
</div>







<?php

require_once "includes/include_footer.php";

?>