<?php

$kiw_page = "Profile";

require_once "includes/include_general.php";
require_once "includes/include_session.php";
require_once "includes/include_header.php";
require_once "includes/include_nav.php";


global $kiw_db, $kiw_tenant, $kiw_username, $kiw_cloud;


$kiw_user = $kiw_db->query_first("SELECT profile_subs,profile_curr,session_time,quota_out,quota_in,date_last_login,date_activate FROM kiwire_account_auth WHERE tenant_id = '{$kiw_tenant}' AND username = '{$kiw_username}' LIMIT 1");


$kiw_profile = $kiw_db->query_first("SELECT * FROM kiwire_profiles WHERE tenant_id = '{$kiw_tenant}' AND name = '{$kiw_user['profile_subs']}' LIMIT 1");

$kiw_profile['attribute'] = json_decode($kiw_profile['attribute'], true);


// $kiw_user['quota'] = number_format(($kiw_user['quota_out'] + $kiw_user ['quota_in']) / pow(1024, 3), 2);

$kiw_user['quota'] = number_format((int)($kiw_user['quota_out'] + $kiw_user ['quota_in']) / (pow(2, 20)), 2);



$kiw_time['hour'] = floor($kiw_user['session_time'] / 3600);
$kiw_time['mins'] = floor($kiw_user['session_time'] / 60 % 60);
$kiw_time['secs'] = floor($kiw_user['session_time'] % 60);

$kiw_time = sprintf('%02d hours %02d minutes %02d seconds', $kiw_time['hour'], $kiw_time['mins'], $kiw_time['secs']);


$kiw_user['date_activate'] = sync_tolocaltime($kiw_user['date_activate'], $kiw_cloud['timezone']);

$kiw_user['date_last_login'] = sync_tolocaltime($kiw_user['date_last_login'], $kiw_cloud['timezone']);



?>


<div class="be-content">
    <div class="main-content container-fluid">

        <div class="row">
            <div class="col-md-10">
                <div class="panel panel-default panel-border-color panel-border-color-primary">
                    <div class="panel-heading panel-heading-divider">Current Usage</div>
                    <div class="panel-body">


                        <table class="table table-condensed table-hover table-bordered table-striped">
                            <tbody>
                                <tr>
                                    <td>Total Transfer</td>
                                    <td><input type="text" readonly="readonly" value="<?= $kiw_user['quota'] ?> Mb" class="form-control"></td>
                                </tr>
                                <tr>
                                    <td>Total Time</td>
                                    <td><input type="text" readonly="readonly" value="<?= $kiw_time ?>" class="form-control"></td>
                                </tr>
                                <tr>
                                    <td>First Login</td>
                                    <td><input type="text" readonly="readonly" value="<?= $kiw_user['date_activate'] ?>" class="form-control"></td>
                                </tr>
                                <tr>
                                    <td>Last Login</td>
                                    <td><input type="text" readonly="readonly" value="<?= $kiw_user['date_last_login'] ?>" class="form-control"></td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
            <div class="col-md-2"></div>
        </div>
        <div class="row">
            <div class="col-md-10">
                <div class="panel panel-default panel-border-color panel-border-color-primary">
                    <div class="panel-heading panel-heading-divider">Profile Information</div>
                    <div class="panel-body">


                        <table class="table table-condensed table-hover table-bordered table-striped">
                            <tbody>
                                <tr>
                                    <td>Profile Name</td>
                                    <td><input type="text" readonly="readonly" value="<?= $kiw_profile['name'] ?>" class="form-control"></td>
                                </tr>
                                <tr>
                                    <td>Profile Type</td>
                                    <td><input type="text" readonly="readonly" value="<?= ucfirst($kiw_profile['type']) ?>" class="form-control"></td>
                                </tr>
                                <tr>
                                    <td>Price</td>
                                    <td><input type="text" readonly="readonly" value="<?= $kiw_profile['price'] ?>" class="form-control"></td>
                                </tr>
                                <tr>
                                    <td>Simultaneous User</td>
                                    <td><input type="text" readonly="readonly" value="<?= $kiw_profile['attribute']['control:Simultaneous-Use'] ?>" class="form-control"></td>
                                </tr>
                                <tr>
                                    <td>Max Upload Speed</td>
                                    <td><input type="text" readonly="readonly" value="<?= $kiw_profile['attribute']['reply:WISPr-Bandwidth-Max-Up'] / pow(1024, 2) ?> Mbps" class="form-control"></td>
                                </tr>
                                <tr>
                                    <td>Max Download Speed</td>
                                    <td><input type="text" readonly="readonly" value="<?= $kiw_profile['attribute']['reply:WISPr-Bandwidth-Max-Down'] / pow(1024, 2) ?> Mbps" class="form-control"></td>
                                </tr>
                            </tbody>
                        </table>

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