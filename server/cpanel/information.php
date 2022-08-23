<?php


$kiw_page = "Account Information";


global $kiw_db, $kiw_tenant, $kiw_username;


require_once "includes/include_general.php";
require_once "includes/include_session.php";
require_once "includes/include_header.php";
require_once "includes/include_nav.php";


?>


<div class="be-content">
    <div class="main-content container-fluid">

        <div class="row">

            <div class="col-md-10">
                <div class="panel panel-default panel-border-color panel-border-color-primary">
                    <div class="panel-heading panel-heading-divider">Personal Details</div>
                    <div class="panel-body">

                        <form class="create-form" action="#">

                            <table class="table table-condensed table-hover table-bordered table-striped">
                                <tbody>
                                  
                                    <tr>
                                        <td>Username</td>
                                        <td><input type="text" readonly="readonly" name="username" id="username" class="form-control"></td>
                                    </tr>
                                   
                                    <tr>
                                        <td>Password</td>
                                        <td>
                                            <input type="password"  name="password" id="password" class="form-control"><br>
                                        </td>
                                    </tr>

                                    <!-- <tr>
                                        <td>New Password</td>
                                        <td><input type="password" name="new_password" class="form-control" value=""></td>
                                    </tr> -->
                                   
                                    <tr>
                                        <td>Full Name</td>
                                        <td><input type="text" name="fullname" id="fullname" class="form-control"></td>
                                    </tr>
                                  
                                    <tr>
                                        <td>Email Address</td>
                                        <td><input type="text" name="email_address" id="email_address" class="form-control"></td>
                                    </tr>
                                 
                                    <tr>
                                        <td>Phone Number</td>
                                        <td><input type="text" name="phone_no" id="phone_no" class="form-control"></td>
                                    </tr>
                                  
                                    <tr>
                                        <td>Status</td>
                                        <td><input type="text" readonly="readonly" name="status" id="status" class="form-control"></td>
                                    </tr>
                                   
                                    <tr>
                                        <td>Creation Date</td>
                                        <td><input type="text" readonly="readonly" name="created_date" id="created_date" class="form-control"></td>
                                    </tr>
                                   
                                    <tr>
                                        <td>Expiry Date</td>
                                        <td><input type="text" readonly="readonly" name="expired_date" id="expired_date" class="form-control"></td>
                                    </tr>
                                   
                                    <tr>
                                        <td>Profile Name [ Subscribe ]</td>
                                        <td><input type="text" readonly="readonly" name="profile_sub" id="profile_sub" class="form-control"></td>
                                    </tr>
                                   
                                    <tr>
                                        <td>Profile Name [ Current ]</td>
                                        <td><input type="text" readonly="readonly" name="profile_curr" id="profile_curr" class="form-control"></td>
                                    </tr>
                                </tbody>
                            </table>

                            <input type="hidden" name="update" value="true" />
                            <button type="button" class="btn btn-space btn-primary btn-update">Update Profile</button>

                        </form>

                    </div>
                </div>
            </div>

            <div class="col-md-2"></div>
        </div>

    </div>
</div>


<?php

require_once "includes/include_footer.php";


?>