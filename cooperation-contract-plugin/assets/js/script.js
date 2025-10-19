jQuery(document).ready(function($) {
    'use strict';
    
    // Global variable for signature pad
    var signaturePad = null;
    
    // Initialize signature pad
    function initializeSignaturePad() {
        var canvas = document.getElementById('signature-pad');
        if (!canvas) {
            console.error('Signature canvas not found!');
            return false;
        }
        
        try {
            signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(0, 0, 0)',
                minWidth: 0.5,
                maxWidth: 2.5
            });
            
            // Resize canvas properly
            function resizeCanvas() {
                var ratio = Math.max(window.devicePixelRatio || 1, 1);
                var data = signaturePad.toData(); // Save current signature
                
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext("2d").scale(ratio, ratio);
                
                if (data && data.length > 0) {
                    signaturePad.fromData(data); // Restore signature
                } else {
                    signaturePad.clear();
                }
            }
            
            // Debounce resize
            var resizeTimeout;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(resizeCanvas, 250);
            });
            
            // Initial resize
            resizeCanvas();
            
            // Clear signature button
            $('#clear-signature').on('click', function(e) {
                e.preventDefault();
                if (signaturePad) {
                    signaturePad.clear();
                }
            });
            
            return true;
        } catch(error) {
            console.error('Error initializing signature pad:', error);
            return false;
        }
    }
    
    // Initialize on page load
    var signaturePadReady = initializeSignaturePad();
    
    // Miladi to Jalali converter with error handling
    function convertDate() {
        var miladiDate = $('#miladi_date').val();
        
        if (!miladiDate) {
            $('#jalali_result').hide();
            $('#contract_date').val('').removeAttr('data-converted');
            return;
        }
        
        // Check if persianDate is available
        if (typeof persianDate === 'undefined') {
            console.error('persianDate library not loaded!');
            $('#jalali_result')
                .html('⚠️ خطا: کتابخانه تقویم لود نشده. لطفاً صفحه را Refresh کنید.')
                .css('color', '#dc3232')
                .fadeIn();
            return;
        }
        
        try {
            // Convert to Jalali
            var dateObj = new Date(miladiDate);
            
            // Validate date
            if (isNaN(dateObj.getTime())) {
                throw new Error('Invalid date');
            }
            
            var d = new persianDate(dateObj);
            var jalali = d.format('YYYY/MM/DD');
            
            // Set the jalali date in hidden field
            $('#contract_date').val(jalali).attr('data-converted', 'true');
            
            // Show result with animation
            $('#jalali_result')
                .html('📅 تاریخ شمسی: <strong>' + jalali + '</strong>')
                .css('color', '#0073aa')
                .fadeIn(300);
            
            console.log('✅ Date converted:', miladiDate, '→', jalali);
            
        } catch(error) {
            console.error('Error converting date:', error);
            $('#jalali_result')
                .html('❌ خطا در تبدیل تاریخ. لطفاً دوباره تلاش کنید.')
                .css('color', '#dc3232')
                .fadeIn(300);
            $('#contract_date').val('').removeAttr('data-converted');
        }
    }
    
    // Attach date converter
    $('#miladi_date').on('change', convertDate);
    
    // Form validation and submission
    $('#cooperation-contract-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $message = $('#form-message');
        var $submitBtn = $form.find('button[type="submit"]');
        
        // Clear previous messages
        $message.html('');
        
        // Get and trim all values
        var formValues = {
            firstName: $('#first_name').val().trim(),
            lastName: $('#last_name').val().trim(),
            nationalId: $('#national_id').val().trim(),
            institutionName: $('#institution_name').val().trim(),
            position: $('#position').val().trim(),
            address: $('#address').val().trim(),
            miladiDate: $('#miladi_date').val().trim(),
            contractDate: $('#contract_date').val().trim(),
            selectedPlan: $('input[name="selected_plan"]:checked').val()
        };
        
        // Validation with specific messages
        if (!formValues.firstName) {
            showError('لطفا نام خود را وارد کنید.', '#first_name');
            return;
        }
        
        if (!formValues.lastName) {
            showError('لطفا نام خانوادگی خود را وارد کنید.', '#last_name');
            return;
        }
        
        if (!formValues.nationalId) {
            showError('لطفا کد ملی خود را وارد کنید.', '#national_id');
            return;
        }
        
        if (!/^\d{10}$/.test(formValues.nationalId)) {
            showError('کد ملی باید دقیقاً 10 رقم باشد.', '#national_id');
            return;
        }
        
        if (!formValues.institutionName) {
            showError('لطفا نام آموزشگاه را وارد کنید.', '#institution_name');
            return;
        }
        
        if (!formValues.position) {
            showError('لطفا سمت/عنوان خود را وارد کنید.', '#position');
            return;
        }
        
        if (!formValues.address) {
            showError('لطفا آدرس را وارد کنید.', '#address');
            return;
        }
        
        if (!formValues.miladiDate) {
            showError('لطفا تاریخ میلادی را انتخاب کنید.', '#miladi_date');
            return;
        }
        
        if (!formValues.contractDate || !$('#contract_date').attr('data-converted')) {
            showError('لطفا منتظر بمانید تا تاریخ تبدیل شود یا دوباره تاریخ را انتخاب کنید.', '#miladi_date');
            // Try to convert again
            convertDate();
            return;
        }
        
        if (!formValues.selectedPlan) {
            showError('لطفا یکی از طرح‌ها را انتخاب کنید.', null);
            $('input[name="selected_plan"]').first().focus();
            return;
        }
        
        // Check signature pad
        if (!signaturePadReady || !signaturePad) {
            showError('خطا: سیستم امضا آماده نیست. لطفاً صفحه را Refresh کنید.', null);
            return;
        }
        
        if (signaturePad.isEmpty()) {
            showError('لطفا امضای خود را در کادر مشخص شده قرار دهید.', null);
            $('#signature-pad').focus();
            return;
        }
        
        // Get signature data
        var signatureData = signaturePad.toDataURL('image/png');
        
        // Validate signature size (should not be too large)
        if (signatureData.length > 5000000) { // 5MB limit
            showError('حجم امضا خیلی زیاد است. لطفاً امضای ساده‌تری بکشید.', null);
            return;
        }
        
        // Prepare form data
        var formData = {
            action: 'save_cooperation_contract',
            nonce: cooperationContract.nonce,
            first_name: formValues.firstName,
            last_name: formValues.lastName,
            national_id: formValues.nationalId,
            institution_name: formValues.institutionName,
            position: formValues.position,
            address: formValues.address,
            contract_date: formValues.contractDate,
            selected_plan: formValues.selectedPlan,
            signature_data: signatureData
        };
        
        console.log('📤 Sending contract data...');
        
        // Disable form
        $submitBtn.prop('disabled', true).text('در حال ثبت...');
        $form.find('input, textarea, button').prop('disabled', true);
        
        // Send AJAX request
        $.ajax({
            url: cooperationContract.ajaxUrl,
            type: 'POST',
            data: formData,
            timeout: 30000, // 30 second timeout
            success: function(response) {
                if (response.success) {
                    // Success
                    $message.html('<div class="success-message">' + response.data.message + '</div>');
                    
                    // Reset form
                    $form[0].reset();
                    if (signaturePad) {
                        signaturePad.clear();
                    }
                    $('#jalali_result').hide();
                    $('#contract_date').val('').removeAttr('data-converted');
                    
                    // Scroll to message
                    $('html, body').animate({
                        scrollTop: $message.offset().top - 100
                    }, 500);
                    
                    console.log('✅ Contract saved successfully! ID:', response.data.contract_id);
                } else {
                    // Error from server
                    $message.html('<div class="error-message">' + (response.data.message || 'خطا در ثبت قرارداد.') + '</div>');
                    console.error('❌ Server error:', response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ AJAX error:', status, error);
                var errorMsg = 'خطا در ارتباط با سرور. ';
                
                if (status === 'timeout') {
                    errorMsg += 'زمان درخواست تمام شد. لطفاً دوباره تلاش کنید.';
                } else if (status === 'error') {
                    errorMsg += 'لطفاً اتصال اینترنت خود را بررسی کنید.';
                } else {
                    errorMsg += 'لطفا دوباره تلاش کنید.';
                }
                
                $message.html('<div class="error-message">' + errorMsg + '</div>');
            },
            complete: function() {
                // Re-enable form
                $submitBtn.prop('disabled', false).text('ثبت قرارداد');
                $form.find('input, textarea, button').prop('disabled', false);
            }
        });
    });
    
    // Helper function to show errors
    function showError(message, focusElement) {
        $('#form-message').html('<div class="error-message">' + message + '</div>');
        
        if (focusElement) {
            $(focusElement).focus();
        }
        
        // Scroll to error message
        $('html, body').animate({
            scrollTop: $('#form-message').offset().top - 100
        }, 300);
    }
    
    // Prevent form submission on Enter key (except in textarea)
    $('#cooperation-contract-form').on('keypress', function(e) {
        if (e.which === 13 && e.target.nodeName !== 'TEXTAREA') {
            e.preventDefault();
            return false;
        }
    });
    
    // Auto-convert date on page load if already selected
    if ($('#miladi_date').val()) {
        convertDate();
    }
    
    console.log('✅ Cooperation Contract Plugin initialized successfully!');
});
