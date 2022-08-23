<?php

$kiw['module'] = "Finance -> PMS Payment Queue";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">PMS Payment Queue</h2>
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
                                    <tr>
                                        <th data-i18n="thead_no">NO</th>
                                        <th data-i18n="thead_username">USERNAME</th>
                                        <th data-i18n="thead_room">ROOM NO.</th>
                                        <th data-i18n="thead_logindate">LOGIN DATE</th>
                                        <th data-i18n="thead_postdate">POST DATE</th>
                                        <th data-i18n="thead_amount">CHARGE AMOUNT (MYR)</th>
                                        <th data-i18n="thead_status">STATUS</th>
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
require_once "includes/include_datatable.php";
?>
