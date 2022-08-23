<?php

$kiw_page = "Account History";

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
                        <div class="panel-heading panel-heading-divider">Usage History (Up to 500 Entries)</div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table id=" table1" class="table responsive no-wrap table-condensed table-hover table-bordered table-striped table-data dtr-inline">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Login <br>Date/Time</th>
                                            <th>Logout<br>Date/Time</th>
                                            <th>Total Time <br> (D:H:M:S)</th>
                                            <th>MAC Address</th>
                                            <th>IP Address</th>
                                            <th>Traffic Use <br> (MB)</th>
                                            <th>Terminate Reason</th>
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