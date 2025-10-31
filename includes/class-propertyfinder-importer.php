<?php
/**
 * PropertyFinder Listing Importer
 *
 * @package PropertyFinder
 * @subpackage Includes
 */

use PropertyFinder\Models\PropertyModel;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Importer class
 */
class PropertyFinder_Importer {

    /**
     * API instance
     */
    private $api;

    /**
     * Property model instance
     */
    private $model;

    /**
     * Constructor
     */
    public function __construct() {
        $this->api = new PropertyFinder_API();
        $this->model = new PropertyModel();

        // Register AJAX handlers
        add_action('wp_ajax_propertyfinder_import', array($this, 'handle_import_ajax'));
        add_action('wp_ajax_propertyfinder_sync_all', array($this, 'handle_sync_all_ajax'));
        
        // Scheduled sync
        add_action('propertyfinder_sync_listings', array($this, 'sync_listings'));
    }

    /**
     * Import listings from PropertyFinder API
     *
     * @param array $params Import parameters
     * @return array Import results
     */
    public function import_listings($params = array()) {
        do_action('propertyfinder_import_start', $params);

        // Get listings from API
        error_log('PropertyFinder: Fetching listings from API with params: ' . print_r($params, true));
        
        $listings_data = $this->api->get_listings($params);

        if (!$listings_data) {
            $error_message = 'Failed to fetch listings from API';
            error_log('PropertyFinder: ' . $error_message);
            error_log('PropertyFinder: API response was: ' . print_r($listings_data, true));
            do_action('propertyfinder_import_error', $error_message);
            return array(
                'success' => false,
                'message' => __('Failed to fetch listings from API. Check debug log for details.', 'propertyfinder'),
            );
        }

        error_log('PropertyFinder: Listings data retrieved successfully');

        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        // Handle API response structure from OpenAPI spec
        if (isset($listings_data['results']) && is_array($listings_data['results'])) {
            foreach ($listings_data['results'] as $listing) {
                $result = $this->import_single_listing($listing);
                
                switch ($result['status']) {
                    case 'imported':
                        $imported++;
                        break;
                    case 'updated':
                        $updated++;
                        break;
                    case 'skipped':
                        $skipped++;
                        break;
                    case 'error':
                        $errors++;
                        break;
                }
            }
        } elseif (isset($listings_data['data']) && is_array($listings_data['data'])) {
            // Fallback for different response structure
            foreach ($listings_data['data'] as $listing) {
                $result = $this->import_single_listing($listing);
                
                switch ($result['status']) {
                    case 'imported':
                        $imported++;
                        break;
                    case 'updated':
                        $updated++;
                        break;
                    case 'skipped':
                        $skipped++;
                        break;
                    case 'error':
                        $errors++;
                        break;
                }
            }
        }

        $results = array(
            'success' => true,
            'imported' => $imported,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors,
            'total' => $imported + $updated + $skipped + $errors,
        );

        do_action('propertyfinder_import_complete', $results);

        return $results;
    }

    /**
     * Import a single listing
     *
     * @param array $listing_data Listing data from API
     * @return array Result status
     */
    private function import_single_listing($listing_data) {
        if (empty($listing_data['id'])) {
            error_log('PropertyFinder: Missing listing ID in listing data');
            return array('status' => 'error', 'message' => 'Missing listing ID');
        }

        $listing_id = sanitize_text_field($listing_data['id']);
        error_log('PropertyFinder: Processing listing ID: ' . $listing_id);
        
        // Allow filtering of listing data before import
        $listing_data = apply_filters('propertyfinder_listing_before_import', $listing_data);

        // Check if listing already exists
        $existing_post = $this->find_listing_by_api_id($listing_id);

        if ($existing_post) {
            error_log('PropertyFinder: Listing exists, updating post ID: ' . $existing_post->ID);
            // Update existing listing
            $result = $this->update_listing($existing_post->ID, $listing_data);
            
            if (!$result || is_wp_error($result)) {
                error_log('PropertyFinder: Failed to update listing - Post ID: ' . $existing_post->ID);
                return array('status' => 'error', 'message' => 'Failed to update listing');
            }
            
            do_action('propertyfinder_listing_updated', $existing_post->ID, $listing_data);
            
            return array('status' => 'updated', 'post_id' => $existing_post->ID);
        } else {
            error_log('PropertyFinder: Creating new listing for ID: ' . $listing_id);
            // Create new listing
            $result = $this->create_listing($listing_data);
            
            if ($result && !is_wp_error($result)) {
                error_log('PropertyFinder: Successfully created listing - Post ID: ' . $result);
                do_action('propertyfinder_listing_imported', $result, $listing_data);
                return array('status' => 'imported', 'post_id' => $result);
            } else {
                $error_msg = is_wp_error($result) ? $result->get_error_message() : 'Unknown error';
                error_log('PropertyFinder: Failed to create listing - Error: ' . $error_msg);
                return array('status' => 'error', 'message' => $error_msg);
            }
        }
    }

    /**
     * Find listing by API ID
     *
     * @param string $api_id API ID
     * @return WP_Post|null
     */
    private function find_listing_by_api_id($api_id) {
        $posts = get_posts(array(
            'post_type' => 'pf_listing',
            'posts_per_page' => 1,
            'post_status' => 'any',
            'meta_query' => array(
                array(
                    'key' => '_pf_api_id',
                    'value' => $api_id,
                    'compare' => '=',
                ),
            ),
        ));

        return !empty($posts) ? $posts[0] : null;
    }

    /**
     * Create listing post
     *
     * @param array $listing_data Listing data
     * @return int|WP_Error Post ID or error
     */
    private function create_listing($listing_data) {
        // Get multilingual title and description from API response
        $title = '';
        if (isset($listing_data['title']['en']) && !empty($listing_data['title']['en'])) {
            $title = sanitize_text_field($listing_data['title']['en']);
        } elseif (isset($listing_data['title']['ar']) && !empty($listing_data['title']['ar'])) {
            $title = sanitize_text_field($listing_data['title']['ar']);
        } elseif (isset($listing_data['reference'])) {
            $title = 'Listing ' . sanitize_text_field($listing_data['reference']);
        } else {
            $title = 'Property Listing ' . time();
        }
        
        $description = '';
        if (isset($listing_data['description']['en']) && !empty($listing_data['description']['en'])) {
            $description = wp_kses_post($listing_data['description']['en']);
        } elseif (isset($listing_data['description']['ar']) && !empty($listing_data['description']['ar'])) {
            $description = wp_kses_post($listing_data['description']['ar']);
        }
        
        error_log('PropertyFinder: Creating listing with title: ' . $title);
        
        // Allow customization of post data
        $post_data = array(
            'post_type'    => 'pf_listing',
            'post_title'   => $title,
            'post_content' => $description,
            'post_status'  => apply_filters('propertyfinder_default_post_status', 'publish'),
            'meta_input'  => array(
                '_pf_api_id' => sanitize_text_field($listing_data['id']),
            ),
        );

        $post_data = apply_filters('propertyfinder_listing_post_data', $post_data, $listing_data);

        $post_id = wp_insert_post($post_data);

        if (!is_wp_error($post_id) && $post_id > 0) {
            $this->set_listing_meta($post_id, $listing_data);
            $this->set_listing_taxonomies($post_id, $listing_data);
        }

        return $post_id;
    }

    /**
     * Update listing post
     *
     * @param int $post_id Post ID
     * @param array $listing_data Listing data
     * @return bool|WP_Error
     */
    private function update_listing($post_id, $listing_data) {
        // Get multilingual title and description from API response
        $title = get_the_title($post_id);
        if (isset($listing_data['title']['en']) && !empty($listing_data['title']['en'])) {
            $title = sanitize_text_field($listing_data['title']['en']);
        } elseif (isset($listing_data['title']['ar']) && !empty($listing_data['title']['ar'])) {
            $title = sanitize_text_field($listing_data['title']['ar']);
        }
        
        $description = get_post_field('post_content', $post_id);
        if (isset($listing_data['description']['en']) && !empty($listing_data['description']['en'])) {
            $description = wp_kses_post($listing_data['description']['en']);
        } elseif (isset($listing_data['description']['ar']) && !empty($listing_data['description']['ar'])) {
            $description = wp_kses_post($listing_data['description']['ar']);
        }
        
        error_log('PropertyFinder: Updating listing ID: ' . $post_id . ' with title: ' . $title);
        
        $post_data = array(
            'ID'           => $post_id,
            'post_title'   => $title,
            'post_content' => $description,
        );

        $post_data = apply_filters('propertyfinder_listing_update_post_data', $post_data, $listing_data);

        $result = wp_update_post($post_data);

        if (!is_wp_error($result)) {
            $this->set_listing_meta($post_id, $listing_data);
            $this->set_listing_taxonomies($post_id, $listing_data);
        }

        return $result;
    }

    /**
     * Set listing meta fields
     *
     * @param int $post_id Post ID
     * @param array $listing_data Listing data
     */
    private function set_listing_meta($post_id, $listing_data) {
        // Map API fields to meta fields based on PropertyFinder API OpenAPI spec
        $meta_fields = apply_filters('propertyfinder_listing_meta_fields', array(
            // Basic Info
            '_pf_api_id'              => isset($listing_data['id']) ? sanitize_text_field($listing_data['id']) : '',
            '_pf_reference'           => isset($listing_data['reference']) ? sanitize_text_field($listing_data['reference']) : '',
            '_pf_category'            => isset($listing_data['category']) ? sanitize_text_field($listing_data['category']) : '',
            '_pf_type'                => isset($listing_data['type']) ? sanitize_text_field($listing_data['type']) : '',
            
            // Title & Description (multilingual)
            '_pf_title_en'            => isset($listing_data['title']['en']) ? $listing_data['title']['en'] : '',
            '_pf_title_ar'            => isset($listing_data['title']['ar']) ? $listing_data['title']['ar'] : '',
            '_pf_description_en'      => isset($listing_data['description']['en']) ? $listing_data['description']['en'] : '',
            '_pf_description_ar'      => isset($listing_data['description']['ar']) ? $listing_data['description']['ar'] : '',
            
            // Property Details
            '_pf_bedrooms'            => isset($listing_data['bedrooms']) ? $listing_data['bedrooms'] : '',
            '_pf_bathrooms'           => isset($listing_data['bathrooms']) ? $listing_data['bathrooms'] : '',
            '_pf_size'                => isset($listing_data['size']) ? $listing_data['size'] : '',
            '_pf_floor_number'        => isset($listing_data['floorNumber']) ? $listing_data['floorNumber'] : '',
            '_pf_unit_number'         => isset($listing_data['unitNumber']) ? $listing_data['unitNumber'] : '',
            '_pf_plot_number'         => isset($listing_data['plotNumber']) ? $listing_data['plotNumber'] : '',
            '_pf_plot_size'           => isset($listing_data['plotSize']) ? $listing_data['plotSize'] : '',
            '_pf_land_number'         => isset($listing_data['landNumber']) ? $listing_data['landNumber'] : '',
            '_pf_number_of_floors'    => isset($listing_data['numberOfFloors']) ? $listing_data['numberOfFloors'] : '',
            '_pf_parking_slots'        => isset($listing_data['parkingSlots']) ? $listing_data['parkingSlots'] : '',
            
            // Furnishing & Finishing
            '_pf_furnishing_type'     => isset($listing_data['furnishingType']) ? $listing_data['furnishingType'] : '',
            '_pf_finishing_type'      => isset($listing_data['finishingType']) ? $listing_data['finishingType'] : '',
            
            // Status & Dates
            '_pf_project_status'      => isset($listing_data['projectStatus']) ? $listing_data['projectStatus'] : '',
            '_pf_available_from'      => isset($listing_data['availableFrom']) ? $listing_data['availableFrom'] : '',
            '_pf_age'                 => isset($listing_data['age']) ? $listing_data['age'] : '',
            
            // Features
            '_pf_has_garden'          => isset($listing_data['hasGarden']) ? ($listing_data['hasGarden'] ? 'yes' : 'no') : '',
            '_pf_has_kitchen'         => isset($listing_data['hasKitchen']) ? ($listing_data['hasKitchen'] ? 'yes' : 'no') : '',
            '_pf_has_parking'         => isset($listing_data['hasParkingOnSite']) ? ($listing_data['hasParkingOnSite'] ? 'yes' : 'no') : '',
            
            // Price Structure (complex nested object from OpenAPI spec)
            '_pf_price_structure'     => isset($listing_data['price']) ? maybe_serialize($listing_data['price']) : '',
            '_pf_price_type'          => isset($listing_data['price']['type']) ? $listing_data['price']['type'] : '',
            '_pf_price_sale'          => isset($listing_data['price']['amounts']['sale']) ? $listing_data['price']['amounts']['sale'] : '',
            '_pf_price_monthly'       => isset($listing_data['price']['amounts']['monthly']) ? $listing_data['price']['amounts']['monthly'] : '',
            '_pf_price_yearly'        => isset($listing_data['price']['amounts']['yearly']) ? $listing_data['price']['amounts']['yearly'] : '',
            '_pf_price_daily'         => isset($listing_data['price']['amounts']['daily']) ? $listing_data['price']['amounts']['daily'] : '',
            '_pf_price_weekly'        => isset($listing_data['price']['amounts']['weekly']) ? $listing_data['price']['amounts']['weekly'] : '',
            '_pf_price_on_request'    => isset($listing_data['price']['onRequest']) ? ($listing_data['price']['onRequest'] ? 'yes' : 'no') : '',
            
            // Location
            '_pf_location_id'         => isset($listing_data['location']['id']) ? $listing_data['location']['id'] : '',
            '_pf_location_path'       => isset($listing_data['location']['path']) ? $listing_data['location']['path'] : '',
            
            // Compliance (from OpenAPI spec)
            '_pf_compliance_type'     => isset($listing_data['compliance']['type']) ? $listing_data['compliance']['type'] : '',
            '_pf_compliance_number'   => isset($listing_data['compliance']['listingAdvertisementNumber']) ? $listing_data['compliance']['listingAdvertisementNumber'] : '',
            '_pf_issuing_license'     => isset($listing_data['compliance']['issuingClientLicenseNumber']) ? $listing_data['compliance']['issuingClientLicenseNumber'] : '',
            
            // Media (store as JSON)
            '_pf_media_images'        => isset($listing_data['media']['images']) ? maybe_serialize($listing_data['media']['images']) : '',
            '_pf_media_videos'        => isset($listing_data['media']['videos']) ? maybe_serialize($listing_data['media']['videos']) : '',
            
            // Additional Info
            '_pf_developer'            => isset($listing_data['developer']) ? $listing_data['developer'] : '',
            '_pf_owner_name'          => isset($listing_data['ownerName']) ? $listing_data['ownerName'] : '',
            '_pf_uae_emirate'         => isset($listing_data['uaeEmirate']) ? $listing_data['uaeEmirate'] : '',
            
            // Street Info
            '_pf_street_direction'    => isset($listing_data['street']['direction']) ? $listing_data['street']['direction'] : '',
            '_pf_street_width'        => isset($listing_data['street']['width']) ? $listing_data['street']['width'] : '',
            
            // State & Quality
            '_pf_state'               => isset($listing_data['state']['stage']) ? $listing_data['state']['stage'] : '',
            '_pf_state_type'          => isset($listing_data['state']['type']) ? $listing_data['state']['type'] : '',
            '_pf_verification_status' => isset($listing_data['verificationStatus']) ? $listing_data['verificationStatus'] : '',
            
            // Amenities
            '_pf_amenities'           => isset($listing_data['amenities']) && is_array($listing_data['amenities']) ? maybe_serialize($listing_data['amenities']) : '',
            
            // Timestamps
            '_pf_last_synced'         => current_time('mysql'),
            '_pf_created_at'          => isset($listing_data['createdAt']) ? $listing_data['createdAt'] : '',
            '_pf_updated_at'          => isset($listing_data['updatedAt']) ? $listing_data['updatedAt'] : '',
        ), $listing_data);

        foreach ($meta_fields as $meta_key => $meta_value) {
            if ($meta_value !== '' && $meta_value !== null) {
                update_post_meta($post_id, $meta_key, $meta_value);
            }
        }

        do_action('propertyfinder_listing_meta_set', $post_id, $listing_data);
    }

    /**
     * Set listing taxonomies
     *
     * @param int $post_id Post ID
     * @param array $listing_data Listing data
     */
    private function set_listing_taxonomies($post_id, $listing_data) {
        // Property Type (type field from API)
        if (isset($listing_data['type'])) {
            $this->set_taxonomy_term($post_id, 'pf_property_type', $listing_data['type']);
        }

        // Category
        if (isset($listing_data['category'])) {
            $this->set_taxonomy_term($post_id, 'pf_category', $listing_data['category']);
        }

        // Location based on ID (you may need to fetch location name separately)
        if (isset($listing_data['location']['id'])) {
            $location_id = $listing_data['location']['id'];
            // Store as taxonomy term (you might need to fetch the actual location name)
            $this->set_taxonomy_term($post_id, 'pf_location', 'location-' . $location_id);
        }

        // Transaction Type (from category and price structure)
        if (isset($listing_data['category'])) {
            $this->set_taxonomy_term($post_id, 'pf_transaction_type', $listing_data['category']);
        }

        // Furnishing Type
        if (isset($listing_data['furnishingType'])) {
            $this->set_taxonomy_term($post_id, 'pf_furnishing_status', $listing_data['furnishingType']);
        }
        
        // Finishing Type as an amenity or separate term
        if (isset($listing_data['finishingType'])) {
            $this->set_taxonomy_term($post_id, 'pf_furnishing_status', $listing_data['finishingType']);
        }

        // Project Status
        if (isset($listing_data['projectStatus'])) {
            $this->set_taxonomy_term($post_id, 'pf_category', $listing_data['projectStatus']);
        }

        // UAE Emirate
        if (isset($listing_data['uaeEmirate'])) {
            $this->set_taxonomy_term($post_id, 'pf_location', $listing_data['uaeEmirate']);
        }

        // Amenities array
        if (isset($listing_data['amenities']) && is_array($listing_data['amenities'])) {
            foreach ($listing_data['amenities'] as $amenity) {
                $this->set_taxonomy_term($post_id, 'pf_amenity', $amenity);
            }
        }

        // Payment Methods as amenities/categories
        if (isset($listing_data['price']['paymentMethods']) && is_array($listing_data['price']['paymentMethods'])) {
            foreach ($listing_data['price']['paymentMethods'] as $method) {
                $this->set_taxonomy_term($post_id, 'pf_category', 'payment-' . $method);
            }
        }

        do_action('propertyfinder_listing_taxonomies_set', $post_id, $listing_data);
    }

    /**
     * Set taxonomy term
     *
     * @param int $post_id Post ID
     * @param string $taxonomy Taxonomy name
     * @param string $term_name Term name
     */
    private function set_taxonomy_term($post_id, $taxonomy, $term_name) {
        if (empty($term_name)) {
            return;
        }

        $term = get_term_by('name', $term_name, $taxonomy);

        if (!$term) {
            $term = wp_insert_term($term_name, $taxonomy);
            if (!is_wp_error($term)) {
                $term_id = $term['term_id'];
            } else {
                return;
            }
        } else {
            $term_id = $term->term_id;
        }

        wp_set_object_terms($post_id, array($term_id), $taxonomy, true);
    }

    /**
     * Handle import AJAX request
     */
    public function handle_import_ajax() {
        check_ajax_referer('propertyfinder_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'propertyfinder')));
        }

        $params = array();

        if (isset($_POST['page'])) {
            $params['page'] = intval($_POST['page']);
        }

        if (isset($_POST['perPage'])) {
            $params['perPage'] = intval($_POST['perPage']);
        }

        if (isset($_POST['status'])) {
            $params['status'] = sanitize_text_field($_POST['status']);
        }

        $results = $this->import_listings($params);

        if ($results['success']) {
            wp_send_json_success($results);
        } else {
            wp_send_json_error($results);
        }
    }

    /**
     * Handle sync all AJAX request
     */
    public function handle_sync_all_ajax() {
        check_ajax_referer('propertyfinder_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'propertyfinder')));
        }

        // Sync all pages
        $page = 1;
        $total_imported = 0;
        $total_updated = 0;

        do {
            $results = $this->import_listings(array(
                'page' => $page,
                'perPage' => 50,
            ));

            if ($results['success']) {
                $total_imported += $results['imported'];
                $total_updated += $results['updated'];
            }

            $page++;
        } while ($results['success'] && ($results['imported'] > 0 || $results['updated'] > 0));

        wp_send_json_success(array(
            'imported' => $total_imported,
            'updated' => $total_updated,
        ));
    }

    /**
     * Sync listings (cron job)
     */
    public function sync_listings() {
        $auto_sync_enabled = get_option('propertyfinder_auto_sync_enabled', false);

        if ($auto_sync_enabled) {
            $this->import_listings();
        }
    }
}

