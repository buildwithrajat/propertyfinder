<?php
/**
 * Meta Fields Configuration
 * 
 * Maps API fields to WordPress meta fields
 * Easy to modify and maintain
 *
 * @package PropertyFinder
 * @subpackage Config
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Meta Fields Configuration Class
 */
class PropertyFinder_Meta_Fields_Config {

    /**
     * Get API to Meta field mapping
     * 
     * Format: 'api_field_path' => 'meta_field_key'
     * Use dot notation for nested API fields (e.g., 'location.id' => 'location_id')
     * 
     * @return array Mapping array
     */
    public static function get_mapping() {
        return apply_filters('propertyfinder_meta_fields_mapping', array(
            // Basic Info
            'id'                    => 'api_id',
            'reference'             => 'reference',
            'category'              => 'category',
            'type'                  => 'type',
            
            // Title & Description (multilingual)
            'title.en'              => 'title_en',
            'title.ar'              => 'title_ar',
            'description.en'         => 'description_en',
            'description.ar'       => 'description_ar',
            
            // Property Details
            'bedrooms'              => 'bedrooms',
            'bathrooms'             => 'bathrooms',
            'size'                  => 'size',
            'floorNumber'           => 'floor_number',
            'unitNumber'            => 'unit_number',
            'plotNumber'            => 'plot_number',
            'plotSize'              => 'plot_size',
            'landNumber'            => 'land_number',
            'numberOfFloors'        => 'number_of_floors',
            'parkingSlots'          => 'parking_slots',
            
            // Furnishing & Finishing
            'furnishingType'        => 'furnishing_type',
            'finishingType'         => 'finishing_type',
            
            // Status & Dates
            'projectStatus'         => 'project_status',
            'availableFrom'         => 'available_from',
            'age'                   => 'age',
            
            // Price Structure
            'price'                 => 'price_structure', // Full price object (serialized)
            'price.type'            => 'price_type',
            'price.amounts'          => null, // Handled separately in extract_price_amount
            'price.onRequest'       => 'price_on_request',
            
            // Derived fields
            'offering_type'         => 'offering_type', // Calculated from price.type
            'price_amount'          => 'price_amount',  // Extracted from price.amounts
            
            // Location
            'location.id'           => 'location_id',
            
            // Media
            'media.images'          => 'media_images',
            'media.videos'          => 'media_videos',
            
            // UAE Emirate
            'uaeEmirate'            => 'uae_emirate',
            
            // State & Quality
            'state.stage'           => 'state',
            'state.type'            => 'state_type',
            'verificationStatus'    => 'verification_status',
            
            // Amenities
            'amenities'             => 'amenities',
            
            // Agent Assignment
            'assignedTo.id'         => 'assigned_to_id',
            'assignedTo'            => 'assigned_to_data',
            
            // Created/Updated By
            'createdBy.id'          => 'created_by_id',
            'updatedBy.id'          => 'updated_by_id',
            
            // Timestamps
            'createdAt'             => 'created_at',
            'updatedAt'             => 'updated_at',
        ));
    }

    /**
     * Get meta field key with prefix
     * 
     * @param string $field_key Field key from mapping
     * @return string Full meta field name with prefix
     */
    public static function get_meta_key($field_key) {
        $prefix = PropertyFinder_Config::get('meta_prefix', '_pf_');
        return $prefix . $field_key;
    }

    /**
     * Get API field value from nested path
     * 
     * @param array $data API data array
     * @param string $path Dot-notation path (e.g., 'location.id')
     * @return mixed Field value or null
     */
    public static function get_api_value($data, $path) {
        $keys = explode('.', $path);
        $value = $data;
        
        foreach ($keys as $key) {
            if (!is_array($value) || !isset($value[$key])) {
                return null;
            }
            $value = $value[$key];
        }
        
        return $value;
    }

    /**
     * Extract value from API data using mapping
     * 
     * @param array $api_data API response data
     * @param string $api_path API field path from mapping
     * @return mixed Extracted value
     */
    public static function extract_value($api_data, $api_path) {
        return self::get_api_value($api_data, $api_path);
    }

    /**
     * Get all meta fields for a listing
     * 
     * @param array $listing_data API listing data
     * @return array Array of meta_key => meta_value pairs
     */
    public static function map_listing_fields($listing_data) {
        $mapping = self::get_mapping();
        $meta_fields = array();
        $price_type = self::get_api_value($listing_data, 'price.type') ?: '';
        
        foreach ($mapping as $api_path => $meta_key) {
            // Skip null values (handled separately)
            if ($meta_key === null) {
                continue;
            }
            
            // Get full meta key with prefix
            $full_meta_key = self::get_meta_key($meta_key);
            
            // Extract value from API data
            $value = self::extract_value($listing_data, $api_path);
            
            // Handle special cases
            if ($api_path === 'price') {
                // Serialize full price structure
                $value = !empty($value) ? maybe_serialize($value) : '';
            } elseif (in_array($api_path, array('media.images', 'media.videos', 'amenities', 'assignedTo', 'price.mortgage', 'price.obligation', 'price.paymentMethods', 'price.valueAffected'))) {
                // Serialize arrays/objects
                $value = (!empty($value) && is_array($value)) ? maybe_serialize($value) : '';
            } elseif (strpos($api_path, 'price.') === 0) {
                // Handle price sub-fields separately (already in price structure)
                continue;
            } elseif ($api_path === 'offering_type') {
                // Calculate offering type from price type
                $value = ($price_type === 'sale') ? 'sale' : 'rent';
            } elseif ($api_path === 'price_amount') {
                // Extract price amount based on price type (handled in importer)
                continue;
            } else {
                // Sanitize text fields
                if (is_string($value)) {
                    $value = sanitize_text_field($value);
                } elseif (is_numeric($value)) {
                    $value = is_float($value) ? floatval($value) : intval($value);
                }
            }
            
            // Only add non-empty values
            if ($value !== null && $value !== '') {
                $meta_fields[$full_meta_key] = $value;
            }
        }
        
        // Add special fields
        // Offering type (calculated)
        if (!empty($price_type)) {
            $offering_type = ($price_type === 'sale') ? 'sale' : 'rent';
            $meta_fields[self::get_meta_key('offering_type')] = $offering_type;
        }
        
        // Last synced timestamp
        $meta_fields[self::get_meta_key('last_synced')] = current_time('mysql');
        
        return apply_filters('propertyfinder_mapped_meta_fields', $meta_fields, $listing_data);
    }
}

