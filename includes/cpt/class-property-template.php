<?php
/**
 * Property Template Handler
 *
 * @package PropertyFinder
 * @subpackage CPT
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Property Template Class
 * Handles property single page template loading with header and footer support
 */
class PropertyFinder_Property_Template {

    /**
     * Frontend controller instance
     */
    private $frontend_controller;

    /**
     * Constructor
     */
    public function __construct() {
        $this->frontend_controller = new \PropertyFinder\Controllers\FrontendController();
        add_filter('single_template', array($this, 'load_property_template'));
    }

    /**
     * Load custom template for property single page
     *
     * @param string $template Current template path
     * @return string Template path
     */
    public function load_property_template($template) {
        global $post;
        
        if ($post && $post->post_type === PropertyFinder_Config::get_cpt_name()) {
            // Check for template in theme (WordPress template hierarchy)
            $theme_template = locate_template(array(
                'single-' . PropertyFinder_Config::get_cpt_name() . '.php',
                'propertyfinder/single-property.php',
            ));
            
            if ($theme_template) {
                return $theme_template;
            }
            
            // Use plugin template with header and footer as fallback
            $plugin_template = PROPERTYFINDER_PLUGIN_DIR . 'templates/single-property.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        
        return $template;
    }
}



