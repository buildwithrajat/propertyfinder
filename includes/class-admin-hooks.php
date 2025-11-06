<?php
/**
 * Admin Hooks Handler
 *
 * @package PropertyFinder
 * @subpackage Includes
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Admin Hooks Class
 * Handles all admin area hooks and registrations
 */
class PropertyFinder_Admin_Hooks {

    /**
     * Admin controller instance
     */
    private $admin_controller;

    /**
     * Constructor
     */
    public function __construct() {
        if (!is_admin()) {
            return;
        }

        $this->admin_controller = new \PropertyFinder\Controllers\AdminController();
        $this->register_hooks();
    }

    /**
     * Register all admin hooks
     */
    private function register_hooks() {
        // Admin menu and pages
        add_action('admin_menu', array($this->admin_controller, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this->admin_controller, 'enqueue_admin_assets'));
        add_action('admin_init', array($this->admin_controller, 'register_settings'));
        add_action('admin_init', array($this->admin_controller, 'handle_settings_form'), 1);
        add_action('admin_notices', array($this->admin_controller, 'show_admin_notices'));

        // AJAX handlers
        add_action('wp_ajax_propertyfinder_sync', array($this->admin_controller, 'handle_sync_ajax'));
        add_action('wp_ajax_propertyfinder_test_connection', array($this->admin_controller, 'handle_test_connection_ajax'));
        add_action('wp_ajax_propertyfinder_subscribe_webhook', array($this->admin_controller, 'handle_subscribe_webhook_ajax'));
        add_action('wp_ajax_propertyfinder_unsubscribe_webhook', array($this->admin_controller, 'handle_unsubscribe_webhook_ajax'));
        add_action('wp_ajax_propertyfinder_refresh_webhooks', array($this->admin_controller, 'handle_refresh_webhooks_ajax'));
        add_action('wp_ajax_propertyfinder_clear_log', array($this->admin_controller, 'handle_clear_log_ajax'));
        add_action('wp_ajax_propertyfinder_delete_log', array($this->admin_controller, 'handle_delete_log_ajax'));
        add_action('wp_ajax_propertyfinder_delete_all_logs', array($this->admin_controller, 'handle_delete_all_logs_ajax'));
        add_action('wp_ajax_propertyfinder_check_agent_sync_status', array($this->admin_controller, 'handle_check_agent_sync_status_ajax'));

        // Admin menu customization
        add_action('admin_menu', array($this, 'add_listings_to_menu'));

        // Custom cron intervals
        add_filter('cron_schedules', array($this, 'add_cron_intervals'));

        // Schedule sync if enabled
        add_action('init', array($this, 'maybe_schedule_sync'));

        // Initialize metaboxes
        new PropertyFinder_Metabox();
    }

    /**
     * Add listings CPT to admin menu
     */
    public function add_listings_to_menu() {
        global $submenu;
        
        $cpt_name = propertyfinder_get_cpt_name();
        
        if (isset($submenu['propertyfinder-settings'])) {
            $submenu['propertyfinder-settings'][] = array(
                __('All Listings', 'propertyfinder'),
                'edit_posts',
                'edit.php?post_type=' . $cpt_name,
            );
        }
    }

    /**
     * Add custom cron intervals
     */
    public function add_cron_intervals($schedules) {
        $interval_4h = \PropertyFinder_Config::get('cron_interval_4hours', 'propertyfinder_4hours');
        $interval_6h = \PropertyFinder_Config::get('cron_interval_6hours', 'propertyfinder_6hours');
        
        $schedules[$interval_4h] = array(
            'interval' => 4 * HOUR_IN_SECONDS,
            'display' => __('Every 4 Hours', 'propertyfinder'),
        );

        $schedules[$interval_6h] = array(
            'interval' => 6 * HOUR_IN_SECONDS,
            'display' => __('Every 6 Hours', 'propertyfinder'),
        );

        return $schedules;
    }

    /**
     * Maybe schedule sync on init
     */
    public function maybe_schedule_sync() {
        $cron_hook = \PropertyFinder_Config::get('sync_cron_hook', 'propertyfinder_sync_listings');
        $next_scheduled = wp_next_scheduled($cron_hook);
        
        if (\PropertyFinder_Config::get('sync_enabled', false) && !$next_scheduled) {
            do_action('propertyfinder_maybe_schedule_sync');
        }
    }
}

