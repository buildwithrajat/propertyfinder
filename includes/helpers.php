<?php
/**
 * PropertyFinder Helper Functions
 *
 * @package PropertyFinder
 * @subpackage Includes
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Get amenities for a property/listing
 *
 * @param int $post_id Post ID
 * @return array Array of amenity terms
 */
function propertyfinder_get_amenities($post_id) {
    $amenities = wp_get_post_terms($post_id, 'pf_amenity', array('fields' => 'all'));
    
    // Also check meta field for raw amenities array
    $meta_amenities = get_post_meta($post_id, '_pf_amenities', true);
    if (!empty($meta_amenities)) {
        $meta_amenities = maybe_unserialize($meta_amenities);
        if (is_array($meta_amenities)) {
            return array(
                'terms' => $amenities,
                'meta' => $meta_amenities,
            );
        }
    }
    
    return array(
        'terms' => $amenities,
        'meta' => array(),
    );
}

/**
 * Get category for a property/listing
 *
 * @param int $post_id Post ID
 * @return array Array of category terms
 */
function propertyfinder_get_categories($post_id) {
    $categories = wp_get_post_terms($post_id, 'pf_category', array('fields' => 'all'));
    
    // Also get from meta
    $meta_category = get_post_meta($post_id, '_pf_category', true);
    
    return array(
        'terms' => $categories,
        'meta' => $meta_category,
    );
}

/**
 * Get agent assigned to a property/listing
 *
 * @param int $post_id Post ID
 * @return array|false Agent data or false if not assigned
 */
function propertyfinder_get_assigned_agent($post_id) {
    $agent_id = get_post_meta($post_id, '_pf_assigned_to_id', true);
    
    if (empty($agent_id)) {
        return false;
    }
    
    $agent_data = array(
        'id' => $agent_id,
        'name' => get_post_meta($post_id, '_pf_assigned_to_name', true),
        'data' => get_post_meta($post_id, '_pf_assigned_to_data', true),
    );
    
    // Unserialize if needed
    if (!empty($agent_data['data'])) {
        $agent_data['data'] = maybe_unserialize($agent_data['data']);
    }
    
    return $agent_data;
}

/**
 * Get all agents from PropertyFinder API
 *
 * @param array $params Query parameters
 * @return array|false Users/agents data or false on failure
 */
function propertyfinder_get_agents($params = array()) {
    $api = new PropertyFinder_API();
    return $api->get_users($params);
}

/**
 * Get property by API ID
 *
 * @param string $api_id PropertyFinder API listing ID
 * @return WP_Post|null Post object or null if not found
 */
function propertyfinder_get_property_by_api_id($api_id) {
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
 * Get all amenities available in the system
 *
 * @return array Array of amenity terms
 */
function propertyfinder_get_all_amenities() {
    $amenities = get_terms(array(
        'taxonomy' => 'pf_amenity',
        'hide_empty' => false,
    ));
    
    return is_wp_error($amenities) ? array() : $amenities;
}

/**
 * Get all categories available in the system
 *
 * @return array Array of category terms
 */
function propertyfinder_get_all_categories() {
    $categories = get_terms(array(
        'taxonomy' => 'pf_category',
        'hide_empty' => false,
    ));
    
    return is_wp_error($categories) ? array() : $categories;
}

/**
 * Format property price for display
 *
 * @param int $post_id Post ID
 * @return string Formatted price string
 */
function propertyfinder_format_price($post_id) {
    $price_type = get_post_meta($post_id, '_pf_price_type', true);
    $price_structure = get_post_meta($post_id, '_pf_price_structure', true);
    
    if (empty($price_structure)) {
        return '';
    }
    
    $price = maybe_unserialize($price_structure);
    
    if (empty($price) || !is_array($price)) {
        return '';
    }
    
    $amounts = isset($price['amounts']) ? $price['amounts'] : array();
    
    switch ($price_type) {
        case 'sale':
            $amount = isset($amounts['sale']) ? $amounts['sale'] : 0;
            return number_format($amount) . ' AED';
            
        case 'yearly':
            $amount = isset($amounts['yearly']) ? $amounts['yearly'] : 0;
            return number_format($amount) . ' AED/year';
            
        case 'monthly':
            $amount = isset($amounts['monthly']) ? $amounts['monthly'] : 0;
            return number_format($amount) . ' AED/month';
            
        case 'weekly':
            $amount = isset($amounts['weekly']) ? $amounts['weekly'] : 0;
            return number_format($amount) . ' AED/week';
            
        case 'daily':
            $amount = isset($amounts['daily']) ? $amounts['daily'] : 0;
            return number_format($amount) . ' AED/day';
            
        default:
            return '';
    }
}

/**
 * Download and optimize image from URL
 *
 * @param string $image_url Image URL
 * @param int $post_id Post ID to attach image to
 * @param string $description Image description/alt text
 * @param int $max_width Maximum width for optimization (0 = no resize, default 1920)
 * @param int $max_height Maximum height for optimization (0 = no resize, default 1920)
 * @return int|false Attachment ID or false on failure
 */
function propertyfinder_download_and_optimize_image($image_url, $post_id = 0, $description = '', $max_width = 1920, $max_height = 1920) {
    if (empty($image_url)) {
        return false;
    }

    // Require WordPress media functions
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    // Download image to temp file with timeout
    $temp_file = download_url($image_url, 300); // 5 minute timeout

    if (is_wp_error($temp_file)) {
        if (class_exists('PropertyFinder_Logger')) {
            \PropertyFinder_Logger::log('error', 'Failed to download image', array('url' => $image_url, 'error' => $temp_file->get_error_message()), 'import');
        } else {
            error_log('PropertyFinder: Failed to download image from ' . $image_url . ' - ' . $temp_file->get_error_message());
        }
        return false;
    }

    // Prepare file array for media_handle_sideload
    $file_array = array(
        'name' => basename(parse_url($image_url, PHP_URL_PATH)),
        'tmp_name' => $temp_file,
    );

    // If no filename, generate one
    if (empty($file_array['name'])) {
        $file_array['name'] = 'image-' . time() . '-' . wp_generate_password(6, false) . '.jpg';
    }

    // Ensure proper extension
    $ext = pathinfo($file_array['name'], PATHINFO_EXTENSION);
    if (empty($ext)) {
        $file_array['name'] .= '.jpg';
    }

    // Upload image to media library
    $attachment_id = media_handle_sideload($file_array, $post_id, $description);

    // Clean up temp file if upload failed
    if (is_wp_error($attachment_id)) {
        @unlink($temp_file);
        if (class_exists('PropertyFinder_Logger')) {
            \PropertyFinder_Logger::log('error', 'Failed to upload image', array('url' => $image_url, 'error' => $attachment_id->get_error_message()), 'import');
        } else {
            error_log('PropertyFinder: Failed to upload image - ' . $attachment_id->get_error_message());
        }
        return false;
    }

    // Optimize image - WordPress automatically generates thumbnails, but we can force regeneration
    $attachment_metadata = wp_generate_attachment_metadata($attachment_id, get_attached_file($attachment_id));
    wp_update_attachment_metadata($attachment_id, $attachment_metadata);

    // Compress and resize image if imagick or GD is available
    propertyfinder_compress_image($attachment_id, $max_width, $max_height);

    if (class_exists('PropertyFinder_Logger')) {
        \PropertyFinder_Logger::import('Image downloaded and optimized', array('attachment_id' => $attachment_id, 'url' => $image_url));
    }

    return $attachment_id;
}

/**
 * Compress and optimize image using WordPress image editor
 *
 * @param int $attachment_id Attachment ID
 * @param int $max_width Maximum width for optimization (0 = no resize)
 * @param int $max_height Maximum height for optimization (0 = no resize)
 * @return bool Success status
 */
function propertyfinder_compress_image($attachment_id, $max_width = 1920, $max_height = 1920) {
    $file_path = get_attached_file($attachment_id);
    
    if (empty($file_path) || !file_exists($file_path)) {
        return false;
    }

    $image_editor = wp_get_image_editor($file_path);
    
    if (is_wp_error($image_editor)) {
        return false;
    }

    // Get current image dimensions
    $size = $image_editor->get_size();
    
    // Resize if image is larger than max dimensions
    if (($max_width > 0 || $max_height > 0) && ($size['width'] > $max_width || $size['height'] > $max_height)) {
        $resized = $image_editor->resize($max_width, $max_height, false);
        if (!is_wp_error($resized)) {
            $saved = $image_editor->save($file_path);
            if (is_wp_error($saved)) {
                return false;
            }
        }
    }

    // Get image quality setting (default 85 for better compression vs quality balance)
    $quality = apply_filters('propertyfinder_image_quality', 85, $attachment_id);

    // Set quality and save
    $image_editor->set_quality($quality);
    $saved = $image_editor->save($file_path);

    if (is_wp_error($saved)) {
        return false;
    }

    // Regenerate thumbnails after compression
    $metadata = wp_generate_attachment_metadata($attachment_id, $file_path);
    wp_update_attachment_metadata($attachment_id, $metadata);

    return true;
}

/**
 * Download and optimize multiple images for gallery
 *
 * @param array $image_urls Array of image URLs
 * @param int $post_id Post ID
 * @param string $description Image description
 * @param int $max_width Maximum width for optimization
 * @param int $max_height Maximum height for optimization
 * @return array Array of attachment IDs
 */
function propertyfinder_download_gallery_images($image_urls, $post_id = 0, $description = '', $max_width = 1920, $max_height = 1920) {
    $attachment_ids = array();
    
    if (empty($image_urls) || !is_array($image_urls)) {
        return $attachment_ids;
    }

    foreach ($image_urls as $index => $image_data) {
        // Handle both array (with URL) and string (just URL) formats
        $image_url = '';
        if (is_array($image_data)) {
            // Try different image sizes in order of preference
            if (isset($image_data['original']['url'])) {
                $image_url = $image_data['original']['url'];
            } elseif (isset($image_data['large']['url'])) {
                $image_url = $image_data['large']['url'];
            } elseif (isset($image_data['medium']['url'])) {
                $image_url = $image_data['medium']['url'];
            } elseif (isset($image_data['url'])) {
                $image_url = $image_data['url'];
            }
        } elseif (is_string($image_data)) {
            $image_url = $image_data;
        }

        if (empty($image_url)) {
            continue;
        }

        $img_description = $description;
        if (empty($img_description)) {
            $img_description = sprintf(__('Property gallery image %d', 'propertyfinder'), $index + 1);
        } else {
            $img_description = $description . ' - ' . sprintf(__('Image %d', 'propertyfinder'), $index + 1);
        }

        $attachment_id = propertyfinder_download_and_optimize_image($image_url, $post_id, $img_description, $max_width, $max_height);
        
        if ($attachment_id) {
            $attachment_ids[] = $attachment_id;
        }
    }

    return $attachment_ids;
}

/**
 * Get agent by API ID
 *
 * @param string $api_id PropertyFinder API agent/user ID
 * @return WP_Post|null Post object or null if not found
 */
function propertyfinder_get_agent_by_api_id($api_id) {
    $posts = get_posts(array(
        'post_type' => PropertyFinder_Config::get_agent_cpt_name(),
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
 * Format amenity name for display
 * Replaces hyphens with spaces, capitalizes properly, removes duplicates
 *
 * @param string $amenity Raw amenity name (e.g., "built-in-wardrobes")
 * @return string Formatted amenity name (e.g., "Built In Wardrobes")
 */
function propertyfinder_format_amenity_name($amenity) {
    if (empty($amenity)) {
        return '';
    }
    
    // Replace hyphens with spaces
    $formatted = str_replace('-', ' ', $amenity);
    
    // Remove duplicate words (e.g., "built-in-wardrobes built-in-wardrobes" -> "built in wardrobes")
    $words = explode(' ', $formatted);
    $words = array_unique($words);
    $formatted = implode(' ', $words);
    
    // Capitalize each word properly
    $formatted = ucwords(strtolower(trim($formatted)));
    
    return apply_filters('propertyfinder_formatted_amenity_name', $formatted, $amenity);
}

/**
 * Format property title - remove hyphens
 *
 * @param string $title Property title
 * @return string Formatted title
 */
function propertyfinder_format_property_title($title) {
    if (empty($title)) {
        return $title;
    }
    
    // Replace hyphens with spaces
    $formatted = str_replace('-', ' ', $title);
    
    // Clean up multiple spaces
    $formatted = preg_replace('/\s+/', ' ', $formatted);
    
    // Trim
    $formatted = trim($formatted);
    
    return apply_filters('propertyfinder_formatted_property_title', $formatted, $title);
}

/**
 * Format property type - remove hyphens
 *
 * @param string $type Property type
 * @return string Formatted type
 */
function propertyfinder_format_property_type($type) {
    if (empty($type)) {
        return $type;
    }
    
    // Replace hyphens with spaces
    $formatted = str_replace('-', ' ', $type);
    
    // Capitalize each word
    $formatted = ucwords(strtolower(trim($formatted)));
    
    // Clean up multiple spaces
    $formatted = preg_replace('/\s+/', ' ', $formatted);
    
    return apply_filters('propertyfinder_formatted_property_type', $formatted, $type);
}
