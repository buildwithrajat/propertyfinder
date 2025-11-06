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
        $cron_hook = PropertyFinder_Config::get('sync_cron_hook', 'propertyfinder_sync_listings');
        add_action($cron_hook, array($this, 'sync_listings'));
    }

    /**
     * Import listings from PropertyFinder API
     *
     * @param array $params Import parameters
     * @return array Import results
     */
    public function import_listings($params = array()) {
        \PropertyFinder_Logger::init();
        do_action('propertyfinder_import_start', $params);

        // Get listings from API
        $listings_data = $this->api->get_listings($params);

        if (!$listings_data) {
            $error_message = 'Failed to fetch listings from API';
            \PropertyFinder_Logger::error($error_message);
            do_action('propertyfinder_import_error', $error_message);
            return array(
                'success' => false,
                'message' => __('Failed to fetch listings from API. Check debug log for details.', 'propertyfinder'),
            );
        }

        // Extract listings from API response
        $listings_to_process = array();
        $total_available = 0;
        
        if (isset($listings_data['results']) && is_array($listings_data['results'])) {
            $listings_to_process = $listings_data['results'];
        } elseif (isset($listings_data['data']) && is_array($listings_data['data'])) {
            $listings_to_process = $listings_data['data'];
        }
        
        // Get total count from pagination if available
        if (isset($listings_data['pagination']['total'])) {
            $total_available = intval($listings_data['pagination']['total']);
        } elseif (isset($listings_data['meta']['total'])) {
            $total_available = intval($listings_data['meta']['total']);
        } elseif (isset($listings_data['total'])) {
            $total_available = intval($listings_data['total']);
        }

        // Process each listing
        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($listings_to_process as $listing) {
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

        $total_processed = $imported + $updated + $skipped + $errors;
        
        $results = array(
            'success' => true,
            'imported' => $imported,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors,
            'total' => $total_available > 0 ? $total_available : $total_processed,
            'processed' => $total_processed,
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
    public function import_single_listing($listing_data) {
        if (empty($listing_data['id'])) {
            \PropertyFinder_Logger::error('Missing listing ID');
            return array('status' => 'error', 'message' => 'Missing listing ID');
        }

        $listing_id = sanitize_text_field($listing_data['id']);
        $listing_data = apply_filters('propertyfinder_listing_before_import', $listing_data);

        // Check if listing already exists
        $existing_post = $this->find_listing_by_api_id($listing_id);

        if ($existing_post) {
            // Update existing listing
            $result = $this->update_listing($existing_post->ID, $listing_data);
            
            if (!$result || is_wp_error($result)) {
                \PropertyFinder_Logger::error('Failed to update listing: ' . $listing_id);
                return array('status' => 'error', 'message' => 'Failed to update listing');
            }
            
            do_action('propertyfinder_listing_updated', $existing_post->ID, $listing_data);
            
            return array('status' => 'updated', 'post_id' => $existing_post->ID);
        } else {
            // Create new listing
            $result = $this->create_listing($listing_data);
            
            if ($result && !is_wp_error($result)) {
                do_action('propertyfinder_listing_imported', $result, $listing_data);
                return array('status' => 'imported', 'post_id' => $result);
            } else {
                $error_msg = is_wp_error($result) ? $result->get_error_message() : 'Unknown error';
                \PropertyFinder_Logger::error('Failed to create listing: ' . $listing_id . ' - ' . $error_msg);
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
            'post_type' => PropertyFinder_Config::get_cpt_name(),
            'posts_per_page' => 1,
            'post_status' => 'any',
            'meta_query' => array(
                array(
                    'key' => PropertyFinder_Config::get_meta_field('api_id'),
                    'value' => $api_id,
                    'compare' => '=',
                ),
            ),
        ));

        return !empty($posts) ? $posts[0] : null;
    }

    /**
     * Create or update listing post
     * Unified method that handles both create and update operations
     *
     * @param array $listing_data Listing data
     * @param int|null $post_id Post ID (null for create, existing ID for update)
     * @return int|WP_Error|bool Post ID on success, false/WP_Error on failure
     */
    private function save_listing($listing_data, $post_id = null) {
        // Get title and description
        $title = $this->get_listing_title($listing_data, $post_id);
        $description = $this->get_listing_description($listing_data, $post_id);
        $post_status = $this->get_post_status_from_api_state($listing_data);

        // Prepare post data
        $post_data = array(
            'post_type'    => PropertyFinder_Config::get_cpt_name(),
            'post_title'   => $title,
            'post_content' => $description,
            'meta_input'   => array(
                '_pf_api_id' => sanitize_text_field($listing_data['id']),
            ),
        );

        // Update or create
        if ($post_id) {
            // Update existing post
            $post_data['ID'] = $post_id;
            $post_data['post_status'] = apply_filters('propertyfinder_update_post_status', $post_status, $post_id, $listing_data);
            $post_data = apply_filters('propertyfinder_listing_update_post_data', $post_data, $listing_data);
            $result = wp_update_post($post_data);
        } else {
            // Create new post
            $post_data['post_status'] = apply_filters('propertyfinder_default_post_status', $post_status, $listing_data);
            $post_data = apply_filters('propertyfinder_listing_post_data', $post_data, $listing_data);
            $result = wp_insert_post($post_data);
        }

        // If successful, set all listing data
        if (!is_wp_error($result) && $result > 0) {
            $final_post_id = $post_id ? $post_id : $result;
            $this->set_listing_complete_data($final_post_id, $listing_data);
        }

        return $result;
    }

    /**
     * Get listing title from data
     *
     * @param array $listing_data Listing data
     * @param int|null $post_id Existing post ID (for updates)
     * @return string Title
     */
    private function get_listing_title($listing_data, $post_id = null) {
        // Try to get from API data
        if (!empty($listing_data['title']['en'])) {
            $title = sanitize_text_field($listing_data['title']['en']);
        } elseif (!empty($listing_data['title']['ar'])) {
            $title = sanitize_text_field($listing_data['title']['ar']);
        } elseif (!empty($listing_data['reference'])) {
            $title = 'Property ' . sanitize_text_field($listing_data['reference']);
        } elseif ($post_id) {
            // Keep existing title for updates
            $title = get_the_title($post_id);
        } else {
            // Fallback for new listings
            $title = 'Property ' . time();
        }
        
        return propertyfinder_format_property_title($title);
    }

    /**
     * Get listing description from data
     *
     * @param array $listing_data Listing data
     * @param int|null $post_id Existing post ID (for updates)
     * @return string Description
     */
    private function get_listing_description($listing_data, $post_id = null) {
        // Try to get from API data
        if (!empty($listing_data['description']['en'])) {
            return wp_kses_post($listing_data['description']['en']);
        } elseif (!empty($listing_data['description']['ar'])) {
            return wp_kses_post($listing_data['description']['ar']);
        } elseif ($post_id) {
            // Keep existing description for updates
            return get_post_field('post_content', $post_id);
        }
        
        return '';
    }

    /**
     * Set all listing data (meta, taxonomies, images, etc.)
     * Used by both create and update operations
     *
     * @param int $post_id Post ID
     * @param array $listing_data Listing data
     */
    private function set_listing_complete_data($post_id, $listing_data) {
        // Save complete JSON data and sync time
        update_post_meta($post_id, '_pf_imported_json', json_encode($listing_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        update_post_meta($post_id, '_pf_last_synced', current_time('mysql'));
        
        // Set all listing components
        $this->set_listing_meta($post_id, $listing_data);
        $this->set_listing_taxonomies($post_id, $listing_data);
        $this->set_agent_details($post_id, $listing_data);
        $this->set_listing_image($post_id, $listing_data, true);
        $this->set_listing_gallery($post_id, $listing_data, true);
    }

    /**
     * Create listing post
     *
     * @param array $listing_data Listing data
     * @return int|WP_Error Post ID or error
     */
    private function create_listing($listing_data) {
        return $this->save_listing($listing_data);
    }

    /**
     * Update listing post
     *
     * @param int $post_id Post ID
     * @param array $listing_data Listing data
     * @return bool|WP_Error
     */
    private function update_listing($post_id, $listing_data) {
        return $this->save_listing($listing_data, $post_id);
    }

    /**
     * Get WordPress post status from API state
     *
     * @param array $listing_data Listing data
     * @return string Post status (publish or draft)
     */
    private function get_post_status_from_api_state($listing_data) {
        // Get API state
        $api_state = '';
        if (isset($listing_data['state']['stage'])) {
            $api_state = $listing_data['state']['stage'];
        } elseif (isset($listing_data['state'])) {
            $api_state = is_array($listing_data['state']) ? (isset($listing_data['state']['stage']) ? $listing_data['state']['stage'] : '') : $listing_data['state'];
        }
        
        // If state is 'live', publish; otherwise draft
        return (strtolower($api_state) === 'live') ? 'publish' : 'draft';
    }

    /**
     * Extract price amount from price structure based on type
     *
     * @param array|null $price_data Price data from API
     * @param string $price_type Price type (sale, daily, weekly, monthly, yearly)
     * @return float Price amount
     */
    private function extract_price_amount($price_data, $price_type) {
        if (empty($price_data) || !is_array($price_data) || !isset($price_data['amounts'])) {
            return 0;
        }
        
        $amounts = $price_data['amounts'];
        
        if ($price_type === 'sale' && isset($amounts['sale'])) {
            return floatval($amounts['sale']);
        } elseif (in_array($price_type, array('daily', 'weekly', 'monthly', 'yearly')) && isset($amounts[$price_type])) {
            return floatval($amounts[$price_type]);
        }
        
        return 0;
    }

    /**
     * Set listing meta fields
     *
     * @param int $post_id Post ID
     * @param array $listing_data Listing data
     */
    private function set_listing_meta($post_id, $listing_data) {
        // Determine offering type based on price type: sale = sale, else = rent
        $price_type = isset($listing_data['price']['type']) ? $listing_data['price']['type'] : '';
        $offering_type = ($price_type === 'sale') ? 'sale' : 'rent';
        
        // Use meta fields config mapping (easy to modify)
        $meta_fields = PropertyFinder_Meta_Fields_Config::map_listing_fields($listing_data);
        
        // Extract price amount separately (special handling)
        $price_amount = $this->extract_price_amount($listing_data['price'] ?? null, $price_type);
        if ($price_amount > 0) {
            $meta_fields['_pf_price_amount'] = $price_amount;
        }
        
        // Legacy filter support - merge with config mapping
        $legacy_meta_fields = apply_filters('propertyfinder_listing_meta_fields', array(), $listing_data);
        $meta_fields = array_merge($meta_fields, $legacy_meta_fields);
        
        // Update all meta fields - update even empty strings to clear old values
        foreach ($meta_fields as $meta_key => $meta_value) {
            // Only skip null values (not set in API response)
            // Update empty strings to clear fields that are no longer in API
            if ($meta_value !== null) {
                update_post_meta($post_id, $meta_key, $meta_value);
            }
        }
        
        // Ensure critical fields are always set
        if (isset($price_type) && !empty($price_type)) {
            update_post_meta($post_id, '_pf_price_type', $price_type);
        }
        if (isset($offering_type) && !empty($offering_type)) {
            update_post_meta($post_id, '_pf_offering_type', $offering_type);
        }
        
        // Store price structure if available
        if (isset($listing_data['price']) && is_array($listing_data['price'])) {
            update_post_meta($post_id, '_pf_price_structure', maybe_serialize($listing_data['price']));
        }

        \PropertyFinder_Logger::update('Listing meta fields updated', array('post_id' => $post_id, 'meta_count' => count($meta_fields)));
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
            // Format property type - remove hyphens
            $formatted_type = propertyfinder_format_property_type($listing_data['type']);
            $this->set_taxonomy_term($post_id, 'pf_property_type', $formatted_type);
        }

        // Category - only ONE taxonomy
        if (isset($listing_data['category'])) {
            $this->set_taxonomy_term($post_id, 'pf_category', $listing_data['category']);
        }
        
        // Project Status is NOT a taxonomy - store as meta
        if (isset($listing_data['projectStatus'])) {
            update_post_meta($post_id, '_pf_project_status', sanitize_text_field($listing_data['projectStatus']));
        }

        // Location - fetch from API and store as post meta
        if (isset($listing_data['location']['id'])) {
            $location_api_id = intval($listing_data['location']['id']);
            
            // Fetch location details from API
            $location_data = $this->fetch_location_from_api($location_api_id);
            
            if ($location_data) {
                // Store location data as post meta
                update_post_meta($post_id, '_pf_location_id', $location_api_id);
                update_post_meta($post_id, '_pf_location_name', isset($location_data['name']) ? sanitize_text_field($location_data['name']) : '');
                update_post_meta($post_id, '_pf_location_type', isset($location_data['type']) ? sanitize_text_field($location_data['type']) : '');
                
                // Coordinates
                if (isset($location_data['coordinates']['lat'])) {
                    update_post_meta($post_id, '_pf_location_lat', floatval($location_data['coordinates']['lat']));
                }
                if (isset($location_data['coordinates']['lng'])) {
                    update_post_meta($post_id, '_pf_location_lng', floatval($location_data['coordinates']['lng']));
                }
                
                // Tree structure for hierarchy
                if (isset($location_data['tree']) && is_array($location_data['tree'])) {
                    update_post_meta($post_id, '_pf_location_tree', maybe_serialize($location_data['tree']));
                    
                    // Build location path from tree
                    $location_path = array();
                    foreach ($location_data['tree'] as $tree_item) {
                        if (isset($tree_item['name'])) {
                            $location_path[] = $tree_item['name'];
                        }
                    }
                    if (!empty($location_path)) {
                        update_post_meta($post_id, '_pf_location_path', implode(' > ', $location_path));
                    }
                }
                
                // Full location data as JSON
                update_post_meta($post_id, '_pf_location_data', maybe_serialize($location_data));
                
                \PropertyFinder_Logger::location('Location data fetched and stored', array('post_id' => $post_id, 'location_name' => $location_data['name'], 'location_id' => $location_api_id));
            } else {
                // If API fetch fails, at least store the ID
                update_post_meta($post_id, '_pf_location_id', $location_api_id);
                \PropertyFinder_Logger::warning('Failed to fetch location from API. Location ID stored only.', array('post_id' => $post_id, 'location_id' => $location_api_id));
            }
        }
        
        // Store other fields as metadata (not taxonomies)
        // Furnishing Type - store as meta
        if (isset($listing_data['furnishingType'])) {
            update_post_meta($post_id, '_pf_furnishing_type', sanitize_text_field($listing_data['furnishingType']));
        }
        
        

        // UAE Emirate - store as meta
        if (isset($listing_data['uaeEmirate'])) {
            update_post_meta($post_id, '_pf_uae_emirate', sanitize_text_field($listing_data['uaeEmirate']));
        }

        // Amenities array
        if (isset($listing_data['amenities']) && is_array($listing_data['amenities'])) {
            $formatted_amenities = array();
            foreach ($listing_data['amenities'] as $amenity) {
                // Format amenity name - remove hyphens, capitalize, remove duplicates
                $formatted_amenity = propertyfinder_format_amenity_name($amenity);
                if (!empty($formatted_amenity)) {
                    // Store in taxonomy
                    $this->set_taxonomy_term($post_id, 'pf_amenity', $formatted_amenity);
                    // Also store formatted version in array for meta
                    $formatted_amenities[] = $formatted_amenity;
                }
            }
            // Save formatted amenities to meta (remove duplicates)
            if (!empty($formatted_amenities)) {
                update_post_meta($post_id, '_pf_amenities', maybe_serialize(array_values(array_unique($formatted_amenities))));
            }
        }

        // Payment Methods - store as meta (not taxonomy)
        if (isset($listing_data['price']['paymentMethods']) && is_array($listing_data['price']['paymentMethods'])) {
            update_post_meta($post_id, '_pf_payment_methods', maybe_serialize($listing_data['price']['paymentMethods']));
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
     * Fetch location from API by ID
     *
     * @param int $location_id API location ID
     * @return array|null Location data or null on failure
     */
    private function fetch_location_from_api($location_id) {
        if (empty($location_id)) {
            return null;
        }
        
        try {
            // Use filter by ID to get specific location
            $location_data = $this->api->get_locations(array(
                'filter[id]' => $location_id,
                'perPage' => 1,
            ));
            
            if ($location_data && isset($location_data['data']) && !empty($location_data['data'])) {
                return $location_data['data'][0];
            }
            
            return null;
        } catch (Exception $e) {
            \PropertyFinder_Logger::error('Error fetching location from API', array('location_id' => $location_id, 'error' => $e->getMessage()));
            return null;
        }
    }

    /**
     * Set agent details for property
     *
     * @param int $post_id Post ID
     * @param array $listing_data Listing data
     */
    private function set_agent_details($post_id, $listing_data) {
        if (!isset($listing_data['assignedTo']['id'])) {
            return;
        }

        $agent_api_id = intval($listing_data['assignedTo']['id']);
        
        // Try to find agent WordPress post by API ID
        $agent_posts = get_posts(array(
            'post_type' => PropertyFinder_Config::get_agent_cpt_name(),
            'posts_per_page' => 1,
            'post_status' => 'any',
            'meta_query' => array(
                array(
                    'key' => '_pf_api_id',
                    'value' => $agent_api_id,
                    'compare' => '=',
                ),
            ),
        ));

        if (!empty($agent_posts)) {
            $agent_post = $agent_posts[0];
            $agent_meta = get_post_meta($agent_post->ID);
            
            // Update agent details from WordPress post
            if (isset($agent_meta['_pf_email'][0])) {
                update_post_meta($post_id, '_pf_assigned_to_email', $agent_meta['_pf_email'][0]);
            }
            if (isset($agent_meta['_pf_phone'][0])) {
                update_post_meta($post_id, '_pf_assigned_to_phone', $agent_meta['_pf_phone'][0]);
            }
            
            // Get featured image
            $featured_image_id = get_post_thumbnail_id($agent_post->ID);
            if ($featured_image_id) {
                $photo_url = wp_get_attachment_image_url($featured_image_id, 'thumbnail');
                if ($photo_url) {
                    update_post_meta($post_id, '_pf_assigned_to_photo', $photo_url);
                }
            }
        }
    }

    /**
     * Set listing featured image from API data
     *
     * @param int $post_id Post ID
     * @param array $listing_data Listing data
     * @param bool $force_update Force update even if image exists
     */
    private function set_listing_image($post_id, $listing_data, $force_update = false) {
        // Skip if image exists and not forcing update
        if (get_post_thumbnail_id($post_id) && !$force_update) {
            $force_update = apply_filters('propertyfinder_property_force_image_update', false, $post_id);
            if (!$force_update) {
                return;
            }
        }

        // Get image URL from first image in media array
        $image_url = $this->get_image_url_from_listing($listing_data, 0);

        if (empty($image_url)) {
            return;
        }

        // Download, optimize, and set as featured image
        $attachment_id = propertyfinder_download_and_optimize_image(
            $image_url,
            $post_id,
            sprintf(__('Property image for %s', 'propertyfinder'), get_the_title($post_id))
        );

        if ($attachment_id) {
            set_post_thumbnail($post_id, $attachment_id);
        }
    }

    /**
     * Set property gallery images from API data
     *
     * @param int $post_id Post ID
     * @param array $listing_data Listing data
     * @param bool $force_update Force update even if gallery exists
     */
    private function set_listing_gallery($post_id, $listing_data, $force_update = false) {
        // Skip if gallery exists and not forcing update
        if (!$force_update) {
            $existing_gallery = get_post_meta($post_id, '_pf_gallery_images', true);
            if (!empty($existing_gallery) && is_array($existing_gallery)) {
                $force_update = apply_filters('propertyfinder_property_force_gallery_update', false, $post_id);
                if (!$force_update) {
                    return;
                }
            }
        }
        
        // Clear old gallery meta if forcing update
        if ($force_update) {
            delete_post_meta($post_id, '_pf_gallery_images');
        }

        // Get images from API (skip first one as it's the featured image)
        if (empty($listing_data['media']['images']) || !is_array($listing_data['media']['images'])) {
            return;
        }

        $gallery_images = array_slice($listing_data['media']['images'], 1);
        if (empty($gallery_images)) {
            return;
        }

        // Download and optimize gallery images
        $max_width = apply_filters('propertyfinder_gallery_image_max_width', 1920, $post_id);
        $max_height = apply_filters('propertyfinder_gallery_image_max_height', 1920, $post_id);

        $attachment_ids = propertyfinder_download_gallery_images(
            $gallery_images,
            $post_id,
            sprintf(__('Property gallery image for %s', 'propertyfinder'), get_the_title($post_id)),
            $max_width,
            $max_height
        );

        // Save gallery attachment IDs
        if (!empty($attachment_ids)) {
            update_post_meta($post_id, '_pf_gallery_images', $attachment_ids);
        }
    }

    /**
     * Get image URL from listing data
     *
     * @param array $listing_data Listing data
     * @param int $index Image index (0 for featured, 1+ for gallery)
     * @return string Image URL or empty string
     */
    private function get_image_url_from_listing($listing_data, $index = 0) {
        if (empty($listing_data['media']['images']) || !is_array($listing_data['media']['images'])) {
            return '';
        }

        if (!isset($listing_data['media']['images'][$index])) {
            return '';
        }

        $image = $listing_data['media']['images'][$index];
        
        // Try different image sizes in order of preference
        if (!empty($image['original']['url'])) {
            return $image['original']['url'];
        } elseif (!empty($image['large']['url'])) {
            return $image['large']['url'];
        } elseif (!empty($image['medium']['url'])) {
            return $image['medium']['url'];
        } elseif (!empty($image['thumbnail']['url'])) {
            return $image['thumbnail']['url'];
        } elseif (!empty($image['url'])) {
            return $image['url'];
        }

        return '';
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
        $sync_enabled = PropertyFinder_Config::get('sync_enabled', false);

        if ($sync_enabled) {
            \PropertyFinder_Logger::sync('Scheduled sync started');
            $this->import_listings();
            \PropertyFinder_Logger::sync('Scheduled sync completed');
        }
    }
}

