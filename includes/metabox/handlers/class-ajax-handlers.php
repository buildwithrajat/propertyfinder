<?php
/**
 * AJAX Handlers for Property Metabox
 *
 * @package PropertyFinder
 * @subpackage Metabox
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Property Metabox AJAX Handlers
 */
class PropertyFinder_Property_Ajax_Handlers {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_propertyfinder_view_json', array($this, 'ajax_view_json'));
        add_action('wp_ajax_propertyfinder_fetch_property_from_api', array($this, 'ajax_fetch_from_api'));
        add_action('wp_ajax_propertyfinder_fetch_property_image', array($this, 'ajax_fetch_image'));
        add_action('wp_ajax_propertyfinder_set_featured_image', array($this, 'ajax_set_featured_image'));
        add_action('wp_ajax_propertyfinder_get_location_coords', array($this, 'ajax_get_location_coords'));
        add_action('wp_ajax_propertyfinder_search_locations', array($this, 'handle_search_locations_ajax'));
        add_action('wp_ajax_propertyfinder_download_gallery', array($this, 'ajax_download_gallery'));
        add_action('wp_ajax_propertyfinder_remove_gallery_image', array($this, 'ajax_remove_gallery_image'));
    }

    /**
     * AJAX handler for viewing JSON
     * Shows saved JSON from post meta (no API call - uses cached data saved during sync)
     */
    public function ajax_view_json() {
        check_ajax_referer('propertyfinder_property_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'propertyfinder')));
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        if (empty($post_id)) {
            wp_send_json_error(array('message' => __('Invalid post ID.', 'propertyfinder')));
        }

        // Get saved JSON from post meta (saved during sync/fetch - no API call)
        $json = get_post_meta($post_id, '_pf_imported_json', true);
        
        if (empty($json)) {
            wp_send_json_error(array('message' => __('No synced data found. Please use "Fetch from API" to sync data first.', 'propertyfinder')));
        }

        // If JSON is already a string, use it directly; if it's an array, encode it
        if (is_array($json)) {
            $json = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        wp_send_json_success(array(
            'json' => $json,
            'last_sync' => get_post_meta($post_id, '_pf_last_synced', true),
        ));
    }

    
    /**
     * AJAX handler for fetching property from API
     */
    public function ajax_fetch_from_api() {
        check_ajax_referer('propertyfinder_property_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'propertyfinder')));
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        if (empty($post_id)) {
            wp_send_json_error(array('message' => __('Invalid post ID.', 'propertyfinder')));
        }

        $api_id = get_post_meta($post_id, '_pf_api_id', true);
        if (empty($api_id)) {
            wp_send_json_error(array('message' => __('Property API ID not found.', 'propertyfinder')));
        }

        // Fetch property from API
        $api = new PropertyFinder_API();
        $property_response = $api->get_listings(array(
            'filter[ids]' => $api_id,
            'perPage' => 1,
            'page' => 1
        ));

        if (!$property_response) {
            wp_send_json_error(array('message' => __('Failed to fetch property from API. Please check API credentials and try again.', 'propertyfinder')));
        }

        // Extract property data from response
        $property_data = $this->extract_listing_from_response($property_response);
        
        if (!$property_data || empty($property_data['id'])) {
            wp_send_json_error(array('message' => __('No valid property data found in API response.', 'propertyfinder')));
        }

        // Save JSON data for viewing later
        update_post_meta($post_id, '_pf_imported_json', json_encode($property_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        update_post_meta($post_id, '_pf_last_synced', current_time('mysql'));
        
        // Import/update the property using importer
        $importer = new PropertyFinder_Importer();
        $result = $importer->import_single_listing($property_data);
        
        if ($result['status'] !== 'error') {
            wp_send_json_success(array(
                'message' => __('Property data fetched and updated successfully.', 'propertyfinder'),
                'redirect' => get_edit_post_link($post_id, 'raw'),
            ));
        } else {
            $error_msg = isset($result['message']) ? $result['message'] : __('Failed to update property data.', 'propertyfinder');
            wp_send_json_error(array('message' => $error_msg));
        }
    }
    
    /**
     * AJAX handler for fetching and optimizing property image
     */
    public function ajax_fetch_image() {
        check_ajax_referer('propertyfinder_property_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'propertyfinder')));
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $image_url = isset($_POST['image_url']) ? esc_url_raw($_POST['image_url']) : '';
        
        if (empty($post_id) || empty($image_url)) {
            wp_send_json_error(array('message' => __('Invalid post ID or image URL.', 'propertyfinder')));
        }

        $attachment_id = propertyfinder_download_and_optimize_image(
            $image_url,
            $post_id,
            sprintf(__('Property image for %s', 'propertyfinder'), get_the_title($post_id))
        );

        if ($attachment_id) {
            if (!get_post_thumbnail_id($post_id)) {
                set_post_thumbnail($post_id, $attachment_id);
            }
            
            wp_send_json_success(array(
                'message' => __('Image fetched and optimized successfully.', 'propertyfinder'),
                'attachment_id' => $attachment_id,
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to fetch and optimize image.', 'propertyfinder')));
        }
    }
    
    /**
     * AJAX handler for setting featured image
     */
    public function ajax_set_featured_image() {
        check_ajax_referer('propertyfinder_property_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'propertyfinder')));
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $attachment_id = isset($_POST['attachment_id']) ? intval($_POST['attachment_id']) : 0;
        
        if (empty($post_id) || empty($attachment_id)) {
            wp_send_json_error(array('message' => __('Invalid post ID or attachment ID.', 'propertyfinder')));
        }

        $result = set_post_thumbnail($post_id, $attachment_id);

        if ($result) {
            wp_send_json_success(array('message' => __('Featured image set successfully.', 'propertyfinder')));
        } else {
            wp_send_json_error(array('message' => __('Failed to set featured image.', 'propertyfinder')));
        }
    }

    /**
     * AJAX handler for getting location coordinates
     */
    public function ajax_get_location_coords() {
        check_ajax_referer('propertyfinder_property_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'propertyfinder')));
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        
        if (empty($post_id)) {
            wp_send_json_error(array('message' => __('Invalid post ID.', 'propertyfinder')));
        }

        // Get coordinates from post meta (not term meta)
        $lat = get_post_meta($post_id, '_pf_location_lat', true);
        $lng = get_post_meta($post_id, '_pf_location_lng', true);

        if ($lat && $lng) {
            wp_send_json_success(array(
                'lat' => floatval($lat),
                'lng' => floatval($lng),
            ));
        } else {
            wp_send_json_error(array('message' => __('No coordinates found for this location.', 'propertyfinder')));
        }
    }
    
    /**
     * Handle search locations AJAX
     */
    public function handle_search_locations_ajax() {
        check_ajax_referer('propertyfinder_property_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'propertyfinder')));
        }

        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        
        if (empty($search) || strlen($search) < 2) {
            wp_send_json_error(array('message' => __('Search term must be at least 2 characters.', 'propertyfinder')));
        }

        // Search locations from API
        $api = new PropertyFinder_API();
        $results = $api->get_locations(array(
            'search' => $search,
            'perPage' => 20,
        ));

        // Extract locations from response
            $locations = array();
        if (!empty($results['data']) && is_array($results['data'])) {
            foreach ($results['data'] as $location) {
                $locations[] = array(
                    'id' => isset($location['id']) ? $location['id'] : '',
                    'name' => isset($location['name']) ? $location['name'] : '',
                    'type' => isset($location['type']) ? $location['type'] : '',
                );
            }
            }
            
            wp_send_json_success(array(
                'locations' => $locations,
                'total' => count($locations),
            ));
    }

    /**
     * AJAX handler for downloading gallery images from API
     */
    public function ajax_download_gallery() {
        check_ajax_referer('propertyfinder_property_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'propertyfinder')));
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        if (empty($post_id)) {
            wp_send_json_error(array('message' => __('Invalid post ID.', 'propertyfinder')));
        }

        $api_id = get_post_meta($post_id, '_pf_api_id', true);
        if (empty($api_id)) {
            wp_send_json_error(array('message' => __('Property API ID not found.', 'propertyfinder')));
        }

        // Fetch property from API
        $api = new PropertyFinder_API();
        $property_response = $api->get_listings(array(
            'filter[ids]' => $api_id,
            'perPage' => 1,
            'page' => 1
        ));

        if (!$property_response) {
            wp_send_json_error(array('message' => __('Failed to fetch property from API.', 'propertyfinder')));
        }

        // Extract property data
        $property_data = $this->extract_listing_from_response($property_response);

        if (!$property_data || !is_array($property_data)) {
            wp_send_json_error(array('message' => __('No valid property data found in API response.', 'propertyfinder')));
        }

        // Use importer to sync gallery (this will handle gallery images)
        $importer = new PropertyFinder_Importer();
        $result = $importer->import_single_listing($property_data);

        // Get updated gallery count
        $gallery_images = get_post_meta($post_id, '_pf_gallery_images', true);
        $count = is_array($gallery_images) ? count($gallery_images) : 0;

        if ($result['status'] !== 'error') {
        wp_send_json_success(array(
            'message' => sprintf(__('Gallery images downloaded successfully. %d images added.', 'propertyfinder'), $count),
            'count' => $count
        ));
        } else {
            wp_send_json_error(array('message' => __('Failed to download gallery images.', 'propertyfinder')));
        }
    }

    /**
     * AJAX handler for removing image from gallery
     */
    public function ajax_remove_gallery_image() {
        check_ajax_referer('propertyfinder_property_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'propertyfinder')));
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $attachment_id = isset($_POST['attachment_id']) ? intval($_POST['attachment_id']) : 0;

        if (empty($post_id) || empty($attachment_id)) {
            wp_send_json_error(array('message' => __('Invalid post ID or attachment ID.', 'propertyfinder')));
        }

        // Get current gallery
        $gallery_images = get_post_meta($post_id, '_pf_gallery_images', true);
        if (!is_array($gallery_images)) {
            $gallery_images = array();
        }

        // Remove attachment ID from gallery
        $gallery_images = array_values(array_filter($gallery_images, function($id) use ($attachment_id) {
            return intval($id) !== $attachment_id;
        }));

        // Update gallery meta
        update_post_meta($post_id, '_pf_gallery_images', $gallery_images);

        wp_send_json_success(array(
            'message' => __('Image removed from gallery.', 'propertyfinder'),
            'count' => count($gallery_images)
        ));
    }

    /**
     * Extract listing data from API response
     * Handles different response structures
     *
     * @param array $response API response
     * @return array|null Listing data or null
     */
    private function extract_listing_from_response($response) {
        if (empty($response) || !is_array($response)) {
            return null;
        }

        // Check for 'results' array (standard listings endpoint)
        if (!empty($response['results']) && is_array($response['results'])) {
            return $response['results'][0];
        }

        // Check for 'data' array
        if (!empty($response['data']) && is_array($response['data'])) {
            return isset($response['data'][0]) ? $response['data'][0] : $response['data'];
        }

        // Check if response is a direct listing object
        if (!empty($response['id']) && (isset($response['category']) || isset($response['type']) || isset($response['price']))) {
            return $response;
        }

        return null;
    }
}

