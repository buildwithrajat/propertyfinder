<?php
/**
 * Property Single Page Template (Full Page with Header/Footer)
 *
 * This template is used when there's no theme override
 * It includes WordPress header and footer
 *
 * @package PropertyFinder
 * @subpackage Templates
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get WordPress header
get_header();

// Get property data using FrontendController
global $post;

if ($post && $post->post_type === PropertyFinder_Config::get_cpt_name()) {
    $frontend_controller = new \PropertyFinder\Controllers\FrontendController();
    
    // Get property data using the same method as shortcode
    $meta = get_post_meta($post->ID);
    
    // Build property array (same as in FrontendController)
    $property = array(
        'id' => $post->ID,
        'title' => get_the_title($post->ID),
        'content' => $post->post_content,
        'excerpt' => $post->post_excerpt,
        'featured_image' => get_the_post_thumbnail_url($post->ID, 'large'),
        'gallery_images' => $frontend_controller->get_property_gallery($post->ID),
        
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
        'amenities' => $frontend_controller->get_property_amenities($post->ID),
        
        // Furnishing & Finishing
        'furnishing_type' => isset($meta['_pf_furnishing_type'][0]) ? $meta['_pf_furnishing_type'][0] : '',
        'finishing_type' => isset($meta['_pf_finishing_type'][0]) ? $meta['_pf_finishing_type'][0] : '',
        
        // Agent
        'assigned_agent' => $frontend_controller->get_property_agent($post->ID, $meta),
    );
    
    // Get similar properties (pass as array, method expects array)
    $similar_properties = $frontend_controller->get_similar_properties($post->ID, $property);
    
    // Convert to object for template
    $property = (object) $property;
    
    // Include the property single view template
    $view_file = PROPERTYFINDER_PLUGIN_DIR . 'app/Views/frontend/property-single.php';
    if (file_exists($view_file)) {
        include $view_file;
    } else {
        echo '<p>' . __('Property template not found.', 'propertyfinder') . '</p>';
    }
} else {
    echo '<p>' . __('Property not found.', 'propertyfinder') . '</p>';
}

// Get WordPress footer
get_footer();

