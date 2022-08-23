<?php
http_response_code(1);
exit();
$kiw_page = "Device Registration";

require_once "includes/include_general.php";
require_once "includes/include_session.php";
require_once "includes/include_header.php";
require_once "includes/include_nav.php";

?>

<style>
    .btn-update {
        float: right;
        margin: 4px
    }

    .btn-close {
        float: right;
        margin: 5px;
    }
</style>



<div class="be-content">
    <div class="main-content container-fluid">


        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default panel-border-color panel-border-color-primary">
                    <div class="panel-heading panel-heading-divider">List of Registered Device</div>
                    <div class="panel-body">
                        <table id="table1" class="table table-condensed table-hover table-bordered table-striped table-data">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>MAC Address</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="table-data">
                                <tr>
                                    <td colspan="8" style="text-align:center;">Loading Data....</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default panel-border-color panel-border-color-primary">
                    <div class="panel-heading panel-heading-divider">Registration Form</div>
                    <div class="panel-body">
                        <form class="create-form" action="#">
                            <table class="table table-condensed table-hover table-bordered table-striped">
                                <tbody>
                                    <tr>
                                        <td>MAC Address</td>
                                        <td><input type="text" name="mac_address" class="form-control"></td>
                                    </tr>
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-primary btn-create">Add Device</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>


    </div>
</div>

<div id="edit-modal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Edit Device</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <form class="edit-form" action="#">

                <div class="modal-body">

                    <label>Enter MAC Address</label>
                    <input type="text" name="mac-address" id="mac-address" class="form-control" />
                    <br />

                </div>

                <div class="modal-footer">

                    <input type="hidden" id="reference" name="reference" value="">
                    <button type="button" class="btn btn-danger btn-close" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary btn-update">Update</button>

                </div>
            </form>
        </div>
    </div>
</div>

<?php

require_once "includes/include_footer.php";

?>