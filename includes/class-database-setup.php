<?php
/**
 * Database setup functionality
 *
 * @package wdm-cart-abandonment-calculator
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class to handle database operations
 */
class WDM_Database_Setup {
    /**
     * Create the necessary database tables
     */
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $table_name = $wpdb->prefix . 'wdm_abandoned_carts';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            session_id varchar(255) NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            cart_contents longtext,
            cart_total decimal(10,2) DEFAULT 0.00,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            modified_at datetime DEFAULT CURRENT_TIMESTAMP,
            status varchar(50) DEFAULT 'abandoned',
            PRIMARY KEY  (id),
            KEY session_id (session_id),
            KEY user_id (user_id),
            KEY status (status)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
