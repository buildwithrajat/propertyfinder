<?php
/**
 * Helper functions
 *
 * @package PropertyFinder
 * @subpackage Includes
 */

namespace PropertyFinder\Includes;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Get API credentials
 */
function propertyfinder_get_api_credentials() {
    return array(
        'api_key' => get_option('propertyfinder_api_key', ''),
        'api_secret' => get_option('propertyfinder_api_secret', ''),
        'api_endpoint' => get_option('propertyfinder_api_endpoint', 'https://api.propertyfinder.com/v1'),
    );
}

/**
 * Check if API is configured
 */
function propertyfinder_is_api_configured() {
    $credentials = propertyfinder_get_api_credentials();
    return !empty($credentials['api_key']) && !empty($credentials['api_secret']);
}

/**
 * Log error
 */
function propertyfinder_log_error($message, $data = array()) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('PropertyFinder: ' . $message . ' | Data: ' . print_r($data, true));
    }
}

/**
 * Get plugin options
 */
function propertyfinder_get_option($key, $default = false) {
    return get_option('propertyfinder_' . $key, $default);
}

/**
 * Update plugin option
 */
function propertyfinder_update_option($key, $value) {
    return update_option('propertyfinder_' . $key, $value);
}

/**
 * Sanitize input
 */
function propertyfinder_sanitize($input) {
    return sanitize_text_field($input);
}

