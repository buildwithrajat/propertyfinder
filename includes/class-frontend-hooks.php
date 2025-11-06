<?php
/**
 * Frontend Hooks Handler
 *
 * @package PropertyFinder
 * @subpackage Includes
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Frontend Hooks Class
 * Handles all frontend area hooks and registrations
 */
class PropertyFinder_Frontend_Hooks {

    /**
     * Frontend controller instance
     */
    private $frontend_controller;

    /**
     * Constructor
     */
    public function __construct() {
        $this->frontend_controller = new \PropertyFinder\Controllers\FrontendController();
        $this->register_hooks();
    }

    /**
     * Register all frontend hooks
     */
    private function register_hooks() {
        // Enqueue assets
        add_action('wp_enqueue_scripts', array($this->frontend_controller, 'enqueue_frontend_assets'));
        
        // Shortcodes
        add_shortcode('propertyfinder_list', array($this->frontend_controller, 'shortcode_property_list'));
        add_shortcode('propertyfinder_single', array($this->frontend_controller, 'shortcode_property_single'));
        
        // AJAX handlers (both logged in and not logged in)
        add_action('wp_ajax_propertyfinder_get_properties', array($this->frontend_controller, 'handle_get_properties'));
        add_action('wp_ajax_nopriv_propertyfinder_get_properties', array($this->frontend_controller, 'handle_get_properties'));
    }
}

