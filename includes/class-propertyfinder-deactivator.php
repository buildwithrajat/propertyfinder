<?php
/**
 * Fired during plugin deactivation
 *
 * @package PropertyFinder
 * @subpackage Includes
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Deactivation class
 */
class PropertyFinder_Deactivator {

    /**
     * Deactivate the plugin
     */
    public static function deactivate() {
        // Clear scheduled events
        self::clear_scheduled_events();
        
        // Remove transients
        self::remove_transients();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Set deactivation flag
        update_option('propertyfinder_deactivated', current_time('mysql'));
    }

    /**
     * Clear all scheduled events
     */
    private static function clear_scheduled_events() {
        // Clear any cron jobs
        $cron_hooks = array(
            'propertyfinder_sync_data',
            'propertyfinder_cleanup_data',
        );
        
        foreach ($cron_hooks as $hook) {
            $timestamp = wp_next_scheduled($hook);
            if ($timestamp) {
                wp_unschedule_event($timestamp, $hook);
            }
        }
    }

    /**
     * Remove transients
     */
    private static function remove_transients() {
        global $wpdb;
        
        $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_propertyfinder_%'"
        );
        
        $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_propertyfinder_%'"
        );
    }
}

