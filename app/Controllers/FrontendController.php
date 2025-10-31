<?php
/**
 * Frontend area controller
 *
 * @package PropertyFinder
 * @subpackage Controllers
 */

namespace PropertyFinder\Controllers;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Frontend Controller class
 */
class FrontendController extends BaseController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // CSS
        wp_enqueue_style(
            'propertyfinder-frontend',
            PROPERTYFINDER_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            PROPERTYFINDER_VERSION
        );
        
        // JavaScript
        wp_enqueue_script(
            'propertyfinder-frontend',
            PROPERTYFINDER_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            PROPERTYFINDER_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('propertyfinder-frontend', 'propertyfinderFrontend', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('propertyfinder_frontend_nonce'),
        ));
    }

    /**
     * Property list shortcode
     */
    public function shortcode_property_list($atts) {
        $atts = shortcode_atts(array(
            'limit' => 10,
            'order' => 'desc',
            'status' => 'active',
        ), $atts);
        
        $data = array(
            'properties' => array(), // Load from model
            'atts' => $atts,
        );
        
        return $this->render('frontend/property-list', $data);
    }

    /**
     * Single property shortcode
     */
    public function shortcode_property_single($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts);
        
        if (empty($atts['id'])) {
            return __('Property ID is required.', 'propertyfinder');
        }
        
        // Load property from model
        $property = null; // Load from model
        
        $data = array(
            'property' => $property,
        );
        
        return $this->render('frontend/property-single', $data);
    }

    /**
     * Handle get properties AJAX request
     */
    public function handle_get_properties() {
        check_ajax_referer('propertyfinder_frontend_nonce', 'nonce');
        
        // Get properties from model
        $properties = array(); // Load from model
        
        wp_send_json_success(array(
            'properties' => $properties,
            'total' => count($properties),
        ));
    }
}

