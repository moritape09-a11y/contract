<?php
/**
 * Database Fix Script
 * 
 * اگر خطای دیتابیس دارید، این فایل را یکبار اجرا کنید
 * بعد از اجرا، این فایل را حذف کنید
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check permissions
if (!current_user_can('manage_options')) {
    die('شما مجوز اجرای این اسکریپت را ندارید.');
}

global $wpdb;
$table_name = $wpdb->prefix . 'cooperation_contracts';

echo '<h1>اصلاح جدول دیتابیس</h1>';
echo '<hr>';

// Check if table exists
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;

if ($table_exists) {
    echo '<p>✅ جدول موجود است: ' . $table_name . '</p>';
    
    // Check columns
    $columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name");
    echo '<h3>ستون‌های موجود:</h3><ul>';
    foreach ($columns as $column) {
        echo '<li>' . $column->Field . ' (' . $column->Type . ')</li>';
    }
    echo '</ul>';
    
    // Drop and recreate
    echo '<p><strong>حذف و ایجاد مجدد جدول...</strong></p>';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
} else {
    echo '<p>⚠️ جدول موجود نیست. در حال ایجاد...</p>';
}

// Create table
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE $table_name (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    first_name varchar(100) NOT NULL DEFAULT '',
    last_name varchar(100) NOT NULL DEFAULT '',
    national_id varchar(10) NOT NULL DEFAULT '',
    institution_name varchar(255) NOT NULL DEFAULT '',
    position varchar(100) NOT NULL DEFAULT '',
    address text NOT NULL,
    contract_date varchar(100) NOT NULL DEFAULT '',
    selected_plan varchar(50) NOT NULL DEFAULT '',
    signature_data longtext NOT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    user_id bigint(20) DEFAULT NULL,
    ip_address varchar(45) DEFAULT NULL,
    PRIMARY KEY  (id),
    KEY national_id (national_id),
    KEY created_at (created_at)
) $charset_collate;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);

// Verify
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;

if ($table_exists) {
    echo '<h2 style="color: green;">✅ موفقیت! جدول با موفقیت ایجاد شد.</h2>';
    
    // Show new columns
    $columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name");
    echo '<h3>ستون‌های جدید:</h3><ul>';
    foreach ($columns as $column) {
        echo '<li>' . $column->Field . ' (' . $column->Type . ')</li>';
    }
    echo '</ul>';
    
    echo '<hr>';
    echo '<p><strong>حالا می‌توانید فرم قرارداد را تست کنید.</strong></p>';
    echo '<p style="color: red;"><strong>مهم: این فایل (fix-database.php) را حذف کنید!</strong></p>';
} else {
    echo '<h2 style="color: red;">❌ خطا! جدول ایجاد نشد.</h2>';
    echo '<p>خطای MySQL: ' . $wpdb->last_error . '</p>';
}
