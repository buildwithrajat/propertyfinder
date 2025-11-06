<?php
/**
 * PropertyFinder Agent Importer
 *
 * @package PropertyFinder
 * @subpackage Includes
 */

use PropertyFinder\Models\AgentModel;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Agent Importer class
 */
class PropertyFinder_Agent_Importer {

    /**
     * API instance
     */
    private $api;

    /**
     * Agent model instance
     */
    private $model;

    /**
     * Constructor
     */
    public function __construct() {
        $this->api = new PropertyFinder_API();
        $this->model = new \PropertyFinder\Models\AgentModel();

        // Register AJAX handlers
        add_action('wp_ajax_propertyfinder_import_agents', array($this, 'handle_import_ajax'));
        add_action('wp_ajax_propertyfinder_sync_agents', array($this, 'handle_sync_ajax'));
        
        // Scheduled sync
        $cron_hook = PropertyFinder_Config::get('agent_sync_cron_hook', 'propertyfinder_sync_agents');
        add_action($cron_hook, array($this, 'sync_agents'));
    }

    /**
     * Import agents from PropertyFinder API
     *
     * @param array $params Import parameters
     * @return array Import results
     */
    public function import_agents($params = array()) {
        // Initialize logger
        \PropertyFinder_Logger::init();
        
        // Check for lock to prevent duplicate runs
        $lock_key = 'propertyfinder_agent_import_lock';
        $lock_timeout = 300; // 5 minutes
        
        if (get_transient($lock_key)) {
            $lock_time = get_transient($lock_key . '_time');
            \PropertyFinder_Logger::warning('Agent import already running (locked at: ' . date('Y-m-d H:i:s', $lock_time) . ')', null, 'agent');
            return array(
                'success' => false,
                'message' => __('Agent import is already running. Please wait for the current import to complete.', 'propertyfinder'),
                'imported' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors' => 0,
            );
        }
        
        // Set lock
        set_transient($lock_key, true, $lock_timeout);
        set_transient($lock_key . '_time', current_time('timestamp'), $lock_timeout);
        
        try {
            \PropertyFinder_Logger::info('Agent import started', $params, 'agent');
            do_action('propertyfinder_agent_import_start', $params);

            // Get agents from API
            $agents_data = $this->api->get_users($params);

            if (!$agents_data || (!isset($agents_data['data']) && !isset($agents_data['results']))) {
                $error_message = 'Failed to fetch agents from API';
                \PropertyFinder_Logger::error($error_message, array('params' => $params, 'response' => $agents_data), 'agent');
                do_action('propertyfinder_agent_import_error', $error_message);
                
                // Release lock
                delete_transient($lock_key);
                delete_transient($lock_key . '_time');
                
                return array(
                    'success' => false,
                    'message' => __('Failed to fetch agents from API. Check logs for details.', 'propertyfinder'),
                );
            }

            \PropertyFinder_Logger::info('Agents data retrieved successfully', array('count' => isset($agents_data['data']) ? count($agents_data['data']) : (isset($agents_data['results']) ? count($agents_data['results']) : 0)), 'agent');

            $imported = 0;
            $updated = 0;
            $skipped = 0;
            $errors = 0;

            // Handle API response structure
            $agents = isset($agents_data['data']) ? $agents_data['data'] : (isset($agents_data['results']) ? $agents_data['results'] : array());

            if (!is_array($agents) || empty($agents)) {
                // Release lock
                delete_transient($lock_key);
                delete_transient($lock_key . '_time');
                
                return array(
                    'success' => false,
                    'message' => __('No agents found in API response.', 'propertyfinder'),
                    'imported' => 0,
                    'updated' => 0,
                    'skipped' => 0,
                    'errors' => 0,
                );
            }

            $total_agents = count($agents);
            $current = 0;
            
            foreach ($agents as $agent_data) {
                $current++;
                $result = $this->import_single_agent($agent_data);
                
                switch ($result['status']) {
                    case 'imported':
                        $imported++;
                        \PropertyFinder_Logger::info('Agent imported', array('agent_id' => $agent_data['id'] ?? 'N/A', 'post_id' => $result['post_id'] ?? 'N/A', 'progress' => "$current/$total_agents"), 'agent');
                        break;
                    case 'updated':
                        $updated++;
                        \PropertyFinder_Logger::info('Agent updated', array('agent_id' => $agent_data['id'] ?? 'N/A', 'post_id' => $result['post_id'] ?? 'N/A', 'progress' => "$current/$total_agents"), 'agent');
                        break;
                    case 'skipped':
                        $skipped++;
                        \PropertyFinder_Logger::warning('Agent skipped', array('agent_id' => $agent_data['id'] ?? 'N/A', 'reason' => $result['message'] ?? 'Unknown', 'progress' => "$current/$total_agents"), 'agent');
                        break;
                    case 'error':
                        $errors++;
                        \PropertyFinder_Logger::error('Agent import error', array('agent_id' => $agent_data['id'] ?? 'N/A', 'error' => $result['message'] ?? 'Unknown', 'progress' => "$current/$total_agents"), 'agent');
                        break;
                }
            }

            $results = array(
                'success' => true,
                'imported' => $imported,
                'updated' => $updated,
                'skipped' => $skipped,
                'errors' => $errors,
                'total' => $total_agents,
            );

            // Update last sync time
            update_option('propertyfinder_agent_last_sync', current_time('mysql'));
            update_option('propertyfinder_agent_last_sync_timestamp', current_time('timestamp'));

            \PropertyFinder_Logger::info('Agent import completed', $results, 'agent');
            do_action('propertyfinder_agent_import_complete', $results);
            
            // Release lock
            delete_transient($lock_key);
            delete_transient($lock_key . '_time');

            return $results;
        } catch (Exception $e) {
            // Release lock on error
            delete_transient($lock_key);
            delete_transient($lock_key . '_time');
            
            \PropertyFinder_Logger::error('Agent import exception', array('error' => $e->getMessage(), 'trace' => $e->getTraceAsString()), 'agent');
            return array(
                'success' => false,
                'message' => __('Agent import failed: ', 'propertyfinder') . $e->getMessage(),
                'imported' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors' => 0,
            );
        }
    }

    /**
     * Import a single agent
     *
     * @param array $agent_data Agent data from API
     * @return array Result status
     */
    public function import_single_agent($agent_data) {
        if (empty($agent_data['id'])) {
            \PropertyFinder_Logger::error('Missing agent ID in agent data', null, 'agent');
            return array('status' => 'error', 'message' => 'Missing agent ID');
        }

        $agent_id = sanitize_text_field($agent_data['id']);
        \PropertyFinder_Logger::debug('Processing agent', array('agent_id' => $agent_id), 'agent');
        
        // Allow filtering of agent data before import
        $agent_data = apply_filters('propertyfinder_agent_before_import', $agent_data);

        // Check if agent already exists
        $existing_post = propertyfinder_get_agent_by_api_id($agent_id);

        if ($existing_post) {
            \PropertyFinder_Logger::debug('Agent exists, updating', array('agent_id' => $agent_id, 'post_id' => $existing_post->ID), 'agent');
            // Update existing agent
            $result = $this->update_agent($existing_post->ID, $agent_data);
            
            if (!$result || is_wp_error($result)) {
                \PropertyFinder_Logger::error('Failed to update agent', array('agent_id' => $agent_id, 'post_id' => $existing_post->ID, 'error' => is_wp_error($result) ? $result->get_error_message() : 'Unknown'), 'agent');
                return array('status' => 'error', 'message' => 'Failed to update agent');
            }
            
            do_action('propertyfinder_agent_updated', $existing_post->ID, $agent_data);
            
            return array('status' => 'updated', 'post_id' => $existing_post->ID);
        } else {
            \PropertyFinder_Logger::debug('Creating new agent', array('agent_id' => $agent_id), 'agent');
            $result = $this->create_agent($agent_data);
            
            if (!$result || is_wp_error($result)) {
                \PropertyFinder_Logger::error('Failed to create agent', array('agent_id' => $agent_id, 'error' => is_wp_error($result) ? $result->get_error_message() : 'Unknown'), 'agent');
                return array('status' => 'error', 'message' => 'Failed to create agent');
            }
            
            do_action('propertyfinder_agent_imported', $result, $agent_data);
            
            return array('status' => 'imported', 'post_id' => $result);
        }
    }

    /**
     * Create agent post
     *
     * @param array $agent_data Agent data from API
     * @return int|WP_Error Post ID or error
     */
    private function create_agent($agent_data) {
        // Get agent name from data
        $title = '';
        if (isset($agent_data['firstName']) && isset($agent_data['lastName'])) {
            $title = sanitize_text_field($agent_data['firstName'] . ' ' . $agent_data['lastName']);
        } elseif (isset($agent_data['publicProfile']['name'])) {
            $title = sanitize_text_field($agent_data['publicProfile']['name']);
        } elseif (isset($agent_data['email'])) {
            $title = sanitize_text_field($agent_data['email']);
        } else {
            $title = 'Agent ' . sanitize_text_field($agent_data['id']);
        }
        
        \PropertyFinder_Logger::debug('Creating agent post', array('title' => $title, 'agent_id' => $agent_data['id'] ?? 'N/A'), 'agent');
        
        // Allow customization of post data
        $post_data = array(
            'post_type'    => PropertyFinder_Config::get_agent_cpt_name(),
            'post_title'   => $title,
            'post_content' => '',
            'post_status'  => apply_filters('propertyfinder_default_agent_status', 'publish'),
            'meta_input'  => array(
                '_pf_api_id' => sanitize_text_field($agent_data['id']),
            ),
        );

        $post_data = apply_filters('propertyfinder_agent_post_data', $post_data, $agent_data);

        $post_id = wp_insert_post($post_data);

        if (!is_wp_error($post_id) && $post_id > 0) {
            // Store complete JSON data
            update_post_meta($post_id, '_pf_imported_json', json_encode($agent_data, JSON_PRETTY_PRINT));
            
            $this->set_agent_meta($post_id, $agent_data);
            
            // Handle featured image
            $this->set_agent_image($post_id, $agent_data);
        }

        return $post_id;
    }

    /**
     * Update agent post
     *
     * @param int $post_id Post ID
     * @param array $agent_data Agent data from API
     * @return int|WP_Error Updated post ID or error
     */
    private function update_agent($post_id, $agent_data) {
        // Get agent name from data
        $title = '';
        if (isset($agent_data['firstName']) && isset($agent_data['lastName'])) {
            $title = sanitize_text_field($agent_data['firstName'] . ' ' . $agent_data['lastName']);
        } elseif (isset($agent_data['publicProfile']['name'])) {
            $title = sanitize_text_field($agent_data['publicProfile']['name']);
        } elseif (isset($agent_data['email'])) {
            $title = sanitize_text_field($agent_data['email']);
        }

        $post_data = array(
            'ID' => $post_id,
        );

        if (!empty($title)) {
            $post_data['post_title'] = $title;
        }

        $result = wp_update_post($post_data);

        if (!is_wp_error($result)) {
            // Store complete JSON data
            update_post_meta($post_id, '_pf_imported_json', json_encode($agent_data, JSON_PRETTY_PRINT));
            
            $this->set_agent_meta($post_id, $agent_data);
            
            // Handle featured image
            $this->set_agent_image($post_id, $agent_data);
        }

        return $result;
    }

    /**
     * Set agent meta fields
     *
     * @param int $post_id Post ID
     * @param array $agent_data Agent data
     */
    private function set_agent_meta($post_id, $agent_data) {
        // Use config class to map agent fields
        if (class_exists('PropertyFinder_Agent_Meta_Fields_Config')) {
            $meta_fields = PropertyFinder_Agent_Meta_Fields_Config::map_agent_fields($agent_data);
        } else {
            // Fallback to old method if config class not available
            $meta_fields = apply_filters('propertyfinder_agent_meta_fields', array(
            // Basic Info
            '_pf_api_id'              => isset($agent_data['id']) ? sanitize_text_field($agent_data['id']) : '',
            '_pf_first_name'          => isset($agent_data['firstName']) ? sanitize_text_field($agent_data['firstName']) : '',
            '_pf_last_name'           => isset($agent_data['lastName']) ? sanitize_text_field($agent_data['lastName']) : '',
            '_pf_email'               => isset($agent_data['email']) ? sanitize_email($agent_data['email']) : '',
            '_pf_mobile'              => isset($agent_data['mobile']) ? sanitize_text_field($agent_data['mobile']) : '',
            '_pf_status'              => isset($agent_data['status']) ? sanitize_text_field($agent_data['status']) : '',
            '_pf_created_at'          => isset($agent_data['createdAt']) ? sanitize_text_field($agent_data['createdAt']) : '',
            
            // Call Tracking
            '_pf_call_tracking_number' => isset($agent_data['callTracking']['number']) ? sanitize_text_field($agent_data['callTracking']['number']) : '',
            
            // Role
            '_pf_role_id'             => isset($agent_data['role']['id']) ? intval($agent_data['role']['id']) : '',
            '_pf_role_name'           => isset($agent_data['role']['name']) ? sanitize_text_field($agent_data['role']['name']) : '',
            '_pf_role_key'            => isset($agent_data['role']['roleKey']) ? sanitize_text_field($agent_data['role']['roleKey']) : '',
            '_pf_base_role_key'       => isset($agent_data['role']['baseRoleKey']) ? sanitize_text_field($agent_data['role']['baseRoleKey']) : '',
            '_pf_is_custom_role'      => isset($agent_data['role']['isCustom']) ? ($agent_data['role']['isCustom'] ? '1' : '0') : '',
            '_pf_role_data'           => isset($agent_data['role']) ? maybe_serialize($agent_data['role']) : '',
            
            // Public Profile - Basic
            '_pf_public_profile_id'   => isset($agent_data['publicProfile']['id']) ? intval($agent_data['publicProfile']['id']) : '',
            '_pf_public_profile_name'  => isset($agent_data['publicProfile']['name']) ? sanitize_text_field($agent_data['publicProfile']['name']) : '',
            '_pf_public_profile_email' => isset($agent_data['publicProfile']['email']) ? sanitize_email($agent_data['publicProfile']['email']) : '',
            '_pf_public_profile_phone' => isset($agent_data['publicProfile']['phone']) ? sanitize_text_field($agent_data['publicProfile']['phone']) : '',
            '_pf_public_profile_phone_secondary' => isset($agent_data['publicProfile']['phoneSecondary']) ? sanitize_text_field($agent_data['publicProfile']['phoneSecondary']) : '',
            '_pf_public_profile_whatsapp' => isset($agent_data['publicProfile']['whatsappPhone']) ? sanitize_text_field($agent_data['publicProfile']['whatsappPhone']) : '',
            
            // Public Profile - Bio
            '_pf_bio_primary'         => isset($agent_data['publicProfile']['bio']['primary']) ? wp_kses_post($agent_data['publicProfile']['bio']['primary']) : '',
            '_pf_bio_secondary'       => isset($agent_data['publicProfile']['bio']['secondary']) ? wp_kses_post($agent_data['publicProfile']['bio']['secondary']) : '',
            
            // Public Profile - Position
            '_pf_position_primary'    => isset($agent_data['publicProfile']['position']['primary']) ? sanitize_text_field($agent_data['publicProfile']['position']['primary']) : '',
            '_pf_position_secondary'  => isset($agent_data['publicProfile']['position']['secondary']) ? sanitize_text_field($agent_data['publicProfile']['position']['secondary']) : '',
            
            // Public Profile - Social
            '_pf_linkedin_address'    => isset($agent_data['publicProfile']['linkedinAddress']) ? esc_url_raw($agent_data['publicProfile']['linkedinAddress']) : '',
            
            // Public Profile - Verification
            '_pf_verification_status' => isset($agent_data['publicProfile']['verification']['status']) ? sanitize_text_field($agent_data['publicProfile']['verification']['status']) : '',
            '_pf_verification_request_date' => isset($agent_data['publicProfile']['verification']['requestDate']) ? sanitize_text_field($agent_data['publicProfile']['verification']['requestDate']) : '',
            
            // Public Profile - Super Agent
            '_pf_is_super_agent'      => isset($agent_data['publicProfile']['isSuperAgent']) ? ($agent_data['publicProfile']['isSuperAgent'] ? '1' : '0') : '',
            
            // Public Profile - Image URLs (store for reference)
            '_pf_image_url_large'     => isset($agent_data['publicProfile']['imageVariants']['large']['default']) ? esc_url_raw($agent_data['publicProfile']['imageVariants']['large']['default']) : '',
            '_pf_image_url_large_jpg'  => isset($agent_data['publicProfile']['imageVariants']['large']['jpg']) ? esc_url_raw($agent_data['publicProfile']['imageVariants']['large']['jpg']) : '',
            '_pf_image_url_large_webp' => isset($agent_data['publicProfile']['imageVariants']['large']['webp']) ? esc_url_raw($agent_data['publicProfile']['imageVariants']['large']['webp']) : '',
            
            // Compliances (store as serialized JSON)
            '_pf_compliances'         => isset($agent_data['publicProfile']['compliances']) && is_array($agent_data['publicProfile']['compliances']) ? maybe_serialize($agent_data['publicProfile']['compliances']) : '',
            
            // Store full public profile as JSON
            '_pf_public_profile_data' => isset($agent_data['publicProfile']) ? maybe_serialize($agent_data['publicProfile']) : '',
            ));
        }

        foreach ($meta_fields as $key => $value) {
            if ($value !== '' && $value !== null) {
                update_post_meta($post_id, $key, $value);
            }
        }

        do_action('propertyfinder_agent_meta_set', $post_id, $meta_fields);
    }

    /**
     * Set agent featured image from API data
     *
     * @param int $post_id Post ID
     * @param array $agent_data Agent data
     */
    private function set_agent_image($post_id, $agent_data) {
        // Check if featured image already exists
        if (get_post_thumbnail_id($post_id)) {
            // Optionally skip if image exists, or force update
            $force_update = apply_filters('propertyfinder_agent_force_image_update', false, $post_id);
            if (!$force_update) {
                return;
            }
        }

        // Get image URL from publicProfile
        $image_url = '';
        if (isset($agent_data['publicProfile']['imageVariants']['large']['webp'])) {
            $image_url = $agent_data['publicProfile']['imageVariants']['large']['webp'];
        } elseif (isset($agent_data['publicProfile']['imageVariants']['large']['jpg'])) {
            $image_url = $agent_data['publicProfile']['imageVariants']['large']['jpg'];
        } elseif (isset($agent_data['publicProfile']['imageVariants']['large']['default'])) {
            $image_url = $agent_data['publicProfile']['imageVariants']['large']['default'];
        }

        if (empty($image_url)) {
            return;
        }

        // Download and optimize image
        $attachment_id = propertyfinder_download_and_optimize_image(
            $image_url,
            $post_id,
            sprintf(__('Agent image for %s', 'propertyfinder'), get_the_title($post_id))
        );

        if ($attachment_id) {
            set_post_thumbnail($post_id, $attachment_id);
            \PropertyFinder_Logger::debug('Agent featured image set', array('post_id' => $post_id, 'attachment_id' => $attachment_id), 'agent');
        }
    }

    /**
     * Find agent by API ID
     *
     * @param string $api_id Agent API ID
     * @return WP_Post|null Post object or null
     */
    private function find_agent_by_api_id($api_id) {
        return propertyfinder_get_agent_by_api_id($api_id);
    }

    /**
     * Handle import AJAX request
     */
    public function handle_import_ajax() {
        check_ajax_referer('propertyfinder_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'propertyfinder')));
        }

        $params = array(
            'page' => isset($_POST['page']) ? intval($_POST['page']) : 1,
            'perPage' => isset($_POST['perPage']) ? intval($_POST['perPage']) : 50,
        );

        \PropertyFinder_Logger::info('Manual agent import started', $params, 'agent');

        $results = $this->import_agents($params);

        if ($results['success']) {
            \PropertyFinder_Logger::info('Manual agent import successful', $results, 'agent');
            wp_send_json_success(array(
                'message' => sprintf(
                    __('Agent import completed: %d imported, %d updated, %d skipped', 'propertyfinder'),
                    $results['imported'],
                    $results['updated'],
                    $results['skipped']
                ),
                'results' => $results
            ));
        } else {
            \PropertyFinder_Logger::error('Manual agent import failed', $results, 'agent');
            wp_send_json_error(array(
                'message' => isset($results['message']) ? $results['message'] : __('Agent import failed.', 'propertyfinder'),
                'results' => $results
            ));
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

        $params = array(
            'page' => isset($_POST['page']) ? intval($_POST['page']) : 1,
            'perPage' => isset($_POST['perPage']) ? intval($_POST['perPage']) : 50,
        );

        \PropertyFinder_Logger::info('Agent sync started', $params, 'agent');

        $results = $this->import_agents($params);

        if ($results['success']) {
            \PropertyFinder_Logger::info('Agent sync successful', $results, 'agent');
            wp_send_json_success(array(
                'message' => sprintf(
                    __('Agent sync completed: %d imported, %d updated, %d skipped', 'propertyfinder'),
                    $results['imported'],
                    $results['updated'],
                    $results['skipped']
                ),
                'results' => $results
            ));
        } else {
            \PropertyFinder_Logger::error('Agent sync failed', $results, 'agent');
            wp_send_json_error(array(
                'message' => isset($results['message']) ? $results['message'] : __('Agent sync failed.', 'propertyfinder'),
                'results' => $results
            ));
        }
    }

    /**
     * Sync agents (cron job)
     */
    public function sync_agents() {
        $sync_enabled = PropertyFinder_Config::get('agent_sync_enabled', false);

        if ($sync_enabled) {
            \PropertyFinder_Logger::sync('Agent scheduled sync started');
            $this->import_agents(array(
                'page' => 1,
                'perPage' => 50,
            ));
            \PropertyFinder_Logger::sync('Agent scheduled sync completed');
        }
    }
}

