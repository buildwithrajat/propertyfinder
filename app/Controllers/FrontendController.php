<?php
/**
 * Frontend area controller
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
 * Frontend Controller class
 */
class FrontendController extends BaseController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // CSS
        wp_enqueue_style(
            'propertyfinder-frontend',
            PROPERTYFINDER_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            PROPERTYFINDER_VERSION
        );
        
        // Agent single page CSS (conditionally loaded)
        if (is_singular(\PropertyFinder_Config::get_agent_cpt_name())) {
            wp_enqueue_style(
                'propertyfinder-agent-single',
                PROPERTYFINDER_PLUGIN_URL . 'assets/css/agent-single.css',
                array('propertyfinder-frontend'),
                PROPERTYFINDER_VERSION
            );
        }
        
        // JavaScript
        wp_enqueue_script(
            'propertyfinder-frontend',
            PROPERTYFINDER_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            PROPERTYFINDER_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('propertyfinder-frontend', 'propertyfinderFrontend', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('propertyfinder_frontend_nonce'),
        ));
    }

    /**
     * Property list shortcode
     * 
     * Usage: [propertyfinder_list limit="10" order="desc" status="active"]
     * 
     * Template: frontend/property-list.php
     * Theme Override: wp-content/themes/{your-theme}/propertyfinder/frontend/property-list.php
     * 
     * @param array $atts Shortcode attributes
     * @return string Rendered HTML
     */
    public function shortcode_property_list($atts) {
        $atts = shortcode_atts(array(
            'limit' => 10,
            'order' => 'desc',
            'status' => 'active',
        ), $atts);
        
        $data = array(
            'properties' => array(), // Load from model
            'atts' => $atts,
        );
        
        return $this->render('frontend/property-list', $data);
    }

    /**
     * Single property shortcode
     * 
     * Usage: [propertyfinder_single id="123"]
     * 
     * Template: frontend/property-single.php
     * Theme Override: wp-content/themes/{your-theme}/propertyfinder/frontend/property-single.php
     * 
     * @param array $atts Shortcode attributes
     * @return string Rendered HTML
     */
    public function shortcode_property_single($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts);
        
        // If no ID provided, try to get from current post
        if (empty($atts['id'])) {
            global $post;
            if ($post && $post->post_type === \PropertyFinder_Config::get_cpt_name()) {
                $post_id = $post->ID;
            } else {
                return __('Property ID is required.', 'propertyfinder');
            }
        } else {
            $post_id = intval($atts['id']);
        }
        
        // Load property post
        $property_post = get_post($post_id);
        
        if (!$property_post || $property_post->post_type !== \PropertyFinder_Config::get_cpt_name()) {
            return __('Property not found.', 'propertyfinder');
        }
        
        // Get all property meta
        $meta = get_post_meta($post_id);
        
        // Get property data
        $property = array(
            'id' => $post_id,
            'title' => get_the_title($post_id),
            'content' => $property_post->post_content,
            'excerpt' => $property_post->post_excerpt,
            'featured_image' => get_the_post_thumbnail_url($post_id, 'large'),
            'gallery_images' => $this->get_property_gallery($post_id),
            
            // Basic info
            'reference' => isset($meta['_pf_reference'][0]) ? $meta['_pf_reference'][0] : '',
            'category' => isset($meta['_pf_category'][0]) ? $meta['_pf_category'][0] : '',
            'type' => isset($meta['_pf_type'][0]) ? $meta['_pf_type'][0] : '',
            
            // Description
            'description_en' => isset($meta['_pf_description_en'][0]) ? $meta['_pf_description_en'][0] : '',
            'description_ar' => isset($meta['_pf_description_ar'][0]) ? $meta['_pf_description_ar'][0] : '',
            
            // Property details
            'bedrooms' => isset($meta['_pf_bedrooms'][0]) ? intval($meta['_pf_bedrooms'][0]) : 0,
            'bathrooms' => isset($meta['_pf_bathrooms'][0]) ? intval($meta['_pf_bathrooms'][0]) : 0,
            'size' => isset($meta['_pf_size'][0]) ? floatval($meta['_pf_size'][0]) : 0,
            'parking_slots' => isset($meta['_pf_parking_slots'][0]) ? intval($meta['_pf_parking_slots'][0]) : 0,
            'floor_number' => isset($meta['_pf_floor_number'][0]) ? $meta['_pf_floor_number'][0] : '',
            'unit_number' => isset($meta['_pf_unit_number'][0]) ? $meta['_pf_unit_number'][0] : '',
            'plot_number' => isset($meta['_pf_plot_number'][0]) ? $meta['_pf_plot_number'][0] : '',
            'plot_size' => isset($meta['_pf_plot_size'][0]) ? floatval($meta['_pf_plot_size'][0]) : 0,
            'land_number' => isset($meta['_pf_land_number'][0]) ? $meta['_pf_land_number'][0] : '',
            'number_of_floors' => isset($meta['_pf_number_of_floors'][0]) ? intval($meta['_pf_number_of_floors'][0]) : 0,
            'age' => isset($meta['_pf_age'][0]) ? $meta['_pf_age'][0] : '',
            'project_status' => isset($meta['_pf_project_status'][0]) ? $meta['_pf_project_status'][0] : '',
            'available_from' => isset($meta['_pf_available_from'][0]) ? $meta['_pf_available_from'][0] : '',
            
            // Location
            'location_name' => isset($meta['_pf_location_name'][0]) ? $meta['_pf_location_name'][0] : '',
            'location_path' => isset($meta['_pf_location_path'][0]) ? $meta['_pf_location_path'][0] : '',
            'uae_emirate' => isset($meta['_pf_uae_emirate'][0]) ? $meta['_pf_uae_emirate'][0] : '',
            'street_direction' => isset($meta['_pf_street_direction'][0]) ? $meta['_pf_street_direction'][0] : '',
            'street_width' => isset($meta['_pf_street_width'][0]) ? $meta['_pf_street_width'][0] : '',
            
            // Price
            'price_type' => isset($meta['_pf_price_type'][0]) ? $meta['_pf_price_type'][0] : '',
            'price_amount' => isset($meta['_pf_price_amount'][0]) ? floatval($meta['_pf_price_amount'][0]) : 0,
            'price_structure' => isset($meta['_pf_price_structure'][0]) ? maybe_unserialize($meta['_pf_price_structure'][0]) : null,
            'price_on_request' => isset($meta['_pf_price_on_request'][0]) ? $meta['_pf_price_on_request'][0] : false,
            
            // Amenities/Facilities
            'amenities' => $this->get_property_amenities($post_id),
            
            // Furnishing & Finishing
            'furnishing_type' => isset($meta['_pf_furnishing_type'][0]) ? $meta['_pf_furnishing_type'][0] : '',
            'finishing_type' => isset($meta['_pf_finishing_type'][0]) ? $meta['_pf_finishing_type'][0] : '',
            
            // Agent
            'assigned_agent' => $this->get_property_agent($post_id, $meta),
        );
        
        // Get similar properties
        $similar_properties = $this->get_similar_properties($post_id, $property);
        
        $data = array(
            'property' => (object) $property,
            'similar_properties' => $similar_properties,
        );
        
        return $this->render('frontend/property-single', $data);
    }
    
    /**
     * Get property gallery images
     */
    public function get_property_gallery($post_id) {
        $images = array();
        
        // Add featured image first if available
        $featured_image = get_the_post_thumbnail_url($post_id, 'large');
        if ($featured_image) {
            $images[] = $featured_image;
        }
        
        // Get gallery images from meta
        $media_images = get_post_meta($post_id, '_pf_media_images', true);
        
        if (!empty($media_images)) {
            $media_images = maybe_unserialize($media_images);
            if (is_array($media_images)) {
                foreach ($media_images as $image) {
                    if (is_array($image)) {
                        $url = '';
                        if (isset($image['original']['url'])) {
                            $url = $image['original']['url'];
                        } elseif (isset($image['large']['url'])) {
                            $url = $image['large']['url'];
                        } elseif (isset($image['url'])) {
                            $url = $image['url'];
                        }
                        // Avoid duplicates
                        if ($url && !in_array($url, $images)) {
                            $images[] = $url;
                        }
                    }
                }
            }
        }
        
        return $images;
    }
    
    /**
     * Get property amenities/facilities
     */
    public function get_property_amenities($post_id) {
        $amenities = array();
        
        // Get from taxonomy
        $amenity_terms = wp_get_post_terms($post_id, 'pf_amenity', array('fields' => 'names'));
        if (!is_wp_error($amenity_terms) && !empty($amenity_terms)) {
            $amenities = array_merge($amenities, $amenity_terms);
        }
        
        // Get from meta
        $meta_amenities = get_post_meta($post_id, '_pf_amenities', true);
        if (!empty($meta_amenities)) {
            $meta_amenities = maybe_unserialize($meta_amenities);
            if (is_array($meta_amenities)) {
                foreach ($meta_amenities as $amenity) {
                    if (is_string($amenity)) {
                        $amenities[] = $amenity;
                    } elseif (is_array($amenity) && isset($amenity['name'])) {
                        $amenities[] = $amenity['name'];
                    }
                }
            }
        }
        
        // Remove duplicates and format
        $amenities = array_unique($amenities);
        $amenities = array_map(function($amenity) {
            return propertyfinder_format_amenity_name($amenity);
        }, $amenities);
        
        return array_values($amenities);
    }
    
    /**
     * Get property agent information
     */
    public function get_property_agent($post_id, $meta) {
        $agent_id = isset($meta['_pf_assigned_to_id'][0]) ? $meta['_pf_assigned_to_id'][0] : '';
        
        if (empty($agent_id)) {
            return null;
        }
        
        // Try to get agent post
        $agent_post = propertyfinder_get_agent_by_api_id($agent_id);
        
        $agent_data = array(
            'id' => $agent_id,
            'name' => isset($meta['_pf_assigned_to_name'][0]) ? $meta['_pf_assigned_to_name'][0] : '',
            'email' => isset($meta['_pf_assigned_to_email'][0]) ? $meta['_pf_assigned_to_email'][0] : '',
            'phone' => isset($meta['_pf_assigned_to_phone'][0]) ? $meta['_pf_assigned_to_phone'][0] : '',
            'photo' => isset($meta['_pf_assigned_to_photo'][0]) ? $meta['_pf_assigned_to_photo'][0] : '',
        );
        
        // If agent post exists, get public profile info
        if ($agent_post) {
            $agent_meta = get_post_meta($agent_post->ID);
            if (empty($agent_data['name'])) {
                $agent_data['name'] = isset($agent_meta['_pf_public_profile_name'][0]) ? $agent_meta['_pf_public_profile_name'][0] : '';
                if (empty($agent_data['name'])) {
                    $first_name = isset($agent_meta['_pf_first_name'][0]) ? $agent_meta['_pf_first_name'][0] : '';
                    $last_name = isset($agent_meta['_pf_last_name'][0]) ? $agent_meta['_pf_last_name'][0] : '';
                    $agent_data['name'] = trim($first_name . ' ' . $last_name);
                }
            }
            if (empty($agent_data['email'])) {
                $agent_data['email'] = isset($agent_meta['_pf_public_profile_email'][0]) ? $agent_meta['_pf_public_profile_email'][0] : '';
            }
            if (empty($agent_data['phone'])) {
                $agent_data['phone'] = isset($agent_meta['_pf_public_profile_phone'][0]) ? $agent_meta['_pf_public_profile_phone'][0] : '';
            }
            if (empty($agent_data['photo'])) {
                $agent_data['photo'] = get_the_post_thumbnail_url($agent_post->ID, 'medium');
            }
        }
        
        return !empty($agent_data['name']) ? (object) $agent_data : null;
    }
    
    /**
     * Get similar properties
     */
    public function get_similar_properties($current_post_id, $current_property, $limit = 6) {
        $args = array(
            'post_type' => \PropertyFinder_Config::get_cpt_name(),
            'posts_per_page' => $limit,
            'post_status' => 'publish',
            'post__not_in' => array($current_post_id),
            'orderby' => 'rand',
        );
        
        // Try to match by location or category
        $meta_query = array('relation' => 'OR');
        
        if (!empty($current_property['location_name'])) {
            $meta_query[] = array(
                'key' => '_pf_location_name',
                'value' => $current_property['location_name'],
                'compare' => 'LIKE',
            );
        }
        
        if (!empty($current_property['category'])) {
            $meta_query[] = array(
                'key' => '_pf_category',
                'value' => $current_property['category'],
                'compare' => '=',
            );
        }
        
        if (count($meta_query) > 1) {
            $args['meta_query'] = $meta_query;
        }
        
        $similar_posts = get_posts($args);
        
        $similar_properties = array();
        foreach ($similar_posts as $post) {
            $meta = get_post_meta($post->ID);
            $similar_properties[] = array(
                'id' => $post->ID,
                'title' => get_the_title($post->ID),
                'url' => get_permalink($post->ID),
                'featured_image' => get_the_post_thumbnail_url($post->ID, 'medium'),
                'bedrooms' => isset($meta['_pf_bedrooms'][0]) ? intval($meta['_pf_bedrooms'][0]) : 0,
                'bathrooms' => isset($meta['_pf_bathrooms'][0]) ? intval($meta['_pf_bathrooms'][0]) : 0,
                'size' => isset($meta['_pf_size'][0]) ? floatval($meta['_pf_size'][0]) : 0,
                'price_amount' => isset($meta['_pf_price_amount'][0]) ? floatval($meta['_pf_price_amount'][0]) : 0,
                'location_name' => isset($meta['_pf_location_name'][0]) ? $meta['_pf_location_name'][0] : '',
            );
        }
        
        return $similar_properties;
    }

    /**
     * Handle get properties AJAX request
     */
    public function handle_get_properties() {
        check_ajax_referer('propertyfinder_frontend_nonce', 'nonce');
        
        // Get properties from model
        $properties = array(); // Load from model
        
        wp_send_json_success(array(
            'properties' => $properties,
            'total' => count($properties),
        ));
    }
}

