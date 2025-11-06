<?php
/**
 * Agent Meta Fields Configuration
 * 
 * Maps API fields to WordPress meta fields for agents
 * Easy to modify and maintain
 *
 * @package PropertyFinder
 * @subpackage Config
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Agent Meta Fields Configuration Class
 */
class PropertyFinder_Agent_Meta_Fields_Config {

    /**
     * Get API to Meta field mapping for agents
     * 
     * Format: 'api_field_path' => 'meta_field_key'
     * Use dot notation for nested API fields (e.g., 'role.id' => 'role_id')
     * 
     * @return array Mapping array
     */
    public static function get_mapping() {
        return apply_filters('propertyfinder_agent_meta_fields_mapping', array(
            // Basic Info
            'id'                    => 'api_id',
            'firstName'             => 'first_name',
            'lastName'              => 'last_name',
            'email'                 => 'email',
            'mobile'                => 'mobile',
            'status'                => 'status',
            'createdAt'             => 'created_at',
            
            // Call Tracking
            'callTracking.number'   => 'call_tracking_number',
            
            // Role
            'role.id'               => 'role_id',
            'role.name'             => 'role_name',
            'role.roleKey'          => 'role_key',
            'role.baseRoleKey'      => 'base_role_key',
            'role.isCustom'        => 'is_custom_role',
            'role'                  => 'role_data', // Full role object (serialized)
            
            // Public Profile - Basic
            'publicProfile.id'      => 'public_profile_id',
            'publicProfile.name'    => 'public_profile_name',
            'publicProfile.email'  => 'public_profile_email',
            'publicProfile.phone'   => 'public_profile_phone',
            'publicProfile.phoneSecondary' => 'public_profile_phone_secondary',
            'publicProfile.whatsappPhone' => 'public_profile_whatsapp',
            
            // Public Profile - Bio
            'publicProfile.bio.primary' => 'bio_primary',
            'publicProfile.bio.secondary' => 'bio_secondary',
            
            // Public Profile - Position
            'publicProfile.position.primary' => 'position_primary',
            'publicProfile.position.secondary' => 'position_secondary',
            
            // Public Profile - Social
            'publicProfile.linkedinAddress' => 'linkedin_address',
            
            // Public Profile - Verification
            'publicProfile.verification.status' => 'verification_status',
            'publicProfile.verification.requestDate' => 'verification_request_date',
            
            // Public Profile - Flags
            'publicProfile.isSuperAgent' => 'is_super_agent',
            
            // Public Profile - Images
            'publicProfile.imageVariants.large.default' => 'image_url_large',
            'publicProfile.imageVariants.large.jpg' => 'image_url_large_jpg',
            'publicProfile.imageVariants.large.webp' => 'image_url_large_webp',
            
            // Public Profile - Compliances
            'publicProfile.compliances' => 'compliances', // Array (serialized)
            
            // Public Profile - Full Data
            'publicProfile'         => 'public_profile_data', // Full public profile object (serialized)
            
            // Timestamps
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
     * @param string $path Dot-notation path (e.g., 'role.id')
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
     * Get all meta fields for an agent
     * 
     * @param array $agent_data API agent data
     * @return array Array of meta_key => meta_value pairs
     */
    public static function map_agent_fields($agent_data) {
        $mapping = self::get_mapping();
        $meta_fields = array();
        
        foreach ($mapping as $api_path => $meta_key) {
            // Skip null values (handled separately)
            if ($meta_key === null) {
                continue;
            }
            
            // Get full meta key with prefix
            $full_meta_key = self::get_meta_key($meta_key);
            
            // Extract value from API data
            $value = self::extract_value($agent_data, $api_path);
            
            // Handle special cases
            if (in_array($api_path, array('role', 'publicProfile', 'publicProfile.compliances'))) {
                // Serialize arrays/objects
                $value = (!empty($value) && is_array($value)) ? maybe_serialize($value) : '';
            } elseif (strpos($api_path, 'role.') === 0 && $api_path !== 'role') {
                // Handle role sub-fields separately (already in role_data)
                // But we still want individual fields, so continue
            } elseif (strpos($api_path, 'publicProfile.') === 0 && $api_path !== 'publicProfile') {
                // Handle public profile sub-fields separately (already in public_profile_data)
                // But we still want individual fields, so continue
            } elseif ($api_path === 'role.isCustom' || $api_path === 'publicProfile.isSuperAgent') {
                // Convert boolean to string
                $value = ($value === true || $value === '1' || $value === 1) ? '1' : '0';
            } else {
                // Sanitize text fields
                if (is_string($value)) {
                    // Different sanitization based on field type
                    if (strpos($api_path, 'email') !== false) {
                        $value = sanitize_email($value);
                    } elseif (strpos($api_path, 'url') !== false || strpos($api_path, 'Address') !== false) {
                        $value = esc_url_raw($value);
                    } elseif (strpos($api_path, 'bio') !== false) {
                        $value = wp_kses_post($value);
                    } else {
                        $value = sanitize_text_field($value);
                    }
                } elseif (is_numeric($value)) {
                    $value = is_float($value) ? floatval($value) : intval($value);
                }
            }
            
            // Only add non-empty values
            if ($value !== null && $value !== '') {
                $meta_fields[$full_meta_key] = $value;
            }
        }
        
        // Last synced timestamp
        $meta_fields[self::get_meta_key('last_synced')] = current_time('mysql');
        
        return apply_filters('propertyfinder_mapped_agent_meta_fields', $meta_fields, $agent_data);
    }
}

