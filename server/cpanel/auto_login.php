<?php

$kiw_page = "Account Auto Login";

require_once "includes/include_general.php";
require_once "includes/include_session.php";
require_once "includes/include_header.php";
require_once "includes/include_nav.php";

?>

<div class="be-content">
    <div class="main-content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="col-md-12">
                    <div class="panel panel-default panel-border-color panel-border-color-primary" style="overflow-x: auto;">
                        <div class="panel-heading panel-heading-divider">Auto Login</div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table id="table1" class="table responsive no-wrap table-condensed table-hover table-bordered table-striped table-data dtr-inline">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Action</th>
                                            <th>MAC Address</th>
                                            <th>Last Auto Login</th>
                                            <th>System</th>
                                            <th>Class</th>
                                            <th>Brand</th>
                                            <th>Model</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>


<?php

require_once "includes/include_footer.php";
require_once "../../server/admin/includes/include_datatable.php";

?>