<?php
/**
 * Fired during plugin uninstall
 *
 * @package PropertyFinder
 * @subpackage Includes
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Only allow uninstall from WordPress
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

/**
 * Uninstaller class
 */
class PropertyFinder_Uninstaller {

    /**
     * Uninstall the plugin
     */
    public static function uninstall() {
        // Check if user has permission
        if (!current_user_can('activate_plugins')) {
            return;
        }

        // Option to keep data or delete everything
        $delete_all_data = get_option('propertyfinder_delete_on_uninstall', false);

        if ($delete_all_data) {
            // Delete database tables
            self::delete_database_tables();
            
            // Delete all options
            self::delete_plugin_options();
            
            // Delete all transients
            self::delete_all_transients();
        }
        
        // Always remove scheduled events
        self::clear_scheduled_events();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Delete database tables
     */
    private static function delete_database_tables() {
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'propertyfinder_properties',
        );
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
    }

    /**
     * Delete all plugin options
     */
    private static function delete_plugin_options() {
        global $wpdb;
        
        $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'propertyfinder_%'"
        );
    }

    /**
     * Delete all transients
     */
    private static function delete_all_transients() {
        global $wpdb;
        
        $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_propertyfinder_%'"
        );
        
        $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_propertyfinder_%'"
        );
        
        $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'site_transient_propertyfinder_%'"
        );
        
        $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'site_transient_timeout_propertyfinder_%'"
        );
    }

    /**
     * Clear all scheduled events
     */
    private static function clear_scheduled_events() {
        $timestamp = wp_next_scheduled('propertyfinder_sync_data');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'propertyfinder_sync_data');
        }
        
        $timestamp = wp_next_scheduled('propertyfinder_cleanup_data');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'propertyfinder_cleanup_data');
        }
    }
}

