<?php
/**
 * Base Controller
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
 * Base Controller class
 */
class BaseController {
    
    /**
     * Model instance
     */
    protected $model;
    
    /**
     * View path
     */
    protected $view;

    /**
     * Constructor
     */
    public function __construct() {
        // Base controller functionality
    }

    /**
     * Render view with theme override support
     *
     * @param string $view View file name (without .php extension, e.g., 'frontend/property-list')
     * @param array $data Data to pass to view
     * @return string Rendered HTML
     */
    protected function render($view, $data = []) {
        extract($data);
        
        // Check for theme override first
        $theme_template = locate_template(array(
            'propertyfinder/' . $view . '.php',
            'propertyfinder-' . str_replace('/', '-', $view) . '.php',
        ));
        
        // If theme template exists, use it
        if ($theme_template) {
            ob_start();
            include $theme_template;
            return ob_get_clean();
        }
        
        // Fallback to plugin template
        $view_file = PROPERTYFINDER_PLUGIN_DIR . 'app/Views/' . $view . '.php';
        
        if (file_exists($view_file)) {
            ob_start();
            include $view_file;
            return ob_get_clean();
        }
        
        return '';
    }
    
    /**
     * Get template path (for debugging or advanced usage)
     *
     * @param string $view View file name
     * @return string Template path (theme override or plugin)
     */
    protected function get_template_path($view) {
        // Check for theme override first
        $theme_template = locate_template(array(
            'propertyfinder/' . $view . '.php',
            'propertyfinder-' . str_replace('/', '-', $view) . '.php',
        ));
        
        if ($theme_template) {
            return $theme_template;
        }
        
        // Fallback to plugin template
        return PROPERTYFINDER_PLUGIN_DIR . 'app/Views/' . $view . '.php';
    }
}