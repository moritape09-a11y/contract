jQuery(document).ready(function($) {
    // Initialize signature pad
    var canvas = document.getElementById('signature-pad');
    if (canvas) {
        var signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 0)'
        });
        
        // Resize canvas
        function resizeCanvas() {
            var ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            signaturePad.clear();
        }
        
        window.addEventListener('resize', resizeCanvas);
        resizeCanvas();
        
        // Clear signature button
        $('#clear-signature').on('click', function(e) {
            e.preventDefault();
            signaturePad.clear();
        });
    }
    
    // Add date format helper
    $('#contract_date').on('input', function() {
        var value = $(this).val().replace(/[^\d]/g, '');
        
        if (value.length >= 4) {
            var formatted = value.substring(0, 4);
            if (value.length >= 6) {
                formatted += '/' + value.substring(4, 6);
                if (value.length >= 8) {
                    formatted += '/' + value.substring(6, 8);
                }
            }
            $(this).val(formatted);
        }
    });
    
    // Handle form submission
    $('#cooperation-contract-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $message = $('#form-message');
        var $submitBtn = $form.find('button[type="submit"]');
        
        // Get all form values
        var firstName = $('#first_name').val().trim();
        var lastName = $('#last_name').val().trim();
        var nationalId = $('#national_id').val().trim();
        var institutionName = $('#institution_name').val().trim();
        var position = $('#position').val().trim();
        var address = $('#address').val().trim();
        var contractDate = $('#contract_date').val().trim();
        var selectedPlan = $('input[name="selected_plan"]:checked').val();
        
        // Validate all required fields
        if (!firstName) {
            $message.html('<div class="error-message">لطفا نام خود را وارد کنید.</div>');
            $('#first_name').focus();
            return;
        }
        
        if (!lastName) {
            $message.html('<div class="error-message">لطفا نام خانوادگی خود را وارد کنید.</div>');
            $('#last_name').focus();
            return;
        }
        
        if (!nationalId) {
            $message.html('<div class="error-message">لطفا کد ملی خود را وارد کنید.</div>');
            $('#national_id').focus();
            return;
        }
        
        if (nationalId.length !== 10 || !/^\d{10}$/.test(nationalId)) {
            $message.html('<div class="error-message">کد ملی باید دقیقاً 10 رقم باشد.</div>');
            $('#national_id').focus();
            return;
        }
        
        if (!institutionName) {
            $message.html('<div class="error-message">لطفا نام آموزشگاه را وارد کنید.</div>');
            $('#institution_name').focus();
            return;
        }
        
        if (!position) {
            $message.html('<div class="error-message">لطفا سمت/عنوان خود را وارد کنید.</div>');
            $('#position').focus();
            return;
        }
        
        if (!address) {
            $message.html('<div class="error-message">لطفا آدرس را وارد کنید.</div>');
            $('#address').focus();
            return;
        }
        
        if (!contractDate) {
            $message.html('<div class="error-message">لطفا تاریخ قرارداد را وارد کنید.</div>');
            $('#contract_date').focus();
            return;
        }
        
        if (!selectedPlan) {
            $message.html('<div class="error-message">لطفا یکی از طرح‌ها را انتخاب کنید.</div>');
            return;
        }
        
        // Check if signature is empty
        if (signaturePad.isEmpty()) {
            $message.html('<div class="error-message">لطفا امضای خود را در کادر مشخص شده قرار دهید.</div>');
            return;
        }
        
        // Get signature data
        var signatureData = signaturePad.toDataURL();
        
        // Prepare form data
        var formData = {
            action: 'save_cooperation_contract',
            nonce: cooperationContract.nonce,
            first_name: firstName,
            last_name: lastName,
            national_id: nationalId,
            institution_name: institutionName,
            position: position,
            address: address,
            contract_date: contractDate,
            selected_plan: selectedPlan,
            signature_data: signatureData
        };
        
        // Debug log (can be removed in production)
        console.log('Sending form data:', formData);
        
        // Disable submit button
        $submitBtn.prop('disabled', true).text('در حال ثبت...');
        $message.html('');
        
        // Send AJAX request
        $.ajax({
            url: cooperationContract.ajaxUrl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $message.html('<div class="success-message">' + response.data.message + '</div>');
                    $form[0].reset();
                    signaturePad.clear();
                    
                    // Scroll to message
                    $('html, body').animate({
                        scrollTop: $message.offset().top - 100
                    }, 500);
                } else {
                    $message.html('<div class="error-message">' + response.data.message + '</div>');
                }
            },
            error: function() {
                $message.html('<div class="error-message">خطا در ارتباط با سرور. لطفا دوباره تلاش کنید.</div>');
            },
            complete: function() {
                $submitBtn.prop('disabled', false).text('ثبت قرارداد');
            }
        });
    });
});
