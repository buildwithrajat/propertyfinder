<?php
namespace PropertyFinder\Controllers;

class BaseController {
    protected $model;
    protected $view;

    public function __construct() {
        // Base controller functionality
    }

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