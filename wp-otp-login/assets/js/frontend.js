/**
 * Frontend JavaScript for OTP Login Forms
 */

(function($) {
    'use strict';

    // Timer for resend code
    let resendTimer = null;
    let timeLeft = 120;

    function startTimer(widgetId, callback) {
        timeLeft = 120;
        const timerElement = $('#wp-otp-login-form-' + widgetId + ' .wp-otp-timer, #wp-otp-register-form-' + widgetId + ' .wp-otp-timer');
        const resendLink = $('#wp-otp-login-form-' + widgetId + ' .wp-otp-resend-link, #wp-otp-register-form-' + widgetId + ' .wp-otp-resend-link');
        
        if (resendTimer) {
            clearInterval(resendTimer);
        }
        
        resendLink.hide();
        timerElement.show();
        
        resendTimer = setInterval(function() {
            timeLeft--;
            
            var minutes = Math.floor(timeLeft / 60);
            var seconds = timeLeft % 60;
            
            timerElement.text(minutes + ':' + (seconds < 10 ? '0' : '') + seconds);
            
            if (timeLeft <= 0) {
                clearInterval(resendTimer);
                timerElement.hide();
                resendLink.show();
                
                if (callback) {
                    callback();
                }
            }
        }, 1000);
    }

    // Send OTP Code
    $(document).on('click', '.wp-otp-send-code-btn', function(e) {
        e.preventDefault();
        
        var widgetId = $(this).data('widget-id');
        var form = $(this).closest('form');
        var phoneInput = form.find('#wp-otp-phone-' + widgetId);
        var messageDiv = form.find('.wp-otp-message');
        var phoneNumber = phoneInput.val().trim();
        
        var phonePattern = /^(\+98|98|0)?9\d{9}$/;
        if (!phonePattern.test(phoneNumber)) {
            showMessage(messageDiv, wpOtpLogin.messages.invalidPhone, 'error');
            return;
        }
        
        $(this).prop('disabled', true).text('در حال ارسال...');
        
        $.ajax({
            url: wpOtpLogin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wp_otp_request_code',
                nonce: wpOtpLogin.nonce,
                phone_number: phoneNumber
            },
            success: function(response) {
                if (response.success) {
                    showMessage(messageDiv, wpOtpLogin.messages.codeSent, 'success');
                    
                    form.find('.wp-otp-step-phone').hide();
                    form.find('.wp-otp-step-verify').fadeIn();
                    form.find('#wp-otp-code-' + widgetId).focus();
                    
                    startTimer(widgetId, function() {
                        form.find('.wp-otp-send-code-btn').prop('disabled', false);
                    });
                    
                    form.data('phone-number', phoneNumber);
                } else {
                    showMessage(messageDiv, response.data.message || wpOtpLogin.messages.error, 'error');
                    $('#wp-otp-login-form-' + widgetId + ' .wp-otp-send-code-btn, #wp-otp-register-form-' + widgetId + ' .wp-otp-send-code-btn').prop('disabled', false).text('ارسال کد تأیید');
                }
            },
            error: function() {
                showMessage(messageDiv, wpOtpLogin.messages.error, 'error');
                $('#wp-otp-login-form-' + widgetId + ' .wp-otp-send-code-btn, #wp-otp-register-form-' + widgetId + ' .wp-otp-send-code-btn').prop('disabled', false).text('ارسال کد تأیید');
            }
        });
    });

    // Verify OTP and Login/Register
    $(document).on('submit', '.wp-otp-login-form form, .wp-otp-register-form form', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var widgetId = form.find('.wp-otp-verify-btn').data('widget-id');
        var widgetType = form.closest('[data-widget-type]').data('widget-type');
        var otpInput = form.find('#wp-otp-code-' + widgetId);
        var messageDiv = form.find('.wp-otp-message');
        var submitButton = form.find('.wp-otp-verify-btn');
        
        var phoneNumber = form.data('phone-number');
        var otpCode = otpInput.val().trim();
        
        if (!phoneNumber || !otpCode) {
            showMessage(messageDiv, 'لطفاً تمام فیلدها را پر کنید', 'error');
            return;
        }
        
        submitButton.prop('disabled', true).text('در حال بررسی...');
        
        var action = widgetType === 'register' ? 'wp_otp_register_user' : 'wp_otp_login_user';
        
        var data = {
            action: action,
            nonce: wpOtpLogin.nonce,
            phone_number: phoneNumber,
            otp_code: otpCode
        };
        
        if (widgetType === 'register') {
            var firstName = form.find('#wp-otp-firstname-' + widgetId).val();
            var lastName = form.find('#wp-otp-lastname-' + widgetId).val();
            
            if (firstName) data.first_name = firstName;
            if (lastName) data.last_name = lastName;
        }
        
        $.ajax({
            url: wpOtpLogin.ajaxUrl,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    showMessage(messageDiv, widgetType === 'register' ? 'ثبت‌نام موفقیت‌آمیز بود' : 'ورود موفقیت‌آمیز بود', 'success');
                    
                    var redirectUrl = submitButton.data('redirect-url');
                    setTimeout(function() {
                        window.location.href = redirectUrl;
                    }, 1000);
                } else {
                    showMessage(messageDiv, response.data.message || 'خطایی رخ داد', 'error');
                    submitButton.prop('disabled', false).text(widgetType === 'register' ? 'ثبت‌نام' : 'ورود به سایت');
                    
                    otpInput.val('');
                    otpInput.focus();
                }
            },
            error: function() {
                showMessage(messageDiv, 'خطایی رخ داد', 'error');
                submitButton.prop('disabled', false).text(widgetType === 'register' ? 'ثبت‌نام' : 'ورود به سایت');
            }
        });
    });

    // Resend code link
    $(document).on('click', '.wp-otp-resend-link', function(e) {
        e.preventDefault();
        
        var widgetId = $(this).closest('form').find('.wp-otp-verify-btn').data('widget-id');
        var form = $(this).closest('form');
        
        form.find('.wp-otp-send-code-btn').trigger('click');
    });

    // Auto-format OTP input
    $(document).on('input', '.wp-otp-code-input', function() {
        var value = $(this).val().replace(/\D/g, '');
        $(this).val(value);
    });

    // Show message helper
    function showMessage(element, message, type) {
        element.removeClass('success error').addClass(type).text(message).fadeIn();
        
        setTimeout(function() {
            element.fadeOut();
        }, 5000);
    }

    // Phone number formatting
    $(document).on('input', 'input[type="tel"]', function() {
        var value = $(this).val().replace(/[^\d+]/g, '');
        $(this).val(value);
    });

})(jQuery);
