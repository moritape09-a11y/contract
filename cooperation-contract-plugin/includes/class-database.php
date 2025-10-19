<?php
/**
 * Database class for managing contract data
 */

if (!defined('ABSPATH')) {
    exit;
}

class Cooperation_Contract_Database {
    
    public static function create_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cooperation_contracts';
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
        
        // Check if table was created
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            error_log('Failed to create table: ' . $table_name);
        }
    }
    
    public static function save_contract($data) {
        global $wpdb;
        
        // Validate data array
        if (!is_array($data) || empty($data)) {
            error_log('CC Error: Invalid contract data provided');
            return false;
        }
        
        $table_name = $wpdb->prefix . 'cooperation_contracts';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            error_log('CC Error: Table does not exist: ' . $table_name);
            // Try to create it
            self::create_tables();
        }
        
        // Prepare data with proper sanitization
        $insert_data = array(
            'first_name' => isset($data['first_name']) ? sanitize_text_field($data['first_name']) : '',
            'last_name' => isset($data['last_name']) ? sanitize_text_field($data['last_name']) : '',
            'national_id' => isset($data['national_id']) ? sanitize_text_field($data['national_id']) : '',
            'institution_name' => isset($data['institution_name']) ? sanitize_text_field($data['institution_name']) : '',
            'position' => isset($data['position']) ? sanitize_text_field($data['position']) : '',
            'address' => isset($data['address']) ? sanitize_textarea_field($data['address']) : '',
            'contract_date' => isset($data['contract_date']) ? sanitize_text_field($data['contract_date']) : '',
            'selected_plan' => isset($data['selected_plan']) ? sanitize_text_field($data['selected_plan']) : '',
            'signature_data' => isset($data['signature_data']) ? $data['signature_data'] : '',
            'user_id' => get_current_user_id() ? get_current_user_id() : 0,
            'ip_address' => self::get_user_ip()
        );
        
        // Log data being inserted (for debugging)
        error_log('CC Debug: Attempting to insert contract for: ' . $insert_data['first_name'] . ' ' . $insert_data['last_name']);
        
        // Validate that all required fields are present
        $required_fields = array('first_name', 'last_name', 'national_id', 'institution_name', 'position', 'address', 'contract_date', 'selected_plan', 'signature_data');
        foreach ($required_fields as $field) {
            if (empty($insert_data[$field])) {
                error_log("CC Error: Empty required field: $field");
                return false;
            }
        }
        
        // Insert with proper format specifiers
        $result = $wpdb->insert(
            $table_name,
            $insert_data,
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s')
        );
        
        if ($result === false) {
            error_log('CC Error: Database insert failed. Error: ' . $wpdb->last_error);
            error_log('CC Error: Last query: ' . $wpdb->last_query);
            return false;
        }
        
        $insert_id = $wpdb->insert_id;
        error_log('CC Success: Contract saved with ID: ' . $insert_id);
        
        return $insert_id;
    }
    
    public static function get_contracts($limit = 20, $offset = 0) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cooperation_contracts';
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $limit,
                $offset
            )
        );
    }
    
    public static function get_contract($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cooperation_contracts';
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE id = %d",
                $id
            )
        );
    }
    
    public static function delete_contract($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cooperation_contracts';
        
        return $wpdb->delete(
            $table_name,
            array('id' => $id),
            array('%d')
        );
    }
    
    private static function get_user_ip() {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // X-Forwarded-For can contain multiple IPs, get the first one
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
            
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                $ip = '';
            }
        }
        
        if (empty($ip) && !empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        // Validate final IP
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $ip = '0.0.0.0';
        }
        
        return sanitize_text_field($ip);
    }
}
