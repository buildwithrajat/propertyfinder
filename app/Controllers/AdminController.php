<?php
/**
 * Admin area controller
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
 * Admin Controller class
 */
class AdminController extends BaseController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('PropertyFinder Settings', 'propertyfinder'),
            __('PropertyFinder', 'propertyfinder'),
            'manage_options',
            'propertyfinder-settings',
            array($this, 'render_settings_page'),
            'dashicons-admin-multisite',
            30
        );
        
        add_submenu_page(
            'propertyfinder-settings',
            __('Settings', 'propertyfinder'),
            __('Settings', 'propertyfinder'),
            'manage_options',
            'propertyfinder-settings',
            array($this, 'render_settings_page')
        );
        
        add_submenu_page(
            'propertyfinder-settings',
            __('Listings', 'propertyfinder'),
            __('Listings', 'propertyfinder'),
            'manage_options',
            'propertyfinder-listings',
            array($this, 'render_listings_page')
        );
        
        add_submenu_page(
            'propertyfinder-settings',
            __('Import Listings', 'propertyfinder'),
            __('Import Listings', 'propertyfinder'),
            'manage_options',
            'propertyfinder-import',
            array($this, 'render_import_page')
        );
        
        add_submenu_page(
            'propertyfinder-settings',
            __('Logs', 'propertyfinder'),
            __('Logs', 'propertyfinder'),
            'manage_options',
            'propertyfinder-logs',
            array($this, 'render_logs_page')
        );
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $data = array(
            'page_title' => __('PropertyFinder Settings', 'propertyfinder'),
        );
        
        echo $this->render('admin/settings', $data);
    }

    /**
     * Render listings page
     */
    public function render_listings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $data = array(
            'page_title' => __('PropertyFinder Listings', 'propertyfinder'),
        );
        
        echo $this->render('admin/listings', $data);
    }

    /**
     * Render import page
     */
    public function render_import_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $data = array(
            'page_title' => __('Import Listings', 'propertyfinder'),
        );
        
        echo $this->render('admin/import', $data);
    }

    /**
     * Render logs page
     */
    public function render_logs_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $log_file = WP_CONTENT_DIR . '/debug.log';
        
        $data = array(
            'page_title' => __('PropertyFinder Logs', 'propertyfinder'),
            'log_content' => '',
            'log_file' => $log_file,
        );
        
        echo $this->render('admin/logs', $data);
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only load on plugin pages
        if (strpos($hook, 'propertyfinder') === false) {
            return;
        }
        
        // CSS
        wp_enqueue_style(
            'propertyfinder-admin',
            PROPERTYFINDER_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            PROPERTYFINDER_VERSION
        );
        
        // JavaScript
        wp_enqueue_script(
            'propertyfinder-admin',
            PROPERTYFINDER_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            PROPERTYFINDER_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('propertyfinder-admin', 'propertyfinderAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('propertyfinder_admin_nonce'),
        ));
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('propertyfinder_settings', 'propertyfinder_api_key');
        register_setting('propertyfinder_settings', 'propertyfinder_api_secret');
        register_setting('propertyfinder_settings', 'propertyfinder_api_endpoint');
    }
    
    /**
     * Handle settings form submission
     * This is called before any output to prevent headers already sent error
     */
    public function handle_settings_form() {
        // Only process on our settings page
        if (!isset($_GET['page']) || $_GET['page'] !== 'propertyfinder-settings') {
            return;
        }
        
        if (isset($_POST['propertyfinder_save_settings']) && current_user_can('manage_options')) {
            // Verify nonce
            if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'propertyfinder_settings_nonce')) {
                wp_die(__('Security check failed. Please try again.', 'propertyfinder'));
            }
            
            $api_key = isset($_POST['propertyfinder_api_key']) ? sanitize_text_field($_POST['propertyfinder_api_key']) : '';
            $api_secret = isset($_POST['propertyfinder_api_secret']) ? sanitize_text_field($_POST['propertyfinder_api_secret']) : '';
            $api_endpoint = isset($_POST['propertyfinder_api_endpoint']) ? esc_url_raw($_POST['propertyfinder_api_endpoint']) : 'https://atlas.propertyfinder.com/v1';
            
            update_option('propertyfinder_api_key', $api_key);
            update_option('propertyfinder_api_secret', $api_secret);
            update_option('propertyfinder_api_endpoint', $api_endpoint);
            
            // Clear cached token to force new authentication
            delete_transient('propertyfinder_access_token');
            
            // Set transient for success message
            set_transient('propertyfinder_settings_saved', true, 30);
            
            // Redirect to prevent form resubmission
            wp_safe_redirect(add_query_arg('settings-updated', 'true', admin_url('admin.php?page=propertyfinder-settings')));
            exit;
        }
    }

    /**
     * Show admin notices
     */
    public function show_admin_notices() {
        // Settings saved notice
        if (get_transient('propertyfinder_settings_saved')) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e('Settings saved successfully!', 'propertyfinder'); ?></p>
            </div>
            <?php
            delete_transient('propertyfinder_settings_saved');
        }
        
        // Activation notice
        if (get_transient('propertyfinder_activation_notice')) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e('PropertyFinder plugin has been activated successfully!', 'propertyfinder'); ?></p>
            </div>
            <?php
            delete_transient('propertyfinder_activation_notice');
        }
    }

    /**
     * Handle sync AJAX request
     */
    public function handle_sync_ajax() {
        check_ajax_referer('propertyfinder_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'propertyfinder')));
        }
        
        // Import first page of listings from PropertyFinder API
        // Use global class (not namespaced)
        $importer = new \PropertyFinder_Importer();
        $params = array(
            'page' => 1,
            'perPage' => 50,
            'draft' => false, // Get live listings
            'archived' => false
        );
        
        error_log('PropertyFinder: Starting import with params: ' . print_r($params, true));
        
        $results = $importer->import_listings($params);
        
        if ($results['success']) {
            error_log('PropertyFinder: Import successful - ' . print_r($results, true));
            wp_send_json_success(array(
                'message' => sprintf(
                    __('Import completed: %d imported, %d updated, %d skipped', 'propertyfinder'),
                    $results['imported'],
                    $results['updated'],
                    $results['skipped']
                ),
                'results' => $results
            ));
        } else {
            error_log('PropertyFinder: Import failed - ' . print_r($results, true));
            wp_send_json_error(array(
                'message' => isset($results['message']) ? $results['message'] : __('Import failed.', 'propertyfinder'),
                'results' => $results
            ));
        }
    }

    /**
     * Handle test connection AJAX request
     */
    public function handle_test_connection_ajax() {
        check_ajax_referer('propertyfinder_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'propertyfinder')));
        }
        
        // Get API credentials
        $api_key = get_option('propertyfinder_api_key', '');
        $api_secret = get_option('propertyfinder_api_secret', '');
        $api_endpoint = get_option('propertyfinder_api_endpoint', 'https://atlas.propertyfinder.com/v1');
        
        // Log credentials (without secret)
        error_log('PropertyFinder: Testing connection with endpoint: ' . $api_endpoint);
        error_log('PropertyFinder: API Key: ' . substr($api_key, 0, 10) . '...');
        
        if (empty($api_key) || empty($api_secret)) {
            wp_send_json_error(array(
                'message' => __('API credentials not configured. Please save your settings first.', 'propertyfinder')
            ));
        }
        
        // Use global class (not namespaced)
        $api = new \PropertyFinder_API();
        
        // Try to get access token
        $token = $api->get_access_token();
        
        if ($token) {
            error_log('PropertyFinder: Connection test successful - Token obtained');
            wp_send_json_success(array(
                'message' => __('Connection successful! Access token obtained successfully.', 'propertyfinder'),
                'token' => substr($token, 0, 20) . '...',
                'details' => 'Access token expires in 30 minutes'
            ));
        } else {
            // Get detailed error from last API call
            $error_log = error_get_last();
            $error_message = __('Connection failed. Please check your API credentials and WordPress debug log for details.', 'propertyfinder');
            
            error_log('PropertyFinder: Connection test failed');
            
            wp_send_json_error(array(
                'message' => $error_message,
                'debug' => 'Check debug.log file for error details'
            ));
        }
    }

    /**
     * Handle import AJAX request (delegates to importer)
     */
    public function handle_import_ajax() {
        // This will be handled by PropertyFinder_Importer class
        return;
    }

    /**
     * Handle sync all AJAX request (delegates to importer)
     */
    public function handle_sync_all_ajax() {
        // This will be handled by PropertyFinder_Importer class
        return;
    }
}

