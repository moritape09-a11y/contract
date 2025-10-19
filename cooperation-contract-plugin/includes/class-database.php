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
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            institution_name varchar(255) NOT NULL,
            position varchar(100) NOT NULL,
            address text NOT NULL,
            contract_date varchar(50) NOT NULL,
            selected_plan varchar(50) NOT NULL,
            signature_data longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public static function save_contract($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cooperation_contracts';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'first_name' => sanitize_text_field($data['first_name']),
                'last_name' => sanitize_text_field($data['last_name']),
                'institution_name' => sanitize_text_field($data['institution_name']),
                'position' => sanitize_text_field($data['position']),
                'address' => sanitize_textarea_field($data['address']),
                'contract_date' => sanitize_text_field($data['contract_date']),
                'selected_plan' => sanitize_text_field($data['selected_plan']),
                'signature_data' => $data['signature_data'],
                'user_id' => get_current_user_id(),
                'ip_address' => self::get_user_ip()
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s')
        );
        
        return $result !== false ? $wpdb->insert_id : false;
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
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }
}
