<?php
/**
 * Webhook Hooks Handler
 *
 * @package PropertyFinder
 * @subpackage Includes
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Webhook Hooks Class
 * Handles webhook-related hooks
 */
class PropertyFinder_Webhook_Hooks {

    /**
     * Constructor
     */
    public function __construct() {
        $this->register_hooks();
    }

    /**
     * Register webhook hooks
     */
    private function register_hooks() {
        // Add webhook query var
        add_filter('query_vars', array($this, 'add_webhook_query_var'));
    }

    /**
     * Add webhook query var
     */
    public function add_webhook_query_var($vars) {
        $vars[] = 'propertyfinder_webhook';
        return $vars;
    }
}



