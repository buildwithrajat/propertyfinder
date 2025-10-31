<?php
/**
 * Fired during plugin activation
 *
 * @package PropertyFinder
 * @subpackage Includes
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Activation class
 */
class PropertyFinder_Activator {

    /**
     * Activate the plugin
     */
    public static function activate() {
        try {
            // Initialize logger
            self::init_logger();
            
            if (class_exists('PropertyFinder_Logger')) {
                PropertyFinder_Logger::info('Starting plugin activation');
            }
            
            // Check requirements
            self::check_requirements();
            
            // Set activation flag
            add_option('propertyfinder_installed_version', PROPERTYFINDER_VERSION);
            
            // Create database tables if needed
            self::create_database_tables();
            
            // Set default options
            self::set_default_options();
            
            // Flush rewrite rules
            flush_rewrite_rules();
            
            // Trigger activation notice
            set_transient('propertyfinder_activation_notice', true, 30);
            
            if (class_exists('PropertyFinder_Logger')) {
                PropertyFinder_Logger::log_activation();
                PropertyFinder_Logger::info('Plugin activation completed successfully');
            }
            
        } catch (Exception $e) {
            if (class_exists('PropertyFinder_Logger')) {
                PropertyFinder_Logger::fatal('Plugin activation failed', array(
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ));
            }
            throw $e;
        }
    }

    /**
     * Initialize logger
     */
    private static function init_logger() {
        require_once PROPERTYFINDER_PLUGIN_DIR . 'includes/class-propertyfinder-logger.php';
        PropertyFinder_Logger::init();
    }

    /**
     * Check system requirements
     */
    private static function check_requirements() {
        global $wp_version;
        
        // Check PHP version
        if (version_compare(PHP_VERSION, '7.2', '<')) {
            throw new Exception('PropertyFinder requires PHP 7.2 or higher. Current version: ' . PHP_VERSION);
        }
        
        // Check WordPress version
        if (version_compare($wp_version, '5.0', '<')) {
            throw new Exception('PropertyFinder requires WordPress 5.0 or higher. Current version: ' . $wp_version);
        }
        
        if (class_exists('PropertyFinder_Logger')) {
            PropertyFinder_Logger::debug('System requirements check passed', array(
                'php_version' => PHP_VERSION,
                'wp_version' => $wp_version,
            ));
        }
    }

    /**
     * Create database tables
     */
    private static function create_database_tables() {
        global $wpdb;
        
        try {
            $charset_collate = $wpdb->get_charset_collate();
            
            // Example: Create a table for storing property data
            $table_name = $wpdb->prefix . 'propertyfinder_properties';
            
            $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                property_id varchar(255) NOT NULL,
                title varchar(255) NOT NULL,
                data longtext,
                status varchar(50) DEFAULT 'active',
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY  (id),
                KEY property_id (property_id),
                KEY status (status)
            ) $charset_collate;";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            $result = dbDelta($sql);
            
            if (class_exists('PropertyFinder_Logger')) {
                PropertyFinder_Logger::debug('Database tables created', array('table' => $table_name));
            }
            
        } catch (Exception $e) {
            if (class_exists('PropertyFinder_Logger')) {
                PropertyFinder_Logger::error('Failed to create database tables', array(
                    'error' => $e->getMessage(),
                ));
            }
            throw $e;
        }
    }

    /**
     * Set default options
     */
    private static function set_default_options() {
        try {
            $default_options = array(
                'api_key' => '',
                'api_secret' => '',
                'api_endpoint' => 'https://atlas.propertyfinder.com/v1',
                'sync_interval' => 3600, // 1 hour in seconds
                'auto_sync_enabled' => false,
                'log_level' => 'error',
                'notify_on_fatal' => false,
            );
            
            foreach ($default_options as $key => $value) {
                if (get_option('propertyfinder_' . $key) === false) {
                    add_option('propertyfinder_' . $key, $value);
                }
            }
            
            if (class_exists('PropertyFinder_Logger')) {
                PropertyFinder_Logger::debug('Default options set', array('options' => array_keys($default_options)));
            }
            
        } catch (Exception $e) {
            if (class_exists('PropertyFinder_Logger')) {
                PropertyFinder_Logger::error('Failed to set default options', array(
                    'error' => $e->getMessage(),
                ));
            }
        }
    }
}

