$(document).ready(function () {

    $(".default-button").on("click", function () {

        $('#notification_account_created').val('Your account has been created.');
        $('#notification_password_reset').val('Your password has been reset. Please check your Email Inbox / SMS.');

        $('#error_no_credential').val('Please provide credential to login.');
        $('#error_password_verification_failed').val('You have entered wrong password or verfication.');
        $('#error_wrong_otp').val('You have provided wrong OTP code.');
        $('#error_username_existed').val('This username already existed in the system.');

        $('#error_future_value_date').val('Your account can only login after {{value_date}}');
        $('#error_account_inactive').val('This account is not active.');
        $('#error_wrong_credential').val('You have provided wrong username or password.');
        $('#error_reached_quota_limit').val('You have reached quota limit.');
        $('#error_reached_time_limit').val('You have reached time limit.');
        $('#error_max_simultaneous_use').val('You have reached max simultaneous use limit.');
        $('#error_zone_restriction').val('You are not allowed to login from this zone.');
        $('#error_wrong_mac_address').val('You are not allowed to login using this device.');
        $('#error_zone_reached_limit').val('This zone already reached maximum limit of login.');

        $('#error_invalid_email_address').val('You have provided invalid email address.');
        $('#error_invalid_phone_number').val('You have provided invalid phone number.');
        $('#error_no_profile_subscribe').val('This account has not subscribe to any profile.');

        $('#error_wrong_captcha').val('You have provided wrong captcha code.');
        $('#error_country_code').val('You are not allowed to register using this country code.');

        $('#error_device_blacklisted').val('This device has been blacklisted.');

        $('#error_password_expired').val('Your password already expired. Please change immediately.');
        $('#error_password_contained_num').val('Your password must contain atleast a number.');
        $('#error_password_contained_alp').val('Your password must contain atleast a character.');
        $('#error_password_contained_sym').val('Your password must contain atleast a symbol.');
        $('#error_password_length').val('Your password must be atleast {{character_count}} character long.');
        $('#error_password_not_same').val('You are not allowed to use same password as previous.');
        $('#error_password_max_attemp').val('You have reached max login attempts.');
        $('#error_pass_username_matched').val('You are not allowed to use username as your password.');
        $('#error_password_reused').val('You are not allowed to use previous password.');

        $('#error_user_email_mismatched').val('This email address are not belong to the account.');
        $('#error_user_sms_mismatched').val('This phone number not belong to the account.');
        $('#error_user_not_found').val('We unable to locate this account. Please try again.');
        $('#error_username_cannot_space').val('Username cannot have any space.');
        $('#error_missing_sponsor_email').val('Please provide your sponsor email address.');
        $('#error_missing_credential_check').val('Please provide your account ID.');

        $('#error_empty_password').val('Please provide a valid password.');
        $('#notification_password_changed').val('Your password has been changed. Please login using new password.');
        $('#error_inactive_account').val('Your account already inactive.');
        $('#error_ot_reset_grace').val('You need to wait another {{remaining_minute}} minutes before you are allowed to login.');
        $('#error_password_need_to_change').val('You need to change your password upon the first login.');
        $('#error_password_change_day').val('You need to change your password every 90 days.');
        $('#error_password_too_much_retries').val('Too many retries. Your account has been suspended.');

    });

    $("button.save-button").on("click", function (e) {

        let data = $("form").serialize();

        $.ajax({
            url: "ajax/ajax_login_engine_notification.php?action=update",
            method: "POST",
            data: data,
            success: function (data) {

                if (data['status'] === "success") {

                    swal("Success", data['message'], "success");

                } else {

                    swal("Error", data['message'], "error");

                }

            },
            error: function (data) {

                swal("Error", "There is unexpected error. Please try again.", "error");

            }
        })

    });

})
