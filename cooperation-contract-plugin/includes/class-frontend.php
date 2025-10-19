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
        // Enqueue Persian Datepicker CSS
        wp_enqueue_style(
            'persian-datepicker-css',
            COOPERATION_CONTRACT_PLUGIN_URL . 'assets/css/persian-datepicker.min.css',
            array(),
            COOPERATION_CONTRACT_VERSION
        );
        
        wp_enqueue_style(
            'cooperation-contract-style',
            COOPERATION_CONTRACT_PLUGIN_URL . 'assets/css/style.css',
            array('persian-datepicker-css'),
            COOPERATION_CONTRACT_VERSION
        );
        
        // Enqueue Persian Date library
        wp_enqueue_script(
            'persian-date',
            COOPERATION_CONTRACT_PLUGIN_URL . 'assets/js/persian-date.min.js',
            array('jquery'),
            COOPERATION_CONTRACT_VERSION,
            true
        );
        
        // Enqueue Persian Datepicker
        wp_enqueue_script(
            'persian-datepicker',
            COOPERATION_CONTRACT_PLUGIN_URL . 'assets/js/persian-datepicker.min.js',
            array('jquery', 'persian-date'),
            COOPERATION_CONTRACT_VERSION,
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
            array('jquery', 'signature-pad', 'persian-datepicker'),
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
                            <label for="contract_date">تاریخ قرارداد (شمسی): <span class="required">*</span></label>
                            <div class="date-picker-wrapper">
                                <input type="text" id="contract_date" name="contract_date" 
                                       placeholder="روی آیکون تقویم کلیک کنید" 
                                       readonly
                                       required>
                                <span class="date-icon">📅</span>
                            </div>
                            <small class="field-hint">روی آیکون تقویم کلیک کنید و تاریخ را انتخاب کنید</small>
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
        check_ajax_referer('cooperation_contract_nonce', 'nonce');
        
        // Validate required fields
        $required_fields = array('first_name', 'last_name', 'national_id', 'institution_name', 'position', 'address', 'contract_date', 'selected_plan', 'signature_data');
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error(array('message' => 'لطفا تمام فیلدها را پر کنید.'));
                return;
            }
        }
        
        // Validate national ID (10 digits)
        if (!preg_match('/^[0-9]{10}$/', $_POST['national_id'])) {
            wp_send_json_error(array('message' => 'کد ملی باید 10 رقم باشد.'));
            return;
        }
        
        // Validate date (at least 8 characters)
        if (strlen($_POST['contract_date']) < 8) {
            wp_send_json_error(array('message' => 'تاریخ قرارداد باید حداقل 8 کاراکتر باشد.'));
            return;
        }
        
        // Save contract
        $contract_id = Cooperation_Contract_Database::save_contract($_POST);
        
        if ($contract_id) {
            wp_send_json_success(array(
                'message' => '<strong>قرارداد همکاری به‌صورت رسمی و قانونی تنظیم و امضا گردید.</strong><br>قرارداد با شماره ' . $contract_id . ' ذخیره شد.',
                'contract_id' => $contract_id
            ));
        } else {
            wp_send_json_error(array('message' => 'خطا در ثبت قرارداد. لطفا دوباره تلاش کنید.'));
        }
    }
}
