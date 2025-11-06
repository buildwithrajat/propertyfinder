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

// Use global logger class
if (!class_exists('PropertyFinder_Logger')) {
    require_once PROPERTYFINDER_PLUGIN_DIR . 'includes/class-propertyfinder-logger.php';
}

/**
 * Admin Controller class
 */
class AdminController extends BaseController {


    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Add Agents page - link to Agent CPT instead of separate page
        // The Agent CPT already has its own menu, so we'll just add a shortcut
        $agent_cpt_name = \PropertyFinder_Config::get_agent_cpt_name();
        
        // Optional: Add a submenu to link to the agents page if needed
        // But since we're unifying, we'll redirect the agents page to the CPT
        add_submenu_page(
            'edit.php?post_type=' . $agent_cpt_name,
            __('Agents Overview', 'propertyfinder'),
            __('Overview', 'propertyfinder'),
            'manage_options',
            'propertyfinder-agents',
            array($this, 'render_agents_page')
        );
        
        // Settings menu (separate)
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
            __('Import Properties', 'propertyfinder'),
            __('Import Properties', 'propertyfinder'),
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
        
        $api = new \PropertyFinder_API();
        $webhooks_data = $api->get_webhooks();
        
        $data = array(
            'page_title' => __('PropertyFinder Settings', 'propertyfinder'),
            'webhooks' => isset($webhooks_data['data']) ? $webhooks_data['data'] : array(),
            'webhook_url' => \PropertyFinder_Config::get_webhook_url(true),
            'webhook_secret' => \PropertyFinder_Config::get('webhook_secret', ''),
        );
        
        echo $this->render('admin/settings', $data);
    }

    /**
     * Render agents page
     */
    public function render_agents_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $agent_model = new \PropertyFinder\Models\AgentModel();
        $agent_posts = $agent_model->getAll(array(
            'posts_per_page' => -1,
            'post_status' => 'any',
            'orderby' => 'title',
            'order' => 'ASC',
        ));
        
        $agents = array();
        $published = 0;
        $draft = 0;
        
        foreach ($agent_posts as $post) {
            $meta = get_post_meta($post->ID);
            
            if ($post->post_status === 'publish') {
                $published++;
            } else {
                $draft++;
            }
            
            $agents[] = $this->prepare_agent_data($post, $meta);
        }
        
        $data = array(
            'page_title' => __('Agents', 'propertyfinder'),
            'agents' => $agents,
            'stats' => array(
                'total' => count($agent_posts),
                'published' => $published,
                'draft' => $draft,
            ),
        );
        
        echo $this->render('admin/agents', $data);
    }

    /**
     * Prepare agent data for display
     *
     * @param WP_Post $post Post object
     * @param array $meta Post meta
     * @return array
     */
    private function prepare_agent_data($post, $meta) {
        $meta_fields = array(
            'api_id', 'first_name', 'last_name', 'email', 'mobile', 'status',
            'public_profile_id', 'public_profile_phone', 'role_name', 'last_synced'
        );
        
        $agent_data = array(
            'post_id' => $post->ID,
            'post_title' => $post->post_title,
            'post_status' => $post->post_status,
            'edit_link' => get_edit_post_link($post->ID),
            'view_link' => get_permalink($post->ID),
            'featured_image' => get_the_post_thumbnail_url($post->ID, 'thumbnail'),
        );
        
        foreach ($meta_fields as $field) {
            $agent_data[$field] = isset($meta['_pf_' . $field][0]) ? $meta['_pf_' . $field][0] : '';
        }
        
        return $agent_data;
    }

    /**
     * Render import page
     */
    public function render_import_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $data = array(
            'page_title' => __('Import Properties', 'propertyfinder'),
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
        
        // Ensure logger is initialized
        if (class_exists('PropertyFinder_Logger')) {
            \PropertyFinder_Logger::init();
        }
        
        // Get enabled modules from settings
        $enabled_modules = get_option('propertyfinder_log_modules', array('import', 'sync', 'update'));
        
        // Get all available modules (for display)
        $all_modules = \PropertyFinder_Logger::get_modules();
        
        // Filter modules to only show enabled ones + general
        $modules = array('general' => __('General', 'propertyfinder'));
        foreach ($all_modules as $module_key => $module_name) {
            if ($module_key === 'general' || in_array($module_key, $enabled_modules)) {
                $modules[$module_key] = $module_name;
            }
        }
        
        // Get selected module filter
        $selected_module = isset($_GET['module']) ? sanitize_text_field($_GET['module']) : null;
        
        // Get log files - filter to only show enabled modules
        $all_log_files = \PropertyFinder_Logger::get_log_files($selected_module);
        $log_files = array();
        
        foreach ($all_log_files as $file) {
            $file_module = $file['module'] ?? 'general';
            // Only show files for enabled modules or general logs
            if ($file_module === 'general' || in_array($file_module, $enabled_modules)) {
                // If a module filter is selected, only show that module's files
                if ($selected_module && $file_module !== $selected_module) {
                    continue;
                }
                $log_files[] = $file;
            }
        }
        $log_dir = \PropertyFinder_Logger::get_log_dir();
        
        // Get selected log file content
        $selected_log = isset($_GET['log_file']) ? sanitize_text_field($_GET['log_file']) : null;
        $log_content = '';
        
        if ($selected_log) {
            $log_content = \PropertyFinder_Logger::get_log($selected_log, 0); // Get all lines
        } elseif (!empty($log_files)) {
            // Default to today's log file for selected module or general
            $today_date = date('Y-m-d');
            if ($selected_module && $selected_module !== 'general') {
                $today_log = 'propertyfinder-' . $selected_module . '-' . $today_date . '.log';
            } else {
                $today_log = 'propertyfinder-' . $today_date . '.log';
            }
            $log_content = \PropertyFinder_Logger::get_log($today_log, 0);
            $selected_log = $today_log;
        }
        
        // Group files by module for display
        $files_by_module = array();
        foreach ($log_files as $file) {
            $module = $file['module'] ?? 'general';
            if (!isset($files_by_module[$module])) {
                $files_by_module[$module] = array();
            }
            $files_by_module[$module][] = $file;
        }
        
        $data = array(
            'page_title' => __('PropertyFinder Logs', 'propertyfinder'),
            'log_files' => $log_files,
            'files_by_module' => $files_by_module,
            'modules' => $modules,
            'selected_module' => $selected_module,
            'log_content' => $log_content,
            'selected_log' => $selected_log,
            'log_dir' => $log_dir,
        );
        
        echo $this->render('admin/logs', $data);
    }

    /**
     * Handle clear log AJAX request
     */
    public function handle_clear_log_ajax() {
        $this->verify_ajax_permission();
        
        $filename = isset($_POST['filename']) ? sanitize_text_field($_POST['filename']) : null;
        
        if ($filename) {
            $log_file = \PropertyFinder_Logger::get_log_dir() . basename($filename);
            if (file_exists($log_file)) {
                file_put_contents($log_file, '');
                wp_send_json_success(array('message' => __('Log file cleared successfully.', 'propertyfinder')));
            } else {
                wp_send_json_error(array('message' => __('Log file not found.', 'propertyfinder')));
            }
        } else {
            \PropertyFinder_Logger::clear_log();
            wp_send_json_success(array('message' => __('Current log file cleared successfully.', 'propertyfinder')));
        }
    }

    /**
     * Handle delete log file AJAX request
     */
    public function handle_delete_log_ajax() {
        $this->verify_ajax_permission();
        
        $filename = isset($_POST['filename']) ? sanitize_text_field($_POST['filename']) : '';
        
        if (empty($filename)) {
            wp_send_json_error(array('message' => __('Log filename is required.', 'propertyfinder')));
        }
        
        if (\PropertyFinder_Logger::delete_log_file($filename)) {
            wp_send_json_success(array('message' => __('Log file deleted successfully.', 'propertyfinder')));
        } else {
            wp_send_json_error(array('message' => __('Failed to delete log file.', 'propertyfinder')));
        }
    }

    /**
     * Handle delete all logs AJAX request
     */
    public function handle_delete_all_logs_ajax() {
        $this->verify_ajax_permission();
        
        $deleted = \PropertyFinder_Logger::delete_all_logs();
        
        if ($deleted > 0) {
            wp_send_json_success(array(
                'message' => sprintf(__('%d log file(s) deleted successfully.', 'propertyfinder'), $deleted),
                'deleted' => $deleted
            ));
        } else {
            wp_send_json_error(array('message' => __('No log files found to delete.', 'propertyfinder')));
        }
    }

    /**
     * Verify AJAX request permission and nonce
     */
    private function verify_ajax_permission() {
        check_ajax_referer('propertyfinder_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'propertyfinder')));
        }
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
        
        // Settings page specific CSS
        if ($hook === 'toplevel_page_propertyfinder-settings' || $hook === 'propertyfinder_page_propertyfinder-settings') {
            wp_enqueue_style(
                'propertyfinder-admin-settings',
                PROPERTYFINDER_PLUGIN_URL . 'assets/css/admin-settings.css',
                array('propertyfinder-admin'),
                PROPERTYFINDER_VERSION
            );
        }
        
        // JavaScript
        wp_enqueue_script(
            'propertyfinder-admin',
            PROPERTYFINDER_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            PROPERTYFINDER_VERSION,
            true
        );
        
        // Settings page specific JavaScript
        if ($hook === 'toplevel_page_propertyfinder-settings' || $hook === 'propertyfinder_page_propertyfinder-settings') {
            wp_enqueue_script(
                'propertyfinder-admin-settings',
                PROPERTYFINDER_PLUGIN_URL . 'assets/js/admin-settings.js',
                array('jquery', 'propertyfinder-admin'),
                PROPERTYFINDER_VERSION,
                true
            );
        }
        
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
        register_setting('propertyfinder_settings', 'propertyfinder_webhook_secret');
        register_setting('propertyfinder_settings', 'propertyfinder_sync_enabled');
        register_setting('propertyfinder_settings', 'propertyfinder_sync_interval');
        register_setting('propertyfinder_settings', 'propertyfinder_sync_time');
    }
    
    /**
     * Handle settings form submission
     */
    public function handle_settings_form() {
        if (!isset($_GET['page']) || $_GET['page'] !== 'propertyfinder-settings') {
            return;
        }
        
        if (!isset($_POST['propertyfinder_save_settings']) || !current_user_can('manage_options')) {
            return;
        }
        
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'propertyfinder_settings_nonce')) {
            wp_die(__('Security check failed. Please try again.', 'propertyfinder'));
        }
        
        $section = isset($_POST['propertyfinder_save_section']) ? sanitize_text_field($_POST['propertyfinder_save_section']) : 'all';
        
        // Save settings based on section
        if ($section === 'api_config' || $section === 'all') {
            update_option('propertyfinder_api_key', sanitize_text_field($_POST['propertyfinder_api_key'] ?? ''));
            update_option('propertyfinder_api_secret', sanitize_text_field($_POST['propertyfinder_api_secret'] ?? ''));
            update_option('propertyfinder_api_endpoint', esc_url_raw($_POST['propertyfinder_api_endpoint'] ?? 'https://atlas.propertyfinder.com/v1'));
            update_option('propertyfinder_webhook_secret', sanitize_text_field($_POST['propertyfinder_webhook_secret'] ?? ''));
        }
        
        if ($section === 'scheduler' || $section === 'all') {
            update_option('propertyfinder_sync_enabled', isset($_POST['propertyfinder_sync_enabled']) ? 1 : 0);
            update_option('propertyfinder_sync_interval', sanitize_text_field($_POST['propertyfinder_sync_interval'] ?? 'hourly'));
            update_option('propertyfinder_sync_time', sanitize_text_field($_POST['propertyfinder_sync_time'] ?? '00:00'));
            
            // Reschedule cron if needed
            $cron_hook = \PropertyFinder_Config::get('sync_cron_hook', 'propertyfinder_sync_listings');
            wp_clear_scheduled_hook($cron_hook);
            
            if (isset($_POST['propertyfinder_sync_enabled'])) {
                $this->schedule_sync();
            }
        }
        
        if ($section === 'logging' || $section === 'all') {
            update_option('propertyfinder_log_level', sanitize_text_field($_POST['propertyfinder_log_level'] ?? 'info'));
            update_option('propertyfinder_log_modules', isset($_POST['propertyfinder_log_modules']) ? array_map('sanitize_text_field', $_POST['propertyfinder_log_modules']) : array());
        }
        
        // Legacy support - save agent settings if present
        if (isset($_POST['propertyfinder_agent_sync_enabled'])) {
            update_option('propertyfinder_agent_sync_enabled', isset($_POST['propertyfinder_agent_sync_enabled']) ? 1 : 0);
            update_option('propertyfinder_agent_sync_interval', sanitize_text_field($_POST['propertyfinder_agent_sync_interval'] ?? 'hourly'));
            update_option('propertyfinder_agent_sync_time', sanitize_text_field($_POST['propertyfinder_agent_sync_time'] ?? '00:00'));
            $this->update_agent_scheduler();
        }
        
        // Clear access token if API config changed
        if ($section === 'api_config') {
            delete_transient('propertyfinder_access_token');
        }
        
        // Set success message
        set_transient('propertyfinder_settings_saved', true, 30);
        
        wp_safe_redirect(add_query_arg('settings-updated', 'true', admin_url('admin.php?page=propertyfinder-settings')));
        exit;
    }

    /**
     * Update scheduler based on settings
     */
    private function update_scheduler() {
        // Clear all existing schedules for this hook
        $cron_hook = \PropertyFinder_Config::get('sync_cron_hook', 'propertyfinder_sync_listings');
        $timestamp = wp_next_scheduled($cron_hook);
        while ($timestamp) {
            wp_unschedule_event($timestamp, $cron_hook);
            $timestamp = wp_next_scheduled($cron_hook);
        }
        
        // Get fresh values from options (not from config cache)
        $sync_enabled = get_option('propertyfinder_sync_enabled', false);
        if (!$sync_enabled) {
            \PropertyFinder_Logger::sync('Automatic sync disabled');
            return;
        }
        
        $sync_interval = get_option('propertyfinder_sync_interval', 'hourly');
        $sync_time = get_option('propertyfinder_sync_time', '00:00');
        
        // Calculate next run time
        $next_run = $this->calculate_next_sync_time($sync_interval, $sync_time);
        $cron_interval = $this->get_cron_interval($sync_interval);
        
        // Schedule the recurring event
        $scheduled = wp_schedule_event($next_run, $cron_interval, $cron_hook);
        
        if ($scheduled === false) {
            \PropertyFinder_Logger::error('Failed to schedule sync event');
        } else {
            \PropertyFinder_Logger::sync('Scheduled sync', array('interval' => $sync_interval, 'next_run' => date('Y-m-d H:i:s', $next_run)));
        }
    }

    /**
     * Calculate next sync time based on interval and time
     */
    private function calculate_next_sync_time($interval, $time) {
        $now = current_time('timestamp');
        $time_parts = explode(':', $time);
        $hour = isset($time_parts[0]) ? intval($time_parts[0]) : 0;
        $minute = isset($time_parts[1]) ? intval($time_parts[1]) : 0;
        
        switch ($interval) {
            case 'hourly':
                return $now + HOUR_IN_SECONDS;
                
            case '4hours':
                return $now + (4 * HOUR_IN_SECONDS);
                
            case '6hours':
                return $now + (6 * HOUR_IN_SECONDS);
                
            case 'daily':
                $next = strtotime("today {$hour}:{$minute}:00", $now);
                if ($next <= $now) {
                    $next = strtotime("tomorrow {$hour}:{$minute}:00", $now);
                }
                return $next;
                
            case 'weekly':
                $next = strtotime("next monday {$hour}:{$minute}:00", $now);
                return $next;
                
            case 'daily_12am':
                $next = strtotime("today 00:00:00", $now);
                if ($next <= $now) {
                    $next = strtotime("tomorrow 00:00:00", $now);
                }
                return $next;
                
            default:
                return $now + HOUR_IN_SECONDS;
        }
    }

    /**
     * Update agent scheduler based on settings
     */
    private function update_agent_scheduler() {
        // Clear all existing schedules for agent sync hook
        $cron_hook = \PropertyFinder_Config::get('agent_sync_cron_hook', 'propertyfinder_sync_agents');
        $timestamp = wp_next_scheduled($cron_hook);
        while ($timestamp) {
            wp_unschedule_event($timestamp, $cron_hook);
            $timestamp = wp_next_scheduled($cron_hook);
        }
        
        // Get fresh values from options (not from config cache)
        $sync_enabled = get_option('propertyfinder_agent_sync_enabled', false);
        if (!$sync_enabled) {
            \PropertyFinder_Logger::sync('Agent automatic sync disabled');
            return;
        }
        
        $sync_interval = get_option('propertyfinder_agent_sync_interval', 'hourly');
        $sync_time = get_option('propertyfinder_agent_sync_time', '00:00');
        
        // Calculate next run time
        $next_run = $this->calculate_next_sync_time($sync_interval, $sync_time);
        $cron_interval = $this->get_cron_interval($sync_interval);
        
        // Schedule the recurring event
        $scheduled = wp_schedule_event($next_run, $cron_interval, $cron_hook);
        
        if ($scheduled === false) {
            \PropertyFinder_Logger::error('Failed to schedule agent sync event');
        } else {
            \PropertyFinder_Logger::sync('Scheduled agent sync', array('interval' => $sync_interval, 'next_run' => date('Y-m-d H:i:s', $next_run)));
        }
    }

    /**
     * Get cron interval name
     */
    private function get_cron_interval($interval) {
        switch ($interval) {
            case 'hourly':
                return 'hourly';
            case '4hours':
                return \PropertyFinder_Config::get('cron_interval_4hours', 'propertyfinder_4hours');
            case '6hours':
                return \PropertyFinder_Config::get('cron_interval_6hours', 'propertyfinder_6hours');
            case 'daily':
            case 'daily_12am':
                return 'daily';
            case 'weekly':
                return 'weekly';
            default:
                return 'hourly';
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
     * Handle check agent sync status AJAX request
     */
    public function handle_check_agent_sync_status_ajax() {
        $this->verify_ajax_permission();
        
        $last_sync = get_option('propertyfinder_agent_last_sync', '');
        $is_running = get_transient('propertyfinder_agent_import_lock');
        
        $last_sync_text = '';
        if ($last_sync) {
            $last_sync_time = strtotime($last_sync);
            $last_sync_text = human_time_diff($last_sync_time, current_time('timestamp'));
        }
        
        wp_send_json_success(array(
            'last_sync' => $last_sync_text,
            'is_running' => (bool) $is_running,
        ));
    }

    /**
     * Handle sync AJAX request
     */
    public function handle_sync_ajax() {
        $this->verify_ajax_permission();
        
        \PropertyFinder_Logger::init();
        
        $importer = new \PropertyFinder_Importer();
        $results = $importer->import_listings(array(
            'page' => 1,
            'perPage' => 50,
            'draft' => false,
            'archived' => false
        ));
        
        if ($results['success']) {
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
            wp_send_json_error(array(
                'message' => $results['message'] ?? __('Import failed.', 'propertyfinder'),
                'results' => $results
            ));
        }
    }

    /**
     * Handle test connection AJAX request
     */
    public function handle_test_connection_ajax() {
        $this->verify_ajax_permission();
        
        $api_key = \PropertyFinder_Config::get_api_key();
        $api_secret = \PropertyFinder_Config::get_api_secret();
        
        if (empty($api_key) || empty($api_secret)) {
            wp_send_json_error(array(
                'message' => __('API credentials not configured. Please save your settings first.', 'propertyfinder')
            ));
        }
        
        $api = new \PropertyFinder_API();
        $token = $api->get_access_token();
        
        if ($token) {
            wp_send_json_success(array(
                'message' => __('Connection successful! Access token obtained successfully.', 'propertyfinder'),
                'token' => substr($token, 0, 20) . '...',
                'details' => 'Access token expires in 30 minutes'
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Connection failed. Please check your API credentials and log files for details.', 'propertyfinder')
            ));
        }
    }


    /**
     * Handle subscribe webhook AJAX request
     */
    public function handle_subscribe_webhook_ajax() {
        $this->verify_ajax_permission();
        
        $event_id = isset($_POST['event_id']) ? sanitize_text_field($_POST['event_id']) : '';
        $callback_url = isset($_POST['callback_url']) ? esc_url_raw($_POST['callback_url']) : '';
        $secret = isset($_POST['secret']) ? sanitize_text_field($_POST['secret']) : '';
        
        if (empty($event_id) || empty($callback_url)) {
            wp_send_json_error(array('message' => __('Event ID and Callback URL are required.', 'propertyfinder')));
        }
        
        $api = new \PropertyFinder_API();
        $result = $api->create_webhook($event_id, $callback_url, $secret);
        
        if ($this->is_webhook_success($result)) {
            wp_send_json_success(array(
                'message' => __('Webhook subscribed successfully.', 'propertyfinder'),
                'data' => $result
            ));
        } else {
            $error_message = $this->get_webhook_error_message($result);
            wp_send_json_error(array(
                'message' => $error_message,
                'debug' => is_array($result) ? $result : array()
            ));
        }
    }

    /**
     * Check if webhook result is successful
     *
     * @param mixed $result API result
     * @return bool
     */
    private function is_webhook_success($result) {
        if (!is_array($result)) {
            return $result !== false && $result !== null;
        }
        
        if (isset($result['error']) && $result['error']) {
            return false;
        }
        
        return isset($result['data']) || isset($result['eventId']) || isset($result['success']) || !isset($result['error']);
    }

    /**
     * Get webhook error message
     *
     * @param mixed $result API result
     * @return string
     */
    private function get_webhook_error_message($result) {
        if (!is_array($result) || !isset($result['error']) || !$result['error']) {
            return __('Failed to subscribe webhook.', 'propertyfinder');
        }
        
        $message = $result['message'] ?? __('Failed to subscribe webhook.', 'propertyfinder');
        
        if (isset($result['status_code']) && $result['status_code'] === 403) {
            $message = __('Access forbidden (403). Your API key may not have permission to create webhooks. Please verify your API key has webhook permissions or contact PropertyFinder support.', 'propertyfinder');
        }
        
        return $message;
    }

    /**
     * Handle unsubscribe webhook AJAX request
     */
    public function handle_unsubscribe_webhook_ajax() {
        $this->verify_ajax_permission();
        
        $event_id = isset($_POST['event_id']) ? sanitize_text_field($_POST['event_id']) : '';
        
        if (empty($event_id)) {
            wp_send_json_error(array('message' => __('Event ID is required.', 'propertyfinder')));
        }
        
        $api = new \PropertyFinder_API();
        $result = $api->delete_webhook($event_id);
        
        if ($result) {
            wp_send_json_success(array('message' => __('Webhook unsubscribed successfully.', 'propertyfinder')));
        } else {
            wp_send_json_error(array('message' => __('Failed to unsubscribe webhook.', 'propertyfinder')));
        }
    }

    /**
     * Handle refresh webhooks AJAX request
     */
    public function handle_refresh_webhooks_ajax() {
        $this->verify_ajax_permission();
        
        $api = new \PropertyFinder_API();
        $webhooks_data = $api->get_webhooks();
        
        wp_send_json_success(array(
            'webhooks' => isset($webhooks_data['data']) ? $webhooks_data['data'] : array(),
            'message' => __('Webhooks refreshed successfully.', 'propertyfinder')
        ));
    }

}

