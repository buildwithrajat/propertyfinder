<?php
/**
 * PropertyFinder Plugin Configuration
 *
 * Global configuration file for managing all plugin settings
 *
 * @package PropertyFinder
 * @subpackage Includes
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Load meta fields config
require_once dirname(__FILE__) . '/config/class-meta-fields-config.php';
require_once dirname(__FILE__) . '/config/class-agent-meta-fields-config.php';

/**
 * PropertyFinder Configuration Class
 */
class PropertyFinder_Config {

    /**
     * Get configuration value
     *
     * @param string $key Configuration key
     * @param mixed $default Default value if not set
     * @return mixed Configuration value
     */
    public static function get($key, $default = null) {
        $config = self::get_all();
        return isset($config[$key]) ? $config[$key] : $default;
    }

    /**
     * Get all configuration
     *
     * @return array All configuration values
     */
    public static function get_all() {
        return array(
            // Custom Post Type
            'cpt_name' => 'pf_listing',
            'cpt_singular' => 'Property',
            'cpt_plural' => 'Properties',
            'cpt_slug' => 'listing',
            
            // Agent Custom Post Type
            'agent_cpt_name' => 'pf_agent',
            'agent_cpt_singular' => 'Agent',
            'agent_cpt_plural' => 'Agents',
            'agent_cpt_slug' => 'agent',
            
            // Taxonomies
            'taxonomy_category' => 'pf_category',
            'taxonomy_property_type' => 'pf_property_type',
            'taxonomy_amenity' => 'pf_amenity',
            'taxonomy_location' => 'pf_location',
            'taxonomy_transaction_type' => 'pf_transaction_type',
            'taxonomy_furnishing_status' => 'pf_furnishing_status',
            
            // API Settings
            'api_endpoint' => get_option('propertyfinder_api_endpoint', 'https://atlas.propertyfinder.com/v1'),
            'api_key' => get_option('propertyfinder_api_key', ''),
            'api_secret' => get_option('propertyfinder_api_secret', ''),
            
            // Webhook Settings
            'webhook_secret' => get_option('propertyfinder_webhook_secret', ''),
            'webhook_url' => home_url('/wp-json/propertyfinder/v1/webhook'),
            'webhook_url_alt' => home_url('/propertyfinder-webhook'),
            
            // Sync Settings
            'sync_enabled' => get_option('propertyfinder_sync_enabled', false),
            'sync_interval' => get_option('propertyfinder_sync_interval', 'hourly'),
            'sync_time' => get_option('propertyfinder_sync_time', '00:00'),
            'sync_cron_hook' => 'propertyfinder_sync_listings',
            
            // Agent Sync Settings
            'agent_sync_enabled' => get_option('propertyfinder_agent_sync_enabled', false),
            'agent_sync_interval' => get_option('propertyfinder_agent_sync_interval', 'hourly'),
            'agent_sync_time' => get_option('propertyfinder_agent_sync_time', '00:00'),
            'agent_sync_cron_hook' => 'propertyfinder_sync_agents',
            
            // Meta Field Prefixes
            'meta_prefix' => '_pf_',
            
            // Database Table
            'db_table' => 'propertyfinder_properties',
            
            // Default Values
            'default_per_page' => 50,
            'default_post_status' => 'publish',
            
            // Cache Settings
            'token_cache_time' => 1740, // 29 minutes (30 min - 1 min buffer)
            
            // Rate Limiting
            'rate_limit_token' => 60, // requests per minute for token endpoint
            'rate_limit_general' => 650, // requests per minute for other endpoints
            
            // Cron Intervals
            'cron_interval_4hours' => 'propertyfinder_4hours',
            'cron_interval_6hours' => 'propertyfinder_6hours',
            
            // Option Names
            'option_api_key' => 'propertyfinder_api_key',
            'option_api_secret' => 'propertyfinder_api_secret',
            'option_api_endpoint' => 'propertyfinder_api_endpoint',
            'option_webhook_secret' => 'propertyfinder_webhook_secret',
            'option_sync_enabled' => 'propertyfinder_sync_enabled',
            'option_sync_interval' => 'propertyfinder_sync_interval',
            'option_sync_time' => 'propertyfinder_sync_time',
            'option_version' => 'propertyfinder_installed_version',
            
            // Transient Names
            'transient_token' => 'propertyfinder_access_token',
            
            // REST API Routes
            'rest_namespace' => 'propertyfinder/v1',
            'rest_webhook_route' => '/webhook',
            
            // Admin Pages
            'admin_slug' => 'propertyfinder-settings',
            'admin_menu_title' => 'PropertyFinder',
            'admin_menu_icon' => 'dashicons-admin-multisite',
            'admin_menu_position' => 30,
            
            // Capabilities
            'capability' => 'manage_options',
            
            // Nonce Names
            'nonce_name' => 'propertyfinder_admin_nonce',
            'nonce_settings' => 'propertyfinder_settings_nonce',
        );
    }

    /**
     * Get CPT name
     *
     * @return string CPT name
     */
    public static function get_cpt_name() {
        return self::get('cpt_name', 'pf_listing');
    }

    /**
     * Get Agent CPT name
     *
     * @return string Agent CPT name
     */
    public static function get_agent_cpt_name() {
        return self::get('agent_cpt_name', 'pf_agent');
    }

    /**
     * Get API endpoint
     *
     * @return string API endpoint URL
     */
    public static function get_api_endpoint() {
        return self::get('api_endpoint', 'https://atlas.propertyfinder.com/v1');
    }

    /**
     * Get API key
     *
     * @return string API key
     */
    public static function get_api_key() {
        return self::get('api_key', '');
    }

    /**
     * Get API secret
     *
     * @return string API secret
     */
    public static function get_api_secret() {
        return self::get('api_secret', '');
    }

    /**
     * Get taxonomy name
     *
     * @param string $type Taxonomy type
     * @return string Taxonomy name
     */
    public static function get_taxonomy($type) {
        $taxonomies = array(
            'category' => 'pf_category',
            'property_type' => 'pf_property_type',
            'amenity' => 'pf_amenity',
            'location' => 'pf_location',
            'transaction_type' => 'pf_transaction_type',
            'furnishing_status' => 'pf_furnishing_status',
        );
        
        return isset($taxonomies[$type]) ? $taxonomies[$type] : '';
    }

    /**
     * Get meta field name
     *
     * @param string $field Field name (without prefix)
     * @return string Full meta field name
     */
    public static function get_meta_field($field) {
        return self::get('meta_prefix', '_pf_') . $field;
    }

    /**
     * Get webhook URL
     *
     * @param bool $use_rest Use REST API URL or alternative
     * @return string Webhook URL
     */
    public static function get_webhook_url($use_rest = true) {
        if ($use_rest) {
            return self::get('webhook_url', home_url('/wp-json/propertyfinder/v1/webhook'));
        }
        return self::get('webhook_url_alt', home_url('/propertyfinder-webhook'));
    }

    /**
     * Get option name
     *
     * @param string $option Option key
     * @return string Full option name
     */
    public static function get_option_name($option) {
        $options = array(
            'api_key' => 'propertyfinder_api_key',
            'api_secret' => 'propertyfinder_api_secret',
            'api_endpoint' => 'propertyfinder_api_endpoint',
            'webhook_secret' => 'propertyfinder_webhook_secret',
            'sync_enabled' => 'propertyfinder_sync_enabled',
            'sync_interval' => 'propertyfinder_sync_interval',
            'sync_time' => 'propertyfinder_sync_time',
        );
        
        return isset($options[$option]) ? $options[$option] : 'propertyfinder_' . $option;
    }

    /**
     * Get sync interval options
     *
     * @return array Sync interval options
     */
    public static function get_sync_intervals() {
        return array(
            'hourly' => __('Every Hour', 'propertyfinder'),
            '4hours' => __('Every 4 Hours', 'propertyfinder'),
            '6hours' => __('Every 6 Hours', 'propertyfinder'),
            'daily' => __('24 Hours', 'propertyfinder'),
            'daily_12am' => __('Daily at 12:00 AM', 'propertyfinder'),
            'weekly' => __('Weekly', 'propertyfinder'),
        );
    }

    /**
     * Get webhook event types
     *
     * @return array Webhook event types
     */
    public static function get_webhook_events() {
        return array(
            'listing.published' => __('Listing Published', 'propertyfinder'),
            'listing.unpublished' => __('Listing Unpublished', 'propertyfinder'),
            'lead.created' => __('Lead Created', 'propertyfinder'),
            'lead.updated' => __('Lead Updated', 'propertyfinder'),
            'lead.assigned' => __('Lead Assigned', 'propertyfinder'),
            'user.created' => __('User Created', 'propertyfinder'),
            'user.updated' => __('User Updated', 'propertyfinder'),
            'user.deleted' => __('User Deleted', 'propertyfinder'),
            'user.activated' => __('User Activated', 'propertyfinder'),
            'user.deactivated' => __('User Deactivated', 'propertyfinder'),
            'publicProfile.verification.approved' => __('Public Profile Verification Approved', 'propertyfinder'),
            'publicProfile.verification.rejected' => __('Public Profile Verification Rejected', 'propertyfinder'),
        );
    }
}

/**
 * Helper function to get config value
 *
 * @param string $key Configuration key
 * @param mixed $default Default value
 * @return mixed Configuration value
 */
function propertyfinder_config($key, $default = null) {
    return PropertyFinder_Config::get($key, $default);
}

/**
 * Helper function to get CPT name
 *
 * @return string CPT name
 */
function propertyfinder_get_cpt_name() {
    return PropertyFinder_Config::get_cpt_name();
}

/**
 * Helper function to get taxonomy name
 *
 * @param string $type Taxonomy type
 * @return string Taxonomy name
 */
function propertyfinder_get_taxonomy($type) {
    return PropertyFinder_Config::get_taxonomy($type);
}

/**
 * Helper function to get meta field name
 *
 * @param string $field Field name
 * @return string Full meta field name
 */
function propertyfinder_get_meta_field($field) {
    return PropertyFinder_Config::get_meta_field($field);
}

