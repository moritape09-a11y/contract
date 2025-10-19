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
    
    // Initialize Persian date picker
    if (typeof $.fn.persianDatepicker !== 'undefined') {
        $('.persian-datepicker').persianDatepicker({
            format: 'YYYY/MM/DD',
            initialValue: false,
            autoClose: true,
            calendar: {
                persian: {
                    locale: 'fa'
                }
            },
            navigator: {
                enabled: true,
                text: {
                    btnNextText: '<',
                    btnPrevText: '>'
                }
            },
            toolbox: {
                calendarSwitch: {
                    enabled: false
                }
            },
            onSelect: function(unix) {
                // Date selected
            }
        });
    }
    
    // Handle form submission
    $('#cooperation-contract-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $message = $('#form-message');
        var $submitBtn = $form.find('button[type="submit"]');
        
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
            first_name: $('#first_name').val(),
            last_name: $('#last_name').val(),
            institution_name: $('#institution_name').val(),
            position: $('#position').val(),
            address: $('#address').val(),
            contract_date: $('#contract_date').val(),
            selected_plan: $('input[name="selected_plan"]:checked').val(),
            signature_data: signatureData
        };
        
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
