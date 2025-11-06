<?php
/**
 * API Sync Handler
 *
 * @package PropertyFinder
 * @subpackage Metabox
 */

if (!defined('WPINC')) {
    die;
}

/**
 * API Sync Handler
 */
class PropertyFinder_API_Sync {

    /**
     * Sync property to API
     *
     * @param int $post_id Post ID
     * @return bool
     */
    public function sync_property_to_api($post_id) {
        $api_id = get_post_meta($post_id, '_pf_api_id', true);
        if (empty($api_id)) {
            return false;
        }

        $api_data = $this->build_api_payload($post_id);

        $api = new PropertyFinder_API();
        $result = $api->update_listing($api_id, $api_data);

        if ($result) {
            update_post_meta($post_id, '_pf_last_synced', current_time('mysql'));
            update_post_meta($post_id, '_pf_sync_status', 'success');
            return true;
        } else {
            update_post_meta($post_id, '_pf_sync_status', 'error');
            return false;
        }
    }

    /**
     * Build API payload from post meta
     *
     * @param int $post_id Post ID
     * @return array
     */
    private function build_api_payload($post_id) {
        $meta = get_post_meta($post_id);
        $post = get_post($post_id);
        
        $payload = array();

        // Title and description
        $payload['title']['en'] = $this->get_meta_value($meta, '_pf_title_en') ?: $post->post_title;
        $payload['description']['en'] = $this->get_meta_value($meta, '_pf_description_en') ?: $post->post_content;

        // Price structure
        $price_data = $this->build_price_structure($meta);
        if (!empty($price_data['type'])) {
            $payload['price'] = $price_data;
        }

        // Offering type
        $offering_type = $this->get_meta_value($meta, '_pf_offering_type');
        if (empty($offering_type) && !empty($price_data['type'])) {
            $offering_type = ($price_data['type'] === 'sale') ? 'sale' : 'rent';
        }
        if (!empty($offering_type)) {
            $payload['offeringType'] = $offering_type;
        }

        // Basic property info
        $this->add_basic_info($payload, $meta);

        // Assigned agent
        $assigned_to_id = $this->get_meta_int($meta, '_pf_assigned_to_id');
        if ($assigned_to_id > 0) {
            $payload['assignedTo'] = array('id' => $assigned_to_id);
        }

        // Location
        $location_id = $this->get_meta_int($meta, '_pf_location_id');
        if ($location_id > 0) {
            $payload['location'] = array('id' => $location_id);
        }

        // State
        $state_data = $this->build_state_structure($meta);
        if (!empty($state_data)) {
            $payload['state'] = $state_data;
        }

        return apply_filters('propertyfinder_metabox_api_payload', $payload, $post_id);
    }

    /**
     * Get meta value from meta array
     *
     * @param array $meta Meta array
     * @param string $key Meta key
     * @return string
     */
    private function get_meta_value($meta, $key) {
        return isset($meta[$key][0]) && !empty($meta[$key][0]) ? $meta[$key][0] : '';
    }

    /**
     * Get meta value as integer
     *
     * @param array $meta Meta array
     * @param string $key Meta key
     * @return int
     */
    private function get_meta_int($meta, $key) {
        $value = $this->get_meta_value($meta, $key);
        return !empty($value) ? intval($value) : 0;
    }

    /**
     * Get meta value as float
     *
     * @param array $meta Meta array
     * @param string $key Meta key
     * @return float
     */
    private function get_meta_float($meta, $key) {
        $value = $this->get_meta_value($meta, $key);
        return !empty($value) ? floatval($value) : 0;
    }

    /**
     * Get unserialized meta value
     *
     * @param array $meta Meta array
     * @param string $key Meta key
     * @return array|null
     */
    private function get_meta_unserialized($meta, $key) {
        $value = $this->get_meta_value($meta, $key);
        if (empty($value)) {
            return null;
        }
        $unserialized = maybe_unserialize($value);
        return is_array($unserialized) && !empty($unserialized) ? $unserialized : null;
    }

    /**
     * Build price structure from meta
     *
     * @param array $meta Meta array
     * @return array
     */
    private function build_price_structure($meta) {
        $price_type = $this->get_meta_value($meta, '_pf_price_type');
        $price_amount = $this->get_meta_float($meta, '_pf_price_amount');
        
        if (empty($price_type)) {
            return array();
        }

        // Initialize amounts structure
        $amounts = array(
            'daily' => 0,
            'weekly' => 0,
            'monthly' => 0,
            'yearly' => 0,
            'sale' => 0
        );

        // Set the price amount based on type
        if ($price_amount > 0) {
            if ($price_type === 'sale') {
                $amounts['sale'] = $price_amount;
            } elseif (in_array($price_type, array('daily', 'weekly', 'monthly', 'yearly'))) {
                $amounts[$price_type] = $price_amount;
            }
        }

        $price_data = array(
            'type' => $price_type,
            'amounts' => $amounts,
            'onRequest' => $this->get_meta_value($meta, '_pf_price_on_request') === 'yes'
        );

        // Add optional price fields
        $optional_fields = array(
            'downpayment' => '_pf_price_downpayment',
            'minimalRentalPeriod' => '_pf_price_minimal_rental_period',
            'numberOfCheques' => '_pf_price_number_of_cheques',
            'numberOfMortgageYears' => '_pf_price_number_of_mortgage_years',
        );

        foreach ($optional_fields as $api_key => $meta_key) {
            $value = $this->get_meta_int($meta, $meta_key);
            if ($value > 0) {
                $price_data[$api_key] = $value;
            }
        }

        // Utilities inclusive
        $utilities = $this->get_meta_value($meta, '_pf_price_utilities_inclusive');
        if (!empty($utilities)) {
            $price_data['utilitiesInclusive'] = ($utilities === 'yes');
        }

        // Complex objects (field name => API key mapping)
        $complex_fields = array(
            'mortgage' => 'mortgage',
            'obligation' => 'obligation',
            'payment_methods' => 'paymentMethods',
            'value_affected' => 'valueAffected'
        );
        
        foreach ($complex_fields as $field => $api_key) {
            $value = $this->get_meta_unserialized($meta, '_pf_price_' . $field);
            if ($value !== null) {
                $price_data[$api_key] = $value;
            }
        }

        return $price_data;
    }

    /**
     * Add basic property info to payload
     *
     * @param array $payload Payload array (passed by reference)
     * @param array $meta Meta array
     */
    private function add_basic_info(&$payload, $meta) {
        $basic_fields = array(
            'bedrooms' => array('key' => '_pf_bedrooms', 'type' => 'int'),
            'bathrooms' => array('key' => '_pf_bathrooms', 'type' => 'int'),
            'size' => array('key' => '_pf_size', 'type' => 'float'),
            'unitNumber' => array('key' => '_pf_unit_number', 'type' => 'string'),
            'floorNumber' => array('key' => '_pf_floor_number', 'type' => 'int'),
            'parkingSlots' => array('key' => '_pf_parking_slots', 'type' => 'int'),
        );

        foreach ($basic_fields as $api_key => $field_config) {
            $value = '';
            if ($field_config['type'] === 'int') {
                $value = $this->get_meta_int($meta, $field_config['key']);
            } elseif ($field_config['type'] === 'float') {
                $value = $this->get_meta_float($meta, $field_config['key']);
            } else {
                $value = $this->get_meta_value($meta, $field_config['key']);
            }

            if (!empty($value) || ($field_config['type'] === 'int' && $value === 0)) {
                $payload[$api_key] = $value;
            }
        }
    }

    /**
     * Build state structure from meta
     *
     * @param array $meta Meta array
     * @return array
     */
    private function build_state_structure($meta) {
        $state_stage = $this->get_meta_value($meta, '_pf_state');
        $state_type = $this->get_meta_value($meta, '_pf_state_type');
        
        if (empty($state_type) && empty($state_stage)) {
            return array();
        }

        if (!empty($state_type)) {
            return array(
                'type' => $state_type,
                'stage' => !empty($state_stage) ? $state_stage : $state_type
            );
        }

        return array(
            'stage' => $state_stage,
            'type' => $state_stage
        );
    }
}

