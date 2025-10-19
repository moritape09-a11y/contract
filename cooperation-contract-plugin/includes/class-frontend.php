<?php
/**
 * Frontend class for displaying contract form
 */

if (!defined('ABSPATH')) {
    exit;
}

class Cooperation_Contract_Frontend {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_save_cooperation_contract', array($this, 'ajax_save_contract'));
        add_action('wp_ajax_nopriv_save_cooperation_contract', array($this, 'ajax_save_contract'));
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style(
            'cooperation-contract-style',
            COOPERATION_CONTRACT_PLUGIN_URL . 'assets/css/style.css',
            array(),
            COOPERATION_CONTRACT_VERSION
        );
        
        // Enqueue Persian Date library from CDN
        wp_enqueue_script(
            'persian-date',
            'https://cdn.jsdelivr.net/npm/persian-date@1.1.0/dist/persian-date.js',
            array('jquery'),
            '1.1.0',
            true
        );
        
        wp_enqueue_script(
            'signature-pad',
            'https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js',
            array(),
            '4.1.7',
            true
        );
        
        wp_enqueue_script(
            'cooperation-contract-script',
            COOPERATION_CONTRACT_PLUGIN_URL . 'assets/js/script.js',
            array('jquery', 'persian-date', 'signature-pad'),
            COOPERATION_CONTRACT_VERSION,
            true
        );
        
        wp_localize_script('cooperation-contract-script', 'cooperationContract', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cooperation_contract_nonce')
        ));
    }
    
    public static function render_contract_form() {
        $contract_text = get_option('cooperation_contract_text', '<h2>قرارداد همکاری</h2><p>لطفا متن قرارداد را از بخش تنظیمات وارد کنید.</p>');
        
        ob_start();
        ?>
        <div class="cooperation-contract-wrapper">
            <div class="contract-text-section">
                <?php echo wp_kses_post($contract_text); ?>
            </div>
            
            <div class="contract-form-section">
                <h3>اطلاعات قرارداد</h3>
                <form id="cooperation-contract-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">نام: <span class="required">*</span></label>
                            <input type="text" id="first_name" name="first_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">نام خانوادگی: <span class="required">*</span></label>
                            <input type="text" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="national_id">کد ملی: <span class="required">*</span></label>
                            <input type="text" id="national_id" name="national_id" pattern="[0-9]{10}" maxlength="10" placeholder="1234567890" required>
                            <small class="field-hint">کد ملی 10 رقمی</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="institution_name">نام آموزشگاه: <span class="required">*</span></label>
                            <input type="text" id="institution_name" name="institution_name" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="position">سمت/عنوان: <span class="required">*</span></label>
                            <input type="text" id="position" name="position" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="miladi_date">تاریخ قرارداد: <span class="required">*</span></label>
                            <input type="date" 
                                   id="miladi_date" 
                                   name="miladi_date"
                                   style="padding: 10px 15px; width: 100%; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                                   required>
                            <input type="hidden" id="contract_date" name="contract_date" required>
                            <div id="jalali_result" style="margin-top: 8px; padding: 8px; background: #e8f4f8; border-radius: 4px; font-weight: bold; color: #0073aa; display: none;"></div>
                            <small class="field-hint">تاریخ میلادی را انتخاب کنید، معادل شمسی خودکار نمایش داده می‌شود</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">آدرس: <span class="required">*</span></label>
                        <textarea id="address" name="address" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>طرح انتخابی: <span class="required">*</span></label>
                        <div class="radio-group">
                            <label class="radio-label">
                                <input type="radio" name="selected_plan" value="طرح سایت" required>
                                <span>طرح سایت</span>
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="selected_plan" value="طرح گروه" required>
                                <span>طرح گروه</span>
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="selected_plan" value="طرح طلایی" required>
                                <span>طرح طلایی</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>امضا: <span class="required">*</span></label>
                        <div class="signature-container">
                            <canvas id="signature-pad" class="signature-pad"></canvas>
                        </div>
                        <button type="button" id="clear-signature" class="button-secondary">پاک کردن امضا</button>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="button-primary">ثبت قرارداد</button>
                    </div>
                    
                    <div id="form-message"></div>
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function ajax_save_contract() {
        // Verify nonce
        if (!check_ajax_referer('cooperation_contract_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'خطای امنیتی. لطفاً صفحه را Refresh کنید.'));
            return;
        }
        
        // Validate required fields
        $required_fields = array('first_name', 'last_name', 'national_id', 'institution_name', 'position', 'address', 'contract_date', 'selected_plan', 'signature_data');
        
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                wp_send_json_error(array('message' => 'لطفا تمام فیلدها را پر کنید.'));
                return;
            }
        }
        
        // Validate and sanitize national ID
        $national_id = sanitize_text_field($_POST['national_id']);
        if (!preg_match('/^\d{10}$/', $national_id)) {
            wp_send_json_error(array('message' => 'کد ملی باید دقیقاً 10 رقم باشد.'));
            return;
        }
        
        // Validate contract date
        $contract_date = sanitize_text_field($_POST['contract_date']);
        if (strlen($contract_date) < 8) {
            wp_send_json_error(array('message' => 'لطفاً تاریخ قرارداد را انتخاب کنید.'));
            return;
        }
        
        // Optional: Log date for debugging (can be removed)
        error_log('Contract date received: ' . $contract_date);
        
        // Validate signature data
        $signature_data = $_POST['signature_data'];
        if (!preg_match('/^data:image\/png;base64,/', $signature_data)) {
            wp_send_json_error(array('message' => 'فرمت امضا نامعتبر است.'));
            return;
        }
        
        // Check signature size (max 5MB)
        if (strlen($signature_data) > 5000000) {
            wp_send_json_error(array('message' => 'حجم امضا خیلی زیاد است.'));
            return;
        }
        
        // Validate selected plan
        $valid_plans = array('طرح سایت', 'طرح گروه', 'طرح طلایی');
        if (!in_array($_POST['selected_plan'], $valid_plans, true)) {
            wp_send_json_error(array('message' => 'طرح انتخابی نامعتبر است.'));
            return;
        }
        
        // Additional validation for text fields
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        
        if (strlen($first_name) < 2 || strlen($first_name) > 50) {
            wp_send_json_error(array('message' => 'نام باید بین 2 تا 50 کاراکتر باشد.'));
            return;
        }
        
        if (strlen($last_name) < 2 || strlen($last_name) > 50) {
            wp_send_json_error(array('message' => 'نام خانوادگی باید بین 2 تا 50 کاراکتر باشد.'));
            return;
        }
        
        // Prepare data for saving
        $safe_data = array(
            'first_name' => $first_name,
            'last_name' => $last_name,
            'national_id' => $national_id,
            'institution_name' => sanitize_text_field($_POST['institution_name']),
            'position' => sanitize_text_field($_POST['position']),
            'address' => sanitize_textarea_field($_POST['address']),
            'contract_date' => $contract_date,
            'selected_plan' => sanitize_text_field($_POST['selected_plan']),
            'signature_data' => $signature_data
        );
        
        // Save contract
        $contract_id = Cooperation_Contract_Database::save_contract($safe_data);
        
        if ($contract_id) {
            // Log success (optional)
            error_log(sprintf('Contract saved successfully. ID: %d, User: %s', $contract_id, $first_name . ' ' . $last_name));
            
            wp_send_json_success(array(
                'message' => '<strong>قرارداد همکاری به‌صورت رسمی و قانونی تنظیم و امضا گردید.</strong><br>قرارداد با شماره ' . esc_html($contract_id) . ' ذخیره شد.',
                'contract_id' => $contract_id
            ));
        } else {
            // Log error
            error_log('Failed to save contract for: ' . $first_name . ' ' . $last_name);
            
            wp_send_json_error(array('message' => 'خطا در ثبت قرارداد در دیتابیس. لطفا دوباره تلاش کنید.'));
        }
    }
}
