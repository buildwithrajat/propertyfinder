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
            PropertyFinder_Logger::init();
            
            $this->set_locale();
            $this->init_components();
            $this->init_hooks();
        } catch (Exception $e) {
            $this->log_error('Plugin initialization failed', $e);
            add_action('admin_notices', array($this, 'display_init_errors'));
        }
    }

    /**
     * Initialize plugin components
     */
    private function init_components() {
        new PropertyFinder_CPT();
        new PropertyFinder_Importer();
        new PropertyFinder_Agent_Importer();
        new PropertyFinder_Webhook();
        
        if (function_exists('register_block_type')) {
            new PropertyFinder_Blocks();
        }
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        new PropertyFinder_Admin_Hooks();
        new PropertyFinder_Frontend_Hooks();
        new PropertyFinder_Webhook_Hooks();
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
     * Load required dependencies
     */
    private function load_dependencies() {
        // Core includes
        require_once PROPERTYFINDER_PLUGIN_DIR . 'includes/class-propertyfinder-logger.php';
        require_once PROPERTYFINDER_PLUGIN_DIR . 'includes/config.php';
        require_once PROPERTYFINDER_PLUGIN_DIR . 'includes/class-propertyfinder-i18n.php';
        require_once PROPERTYFINDER_PLUGIN_DIR . 'includes/class-propertyfinder-api.php';
        require_once PROPERTYFINDER_PLUGIN_DIR . 'includes/helpers.php';
        
        // CPT and Taxonomies
        require_once PROPERTYFINDER_PLUGIN_DIR . 'includes/cpt/class-cpt-manager.php';
        
        // Importers
        require_once PROPERTYFINDER_PLUGIN_DIR . 'includes/class-propertyfinder-importer.php';
        require_once PROPERTYFINDER_PLUGIN_DIR . 'includes/class-propertyfinder-agent-importer.php';
        
        // Webhook and Blocks
        require_once PROPERTYFINDER_PLUGIN_DIR . 'includes/class-propertyfinder-webhook.php';
        require_once PROPERTYFINDER_PLUGIN_DIR . 'includes/blocks/class-propertyfinder-blocks.php';
        
        // Metabox
        require_once PROPERTYFINDER_PLUGIN_DIR . 'includes/metabox/class-metabox-manager.php';
        
        // Base classes
        require_once PROPERTYFINDER_PLUGIN_DIR . 'app/Models/BaseModel.php';
        require_once PROPERTYFINDER_PLUGIN_DIR . 'app/Controllers/BaseController.php';
        
        // Models
        require_once PROPERTYFINDER_PLUGIN_DIR . 'app/Models/PropertyModel.php';
        require_once PROPERTYFINDER_PLUGIN_DIR . 'app/Models/AgentModel.php';
        
        // Controllers
        require_once PROPERTYFINDER_PLUGIN_DIR . 'app/Controllers/AdminController.php';
        require_once PROPERTYFINDER_PLUGIN_DIR . 'app/Controllers/FrontendController.php';
        
        // Hook classes
        require_once PROPERTYFINDER_PLUGIN_DIR . 'includes/class-admin-hooks.php';
        require_once PROPERTYFINDER_PLUGIN_DIR . 'includes/class-frontend-hooks.php';
        require_once PROPERTYFINDER_PLUGIN_DIR . 'includes/class-webhook-hooks.php';
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
