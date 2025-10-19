<?php
/**
 * Plugin Name: قرارداد همکاری
 * Plugin URI: https://example.com/
 * Description: افزونه مدیریت قراردادهای همکاری با امضای دیجیتال
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com/
 * Text Domain: cooperation-contract
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
if (!defined('COOPERATION_CONTRACT_VERSION')) {
    define('COOPERATION_CONTRACT_VERSION', '3.0.1');
}
if (!defined('COOPERATION_CONTRACT_PLUGIN_DIR')) {
    define('COOPERATION_CONTRACT_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('COOPERATION_CONTRACT_PLUGIN_URL')) {
    define('COOPERATION_CONTRACT_PLUGIN_URL', plugin_dir_url(__FILE__));
}

// Include required files
require_once COOPERATION_CONTRACT_PLUGIN_DIR . 'includes/class-database.php';
require_once COOPERATION_CONTRACT_PLUGIN_DIR . 'includes/class-admin.php';
require_once COOPERATION_CONTRACT_PLUGIN_DIR . 'includes/class-frontend.php';

// Activation hook
register_activation_hook(__FILE__, 'cooperation_contract_activate');
function cooperation_contract_activate() {
    Cooperation_Contract_Database::create_tables();
    flush_rewrite_rules();
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'cooperation_contract_deactivate');
function cooperation_contract_deactivate() {
    flush_rewrite_rules();
}

// Initialize plugin
add_action('plugins_loaded', 'cooperation_contract_init');
function cooperation_contract_init() {
    // Load text domain for translations
    load_plugin_textdomain('cooperation-contract', false, dirname(plugin_basename(__FILE__)) . '/languages');
    
    // Initialize admin
    new Cooperation_Contract_Admin();
    
    // Initialize frontend
    new Cooperation_Contract_Frontend();
}

// Add shortcode for contract form
add_shortcode('cooperation_contract', 'cooperation_contract_shortcode');
function cooperation_contract_shortcode($atts) {
    return Cooperation_Contract_Frontend::render_contract_form();
}
