<?php
/**
 * Plugin Name: Property Finder
 * Plugin URI: https://github.com/yourusername/propertyfinder
 * Description: A professional property listing and management plugin with MVC architecture
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://your-website.com
 * Text Domain: propertyfinder
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Plugin Constants
define('PROPERTYFINDER_VERSION', '1.0.0');
define('PROPERTYFINDER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PROPERTYFINDER_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'PropertyFinder\\';
    $base_dir = PROPERTYFINDER_PLUGIN_DIR . 'app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Main Plugin Class
class PropertyFinder {
    private static $instance = null;

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init();
    }

    private function init() {
        // Initialize plugin components
        add_action('plugins_loaded', [$this, 'loadTextDomain']);
        add_action('init', [$this, 'initializePlugin']);
    }

    public function loadTextDomain() {
        load_plugin_textdomain('propertyfinder', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function initializePlugin() {
        // Initialize controllers
        // Will be implemented as we add features
    }

    public function activate() {
        // Activation tasks
        flush_rewrite_rules();
    }

    public function deactivate() {
        // Deactivation tasks
        flush_rewrite_rules();
    }
}

// Initialize the plugin
function propertyfinder_init() {
    return PropertyFinder::getInstance();
}

// Hooks for activation and deactivation
register_activation_hook(__FILE__, [PropertyFinder::getInstance(), 'activate']);
register_deactivation_hook(__FILE__, [PropertyFinder::getInstance(), 'deactivate']);

// Start the plugin
propertyfinder_init();