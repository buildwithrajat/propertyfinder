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
     * Render view
     *
     * @param string $view View file name (without .php extension)
     * @param array $data Data to pass to view
     * @return string Rendered HTML
     */
    protected function render($view, $data = []) {
        extract($data);
        $view_file = PROPERTYFINDER_PLUGIN_DIR . 'app/Views/' . $view . '.php';
        
        if (file_exists($view_file)) {
            ob_start();
            include $view_file;
            return ob_get_clean();
        }
        
        return '';
    }
}