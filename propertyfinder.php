<?php
/**
 * Plugin Name: PropertyFinder CRM Integration
 * Plugin URI: https://propertyfinder.com
 * Description: Professional CRM integration plugin for PropertyFinder with MVC architecture
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: propertyfinder
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Current plugin version
 */
define('PROPERTYFINDER_VERSION', '1.0.0');

/**
 * Plugin directory path
 */
define('PROPERTYFINDER_PLUGIN_DIR', plugin_dir_path(__FILE__));

/**
 * Plugin directory URL
 */
define('PROPERTYFINDER_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Plugin basename
 */
define('PROPERTYFINDER_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * The code that runs during plugin activation
 */
function activate_propertyfinder() {
    try {
        require_once PROPERTYFINDER_PLUGIN_DIR . 'includes/class-propertyfinder-activator.php';
        PropertyFinder_Activator::activate();
    } catch (Exception $e) {
        // Log error to WordPress debug log
        if (function_exists('error_log')) {
            error_log('PropertyFinder Activation Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
        }
        
        // Try to log to file
        $log_file = WP_CONTENT_DIR . '/propertyfinder-activation-error.log';
        file_put_contents(
            $log_file,
            date('Y-m-d H:i:s') . ' - Activation Failed: ' . $e->getMessage() . "\n" .
            'Stack trace: ' . $e->getTraceAsString() . "\n\n",
            FILE_APPEND
        );
        
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            '<h1>Plugin Activation Failed</h1>' .
            '<p>PropertyFinder plugin could not be activated.</p>' .
            '<p><strong>Error:</strong> ' . esc_html($e->getMessage()) . '</p>' .
            '<p>Please check the error log at: <code>' . esc_html($log_file) . '</code></p>' .
            '<p>PHP Version: ' . PHP_VERSION . '</p>' .
            '<p>WordPress Version: ' . get_bloginfo('version') . '</p>',
            'PropertyFinder Activation Error',
            array('back_link' => true)
        );
    }
}

/**
 * The code that runs during plugin deactivation
 */
function deactivate_propertyfinder() {
    try {
        require_once PROPERTYFINDER_PLUGIN_DIR . 'includes/class-propertyfinder-deactivator.php';
        PropertyFinder_Deactivator::deactivate();
    } catch (Exception $e) {
        error_log('PropertyFinder Deactivation Error: ' . $e->getMessage());
    }
}

register_activation_hook(__FILE__, 'activate_propertyfinder');
register_deactivation_hook(__FILE__, 'deactivate_propertyfinder');

/**
 * Register uninstall hook
 */
function uninstall_propertyfinder() {
    // Only proceed if WP_UNINSTALL_PLUGIN is defined
    if (!defined('WP_UNINSTALL_PLUGIN')) {
        exit;
    }
    
    require_once PROPERTYFINDER_PLUGIN_DIR . 'includes/class-propertyfinder-uninstaller.php';
    PropertyFinder_Uninstaller::uninstall();
}

register_uninstall_hook(__FILE__, 'uninstall_propertyfinder');

// Only run the plugin if not doing activation/uninstall
if (!defined('WP_UNINSTALL_PLUGIN')) {
    add_action('plugins_loaded', 'run_propertyfinder', 10);
}

/**
 * The core plugin class that includes and coordinates different components
 */
class PropertyFinder {

    /**
     * The single instance of the class
     */
    private static $instance = null;
    
    /**
     * Error messages
     */
    private $errors = array();

    /**
     * Get single instance of PropertyFinder
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }

    /**
     * Initialize the plugin
     */
    private function init() {
        try {
            $this->load_dependencies();
            $this->set_locale();
            $this->init_cpt_and_taxonomies();
            $this->init_importer();
            $this->define_admin_hooks();
            $this->define_public_hooks();
        } catch (Exception $e) {
            $this->log_error('Plugin initialization failed', $e);
            add_action('admin_notices', array($this, 'display_init_errors'));
        }
    }
    
    /**
     * Log error
     */
    private function log_error($message, $exception = null) {
        $this->errors[] = $message;
        
        if (function_exists('error_log')) {
            error_log('PropertyFinder: ' . $message);
            if ($exception instanceof Exception) {
                error_log('Exception: ' . $exception->getMessage());
                error_log('Stack trace: ' . $exception->getTraceAsString());
            }
        }
    }
    
    /**
     * Display initialization errors
     */
    public function display_init_errors() {
        if (!empty($this->errors)) {
            echo '<div class="notice notice-error"><p>';
            echo '<strong>PropertyFinder Error:</strong> ';
            echo esc_html(implode(', ', $this->errors));
            echo '</p></div>';
        }
    }

    /**
     * Initialize CPT and Taxonomies
     */
    private function init_cpt_and_taxonomies() {
        new PropertyFinder_CPT();
    }

    /**
     * Initialize Importer
     */
    private function init_importer() {
        new PropertyFinder_Importer();
    }

    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        // Core includes
        require_once PROPERTYFINDER_PLUGIN_DIR . 'includes/class-propertyfinder-i18n.php';
        require_once PROPERTYFINDER_PLUGIN_DIR . 'includes/class-propertyfinder-api.php';
        require_once PROPERTYFINDER_PLUGIN_DIR . 'includes/class-propertyfinder-cpt.php';
        require_once PROPERTYFINDER_PLUGIN_DIR . 'includes/class-propertyfinder-importer.php';
        
        // Base classes
        require_once PROPERTYFINDER_PLUGIN_DIR . 'app/Models/BaseModel.php';
        require_once PROPERTYFINDER_PLUGIN_DIR . 'app/Controllers/BaseController.php';
        
        // Models
        require_once PROPERTYFINDER_PLUGIN_DIR . 'app/Models/PropertyModel.php';
        
        // Controllers
        require_once PROPERTYFINDER_PLUGIN_DIR . 'app/Controllers/AdminController.php';
        require_once PROPERTYFINDER_PLUGIN_DIR . 'app/Controllers/FrontendController.php';
    }

    /**
     * Load language files
     */
    private function set_locale() {
        $this->i18n = new PropertyFinder_i18n();
        add_action('plugins_loaded', array($this->i18n, 'load_plugin_textdomain'));
    }

    /**
     * i18n instance
     */
    private $i18n;

    /**
     * Register all of the hooks related to the admin area
     */
    private function define_admin_hooks() {
        if (!is_admin()) {
            return;
        }
        
        $admin_controller = new \PropertyFinder\Controllers\AdminController();
        add_action('admin_menu', array($admin_controller, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($admin_controller, 'enqueue_admin_assets'));
        add_action('admin_init', array($admin_controller, 'register_settings'));
        
        // Form submission handler - must be called early before any output
        add_action('admin_init', array($admin_controller, 'handle_settings_form'), 1);
        
        add_action('admin_notices', array($admin_controller, 'show_admin_notices'));
        
        // AJAX handlers
        add_action('wp_ajax_propertyfinder_sync', array($admin_controller, 'handle_sync_ajax'));
        add_action('wp_ajax_propertyfinder_test_connection', array($admin_controller, 'handle_test_connection_ajax'));
        add_action('wp_ajax_propertyfinder_import', array($admin_controller, 'handle_import_ajax'));
        add_action('wp_ajax_propertyfinder_sync_all', array($admin_controller, 'handle_sync_all_ajax'));
        
        // Add CPT to admin menu
        add_action('admin_menu', array($this, 'add_listings_to_menu'));
    }

    /**
     * Add listings CPT to admin menu
     */
    public function add_listings_to_menu() {
        global $submenu;
        
        if (isset($submenu['propertyfinder-settings'])) {
            $submenu['propertyfinder-settings'][] = array(
                __('All Listings', 'propertyfinder'),
                'edit_posts',
                'edit.php?post_type=pf_listing',
                '',
            );
        }
    }

    /**
     * Register all of the hooks related to the public area
     */
    private function define_public_hooks() {
        $frontend_controller = new \PropertyFinder\Controllers\FrontendController();
        
        add_action('wp_enqueue_scripts', array($frontend_controller, 'enqueue_frontend_assets'));
        
        // Shortcodes
        add_shortcode('propertyfinder_list', array($frontend_controller, 'shortcode_property_list'));
        add_shortcode('propertyfinder_single', array($frontend_controller, 'shortcode_property_single'));
        
        // AJAX handlers
        add_action('wp_ajax_propertyfinder_get_properties', array($frontend_controller, 'handle_get_properties'));
        add_action('wp_ajax_nopriv_propertyfinder_get_properties', array($frontend_controller, 'handle_get_properties'));
    }
}

/**
 * Initialize the plugin
 */
function run_propertyfinder() {
    // Only run if main class exists
    if (class_exists('PropertyFinder')) {
        return PropertyFinder::get_instance();
    }
}
