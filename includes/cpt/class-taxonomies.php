<?php
/**
 * Taxonomies Registration
 *
 * @package PropertyFinder
 * @subpackage CPT
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Taxonomies Registration
 */
class PropertyFinder_Taxonomies {

    /**
     * Register all taxonomies
     */
    public function register() {
        // Property Category
        $this->register_taxonomy(
            PropertyFinder_Config::get_taxonomy('category'),
            'Property Category',
            'Property Categories',
            array('slug' => 'property-category')
        );

        // Property Type
        $this->register_taxonomy(
            PropertyFinder_Config::get_taxonomy('property_type'),
            'Property Type',
            'Property Types',
            array('slug' => 'property-type')
        );

        // Amenities (non-public, admin only)
        $this->register_taxonomy(
            PropertyFinder_Config::get_taxonomy('amenity'),
            'Amenity',
            'Amenities',
            array('slug' => 'amenity', 'public' => false, 'show_in_rest' => false)
        );

        // Location taxonomy removed - using post meta with API search instead
    }

    /**
     * Register a taxonomy
     *
     * @param string $taxonomy Taxonomy name
     * @param string $singular Singular label
     * @param string $plural Plural label
     * @param array $args Additional arguments
     */
    private function register_taxonomy($taxonomy, $singular, $plural, $args = array()) {
        $labels = array(
            'name'              => _x($plural, 'taxonomy general name', 'propertyfinder'),
            'singular_name'     => _x($singular, 'taxonomy singular name', 'propertyfinder'),
            'search_items'      => __('Search ' . $plural, 'propertyfinder'),
            'all_items'         => __('All ' . $plural, 'propertyfinder'),
            'parent_item'       => __('Parent ' . $singular, 'propertyfinder'),
            'parent_item_colon' => __('Parent ' . $singular . ':', 'propertyfinder'),
            'edit_item'         => __('Edit ' . $singular, 'propertyfinder'),
            'update_item'       => __('Update ' . $singular, 'propertyfinder'),
            'add_new_item'      => __('Add New ' . $singular, 'propertyfinder'),
            'new_item_name'     => __('New ' . $singular . ' Name', 'propertyfinder'),
            'menu_name'         => __($plural, 'propertyfinder'),
        );

        $default_args = array(
            'hierarchical'      => false, // Amenities are flat list
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => false, // No frontend URLs
            'public'            => false, // Admin only
            'show_in_rest'      => false, // No REST API
            'show_tagcloud'     => false, // No tag cloud
        );
        
        // Override with custom args if provided
        if (isset($args['public'])) {
            $default_args['public'] = $args['public'];
            unset($args['public']);
        }
        if (isset($args['show_in_rest'])) {
            $default_args['show_in_rest'] = $args['show_in_rest'];
            unset($args['show_in_rest']);
        }
        if (isset($args['hierarchical'])) {
            $default_args['hierarchical'] = $args['hierarchical'];
            unset($args['hierarchical']);
        }
        if (isset($args['rewrite']) && $args['rewrite'] === false) {
            $default_args['rewrite'] = false;
            unset($args['rewrite']);
        }

        $args = apply_filters('propertyfinder_taxonomy_args', array_merge($default_args, $args), $taxonomy);

        register_taxonomy($taxonomy, array(PropertyFinder_Config::get_cpt_name()), $args);
    }
}

