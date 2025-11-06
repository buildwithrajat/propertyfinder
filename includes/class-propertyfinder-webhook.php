<?php
/**
 * PropertyFinder Webhook Handler
 *
 * @package PropertyFinder
 * @subpackage Includes
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Webhook Handler class
 */
class PropertyFinder_Webhook {

    /**
     * API instance
     */
    private $api;

    /**
     * Importer instance
     */
    private $importer;

    /**
     * Agent Importer instance
     */
    private $agent_importer;

    /**
     * Constructor
     */
    public function __construct() {
        $this->api = new PropertyFinder_API();
        $this->importer = new PropertyFinder_Importer();
        $this->agent_importer = new PropertyFinder_Agent_Importer();

        // Register webhook endpoint
        add_action('rest_api_init', array($this, 'register_webhook_routes'));

        // Alternative: Register custom rewrite rule for webhook endpoint
        add_action('init', array($this, 'add_webhook_rewrite_rules'));
        add_action('template_redirect', array($this, 'handle_webhook_request'));
    }

    /**
     * Register REST API routes for webhooks
     */
    public function register_webhook_routes() {
        register_rest_route('propertyfinder/v1', '/webhook', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_webhook'),
            'permission_callback' => array($this, 'verify_webhook_signature'),
        ));
    }

    /**
     * Add rewrite rules for webhook endpoint
     */
    public function add_webhook_rewrite_rules() {
        add_rewrite_rule(
            '^propertyfinder-webhook/?$',
            'index.php?propertyfinder_webhook=1',
            'top'
        );
    }

    /**
     * Handle webhook request via template_redirect
     */
    public function handle_webhook_request() {
        if (get_query_var('propertyfinder_webhook')) {
            $this->process_webhook();
            exit;
        }
    }

    /**
     * Handle webhook via REST API
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function handle_webhook($request) {
        $payload = $request->get_json_params();
        return $this->process_webhook_payload($payload);
    }

    /**
     * Process webhook request
     */
    private function process_webhook() {
        // Get raw POST data
        $payload = file_get_contents('php://input');
        $data = json_decode($payload, true);

        if (!$data) {
            http_response_code(400);
            echo json_encode(array('error' => 'Invalid JSON payload'));
            exit;
        }

        $this->process_webhook_payload($data);
    }

    /**
     * Process webhook payload
     *
     * @param array $payload Webhook payload
     * @return WP_REST_Response|array
     */
    private function process_webhook_payload($payload) {
        if (empty($payload['type'])) {
            error_log('PropertyFinder Webhook: Missing event type in payload');
            return new WP_Error('missing_event_type', 'Missing event type', array('status' => 400));
        }

        $event_type = sanitize_text_field($payload['type']);
        $entity_id = isset($payload['entity']['id']) ? sanitize_text_field($payload['entity']['id']) : '';

        error_log('PropertyFinder Webhook: Received event - Type: ' . $event_type . ', Entity ID: ' . $entity_id);

        // Handle different event types for real-time imports
        switch ($event_type) {
            case 'listing.published':
            case 'listing.created':
            case 'listing.updated':
                // All create/update events use the same handler
                $this->handle_listing_published($payload);
                break;

            case 'listing.unpublished':
            case 'listing.deleted':
                // Unpublish and delete events
                $this->handle_listing_unpublished($payload);
                break;

            case 'user.created':
            case 'user.updated':
            case 'user.activated':
                // All user create/update events use the same handler
                $this->handle_user_updated($payload);
                break;

            case 'user.deleted':
            case 'user.deactivated':
                // User delete/deactivate events
                $this->handle_user_deleted($payload);
                break;

            default:
                error_log('PropertyFinder Webhook: Unhandled event type: ' . $event_type);
                break;
        }

        // Return success response
        if (defined('REST_REQUEST') && REST_REQUEST) {
            return new WP_REST_Response(array('success' => true), 200);
        }

        http_response_code(200);
        echo json_encode(array('success' => true));
        exit;
    }

    /**
     * Handle listing.published event
     *
     * @param array $payload Webhook payload
     */
    private function handle_listing_published($payload) {
        $listing_id = isset($payload['entity']['id']) ? sanitize_text_field($payload['entity']['id']) : '';

        if (empty($listing_id)) {
            error_log('PropertyFinder Webhook: Missing listing ID in published event');
            return;
        }

        error_log('PropertyFinder Webhook: [REAL-TIME IMPORT] Processing listing for ID: ' . $listing_id);

        // Fetch full listing data from API using filter[ids] parameter (real-time import)
        $listing_response = $this->api->get_listings(array(
            'filter[ids]' => $listing_id,
            'perPage' => 1,
            'page' => 1
        ));

        if (!$listing_response) {
            error_log('PropertyFinder Webhook: Failed to fetch listing data for ID: ' . $listing_id);
            return;
        }

        // Extract listing data from response (same structure handling as AJAX handler)
        $listing_data = null;
        if (isset($listing_response['results']) && is_array($listing_response['results']) && !empty($listing_response['results'])) {
            $listing_data = $listing_response['results'][0];
        } elseif (isset($listing_response['data']) && is_array($listing_response['data'])) {
            $listing_data = isset($listing_response['data'][0]) ? $listing_response['data'][0] : $listing_response['data'];
        } elseif (isset($listing_response['id'])) {
            $listing_data = $listing_response;
        }

        if (!$listing_data || empty($listing_data['id'])) {
            error_log('PropertyFinder Webhook: Invalid listing data structure for ID: ' . $listing_id);
            return;
        }

        // Import/update listing using importer (automatically saves JSON to post meta)
        // This happens in real-time via webhook
        error_log('PropertyFinder Webhook: [REAL-TIME IMPORT] Starting import for listing ID: ' . $listing_id);
        $result = $this->importer->import_single_listing($listing_data);

        if ($result['status'] === 'imported' || $result['status'] === 'updated') {
            $post_id = isset($result['post_id']) ? $result['post_id'] : 'N/A';
            error_log('PropertyFinder Webhook: [REAL-TIME IMPORT] ✓ Successfully imported/updated - Listing ID: ' . $listing_id . ' → Post ID: ' . $post_id);
            do_action('propertyfinder_webhook_listing_published', $listing_id, $listing_data);
        } else {
            $error_msg = isset($result['message']) ? $result['message'] : 'Unknown error';
            error_log('PropertyFinder Webhook: [REAL-TIME IMPORT] ✗ Failed to import - Listing ID: ' . $listing_id . ' - Error: ' . $error_msg);
        }
    }

    /**
     * Handle listing.unpublished event
     *
     * @param array $payload Webhook payload
     */
    private function handle_listing_unpublished($payload) {
        $listing_id = isset($payload['entity']['id']) ? sanitize_text_field($payload['entity']['id']) : '';

        if (empty($listing_id)) {
            error_log('PropertyFinder Webhook: Missing listing ID in unpublished event');
            return;
        }

        error_log('PropertyFinder Webhook: Processing listing.unpublished for ID: ' . $listing_id);

        // Find WordPress post by API ID
        $posts = get_posts(array(
            'post_type' => 'pf_listing',
            'posts_per_page' => 1,
            'post_status' => 'any',
            'meta_query' => array(
                array(
                    'key' => '_pf_api_id',
                    'value' => $listing_id,
                    'compare' => '=',
                ),
            ),
        ));

        if (!empty($posts)) {
            $post_id = $posts[0]->ID;

            // Update post status to draft or trash based on preference
            $unpublish_action = get_option('propertyfinder_unpublish_action', 'draft');
            
            if ($unpublish_action === 'trash') {
                wp_trash_post($post_id);
                error_log('PropertyFinder Webhook: Trashed post ID: ' . $post_id);
            } else {
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_status' => 'draft',
                ));
                error_log('PropertyFinder Webhook: Set post ID: ' . $post_id . ' to draft');
            }

            do_action('propertyfinder_webhook_listing_unpublished', $listing_id, $post_id);
        } else {
            error_log('PropertyFinder Webhook: No WordPress post found for listing ID: ' . $listing_id);
        }
    }

    /**
     * Handle user.created, user.updated, user.activated events (real-time agent sync)
     *
     * @param array $payload Webhook payload
     */
    private function handle_user_updated($payload) {
        $user_id = isset($payload['entity']['id']) ? sanitize_text_field($payload['entity']['id']) : '';

        if (empty($user_id)) {
            error_log('PropertyFinder Webhook: Missing user ID in updated event');
            return;
        }

        error_log('PropertyFinder Webhook: [REAL-TIME AGENT SYNC] Processing user for ID: ' . $user_id);

        // Fetch full user data from API (real-time import)
        // Try using get_user first (single user endpoint)
        $user_data = $this->api->get_user($user_id);
        
        // If get_user doesn't return expected format, try get_users with id filter
        if (!$user_data || empty($user_data['id'])) {
            $user_response = $this->api->get_users(array(
                'id' => $user_id,
                'perPage' => 1,
                'page' => 1
            ));
            
            if (!$user_response) {
                error_log('PropertyFinder Webhook: Failed to fetch user data for ID: ' . $user_id);
                return;
            }
            
            // Extract user data from response
            if (isset($user_response['results']) && is_array($user_response['results']) && !empty($user_response['results'])) {
                $user_data = $user_response['results'][0];
            } elseif (isset($user_response['data']) && is_array($user_response['data'])) {
                $user_data = isset($user_response['data'][0]) ? $user_response['data'][0] : $user_response['data'];
            } else {
                $user_data = $user_response;
            }
        }

        if (!$user_data || empty($user_data['id'])) {
            error_log('PropertyFinder Webhook: Invalid user data structure for ID: ' . $user_id);
            return;
        }

        // Import/update agent using agent importer (real-time sync)
        error_log('PropertyFinder Webhook: [REAL-TIME AGENT SYNC] Starting import for user ID: ' . $user_id);
        $result = $this->agent_importer->import_single_agent($user_data);

        if ($result['status'] === 'imported' || $result['status'] === 'updated') {
            $post_id = isset($result['post_id']) ? $result['post_id'] : 'N/A';
            error_log('PropertyFinder Webhook: [REAL-TIME AGENT SYNC] ✓ Successfully imported/updated - User ID: ' . $user_id . ' → Post ID: ' . $post_id);
            
            // Update last sync time
            update_option('propertyfinder_agent_last_sync', current_time('mysql'));
            update_option('propertyfinder_agent_last_sync_timestamp', current_time('timestamp'));
            
            do_action('propertyfinder_webhook_user_updated', $user_id, $user_data);
        } else {
            $error_msg = isset($result['message']) ? $result['message'] : 'Unknown error';
            error_log('PropertyFinder Webhook: [REAL-TIME AGENT SYNC] ✗ Failed to import - User ID: ' . $user_id . ' - Error: ' . $error_msg);
        }
    }

    /**
     * Handle user.deleted, user.deactivated events
     *
     * @param array $payload Webhook payload
     */
    private function handle_user_deleted($payload) {
        $user_id = isset($payload['entity']['id']) ? sanitize_text_field($payload['entity']['id']) : '';

        if (empty($user_id)) {
            error_log('PropertyFinder Webhook: Missing user ID in deleted event');
            return;
        }

        error_log('PropertyFinder Webhook: Processing user.deleted for ID: ' . $user_id);

        // Find WordPress post by API ID
        $posts = get_posts(array(
            'post_type' => PropertyFinder_Config::get_agent_cpt_name(),
            'posts_per_page' => 1,
            'post_status' => 'any',
            'meta_query' => array(
                array(
                    'key' => '_pf_api_id',
                    'value' => $user_id,
                    'compare' => '=',
                ),
            ),
        ));

        if (!empty($posts)) {
            $post_id = $posts[0]->ID;

            // Update post status to draft or trash based on preference
            $unpublish_action = get_option('propertyfinder_agent_unpublish_action', 'draft');
            
            if ($unpublish_action === 'trash') {
                wp_trash_post($post_id);
                error_log('PropertyFinder Webhook: Trashed agent post ID: ' . $post_id);
            } else {
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_status' => 'draft',
                ));
                error_log('PropertyFinder Webhook: Set agent post ID: ' . $post_id . ' to draft');
            }

            do_action('propertyfinder_webhook_user_deleted', $user_id, $post_id);
        } else {
            error_log('PropertyFinder Webhook: No WordPress post found for user ID: ' . $user_id);
        }
    }

    /**
     * Verify webhook signature (HMAC)
     *
     * @param WP_REST_Request $request Request object
     * @return bool
     */
    public function verify_webhook_signature($request) {
        $secret = get_option('propertyfinder_webhook_secret', '');

        // If no secret is configured, skip verification (for testing)
        if (empty($secret)) {
            error_log('PropertyFinder Webhook: No secret configured, skipping signature verification');
            return true;
        }

        $signature = $request->get_header('X-Signature');
        $payload = $request->get_body();

        if (empty($signature)) {
            error_log('PropertyFinder Webhook: Missing X-Signature header');
            return false;
        }

        // Calculate expected signature
        $expected_signature = hash_hmac('sha256', $payload, $secret);

        // Compare signatures (timing-safe comparison)
        $is_valid = hash_equals($expected_signature, $signature);

        if (!$is_valid) {
            error_log('PropertyFinder Webhook: Invalid signature');
        }

        return $is_valid;
    }

    /**
     * Subscribe to webhook events
     *
     * @param array $events Event types to subscribe to
     * @param string $callback_url Webhook callback URL
     * @param string $secret Optional HMAC secret
     * @return array|false Response data or false on failure
     */
    public function subscribe_to_events($events, $callback_url, $secret = '') {
        $results = array();

        foreach ($events as $event) {
            $body = array(
                'eventId' => $event,
                'callbackUrl' => $callback_url,
            );

            if (!empty($secret)) {
                $body['secret'] = $secret;
            }

            $response = $this->api->request('/webhooks', array(
                'body' => $body,
            ));

            if ($response) {
                $results[$event] = $response;
                error_log('PropertyFinder: Subscribed to webhook event: ' . $event);
            } else {
                error_log('PropertyFinder: Failed to subscribe to webhook event: ' . $event);
                $results[$event] = false;
            }
        }

        return $results;
    }

    /**
     * Get subscribed webhooks
     *
     * @return array|false Webhooks data or false on failure
     */
    public function get_subscribed_webhooks() {
        return $this->api->request('/webhooks');
    }

    /**
     * Unsubscribe from webhook event
     *
     * @param string $event_id Event ID to unsubscribe from
     * @return bool Success status
     */
    public function unsubscribe_from_event($event_id) {
        $response = wp_remote_request(
            $this->api->api_endpoint . '/webhooks/' . $event_id,
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->api->get_access_token(),
                    'Accept' => 'application/json',
                ),
                'method' => 'DELETE',
                'timeout' => 30,
            )
        );

        if (is_wp_error($response)) {
            return false;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        return $status_code === 204 || $status_code === 200;
    }
}

