<?php
/**
 * PropertyFinder API Service
 *
 * @package PropertyFinder
 * @subpackage Includes
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * API Service class
 */
class PropertyFinder_API {

    /**
     * API endpoint
     */
    private $api_endpoint;

    /**
     * API key
     */
    private $api_key;

    /**
     * API secret
     */
    private $api_secret;

    /**
     * Access token
     */
    private $access_token;

    /**
     * Token expiration
     */
    private $token_expires;

    /**
     * Constructor
     */
    public function __construct() {
        $this->api_endpoint = get_option('propertyfinder_api_endpoint', 'https://atlas.propertyfinder.com/v1');
        $this->api_key = get_option('propertyfinder_api_key', '');
        $this->api_secret = get_option('propertyfinder_api_secret', '');
    }

    /**
     * Get access token
     *
     * @return string|false Token or false on failure
     */
    public function get_access_token() {
        // Check if we have a valid cached token
        $cached_token = get_transient('propertyfinder_access_token');
        if ($cached_token) {
            $this->access_token = $cached_token;
            return $this->access_token;
        }

        // Request new token
        $response = $this->request_token();

        if ($response && isset($response['accessToken'])) {
            $this->access_token = $response['accessToken'];
            $expires_in = isset($response['expiresIn']) ? (int)$response['expiresIn'] : 1800;
            
            // Cache token for its lifetime (minus 60 seconds for safety)
            set_transient('propertyfinder_access_token', $this->access_token, $expires_in - 60);
            
            error_log('PropertyFinder: Access token obtained successfully. Expires in: ' . $expires_in . ' seconds');
            
            return $this->access_token;
        }

        error_log('PropertyFinder: Failed to obtain access token. Response: ' . print_r($response, true));
        return false;
    }

    /**
     * Request new access token
     *
     * @return array|false Response data or false on failure
     */
    private function request_token() {
        $endpoint = $this->api_endpoint . '/auth/token';

        $response = wp_remote_post($endpoint, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ),
            'body' => json_encode(array(
                'apiKey' => $this->api_key,
                'apiSecret' => $this->api_secret,
            )),
            'timeout' => 30,
        ));

        if (is_wp_error($response)) {
            $error_message = 'Token Request Error: ' . $response->get_error_message();
            error_log('PropertyFinder: ' . $error_message);
            do_action('propertyfinder_api_error', 'token_request_error', $response->get_error_message());
            return false;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($status_code !== 200) {
            $error_message = sprintf(
                'Token Request Failed - Status Code: %s, Response: %s',
                $status_code,
                $body
            );
            error_log('PropertyFinder: ' . $error_message);
            do_action('propertyfinder_api_error', 'token_request_failed', $data);
            return false;
        }

        error_log('PropertyFinder: Token obtained successfully from ' . $endpoint);
        return $data;
    }

    /**
     * Make authenticated API request
     *
     * @param string $endpoint API endpoint
     * @param array $args Request arguments
     * @return array|false Response data or false on failure
     */
    public function request($endpoint, $args = array()) {
        $token = $this->get_access_token();

        if (!$token) {
            return false;
        }

        $url = $this->api_endpoint . $endpoint;
        
        // Add query parameters if provided
        if (!empty($args['params'])) {
            $url = add_query_arg($args['params'], $url);
        }

        $request_args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ),
            'timeout' => 30,
        );

        // Add body for POST requests
        if (!empty($args['body'])) {
            $request_args['body'] = json_encode($args['body']);
            $request_args['headers']['Content-Type'] = 'application/json';
            $request_args['method'] = 'POST';
        }

        $response = wp_remote_request($url, $request_args);

        if (is_wp_error($response)) {
            $error_message = 'API Request Error: ' . $response->get_error_message() . ' - Endpoint: ' . $url;
            error_log('PropertyFinder: ' . $error_message);
            do_action('propertyfinder_api_error', 'request_error', $response->get_error_message());
            return false;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // Check for rate limiting
        if ($status_code === 429) {
            error_log('PropertyFinder: Rate limit exceeded for endpoint: ' . $url);
            do_action('propertyfinder_rate_limit_exceeded');
            return false;
        }

        if ($status_code !== 200) {
            $error_message = sprintf(
                'API Request Failed - Endpoint: %s, Status Code: %s, Response: %s',
                $url,
                $status_code,
                $body
            );
            error_log('PropertyFinder: ' . $error_message);
            do_action('propertyfinder_api_error', 'request_failed', $data);
            return false;
        }

        error_log('PropertyFinder: API request successful - Endpoint: ' . $url . ', Status: ' . $status_code);

        // Allow filtering of API response
        return apply_filters('propertyfinder_api_response', $data, $endpoint);
    }

    /**
     * Get listings from API
     *
     * @param array $params Query parameters
     * @return array|false Listings data or false on failure
     */
    public function get_listings($params = array()) {
        $default_params = array(
            'page' => 1,
            'perPage' => 50,
            'draft' => false,
            'archived' => false,
        );

        $params = apply_filters('propertyfinder_listings_params', array_merge($default_params, $params));

        $response = $this->request('/listings', array(
            'params' => $params,
        ));

        if ($response && isset($response['results'])) {
            error_log('PropertyFinder: Retrieved ' . count($response['results']) . ' listings from API');
            return $response;
        }

        error_log('PropertyFinder: No listings found in API response');
        return false;
    }

    /**
     * Get single listing by ID
     *
     * @param string $listing_id Listing ID
     * @return array|false Listing data or false on failure
     */
    public function get_listing($listing_id) {
        $response = $this->request('/listings/' . $listing_id);

        return $response;
    }

    /**
     * Create listing via API
     *
     * @param array $data Listing data
     * @return array|false Created listing data or false on failure
     */
    public function create_listing($data) {
        $data = apply_filters('propertyfinder_listing_create_data', $data);

        $response = $this->request('/listings', array(
            'body' => $data,
        ));

        return $response;
    }

    /**
     * Update listing via API
     *
     * @param string $listing_id Listing ID
     * @param array $data Listing data
     * @return array|false Updated listing data or false on failure
     */
    public function update_listing($listing_id, $data) {
        $data = apply_filters('propertyfinder_listing_update_data', $data, $listing_id);

        $response = wp_remote_request($this->api_endpoint . '/listings/' . $listing_id, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->get_access_token(),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ),
            'method' => 'PUT',
            'body' => json_encode($data),
            'timeout' => 30,
        ));

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }

    /**
     * Delete listing via API
     *
     * @param string $listing_id Listing ID
     * @return bool Success status
     */
    public function delete_listing($listing_id) {
        $response = wp_remote_request($this->api_endpoint . '/listings/' . $listing_id, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->get_access_token(),
                'Accept' => 'application/json',
            ),
            'method' => 'DELETE',
            'timeout' => 30,
        ));

        return !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;
    }

    /**
     * Get credits balance
     *
     * @return array|false Credits data or false on failure
     */
    public function get_credits() {
        return $this->request('/credits');
    }

    /**
     * Get available locations
     *
     * @return array|false Locations data or false on failure
     */
    public function get_locations() {
        return $this->request('/locations');
    }
}

