<?php

require_once "include_connection.php";

?>
<div class="main-menu menu-fixed menu-light menu-accordion menu-shadow" data-scroll-to-active="true">
    <div class="navbar-header">
        <ul class="nav navbar-nav flex-row">
            <li class="nav-item mr-auto"><a class="navbar-brand" href="index.php">
                    <div class="brand-logo" style="background-image:url('../../assets/images/<?= sync_brand_decrypt(SYNC_LOGO_SMALL) ?>')"></div>
                    <h2 class="brand-text mb-0 text-uppercase"><?= sync_brand_decrypt(SYNC_PRODUCT) ?></h2>
                </a>
            </li>
            <li class="nav-item nav-toggle"><a class="nav-link modern-nav-toggle pr-0" data-toggle="collapse">
                    <i class="feather icon-x d-block d-xl-none font-medium-4 primary toggle-icon"></i>
                    <i class="toggle-icon feather icon-disc font-medium-4 d-none d-xl-block primary" data-ticon="icon-disc"></i>
                </a>
            </li>
        </ul>
    </div>
    <div class="shadow-bottom"></div>

    <div class="main-menu-content">
        <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">


            <li class="nav-item mb-1" <?= !$_SESSION['tenant_valid'] ? 'style="display:none"' : '' ?> data-module="General -> Dashboard"><a href="dashboard.php"><i class="fa fa-tachometer"></i><span class="menu-title" data-i18n="Dashboard">Dashboard</span></a></li>

            <?php if ($_SESSION['multi_tenant'] == true && $_SESSION['access_level'] == "superuser") { ?>

                <li class="nav-item mb-1"><a href="#"><i class="fa fa-cloud"></i><span class="menu-title" data-i18n="Cloud">Cloud</span></a>
                    <ul class="menu-content">

                        <li data-module="Cloud -> Overview" <?= !$_SESSION['tenant_valid'] ? 'style="display:none"' : '' ?>><a href="cloud_overview.php"><span class="menu-item" data-i18n="Overview">Overview</span></a></li>
                        <li data-module="Cloud -> Manage Client"><a href="cloud_tenant_list.php"><span class="menu-item" data-i18n="Tenant">Tenant</span></a></li>
                        <li data-module="Cloud -> Manage Superuser" <?= !$_SESSION['tenant_valid'] ? 'style="display:none"' : '' ?>><a href="cloud_superuser_list.php"><span class="menu-item" data-i18n="cloud_Administrator">Administrator</span></a></li>
                        <li data-module="Cloud -> Access Level" <?= !$_SESSION['tenant_valid'] ? 'style="display:none"' : '' ?>><a href="cloud_superuser_role.php"><span class="menu-item" data-i18n="cloud_Role">Role</span></a></li>
                        <li data-module="Cloud -> API" <?= !$_SESSION['tenant_valid'] ? 'style="display:none"' : '' ?>><a href="cloud_api.php"><span class="menu-item" data-i18n="cloud_Api">API</span></a></li>
                        <li data-module="Cloud -> Custom Style" <?= !$_SESSION['tenant_valid'] ? 'style="display:none"' : '' ?>><a href="custom_style.php"><span class="menu-item" data-i18n="custom_style">Custom Style</span></a></li>
                        <li data-module="Cloud -> Impersonate User" <?= !$_SESSION['tenant_valid'] ? 'style="display:none"' : '' ?>><a href="cloud_impersonate_user.php"><span class="menu-item" data-i18n="impersonate_user">Impersonate User</span></a></li>

                    </ul>
                </li>

            <?php } ?>

            <?php if (in_array("Configuration", $_SESSION['access_group'])) { ?>

                <li class="nav-item mb-1" <?= !$_SESSION['tenant_valid'] ? 'style="display:none"' : '' ?>><a href="#"><i class="fa fa-cogs"></i><span class="menu-title" data-i18n="Configuration">Configuration</span></a>
                    <ul class="menu-content">

                        <li data-module="Configuration -> Site Branding"><a href="configuration_organisation_profile.php"><span class="menu-item" data-i18n="Organisation Profile"> Organisation Profile</span></a></li>
                        <li data-module="Configuration -> General"><a href="configuration_general.php"><span class="menu-item" data-i18n="Settings">Settings</span></a></li>

                        <?php if ($_SESSION['access_level'] == "superuser" || $_SESSION['multi_tenant'] == false) {  ?>

                            <li data-module="Configuration -> Global"><a href="configuration_system_setting.php"><span class="menu-item" data-i18n="System Settings">System Settings</span></a></li>
                            <li data-module="Configuration -> White Label"><a href="configuration_white_label.php"><span class="menu-item" data-i18n="White Label">White Label</span></a></li>
                            <!-- <li data-module="Configuration -> Network Setting"><a href="configuration_network_setting.php"><span class="menu-item" data-i18n="Network Settings">Network Settings</span></a></li> -->
                            <li data-module="Configuration -> High Availability"><a href="configuration_high_availability.php"><span class="menu-item" data-i18n="High Availability">High Availability</span></a></li>

                        <?php } ?>

                        <li data-module="Configuration -> Administrator"><a href="configuration_administrator.php"><span class="menu-item" data-i18n="Administrator">Administrator</span></a></li>
                        <li data-module="Configuration -> Access Level"><a href="configuration_role.php"><span class="menu-item" data-i18n="Role">Role</span></a></li>
                        <li data-module="Configuration -> License"><a href="configuration_license.php"><span class="menu-item" data-i18n="License">License</span></a></li>

                    </ul>
                </li>

            <?php } ?>

            <?php if (in_array("Integration", $_SESSION['access_group'])) { ?>

                <li class="nav-item mb-1" <?= !$_SESSION['tenant_valid'] ? 'style="display:none"' : '' ?>><a href="#"><i class="fa fa-random"></i><span class="menu-title" data-i18n="Integration">Integration</span></a>
                    <ul class="menu-content">

                        <li data-module="Integration -> Realm"><a href="integration_radius.php"><span class="menu-item" data-i18n="Radius">Radius</span></a></li>
                        <li data-module="Integration -> Active Directory"><a href="integration_active_directory.php"><span class="menu-item" data-i18n="Active Directory">Active Directory</span></a></li>
                        <li data-module="Integration -> LDAP"><a href="integration_ldap.php"><span class="menu-item" data-i18n="LDAP">LDAP</span></a></li>
                        <li data-module="Integration -> Social"><a href="integration_social.php"><span class="menu-item" data-i18n="Social Networks">Social Networks</span></a></li>
                        <li data-module="Integration -> Microsoft Account"><a href="integration_microsoft_account.php"><span class="menu-item" data-i18n="Microsoft Account">Microsoft Account</span></a></li>
                        <li data-module="Integration -> SMTP"><a href="integration_smtp.php"><span class="menu-item" data-i18n="Email">Email</span></a></li>
                        <li data-module="Integration -> SMS"><a href="integration_sms.php"><span class="menu-item" data-i18n="SMS">SMS</span></a></li>
                        <li data-module="Integration -> Mail"><a href="integration_marketing_email.php"><span class="menu-item" data-i18n="Marketing Email">Marketing Email</span></a></li>
                        <li data-module="Integration -> Database"><a href="integration_database.php"><span class="menu-item" data-i18n="Databases">Databases</span></a></li>
                        <!-- <li data-module="Integration -> Radius SSO"><a href="integration_radiussso.php"><span class="menu-item" data-i18n="Radius SSO">Radius SSO</span></a></li> -->
                        <li data-module="Integration -> PMS"><a href="integration_pms.php"><span class="menu-item" data-i18n="PMS">PMS</span></a></li>
                        <!--                    <li data-module="Integration -> LBS"><a href="integration_lbs.php"><span class="menu-item" data-i18n="LBS">LBS</span></a></li>-->
                        <li data-module="Integration -> API"><a href="integration_api.php"><span class="menu-item" data-i18n="API">API</span></a></li>
                        <li data-module="Integration -> Web hook"><a href="integration_webhook.php"><span class="menu-item" data-i18n="webhook">Web Hook</span></a></li>
                        <li data-module="Integration -> E-Payment"><a href="integration_payment_gateway.php"><span class="menu-item" data-i18n="Payment Gateway">Payment Gateway</span></a></li>
                        <!-- <li data-module="Integration -> Zapier"><a href="integration_zapier.php"><span class="menu-item" data-i18n="LBS">Zapier</span></a></li> -->
                        <!-- <li data-module="Integration -> Facebook Reviews"><a href="integration_facebook.php"><span class="menu-item" data-i18n="API">Facebook Review</span></a></li> -->
                        <li data-module="Integration -> HSS"><a href="integration_hss.php"><span class="menu-item" data-i18n="HSS">HSS</span></a></li>

                    </ul>
                </li>

            <?php } ?>

            <?php if (in_array("Device", $_SESSION['access_group'])) { ?>

                <li class="nav-item mb-1" <?= !$_SESSION['tenant_valid'] ? 'style="display:none"' : '' ?>><a href="#"><i class="fa fa-rss"></i><span class="menu-title" data-i18n="Devices">Devices</span></a>
                    <ul class="menu-content">

                        <!-- <li data-module="Device -> Monitoring -> Dashboard"><a href="devices_dashboard.php"><span class="menu-item" data-i18n="Devices Dashboard">Dashboard</span></a></li> -->
                        <li data-module="Device -> Device"><a href="devices_devices.php"><span class="menu-item" data-i18n="Devices Devices">Devices</span></a></li>
                        <!--                    <li data-module="Device -> Monitoring -> Maps"><a href="devices_monitor_maps.php"><span class="menu-item" data-i18n="Maps">Maps</span></a></li>-->
                        <li data-module="Device -> Monitoring -> MIB"><a href="devices_monitor_mib.php"><span class="menu-item" data-i18n="MIB">Management Info Base (MIB)</span></a></li>
                        <li data-module="Device -> Monitoring -> Rules"><a href="devices_monitor_rules.php"><span class="menu-item" data-i18n="Notification Rules">Notification Rules</span></a></li>
                        <li data-module="Device -> Monitoring -> Status"><a href="devices_monitor_status.php"><span class="menu-item" data-i18n="Status">Status</span></a></li>
                        <li data-module="Device -> Monitoring -> Check Logs"><a href="devices_monitor_logs.php"><span class="menu-item" data-i18n="Logs">Monitoring Logs</span></a></li>
                        <li data-module="Device -> Zone"><a href="devices_zones_mapping.php"><span class="menu-item" data-i18n="Zones Mapping">Zones Mapping</span></a></li>
                        <li data-module="Device -> Project"><a href="devices_project_mapping.php"><span class="menu-item" data-i18n="Project Mapping">Project Mapping</span></a></li>

                    </ul>
                </li>

            <?php } ?>

            <?php if (in_array("Login Engine", $_SESSION['access_group'])) { ?>

                <li class="nav-item mb-1" <?= !$_SESSION['tenant_valid'] ? 'style="display:none"' : '' ?>><a href="#"><i class="fa fa-desktop"></i><span class="menu-title" data-i18n="Login Engine">Login Engine</span></a>
                    <ul class="menu-content">

                        <li data-module="Login Engine -> Journey"><a href="login_engine_journey.php"><span class="menu-item" data-i18n="Journey">Journey</span></a></li>
                        <li data-module="Login Engine -> Sign up -> One Click"><a href="login_engine_one_click_login.php"><span class="menu-item" data-i18n="Auto Login">One Click Login</span></a></li>
                        <li data-module="Login Engine -> Sign up -> Public"><a href="login_engine_public_signup.php"><span class="menu-item" data-i18n="Public Sign Up">Public Sign Up</span></a></li>
                        <li data-module="Login Engine -> Sign up -> Sponsor"><a href="login_engine_sponsor_signup.php"><span class="menu-item" data-i18n="Sponsor Sign Up">Sponsor Sign Up</span></a></li>
                        <li data-module="Login Engine -> Notification"><a href="login_engine_notification.php"><span class="menu-item" data-i18n="Notification">Notification</span></a></li>
                        <li data-module="Login Engine -> Media"><a href="login_engine_media.php"><span class="menu-item" data-i18n="Media">Media</span></a></li>
                        <li data-module="Login Engine -> Desiger Tool -> List"><a href="login_engine_page_designer.php"><span class="menu-item" data-i18n="Page Designer">Page Designer</span></a></li>
                        <li data-module="Login Engine -> QR Login"><a href="login_engine_qrlogin.php"><span class="menu-item" data-i18n="QR Login">QR Login</span></a></li>
                        <li data-module="Login Engine -> Template Engine"><a href="login_engine_template.php"><span class="menu-item" data-i18n="Template">Template</span></a></li>
                        <li data-module="Login Engine -> Auto Login Checks"><a href="login_engine_autologin_check.php"><span class="menu-item" data-i18n="Auto Login Checks">Auto Login Checks</span></a></li>
                        <li data-module="Login Engine -> Login Checks"><a href="login_engine_error_check.php"><span class="menu-item" data-i18n="Login Checks">Login Checks</span></a></li>
                        <li data-module="Login Engine -> Additional Field"><a href="login_engine_additional_field.php"><span class="menu-item" data-i18n="Additional Field">Additional Field</span></a></li>

                    </ul>
                </li>

            <?php } ?>

            <?php if (in_array("Policy", $_SESSION['access_group'])) { ?>

                <li class="nav-item mb-1" <?= !$_SESSION['tenant_valid'] ? 'style="display:none"' : '' ?>><a href="#"><i class="fa fa-university"></i><span class="menu-title" data-i18n="Policy">Policy</span></a>
                    <ul class="menu-content">

                        <li data-module="Policy -> General"><a href="policy_config.php"><span class="menu-item" data-i18n="policy_Configuration">Configuration</span></a></li>
                        <li data-module="Policy -> Password"><a href="policy_password.php"><span class="menu-item" data-i18n="Password">Password</span></a></li>
                        <li data-module="Policy -> Firewall"><a href="policy_firewall.php"><span class="menu-item" data-i18n="Firewall">Firewall</span></a></li>
                        <li data-module="Policy -> Zone Restriction"><a href="policy_zone_restriction.php"><span class="menu-item" data-i18n="Zone Restriction">Zone Restriction</span></a></li>
                        <li data-module="Policy -> Dynamic Bandwidth"><a href="policy_dynamic_bandwidth.php"><span class="menu-item" data-i18n="Dynamic Bandwidth">Dynamic Bandwidth</span></a></li>
                        <li data-module="Policy -> Account Policy"><a href="policy_account.php"><span class="menu-item" data-i18n="Account Policy">Account Policy</span></a></li>
                        <li data-module="Policy -> Device Policy"><a href="policy_device.php"><span class="menu-item" data-i18n="Device Policy">Device Policy</span></a></li>
                        <li data-module="CPanel -> Setting"><a href="policy_user_panel.php"><span class="menu-item" data-i18n="User Panel">User Panel</span></a></li>
                        <li data-module="BPanel -> Setting"><a href="policy_bpanel.php"><span class="menu-item" data-i18n="Buy Panel">Buy Profile Panel</span></a></li>
                        <li data-module="CPanel -> Verify Device"><a href="policy_verify_device.php"><span class="menu-item" data-i18n="Approve User Device">Approve User Device</span></a></li>

                    </ul>
                </li>


            <?php } ?>

            <?php if (in_array("Account", $_SESSION['access_group'])) { ?>

                <li class="nav-item mb-1" <?= !$_SESSION['tenant_valid'] ? 'style="display:none"' : '' ?>><a href="#"><i class="fa fa-users"></i><span class="menu-title" data-i18n="Accounts">Accounts</span></a>
                    <ul class="menu-content">

                        <li data-module="Account -> Profile"><a href="account_profile.php"><span class="menu-item" data-i18n="Profile">Profile</span></a></li>
                        <li data-module="Account -> Persona"><a href="account_persona.php"><span class="menu-item" data-i18n="Persona">Persona</span></a></li>
                        <li data-module="Account -> Voucher -> List"><a href="account_voucher.php"><span class="menu-item" data-i18n="Voucher">Voucher</span></a></li>
                        <li data-module="Account -> Account -> List"><a href="account_users.php"><span class="menu-item" data-i18n="Users">Users</span></a></li>
                        <li data-module="Account -> Bulk User Modification"><a href="account_bulk_user_modification.php"><span class="menu-item" data-i18n="Bulk User Modification">Bulk User Modification</span></a></li>
                        <li data-module="Account -> Auto Reset"><a href="account_reset_profile.php"><span class="menu-item" data-i18n="Reset Profile">Reset Profile</span></a></li>
                        <li data-module="Account -> Topup Code"><a href="account_topup.php"><span class="menu-item" data-i18n="Topup Code">Topup Code</span></a></li>
                        <li data-module="Account -> HSS"><a href="account_hss.php"><span class="menu-item" data-i18n="HSS">HSS</span></a></li>
                        <li data-module="Account -> Tejas"><a href="account_tejas.php"><span class="menu-item" data-i18n="Tejas">Tejas</span></a></li>

                    </ul>
                </li>

            <?php } ?>

            <?php if (in_array("Campaign", $_SESSION['access_group'])) { ?>

                <li class="nav-item mb-1" <?= !$_SESSION['tenant_valid'] ? 'style="display:none"' : '' ?>><a href="#"><i class="fa fa-bullhorn"></i><span class="menu-title" data-i18n="Campaigns">Campaigns</span></a>
                    <ul class="menu-content">

                        <li data-module="Campaign -> Campaign Management"><a href="campaign_management.php"><span class="menu-item" data-i18n="Campaigns Management">Campaigns Management</span></a></li>
                        <li data-module="Campaign -> Ads Management"><a href="campaign_ads_management.php"><span class="menu-item" data-i18n="Ads Management">Ads Management</span></a></li>
                        <li data-module="Campaign -> Survey Management"><a href="campaign_surveys_management.php"><span class="menu-item" data-i18n="Surveys Management">Surveys Management</span></a></li>
                        <!--                    <li data-module="Campaign -> Coupon Creation"><a href="campaign_coupons_management.php"><span class="menu-item" data-i18n="Coupons Management">Coupons Management</span></a></li>-->
                        <li data-module="Campaign -> Company Apps"><a href="campaign_smart_banner.php"><span class="menu-item" data-i18n="Smart Banner">Smart Banner</span></a></li>

                    </ul>
                </li>

            <?php } ?>

            <?php if (in_array("Finance", $_SESSION['access_group'])) { ?>


                <li class="nav-item mb-1" <?= !$_SESSION['tenant_valid'] ? 'style="display:none"' : '' ?>><a href="#"><i class="fa fa-usd"></i><span class="menu-title" data-i18n="Finance">Finance</span></a>
                    <ul class="menu-content">

                        <li data-module="Finance -> Report"><a href="finance_invoice.php"><span class="menu-item" data-i18n="Invoice">Invoice</span></a></li>
                        <li data-module="Finance -> E-Payment Transaction"><a href="finance_payment_report.php"><span class="menu-item" data-i18n="Payment Report">Payment Report</span></a></li>
                        <li data-module="Finance -> PMS Payment Queue"><a href="finance_pms_payment_queue.php"><span class="menu-item" data-i18n="PMS Payment Queue">PMS Payment Queue</span></a></li>
                        <li data-module="Finance -> Manual Posting"><a href="finance_pms_manual_posting.php"><span class="menu-item" data-i18n="PMS Manual Posting">PMS Manual Posting</span></a></li>
                        <li data-module="Finance -> Prepaid Creation"><a href="finance_voucher_summary.php"><span class="menu-item" data-i18n="Voucher Summary">Voucher Summary</span></a></li>
                        <li data-module="Finance -> Print Prepaid Slip"><a href="finance_voucher_slip.php"><span class="menu-item" data-i18n="Voucher Slip">Voucher Slip</span></a></li>

                    </ul>
                </li>

            <?php } ?>

            <?php if (in_array("Report", $_SESSION['access_group'])) { ?>

                <li class="nav-item mb-1" <?= !$_SESSION['tenant_valid'] ? 'style="display:none"' : '' ?>><a href="#"><i class="fa fa-line-chart"></i><span class="menu-title" data-i18n="Reports">Reports</span></a>

                    <ul class="menu-content">
                        <li data-module="Report -> Generated Reports"><a href="report_generated.php"><span class="menu-item" data-i18n="">Generated Reports</span></a></li>
                    </ul>

                    <ul class="menu-content">

                        <li><a href="#"><span class="menu-title" data-i18n="report Accounts">Accounts</span></a>
                            <ul class="menu-content">
                                <li data-module="Report -> Accounts -> Account Summary"><a href="report_account_summary.php"><span class="menu-item" data-i18n="Account Summary">Summary</span></a></li>
                                <li data-module="Report -> Accounts -> Account Expiry"><a href="report_account_expiry.php"><span class="menu-item" data-i18n="Account Expiry">Expiry</span></a></li>
                                <li data-module="Report -> Accounts -> Account Creator"><a href="report_account_creator.php"><span class="menu-item" data-i18n="Account Creator">Creator</span></a></li>
                                <!-- <li data-module="Report -> Accounts -> Account Inactive"><a href="report_account_inactive_summary.php"><span class="menu-item" data-i18n="Inactive Summary">Inactive Summary</span></a></li> -->
                                <li data-module="Report -> Accounts -> Voucher Activation"><a href="report_account_voucher_activation.php"><span class="menu-item" data-i18n="Voucher Activation">Voucher Activation</span></a></li>
                                <li data-module="Report -> Accounts -> Voucher Availibility"><a href="report_account_voucher_availability.php"><span class="menu-item" data-i18n="Voucher Availability">Voucher Availability</span></a></li>
                                <li data-module="Report -> Accounts -> Topup Availibility"><a href="report_account_topup_availability.php"><span class="menu-item" data-i18n="Topup Availability">Topup Availability</span></a></li>
                                <li data-module="Report -> Accounts -> Account Creation"><a href="report_account_creation_analytics.php"><span class="menu-item" data-i18n="Account Creation Summary">Creation Summary</span></a></li>
                            </ul>
                        </li>

                    </ul>

                    <ul class="menu-content">
                        <li><a href="#"><span class="menu-title" data-i18n="Login">Login</span></a>
                            <ul class="menu-content">

                                <li data-module="Report -> Login Authentication"><a href="report_login_authentication.php"><span class="menu-item" data-i18n="">Login Authentication</span></a></li>
                                <li data-module="Report -> Who is Online"><a href="report_login_active_session.php"><span class="menu-item" data-i18n="Active Session">Active Session</span></a></li>
                                <li data-module="Report -> Auto Login"><a href="report_login_auto_login.php"><span class="menu-item" data-i18n="Auto login">Auto Login</span></a></li>
                                <li data-module="Report -> Login History"><a href="report_login_logins_record.php"><span class="menu-item" data-i18n="Login Record">Login Record</span></a></li>
                                <li data-module="Report -> Login Summary"><a href="report_login_logins_summary.php"><span class="menu-item" data-i18n="Login Summary">Login Summary</span></a></li>
                                <li data-module="Report -> Login Frequency Summary"><a href="report_login_logins_freq.php"><span class="menu-item" data-i18n="Login Frequency">Login Frequency</span></a></li>
                                <li data-module="Report -> Login Frequency -> Profile"><a href="report_login_logins_freq_profile.php"><span class="menu-item" data-i18n="Login Frequency by Profile">Login Frequency by Profile</span></a></li>
                                <li data-module="Report -> Login Frequency -> Device"><a href="report_login_logins_device_freq.php"><span class="menu-item" data-i18n="Login Device Frequency">Login Device Frequency</span></a></li>
                                <li data-module="Report -> Login Error"><a href="report_login_logins_error.php"><span class="menu-item" data-i18n="Login Error">Login Error</span></a></li>
                                <li data-module="Report -> Login Concurrent"><a href="report_login_logins_concurrency.php"><span class="menu-item" data-i18n="Session Concurrency">Session Concurrency</span></a></li>
                                <li data-module="Report -> User Dwell Time"><a href="report_login_dwell_time_sum.php"><span class="menu-item" data-i18n="Dwell Time">Dwell Time Summary</span></a></li>
                                <li data-module="Report -> User Dwell Time by Profile"><a href="report_login_dwell_time_by_profile.php"><span class="menu-item" data-i18n="Dwell Time by Profile">Dwell Time by Profile</span></a></li>
                                <li data-module="Report -> Top Account"><a href="report_login_top_accounts.php"><span class="menu-item" data-i18n="Top Account">Top Account by Login</span></a></li>
                                <li data-module="Report -> Return Account"><a href="report_login_return_accounts_summary.php"><span class="menu-item" data-i18n="Return Account Summary">Return Account Summary</span></a></li>

                            </ul>
                        </li>
                    </ul>

                    <ul class="menu-content">
                        <li><a href="#"><span class="menu-title" data-i18n="Bandwidth">Bandwidth</span></a>
                            <ul class="menu-content">
                                <li data-module="Report -> Bandwidth Usage Summary"><a href="report_bandwidth_overall_usage_summary.php"><span class="menu-item" data-i18n="Bandwidth Usage Summary">Summary</span></a></li>
                                <li data-module="Report -> Bandwidth Usage User"><a href="report_bandwidth_account_usage_summary.php"><span class="menu-item" data-i18n="Bandwidth Usage Per Account">Usage Per Account</span></a></li>
                                <li data-module="Report -> Top Historic Bandwidth User"><a href="report_bandwidth_top_usage_summary.php"><span class="menu-item" data-i18n="Top Usage Summary">History Top Account</span></a></li>
                                <li data-module="Report -> Top Current Bandwidth User"><a href="report_bandwidth_current_top_account.php"><span class="menu-item" data-i18n="Current Top Account">Current Top Account</span></a></li>
                                <li data-module="Report -> Bandwidth vs Users"><a href="report_bandwidth_vs_no_of_users.php"><span class="menu-item" data-i18n="Bandwidth vs Login">Bandwidth vs Login</span></a></li>
                            </ul>
                        </li>
                    </ul>


                    <ul class="menu-content">
                        <li><a href="#"><span class="menu-title" data-i18n="Controller">Controller</span></a>
                            <ul class="menu-content">
                                <li data-module="Report -> Monitoring -> Controller Bandwidth"><a href="report_monitoring_controller_bandwidth.php"><span class="menu-item" data-i18n="Controller Bandwidth">Controller Bandwidth</span></a></li>
                                <li data-module="Report -> Monitoring -> Controller Report"><a href="report_monitoring_controller_report.php"><span class="menu-item" data-i18n="Controller Report">Controller Report</span></a></li>
                            </ul>
                        </li>
                    </ul>


                    <ul class="menu-content">
                        <li><a href="#"><span class="menu-title" data-i18n="Impression">Impression</span></a>
                            <ul class="menu-content">
                                <li data-module="Report -> Impression -> Summary"><a href="report_impression_summary.php"><span class="menu-item" data-i18n="Impression Summary">Summary</span></a></li>
                            </ul>
                        </li>
                    </ul>


                    <ul class="menu-content">
                        <li><a href="#"><span class="menu-title" data-i18n="Campaign">Campaign</span></a>
                            <ul class="menu-content">
                                <li data-module="Report -> Campaign -> Impression Report"><a href="report_campaign_camp_impression_summary.php"><span class="menu-item" data-i18n="Campaign Impression Summary">Impression Summary</span></a></li>
                                <li data-module="Report -> Campaign -> Click Thru Report"><a href="report_campaign_click_summary.php"><span class="menu-item" data-i18n="Campaign Click Summary">Click Engagement</span></a></li>
                                <li data-module="Report -> Campaign -> Offline Report"><a href="report_campaign_offline_campaign_summary.php"><span class="menu-item" data-i18n="Offline Campaign Summary">Offline Summary</span></a></li>
                                <!-- <li data-module="Report -> Coupon -> View Report"><a href="report_campaign_coupon_click_summary.php"><span class="menu-item" data-i18n="Coupon Click Summary">Coupon View</span></a></li>
                            <li data-module="Report -> Coupon -> Impression Report"><a href="report_campaign_coupon_impression_summary.php"><span class="menu-item" data-i18n="Coupon Impression Report">Coupon Impression</span></a></li> -->
                                <li data-module="Report -> Survey -> Response Data"><a href="report_campaign_survey_response_data.php"><span class="menu-item" data-i18n="Survey Response Data">Survey Response</span></a></li>
                            </ul>
                        </li>
                    </ul>


                    <ul class="menu-content">
                        <li><a href="#"><span class="menu-title" data-i18n="Delivery Log">Delivery Log</span></a>
                            <ul class="menu-content">
                                <li data-module="Report -> Delivery -> SMS"><a href="report_campaign_sms_send_report.php"><span class="menu-item" data-i18n="SMS Send Report">SMS Send Report</span></a></li>
                                <li data-module="Report -> Delivery -> Email"><a href="report_campaign_email_send_report.php"><span class="menu-item" data-i18n="Email Send Report">Email Send Report</span></a></li>
                                <li data-module="Report -> Delivery -> Email"><a href="report_pms_transaction.php"><span class="menu-item" data-i18n="pms_transaction_log">PMS Transactions</span></a></li>
                            </ul>
                        </li>
                    </ul>


                    <ul class="menu-content">
                        <li><a href="#"><span class="menu-title" data-i18n="Insight">Insight</span></a>
                            <ul class="menu-content">
                                <li data-module="Report -> Insight -> User Device Info"><a href="report_insight_deviceinfo.php"><span class="menu-item" data-i18n="
                            ">User Device Info</span></a></li>
                                <li data-module="Report -> Insight -> Sign-Up Data"><a href="report_insight_registration_data.php"><span class="menu-item" data-i18n="Registration Data">Registration Data</span></a></li>
                                <li data-module="Report -> Insight -> Social Network Analytics"><a href="report_insight_social_network_summary.php"><span class="menu-item" data-i18n="Social Networks Analytics">Social Networks Analytics</span></a></li>
                                <li data-module="Report -> Insight -> Social Network Data"><a href="report_insight_social_network_data.php"><span class="menu-item" data-i18n="Social Network Data">Social Network Data</span></a></li>
                                <!-- <li data-module="Report -> Insight -> Net Promoter Summary"><a href="report_insight_netpromoter_summary.php"><span class="menu-item" data-i18n="System Log">Net Promoter Score</span></a></li>
                            <li data-module="Report -> Insight -> Social Network Reputation"><a href="report_insight_reputation_review.php"><span class="menu-item" data-i18n="System Log">Online Reputation Review</span></a></li> -->

                            </ul>
                        </li>
                    </ul>

                   
                    <ul class="menu-content">
                        <li data-module="Report -> Login Scanner"><a href="report_login_scanner.php"><span class="menu-item" data-i18n="">Security</span></a></li>
                    </ul>
                </li>

            <?php } ?>

            <?php if (in_array("Help", $_SESSION['access_group'])) { ?>

                <li class="nav-item mb-1" <?= !$_SESSION['tenant_valid'] ? 'style="display:none"' : '' ?>><a href="#"><i class="fa fa-exclamation-circle"></i><span class="menu-title" data-i18n="Help & Tools">Help & Tools</span></a>
                    <ul class="menu-content">
                    
                        <li><a href="#"><span class="menu-title" data-i18n="">Monitor</span></a>
                            <ul class="menu-content">
                                <li data-module="Report -> Monitor -> Service"><a href="monitor_services.php"><span class="menu-item" data-i18n="">Service</span></a></li>
                                <li data-module="Report -> Monitor -> Scheduler"><a href="monitor_scheduler.php"><span class="menu-item" data-i18n="">Scheduler</span></a></li>
                            </ul>
                        </li>

                        <li data-module="Help -> Online Knowledgebase"><a href="help_documentation.php"><span class="menu-item" data-i18n="Online Knowledge Base">Online Knowledge Base</span></a></li>
                        <li data-module="Help -> User Account Diagnostic"><a href="help_diagnostic_account.php"><span class="menu-item" data-i18n="Diagnostic Account">Diagnostic Account</span></a></li>
                        <li data-module="Help -> Software Update"><a href="help_software_update.php"><span class="menu-item" data-i18n="Software Update">Software Update</span></a></li>

                        <li class="nav-item"><a href="#"><span class="menu-title" data-i18n="Database">Database</span></a>
                            <ul class="menu-content">
                                <li data-module="Help -> Database Diagnostic"><a href="help_database_diagnostic.php"><span class="menu-item" data-i18n="Database Diagnostic">Database Diagnostic</span></a></li>
                                <li data-module="Help -> Database Disk Usage"><a href="help_database_disk_usage.php"><span class="menu-item" data-i18n="Database Disk Usage">Database Disk Usage</span></a></li>
                                <li data-module="Help -> Database Performance"><a href="help_database_performance.php"><span class="menu-item" data-i18n="Database Performance">Database Performance</span></a></li>
                                <li data-module="Help -> Database Maintenance"><a href="help_database_maintenance.php"><span class="menu-item" data-i18n="Database Maintenance">Database Maintenance</span></a></li>
                            </ul>
                        </li>

                        <li data-module="Help -> System Logs"><a href="help_log_maintenance.php"><span class="menu-item" data-i18n="System Logs">System Logs</span></a></li>
                        <li data-module="Help -> Services"><a href="help_services_summary.php"><span class="menu-item" data-i18n="System Service Summary">System Service Summary</span></a></li>
                        <li data-module="Help -> License Usage"><a href="help_license_usage.php"><span class="menu-item" data-i18n="License Usage">License Usage Summary</span></a></li>
                        <li data-module="Help -> Ping Tool"><a href="help_ping_tool.php"><span class="menu-item" data-i18n="Ping Tool">Ping Tool</span></a></li>
                        <li data-module="Help -> Find Mac Address"><a href="help_find_mac_address.php"><span class="menu-item" data-i18n="Find MAC Address">Find MAC Address</span></a></li>
                        <li data-module="Help -> System Quick Fix"><a href="help_system_quick_fix.php"><span class="menu-item" data-i18n="System Quick Fix">System Quick Fix</span></a></li>
                        <li data-module="Help -> Sources & Credits"><a href="help_sources_and_credits.php"><span class="menu-item" data-i18n="Sources And Credits">Sources And Credits</span></a></li>
                        <li data-module="Help -> Version"><a href="help_version.php"><span class="menu-item" data-i18n="Version">Version</span></a></li>
                        <li data-module="Tools -> Bypass Device"><a href="tool_bypass_device.php"><span class="menu-item" data-i18n="Bypass DEVICE">Bypass User Device</span></a></li>

                    </ul>
                </li>

            <?php } ?>


        </ul>
    </div>

</div>

<div class="app-content content">

    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <nav class="header-navbar navbar-expand-lg navbar navbar-with-menu fixed-top navbar-light navbar-shadow">
        <div class="navbar-wrapper">
            <div class="navbar-container content">
                <div class="navbar-collapse" id="navbar-mobile">

                    <div class="mr-auto float-left bookmark-wrapper d-flex align-items-center">
                        <ul class="nav navbar-nav">
                            <li class="nav-item mobile-menu d-xl-none mr-auto"><a class="nav-link nav-menu-main menu-toggle hidden-xs" href="#"><i class="ficon feather icon-menu"></i></a></li>
                        </ul>

                        <ul class="nav navbar-nav">
                            <li class="nav-item d-none d-lg-block">
                                <span data-i18n="customer_name">Customer</span>: <?= $_SESSION['company_name'] ?>

                                <?php

                                if (isset($_SESSION['back_superuser']) && $_SESSION['access_level'] != "superuser") { ?>

                                    <button class="btn btn-success btn-sm btn-login-impersonate" data-tenant="superuser" data-username="<?= $_SESSION['user_superuser'] ?>" data-user="<?= $_SESSION['pass_superuser'] ?>">Back to Superuser</button>
                                <?php
                                }

                                ?>


                            </li>
                        </ul>
                    </div>

                    <ul class="nav navbar-nav float-right">

                        <li class="nav-item d-lg-block"><a class="nav-link change-icon" id="change-theme-icon">
                            <?php
                                    if ($_SESSION['theme'] == "default") { ?>

                                        <i class="ficon fa fa-sun-o" id="theme-icon"></i></a></li>

                            <?php   } else { ?>

                                        <i class="ficon fa fa-moon-o" id="theme-icon"></i></a></li>

                            <?php   }  ?>

                        
                        <li class="dropdown dropdown-language nav-item">
                            <a class="dropdown-toggle nav-link" id="dropdown-flag" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="flag-icon flag-icon-us"></i>
                                <span class="selected-language">EN</span>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="dropdown-flag">
                                <a class="dropdown-item" href="#" data-language="en"><i class="flag-icon flag-icon-us"></i> EN</a>
                                <a class="dropdown-item" href="#" data-language="es"><i class="flag-icon flag-icon-es"></i> ES</a>
                                <a class="dropdown-item" href="#" data-language="ms"><i class="flag-icon flag-icon-my"></i> MS</a>
                            </div>
                        </li>

                        <li class="nav-item d-none d-lg-block"><a class="nav-link nav-link-expand"><i class="ficon feather icon-maximize"></i></a></li>

                        <li class="dropdown dropdown-notification nav-item">
                            <a class="nav-link nav-link-label notification-bell" href="#" data-toggle="dropdown"><i class="ficon feather icon-bell"></i></a>
                            <ul class="dropdown-menu dropdown-menu-media dropdown-menu-right">

                                <li class="dropdown-menu-header">
                                    <div class="dropdown-header m-0 p-2">
                                        <h3 data-i18n="admin_notification" class="white">Notifications</h3>
                                    </div>
                                </li>

                                <li class="scrollable-container media-list notification-list">

                                    <a class="d-flex justify-content-between" href="javascript:void(0)">
                                        <div class="media d-flex align-items-start text-center">
                                            <div data-i18n="msg_notification">There is no notification to display</div>
                                        </div>
                                    </a>

                                </li>

                                <li class="dropdown-menu-footer"><a data-i18n="read_notification" class="dropdown-item p-1 text-center notification-all-read" href="javascript:void(0)">Mark all as Read</a></li>

                            </ul>
                        </li>


                        <?php if ($_SESSION['multi_tenant'] == true && $_SESSION['access_level'] == "superuser") { ?>

                            <li class="dropdown dropdown-notification nav-item">
                                <a class="nav-link nav-link-label" href="#" data-toggle="dropdown"><i class="ficon feather icon-grid"></i></a>
                                <ul class="dropdown-menu dropdown-menu-media dropdown-menu-right">

                                    <li class="dropdown-menu-header">
                                        <div class="dropdown-header m-0 p-2">
                                            <h3 data-i18n="change_tenant" class="white">Change Tenant</h3>
                                        </div>
                                    </li>

                                    <div class="d-flex" style="border-bottom: 1px solid #dae1e7;">
                                        <input type="text" class="form-control-plaintext filter-tenant" style="padding: 1rem;" placeholder="Filter">
                                    </div>

                                    <li class="scrollable-container media-list">

                                        <?php


                                        if (strlen($_SESSION['tenant_allowed']) < 1) {


                                            $kiw_temp = $kiw_db->fetch_array("SELECT SQL_CACHE tenant_id FROM kiwire_clouds");

                                            foreach ($kiw_temp as $kiw_tenant) {

                                        ?>

                                                <a class="d-flex justify-content-between change-tenant" data-tenant="<?= $kiw_tenant['tenant_id'] ?>" href="#">
                                                    <div class="media d-flex align-items-start text-center">
                                                        <?= $kiw_tenant['tenant_id'] ?>
                                                    </div>
                                                </a>

                                            <?php

                                            }
                                        } else {


                                            foreach (explode(",", $_SESSION['tenant_allowed']) as $kiw_tenant) {

                                            ?>

                                                <a class="d-flex justify-content-between change-tenant" data-tenant="<?= $kiw_tenant ?>" href="#">
                                                    <div class="media d-flex align-items-start text-center">
                                                        <?= $kiw_tenant ?>
                                                    </div>
                                                </a>

                                        <?php

                                            }
                                        }

                                        ?>


                                    </li>

                                </ul>
                            </li>

                        <?php } ?>


                        <li class="dropdown dropdown-user nav-item">
                            <a class="dropdown-toggle nav-link dropdown-user-link" href="#" data-toggle="dropdown">
                                <div class="user-nav d-sm-flex d-none"><span class="user-name text-uppercase text-bold-600"><?= $_SESSION['full_name'] ?></span>
                                    <span class="user-tenant"><?= $_SESSION['tenant_id'] ?></span>
                                </div>
                                <span>

                                    <?php if ($_SESSION['photo'] && file_exists(dirname(__FILE__, 3) . "/custom/{$_SESSION['tenant_id']}/profile/{$_SESSION['photo']}") == true) { ?>

                                        <img class="round" src="/custom/<?= $_SESSION['tenant_id'] ?>/profile/<?= $_SESSION['photo'] ?>" alt="avatar" height="40" width="40">

                                    <?php } else { ?>

                                        <span class="avatar">
                                            <span class="avatar-content"><span class="avatar-icon feather icon-user"></span></span>
                                        </span>

                                    <?php } ?>


                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="/admin/profile.php" data-i18n=""><i class="feather icon-lock"></i> Profile</a>
                                <a class="dropdown-item" href="/admin/general_change_password.php" data-i18n="change_password"><i class="feather icon-lock"></i> Change Password</a>
                                <a class="dropdown-item" href="/admin/general_2factor_register.php" data-i18n="2factor_setup"><i class="feather icon-lock"></i> 2-Factors Setup</a>
                                <a class="dropdown-item change-theme" href="#" data-i18n="theme"><i class="feather icon-image"></i> Theme</a>
                                <a class="dropdown-item nav-clear-cache" data-i18n="clearcache"><i class="feather icon-power"></i> Clear Cache</a>
                                <a class="dropdown-item" href="/admin/index.php?logout=now" data-i18n="logout"><i class="feather icon-power"></i> Logout</a>
                            </div>
                        </li>
                    </ul>
                </div>

                <?php if ($_SESSION['tenant_valid'] == false) { ?>

                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <p data-i18n="unlicensed_alert" class="mb-0">This system is unlicensed / expired. Please contact our Sales Representative.</p>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true"><i class="feather icon-x-circle"></i></span>
                        </button>
                    </div>

                <?php } ?>

            </div>

        </div>

    </nav>
