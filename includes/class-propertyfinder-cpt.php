<?php
/**
 * PropertyFinder Custom Post Type and Taxonomies
 *
 * @package PropertyFinder
 * @subpackage Includes
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * CPT and Taxonomies class
 */
class PropertyFinder_CPT {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('init', array($this, 'register_taxonomies'));
    }

    /**
     * Register custom post type for listings
     */
    public function register_post_type() {
        $labels = array(
            'name'                  => _x('Listings', 'Post type general name', 'propertyfinder'),
            'singular_name'         => _x('Listing', 'Post type singular name', 'propertyfinder'),
            'menu_name'             => _x('PropertyFinder Listings', 'Admin Menu text', 'propertyfinder'),
            'name_admin_bar'        => _x('Listing', 'Add New on Toolbar', 'propertyfinder'),
            'add_new'               => __('Add New', 'propertyfinder'),
            'add_new_item'          => __('Add New Listing', 'propertyfinder'),
            'new_item'              => __('New Listing', 'propertyfinder'),
            'edit_item'             => __('Edit Listing', 'propertyfinder'),
            'view_item'             => __('View Listing', 'propertyfinder'),
            'all_items'             => __('All Listings', 'propertyfinder'),
            'search_items'          => __('Search Listings', 'propertyfinder'),
            'parent_item_colon'     => __('Parent Listings:', 'propertyfinder'),
            'not_found'             => __('No listings found.', 'propertyfinder'),
            'not_found_in_trash'    => __('No listings found in Trash.', 'propertyfinder'),
            'featured_image'        => _x('Listing Image', 'Overrides the "Featured Image" phrase for this post type.', 'propertyfinder'),
            'set_featured_image'    => _x('Set listing image', 'Overrides the "Set featured image" phrase for this post type.', 'propertyfinder'),
            'remove_featured_image' => _x('Remove listing image', 'Overrides the "Remove featured image" phrase for this post type.', 'propertyfinder'),
            'use_featured_image'    => _x('Use as listing image', 'Overrides the "Use as featured image" phrase for this post type.', 'propertyfinder'),
            'archives'              => _x('Listing archives', 'The post type archive label used in nav menus.', 'propertyfinder'),
            'insert_into_item'      => _x('Insert into listing', 'Overrides the "Insert into post"/"Insert into page" phrase for this post type.', 'propertyfinder'),
            'uploaded_to_this_item' => _x('Uploaded to this listing', 'Overrides the "Uploaded to this post"/"Uploaded to this page" phrase for this post type.', 'propertyfinder'),
            'filter_items_list'     => _x('Filter listings list', 'Screen reader text for the filter links heading on the post type listing screen.', 'propertyfinder'),
            'items_list_navigation' => _x('Listings list navigation', 'Screen reader text for the pagination heading on the post type listing screen.', 'propertyfinder'),
            'items_list'            => _x('Listings list', 'Screen reader text for the items list heading on the post type listing screen.', 'propertyfinder'),
        );

        $args = array(
            'labels'                => $labels,
            'public'                => true,
            'publicly_queryable'    => true,
            'show_ui'               => true,
            'show_in_menu'          => false, // We'll add it to PropertyFinder menu
            'query_var'             => true,
            'rewrite'               => array('slug' => 'listing'),
            'capability_type'       => 'post',
            'has_archive'           => true,
            'hierarchical'          => false,
            'menu_position'         => null,
            'menu_icon'             => 'dashicons-admin-home',
            'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
            'show_in_rest'          => true,
        );

        $args = apply_filters('propertyfinder_cpt_args', $args);

        register_post_type('pf_listing', $args);
    }

    /**
     * Register taxonomies
     */
    public function register_taxonomies() {
        // Property Category
        $this->register_taxonomy(
            'pf_category',
            'Property Category',
            'Property Categories',
            array('slug' => 'property-category')
        );

        // Property Type
        $this->register_taxonomy(
            'pf_property_type',
            'Property Type',
            'Property Types',
            array('slug' => 'property-type')
        );

        // Amenities
        $this->register_taxonomy(
            'pf_amenity',
            'Amenity',
            'Amenities',
            array('slug' => 'amenity')
        );

        // Location/Community
        $this->register_taxonomy(
            'pf_location',
            'Location',
            'Locations',
            array('slug' => 'location')
        );

        // Transaction Type (Sale/Rent)
        $this->register_taxonomy(
            'pf_transaction_type',
            'Transaction Type',
            'Transaction Types',
            array('slug' => 'transaction-type')
        );

        // Furnishing Status
        $this->register_taxonomy(
            'pf_furnishing_status',
            'Furnishing Status',
            'Furnishing Statuses',
            array('slug' => 'furnishing-status')
        );
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
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => isset($args['slug']) ? $args['slug'] : $taxonomy),
            'show_in_rest'      => true,
        );

        $args = apply_filters('propertyfinder_taxonomy_args', array_merge($default_args, $args), $taxonomy);

        register_taxonomy($taxonomy, array('pf_listing'), $args);
    }
}

