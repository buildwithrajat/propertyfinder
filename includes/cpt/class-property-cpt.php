<?php
/**
 * Property CPT Registration
 *
 * @package PropertyFinder
 * @subpackage CPT
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Property CPT Class
 */
class PropertyFinder_Property_CPT {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
    }

    /**
     * Register property post type
     */
    public function register_post_type() {
        $labels = array(
            'name'                  => _x('Properties', 'Post type general name', 'propertyfinder'),
            'singular_name'         => _x('Property', 'Post type singular name', 'propertyfinder'),
            'menu_name'             => _x('Properties', 'Admin Menu text', 'propertyfinder'),
            'name_admin_bar'        => _x('Property', 'Add New on Toolbar', 'propertyfinder'),
            'add_new'               => __('Add New', 'propertyfinder'),
            'add_new_item'          => __('Add New Property', 'propertyfinder'),
            'new_item'              => __('New Property', 'propertyfinder'),
            'edit_item'             => __('Edit Property', 'propertyfinder'),
            'view_item'             => __('View Property', 'propertyfinder'),
            'all_items'             => __('All Properties', 'propertyfinder'),
            'search_items'          => __('Search Properties', 'propertyfinder'),
            'not_found'             => __('No properties found.', 'propertyfinder'),
            'not_found_in_trash'    => __('No properties found in Trash.', 'propertyfinder'),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => PropertyFinder_Config::get('cpt_slug', 'listing')),
            'capability_type'    => 'post',
            'has_archive'         => true,
            'hierarchical'       => false,
            'menu_position'      => 20,
            'menu_icon'          => 'dashicons-building',
            'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
            'show_in_rest'       => true,
        );

        register_post_type(PropertyFinder_Config::get_cpt_name(), $args);
    }
}

