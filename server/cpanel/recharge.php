<?php

$kiw_page = "Account Recharge";

require_once "includes/include_general.php";
require_once "includes/include_session.php";
require_once "includes/include_header.php";
require_once "includes/include_nav.php";


global $kiw_cloud, $kiw_db;


$kiw_clouds = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_clouds WHERE tenant_id = '{$kiw_tenant}' LIMIT 1");


if (in_array($_SESSION['cpanel']['login_type'], explode(",", $kiw_clouds["allow_topup_to"]))) {


    $kiw_topup_code = "";


    if (isset($_POST["topup_code"])) {


        if (empty($_POST["topup_code"])) {


            $_SESSION["error"]["topup"] = "Topup code is required";
        
        
        } else {

            $kiw_topup_code = $kiw_db->escape($_POST["topup_code"]);


            $kiw_topup = $kiw_db->query_first("SELECT * FROM kiwire_topup_code WHERE tenant_id = '{$kiw_tenant}' AND code = '{$kiw_topup_code}' AND username IS NULL AND status = 'n'  LIMIT 1");


            if (empty($kiw_topup)) {

                $_SESSION["error"]["topup"] = "Topup code is not valid. Please check your code or contact our administrator for more info.";
            
            
            } else {


                $kiw_account = $kiw_db->query_first("SELECT * FROM kiwire_account_auth WHERE tenant_id = '{$kiw_tenant}' AND username = '{$kiw_username}' LIMIT 1");

                $kiw_profile_cus = json_decode($kiw_account["profile_cus"], true);


                recharge_topup($kiw_db, $kiw_account, $kiw_topup, $kiw_username);


                if (recharge_topup($kiw_db, $kiw_account, $kiw_topup, $kiw_username) === true) {

                    $_SESSION["success"]["topup"] = "Your profile has been successfully extended.";
                
                
                } else $_SESSION["error"]["topup"] = "Your profile has expired. Please re-activate to topup.";


                $kiw_topup_code = "";
                unset($_POST);
                unset($_REQUEST);
            }
        }
    }
}


$kiw_user = $kiw_db->query_first("SELECT profile_subs,profile_curr,profile_cus,session_time,quota_out,quota_in,date_last_login,date_activate FROM kiwire_account_auth WHERE tenant_id = '{$kiw_tenant}' AND username = '{$kiw_username}' LIMIT 1");


$kiw_profile = $kiw_db->query_first("SELECT * FROM kiwire_profiles WHERE tenant_id = '{$kiw_tenant}' AND name = '{$kiw_user['profile_subs']}' LIMIT 1");

$kiw_profile['attribute'] = json_decode($kiw_profile['attribute'], true);


// $kiw_user['quota'] = number_format(($kiw_user['quota_out'] + $kiw_user ['quota_in']) / pow(1024, 3), 2);
$kiw_profile_cus = json_decode($kiw_user["profile_cus"], true);

$kiw_quota_extend   = $kiw_profile_cus["quota"] ?? 0;
$kiw_time_extend    = $kiw_profile_cus["time"] ?? 0;


$kiw_user['quota']  = (int)($kiw_user['quota_out'] + $kiw_user['quota_in']) / (pow(2, 20));
$kiw_quota_extend   = (int)($kiw_quota_extend) / (pow(2, 20));



$kiw_time['hour'] = floor($kiw_user['session_time'] / 3600);
$kiw_time['mins'] = floor($kiw_user['session_time'] / 60 % 60);
$kiw_time['secs'] = floor($kiw_user['session_time'] % 60);

$kiw_time = sprintf('%02d hours %02d minutes %02d seconds', $kiw_time['hour'], $kiw_time['mins'], $kiw_time['secs']);


if (isset($kiw_profile['attribute']['control:Kiwire-Total-Quota']) && $kiw_profile['attribute']['control:Kiwire-Total-Quota'] > 0) {

    $kiw_remaining['quota'] = number_format(((($kiw_profile['attribute']['control:Kiwire-Total-Quota'] + $kiw_quota_extend) - $kiw_user['quota'])), 2);

} else $kiw_remaining['quota'] = "NA";


if ($kiw_remaining['quota'] < 0) $kiw_remaining['quota'] = 0;


if (isset($kiw_profile['attribute']['control:Max-All-Session'])) {


    if ($kiw_profile['attribute']['control:Max-All-Session'] > 0) {

        $kiw_remaining['time'] = ($kiw_profile['attribute']['control:Max-All-Session'] + $kiw_time_extend) - $kiw_user['session_time'];
   
    } else $kiw_remaining['time'] = "NA";

} elseif (!isset($kiw_profile['attribute']['control:Max-All-Session'])) {


    if ($kiw_profile['attribute']['control:Access-Period'] > 0) {

        $kiw_remaining['time'] = ($kiw_profile['attribute']['control:Access-Period'] + $kiw_time_extend) - (time() - strtotime($kiw_user['date_activate']));
    
    } else $kiw_remaining['time'] = "NA";


} else $kiw_remaining['time'] = "NA";


if ($kiw_remaining['time'] < 0) {

    $kiw_remaining['time'] = '0 hours 0 minutes 0 seconds';

} else {


    $kiw_time_re['hour'] = floor($kiw_remaining['time'] / 3600);
    $kiw_time_re['mins'] = floor($kiw_remaining['time'] / 60 % 60);
    $kiw_time_re['secs'] = floor($kiw_remaining['time'] % 60);

    $kiw_remaining['time'] = sprintf('%02d hours %02d minutes %02d seconds', $kiw_time_re['hour'], $kiw_time_re['mins'], $kiw_time_re['secs']);

    unset($kiw_time_re);
}


$kiw_user['date_activate'] = sync_tolocaltime($kiw_user['date_activate'], $kiw_cloud['timezone']);

$kiw_user['date_last_login'] = sync_tolocaltime($kiw_user['date_last_login'], $kiw_cloud['timezone']);



?>

<div class="be-content">
    <div class="main-content container-fluid">

        <!-- <div class="row">
                <div class="col-md-10">
                    <div class="panel panel-default panel-border-color panel-border-color-primary">
                        <div class="panel-heading panel-heading-divider">Current Active Profile Usage</div>
                        <div class="panel-body">


                            <table class="table table-condensed table-hover table-bordered table-striped">
                                <tbody>
                                <tr>
                                    <td>Total Transfer</td>
                                    <td><input type="text" readonly="readonly" value="<?= $kiw_user['quota'] ?> Mb"
                                               class="form-control"></td>
                                </tr>
                                <tr>
                                    <td>Total Time</td>
                                    <td><input type="text" readonly="readonly" value="<?= $kiw_time ?>"
                                               class="form-control"></td>
                                </tr>
                                <tr>
                                    <td>First Login</td>
                                    <td><input type="text" readonly="readonly" value="<?= $kiw_user['date_activate'] ?>"
                                               class="form-control"></td>
                                </tr>
                                <tr>
                                    <td>Last Login</td>
                                    <td>
                                        <input type="text" readonly="readonly" value="<?= $kiw_user['date_last_login'] ?>" class="form-control">
                                    </td>
                                </tr>
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div> -->

        <!-- <div class="row">
                <div class="col-md-10">
                    <button class="btn btn-primary pull-right">Recharge / Reset</button>
                </div>
            </div> -->

        <br>

        <div class="row">
            <div class="col-md-10">
                <div class="panel panel-default panel-border-color panel-border-color-primary">
                    <div class="panel-heading panel-heading-divider">Balance</div>
                    <div class="panel-body">

                        <table class="table table-condensed table-hover table-bordered table-striped">
                            <tbody>
                                <tr>
                                    <td>Quota Remaining [ Mb ]</td>
                                    <td><input type="text" readonly="readonly" value="<?= $kiw_remaining['quota'] ?>" class="form-control">
                                    </td>
                                </tr>
                                <tr>
                                    <td>Time Remaining</td>
                                    <td>
                                        <input type="text" readonly="readonly" value="<?= $kiw_remaining['time'] ?>" class="form-control">
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>


        <?php
        if (!in_array($_SESSION['cpanel']['login_type'], explode(",", $kiw_clouds["allow_topup_to"]))) {
        ?>

            <div class="row">

                <div class="col-md-10">
                    <div class="alert alert-primary" role="alert">
                        Your account type is <b>not allow</b> to topup. Please contact our administrator for more info.
                    </div>
                </div>
            </div>

        <?php
        } else {



        ?>


            <div class="row">
                <div class="col-md-10">
                    <div class="panel panel-default panel-border-color panel-border-color-primary">
                        <div class="panel-heading panel-heading-divider">Topup </div>
                        <div class="panel-body">


                            <form action="<?php echo htmlspecialchars($_SERVER[" PHP_SELF "]); ?>" method="POST">

                                <table class="table table-condensed table-hover table-bordered table-striped">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <input type="text" value="<?= $kiw_topup_code ?>" name="topup_code" id="topupcode" class="form-control" autocomplete="off" placeholder="Insert your topup code here . e.g A8RPPFP3SG">
                                                <label class="text-danger"><?= $_SESSION["error"]["topup"] ?></label>
                                                <?php

                                                ?>
                                            </td>
                                            <td style="width: 10%;">
                                                <button class="btn btn-primary submit-topup">Submit</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </form>

                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-10">
                    <div class="panel panel-default panel-border-color panel-border-color-primary">
                        <div class="panel-heading panel-heading-divider">Topup History</div>
                        <div class="panel-body">
                            <table id="table1" class="table responsive no-wrap table-condensed table-hover table-bordered table-striped table-data dtr-inline">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Code</th>
                                        <th>Price</th>
                                        <th>Quota (MB)</th>
                                        <th>Time (D:H:M:S)</th>
                                        <th>Date Activate</th>
                                        <th>Date Expiry</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        <?php
        }
        ?>

    </div>
</div>


<?php

require_once "includes/include_footer.php";
require_once "../../server/admin/includes/include_datatable.php";

if (isset($_SESSION["success"]["topup"])) {

?>

    <script>
        swal("Success", "<?php echo $_SESSION["success"]["topup"] ?>", "success");
    </script>

<?php
}


if (isset($_SESSION["error"]["topup"])) {

?>

    <script>
        swal("Error", "<?php echo $_SESSION["error"]["topup"] ?>", "error");
    </script>

<?php
}


unset($_SESSION["error"]["topup"]);
unset($_SESSION["success"]["topup"]);
?>