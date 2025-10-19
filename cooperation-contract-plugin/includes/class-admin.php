<?php
/**
 * Admin class for managing plugin settings and viewing contracts
 */

if (!defined('ABSPATH')) {
    exit;
}

class Cooperation_Contract_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'قراردادهای همکاری',
            'قراردادهای همکاری',
            'manage_options',
            'cooperation-contracts',
            array($this, 'contracts_list_page'),
            'dashicons-edit-page',
            30
        );
        
        add_submenu_page(
            'cooperation-contracts',
            'تنظیمات قرارداد',
            'تنظیمات',
            'manage_options',
            'cooperation-contract-settings',
            array($this, 'settings_page')
        );
    }
    
    public function register_settings() {
        register_setting('cooperation_contract_settings', 'cooperation_contract_text');
    }
    
    public function contracts_list_page() {
        // Handle delete action
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            if (check_admin_referer('delete_contract_' . $_GET['id'])) {
                Cooperation_Contract_Database::delete_contract($_GET['id']);
                echo '<div class="notice notice-success"><p>قرارداد با موفقیت حذف شد.</p></div>';
            }
        }
        
        $contracts = Cooperation_Contract_Database::get_contracts();
        ?>
        <div class="wrap" style="direction: rtl;">
            <h1>قراردادهای همکاری</h1>
            <p>برای نمایش فرم قرارداد در صفحات، از شورت‌کد زیر استفاده کنید:</p>
            <code>[cooperation_contract]</code>
            
            <table class="wp-list-table widefat fixed striped" style="margin-top: 20px;">
                <thead>
                    <tr>
                        <th>شناسه</th>
                        <th>نام و نام خانوادگی</th>
                        <th>کد ملی</th>
                        <th>آموزشگاه</th>
                        <th>طرح انتخابی</th>
                        <th>تاریخ قرارداد</th>
                        <th>تاریخ ثبت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($contracts)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">هیچ قراردادی ثبت نشده است.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($contracts as $contract): ?>
                            <tr>
                                <td><?php echo esc_html($contract->id); ?></td>
                                <td><?php echo esc_html($contract->first_name . ' ' . $contract->last_name); ?></td>
                                <td><?php echo esc_html($contract->national_id); ?></td>
                                <td><?php echo esc_html($contract->institution_name); ?></td>
                                <td><?php echo esc_html($contract->selected_plan); ?></td>
                                <td><?php echo esc_html($contract->contract_date); ?></td>
                                <td><?php echo esc_html($contract->created_at); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=cooperation-contracts&action=view&id=' . $contract->id); ?>" class="button">مشاهده</a>
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=cooperation-contracts&action=delete&id=' . $contract->id), 'delete_contract_' . $contract->id); ?>" class="button" onclick="return confirm('آیا مطمئن هستید؟');">حذف</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
        
        // Show contract details if viewing
        if (isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['id'])) {
            $contract = Cooperation_Contract_Database::get_contract($_GET['id']);
            if ($contract) {
                ?>
                <div class="wrap" style="direction: rtl; margin-top: 30px; padding: 20px; background: #fff; border: 1px solid #ccc;">
                    <h2>جزئیات قرارداد #<?php echo esc_html($contract->id); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th>نام:</th>
                            <td><?php echo esc_html($contract->first_name); ?></td>
                        </tr>
                        <tr>
                            <th>نام خانوادگی:</th>
                            <td><?php echo esc_html($contract->last_name); ?></td>
                        </tr>
                        <tr>
                            <th>کد ملی:</th>
                            <td><?php echo esc_html($contract->national_id); ?></td>
                        </tr>
                        <tr>
                            <th>نام آموزشگاه:</th>
                            <td><?php echo esc_html($contract->institution_name); ?></td>
                        </tr>
                        <tr>
                            <th>سمت/عنوان:</th>
                            <td><?php echo esc_html($contract->position); ?></td>
                        </tr>
                        <tr>
                            <th>آدرس:</th>
                            <td><?php echo nl2br(esc_html($contract->address)); ?></td>
                        </tr>
                        <tr>
                            <th>تاریخ قرارداد:</th>
                            <td><?php echo esc_html($contract->contract_date); ?></td>
                        </tr>
                        <tr>
                            <th>طرح انتخابی:</th>
                            <td><?php echo esc_html($contract->selected_plan); ?></td>
                        </tr>
                        <tr>
                            <th>امضا:</th>
                            <td>
                                <?php if (!empty($contract->signature_data)): ?>
                                    <img src="<?php echo esc_attr($contract->signature_data); ?>" style="border: 1px solid #ccc; max-width: 400px;" />
                                <?php else: ?>
                                    امضا موجود نیست
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
                <?php
            }
        }
    }
    
    public function settings_page() {
        if (isset($_POST['cooperation_contract_text']) && check_admin_referer('cooperation_contract_settings_nonce')) {
            update_option('cooperation_contract_text', wp_kses_post($_POST['cooperation_contract_text']));
            echo '<div class="notice notice-success"><p>تنظیمات با موفقیت ذخیره شد.</p></div>';
        }
        
        $contract_text = get_option('cooperation_contract_text', '');
        ?>
        <div class="wrap" style="direction: rtl;">
            <h1>تنظیمات قرارداد همکاری</h1>
            <form method="post" action="">
                <?php wp_nonce_field('cooperation_contract_settings_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">متن قرارداد</th>
                        <td>
                            <?php
                            wp_editor($contract_text, 'cooperation_contract_text', array(
                                'textarea_rows' => 20,
                                'media_buttons' => false,
                                'teeny' => false,
                                'tinymce' => array(
                                    'directionality' => 'rtl'
                                )
                            ));
                            ?>
                            <p class="description">متن قرارداد خود را در این قسمت وارد کنید.</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button('ذخیره تنظیمات'); ?>
            </form>
        </div>
        <?php
    }
}
