<?php

$kiw_current_page = basename($_SERVER['SCRIPT_FILENAME']);

global $kiw_cpanel;


?>

<style>

@media only screen and (max-width: 600px) {

    .btn {
        color:black;
        background-color:skyblue;
    }

    .be-right-navbar{
        background-color:skyblue !important;
    }

    .user-info{
        background-color:skyblue !important;
    }


}

@media only screen and (min-width: 601px) {

    .user-name2{
        display:none;
    }

}


</style>

<div class="be-wrapper be-fixed-sidebar">

    <nav class="navbar navbar-default navbar-fixed-top be-top-header">
        <div class="container-fluid">
            <div class="navbar-header">
                <div class="logo" align="center">
                    <a href="/cpanel/dashboard.php">
                        <?php if (!empty($_SESSION['cpanel']['logo'])) { ?>
                            <img src="<?= $_SESSION['cpanel']['logo'] ?>" style="max-width: 80px;">
                        <?php } ?>
                    </a>
                </div>
            </div>
            <div class="be-right-navbar">
                <ul class="nav navbar-nav navbar-right be-user-nav">
                    <li class="dropdown">
                    <?php
                        // show logo if exist, else use avatar

                        $kiw_logo = sync_brand_decrypt(SYNC_CPANEL_LOGO);

                        $kiw_custom = dirname(__FILE__, 2) . "/assets/img/{$kiw_logo}";

                        if (file_exists($kiw_custom) == true) { ?>

                            <button class="btn dropdown-toggle" type="button" id="menu1" data-toggle="dropdown" style="border: none !important;">
                                <img src="assets/img/<?= $kiw_logo ?>">

                                <span class="user-name2 font-size-xsmall">
                                    <?= $_SESSION['cpanel']['username'] ?>
                                </span>
                            </button>
                                                       
                    <? }else{ ?>

                            <a href="#" data-toggle="dropdown" role="button" class="dropdown-toggle">
                                <img src="assets/img/avatar.png">

                                <span class="user-name font-size-xsmall">
                                    <?= $_SESSION['cpanel']['username'] ?>
                                </span>
                            </a>
                        

                    <? } ?>
         
                        <ul role="menu" class="dropdown-menu">
                            <li>
                                <div class="user-info">
                                    <div class="user-name text-uppercase font-size-xsmall">
                                        <?= $_SESSION['cpanel']['username'] ?>
                                    </div>
                                </div>
                            </li>
                            <?php

                            if ($_SESSION['cpanel']['landing_page'] == "mainpage") {
                            ?>

                                <li><a href="/cpanel/?logout=true&login_type=<?= $_SESSION['cpanel']['login_type'] ?>&dst=<?= $_SESSION['cpanel']['landing_page'] ?>&session=<?= $_SESSION['mainpage']['session'] ?>"><span class="icon mdi mdi-power"></span> Logout</a></li>

                            <? } else if ($_SESSION['cpanel']['landing_page'] == "status-mk") { ?>

                                <li><a href="/cpanel/?logout=true&login_type=<?= $_SESSION['cpanel']['login_type'] ?>&dst=<?= $_SESSION['cpanel']['landing_page'] ?>"><span class="icon mdi mdi-power"></span> Logout</a></li>

                            <? } else { ?>

                                <li><a href="/cpanel/?logout=true&login_type=<?= $_SESSION['cpanel']['login_type'] ?>"><span class="icon mdi mdi-power"></span> Logout</a></li>

                            <? } ?>

                        </ul>
                    </li>
                </ul>
                <div class="page-title">
                    <span>DASHBOARD</span>
                </div>
            </div>
        </div>
    </nav>

    <div class="be-left-sidebar">
        <div class="left-sidebar-wrapper">
            <a href="#" class="left-sidebar-toggle">
                DASHBOARD
            </a>
            <div class="left-sidebar-spacer">
                <div class="left-sidebar-scroll">
                    <div class="left-sidebar-content">
                        <ul class="sidebar-elements">
                            <li class="divider">Menu</li>
                            <?php if ($kiw_cpanel['dashboard'] == "y") { ?>
                                <li class="<?= ($kiw_current_page == "dashboard.php") ? "active" : "" ?>">
                                    <a href="/cpanel/dashboard.php">
                                        <i class="icon mdi mdi-home"></i><span>Dashboard</span>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if ($kiw_cpanel['information'] == "y") { ?>
                                <li class="<?= ($kiw_current_page == "information.php") ? "active" : "" ?>">
                                    <a href="/cpanel/information.php">
                                        <i class="icon mdi mdi-face"></i><span>Personal Details</span>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if ($kiw_cpanel['profile'] == "y") { ?>
                                <li class="<?= ($kiw_current_page == "profile.php") ? "active" : "" ?>">
                                    <a href="/cpanel/profile.php">
                                        <i class="icon mdi mdi-wifi-alt"></i><span>Profile Information</span>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if ($kiw_cpanel['register'] == "y") { ?>
                                <!-- <li class="<?= ($kiw_current_page == "register.php") ? "active" : "" ?>">
                                <a href="/cpanel/register.php">
                                    <i class="icon mdi mdi-assignment"></i><span>Register Device</span>
                                </a>
                            </li> -->
                            <?php } ?>
                            <?php if ($kiw_cpanel['recharge'] == "y") { ?>
                                <li class="<?= ($kiw_current_page == "recharge.php") ? "active" : "" ?>">
                                    <a href="/cpanel/recharge.php">
                                        <i class="icon mdi mdi-money"></i><span>Topup</span>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if ($kiw_cpanel['statistics'] == "y") { ?>
                                <!-- <li class="<?= ($kiw_current_page == "statistic.php") ? "active" : "" ?>">
                                <a href="/cpanel/statistic.php">
                                    <i class="icon mdi mdi-trending-up"></i><span>Statistics</span>
                                </a>
                            </li> -->
                            <?php } ?>
                            <?php if ($kiw_cpanel['history'] == "y") { ?>
                                <li class="<?= ($kiw_current_page == "history.php") ? "active" : "" ?>">
                                    <a href="/cpanel/history.php">
                                        <i class="icon mdi mdi-menu"></i><span>Usage History</span>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if ($kiw_cpanel['login'] == "y") { ?>
                                <li class="<?= ($kiw_current_page == "auto_login.php") ? "active" : "" ?>">
                                    <a href="/cpanel/auto_login.php">
                                        <i class="icon mdi mdi-account"></i><span>Report Auto Login</span>
                                    </a>
                                </li>
                            <?php } ?>

                            <?php

                            if ($_SESSION['cpanel']['profile_subs'] == "staff") {

                                if ($kiw_cpanel['voucher'] == "y") { ?>
                                    <li class="<?= ($kiw_current_page == "generate_voucher.php") ? "active" : "" ?>">
                                        <a href="/cpanel/generate_voucher.php">
                                            <i class="icon mdi mdi-download"></i><span>Generate Code</span>
                                        </a>
                                    </li>
                            <?php }
                            } ?>

                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>