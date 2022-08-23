<?php

$kiw['module'] = "Login Engine -> Notification";
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

$kiw_row = $kiw_db->query_first("SELECT * FROM kiwire_notification WHERE tenant_id = '{$tenant_id}' LIMIT 1");

if (empty($kiw_row)) $kiw_db->query("INSERT INTO kiwire_notification(tenant_id) VALUE ('{$tenant_id}')");


?>


<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="logine_engine_notification_title">Notification</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="logine_engine_notification_subtitle">
                                Words and sentences for notification
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <section id="basic-tabs-components">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card overflow-hidden">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="tab-content">

                                    <form class="update-form">
                                        
                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_1">Your account has been created</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="notification_account_created" id="notification_account_created" value="<?= $kiw_row['notification_account_created']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_2">Your password has been reset. Please check your Email Inbox / SMS</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="notification_password_reset" id="notification_password_reset" value="<?= $kiw_row['notification_password_reset']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_3">Please provide credential to login</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_no_credential" id="error_no_credential" value="<?= $kiw_row['error_no_credential']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_4">You have entered wrong password or verfication</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_password_verification_failed" id="error_password_verification_failed" value="<?= $kiw_row['error_password_verification_failed']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_5">You have provided wrong OTP code</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_wrong_otp" id="error_wrong_otp" value="<?= $kiw_row['error_wrong_otp']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_6">This username already existed in the system</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_username_existed" id="error_username_existed" value="<?= $kiw_row['error_username_existed']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_7">Your account can only login after {{value_date}}</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_future_value_date" id="error_future_value_date" value="<?= $kiw_row['error_future_value_date']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_8">This account is not active</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_account_inactive" id="error_account_inactive" value="<?= $kiw_row['error_account_inactive']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_9">You have provided wrong username or password</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_wrong_credential" id="error_wrong_credential" value="<?= $kiw_row['error_wrong_credential']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_10">You have reached quota limit</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_reached_quota_limit" id="error_reached_quota_limit" value="<?= $kiw_row['error_reached_quota_limit']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_11">You have reached time limit</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_reached_time_limit" id="error_reached_time_limit" value="<?= $kiw_row['error_reached_time_limit']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_12">You have reached max simultaneous use limit</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_max_simultaneous_use" id="error_max_simultaneous_use" value="<?= $kiw_row['error_max_simultaneous_use']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_13">You are not allowed to login from this zone</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_zone_restriction" id="error_zone_restriction" value="<?= $kiw_row['error_zone_restriction']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_14">You are not allowed to login using this device</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_wrong_mac_address" id="error_wrong_mac_address" value="<?= $kiw_row['error_wrong_mac_address']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_15">This zone already reached maximum limit of login</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_zone_reached_limit" id="error_zone_reached_limit" value="<?= $kiw_row['error_zone_reached_limit']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_16">You have provided invalid email address</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_invalid_email_address" id="error_invalid_email_address" value="<?= $kiw_row['error_invalid_email_address']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_17">You have provided invalid phone number</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_invalid_phone_number" id="error_invalid_phone_number" value="<?= $kiw_row['error_invalid_phone_number']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_18">This account has not subscribe to any profile</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_no_profile_subscribe" id="error_no_profile_subscribe" value="<?= $kiw_row['error_no_profile_subscribe']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_19">You have provided wrong captcha code</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_wrong_captcha" id="error_wrong_captcha" value="<?= $kiw_row['error_wrong_captcha']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_20">You are not allowed to register using this country code</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_country_code" id="error_country_code" value="<?= $kiw_row['error_country_code']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_21">This device has been blacklisted</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_device_blacklisted" id="error_device_blacklisted" value="<?= $kiw_row['error_device_blacklisted']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_22">Your password already expired. Please change immediately</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_password_expired" id="error_password_expired" value="<?= $kiw_row['error_password_expired']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_23">Your password must contain atleast a number</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_password_contained_num" id="error_password_contained_num" value="<?= $kiw_row['error_password_contained_num']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_24">Your password must contain atleast a character</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_password_contained_alp" id="error_password_contained_alp" value="<?= $kiw_row['error_password_contained_alp']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_25">Your password must contain atleast a symbol</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_password_contained_sym" id="error_password_contained_sym" value="<?= $kiw_row['error_password_contained_sym']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_26">Your password must be atleast {{character_count}} character long</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_password_length" id="error_password_length" value="<?= $kiw_row['error_password_length']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_27">You are not allowed to use same password as previous</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_password_not_same" id="error_password_not_same" value="<?= $kiw_row['error_password_not_same']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_28">You have reached max login attempts</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_password_max_attemp" id="error_password_max_attemp" value="<?= $kiw_row['error_password_max_attemp']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_29">You are not allowed to use username as your password</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_pass_username_matched" id="error_pass_username_matched" value="<?= $kiw_row['error_pass_username_matched']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_30">You are not allowed to use previous password</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_password_reused" id="error_password_reused" value="<?= $kiw_row['error_password_reused']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_31">This email address are not belong to the account</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_user_email_mismatched" id="error_user_email_mismatched" value="<?= $kiw_row['error_user_email_mismatched']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_32">This phone number not belong to the account</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_user_sms_mismatched" id="error_user_sms_mismatched" value="<?= $kiw_row['error_user_sms_mismatched']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_33">We unable to locate this account. Please try again</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_user_not_found" id="error_user_not_found" value="<?= $kiw_row['error_user_not_found']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_34">Username cannot have any space</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_username_cannot_space" id="error_username_cannot_space" value="<?= $kiw_row['error_username_cannot_space']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_35">Please provide your sponsor email address</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_missing_sponsor_email" id="error_missing_sponsor_email" value="<?= $kiw_row['error_missing_sponsor_email']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_36">Please provide your account ID</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_missing_credential_check" id="error_missing_credential_check" value="<?= $kiw_row['error_missing_credential_check']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_37">Please provide a valid password</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_empty_password" id="error_empty_password" value="<?= $kiw_row['error_empty_password']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_38">Your password has been changed. Please login using new password</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="notification_password_changed" id="notification_password_changed" value="<?= $kiw_row['notification_password_changed']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_39">Your account already inactive</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_inactive_account" id="error_inactive_account" value="<?= $kiw_row['error_inactive_account']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_40">You need to wait another {{remaining_minute}} minutes before you are allowed to login</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_ot_reset_grace" id="error_ot_reset_grace" value="<?= $kiw_row['error_ot_reset_grace']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_41">You need to change your password upon the first login</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_password_need_to_change" id="error_password_need_to_change" value="<?= $kiw_row['error_password_need_to_change']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_42">You need to change your password every 90 days</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_password_change_day" id="error_password_change_day" value="<?= $kiw_row['error_password_change_day']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <span data-i18n="logine_engine_notification_43">Too many retries. Your account has been suspended</span> <span class="text-danger">*</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="error_password_too_much_retries" id="error_password_too_much_retries" value="<?= $kiw_row['error_password_too_much_retries']; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                                    </form>

                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-primary  save-button" data-i18n="logine_engine_notification_save">Save</button>
                                <button type="button" class="btn btn-primary mr-1 default-button" data-i18n="logine_engine_notification_reset">Reset Default</button>
                            </div>
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
