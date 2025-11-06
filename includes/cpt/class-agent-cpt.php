<?php
/**
 * Agent CPT Registration
 *
 * @package PropertyFinder
 * @subpackage CPT
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Agent CPT Class
 */
class PropertyFinder_Agent_CPT {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
    }

    /**
     * Register agent post type
     */
    public function register_post_type() {
        $labels = array(
            'name'                  => _x('Agents', 'Post type general name', 'propertyfinder'),
            'singular_name'         => _x('Agent', 'Post type singular name', 'propertyfinder'),
            'menu_name'             => _x('Agents', 'Admin Menu text', 'propertyfinder'),
            'name_admin_bar'        => _x('Agent', 'Add New on Toolbar', 'propertyfinder'),
            'add_new'               => __('Add New', 'propertyfinder'),
            'add_new_item'          => __('Add New Agent', 'propertyfinder'),
            'new_item'              => __('New Agent', 'propertyfinder'),
            'edit_item'             => __('Edit Agent', 'propertyfinder'),
            'view_item'             => __('View Agent', 'propertyfinder'),
            'all_items'             => __('All Agents', 'propertyfinder'),
            'search_items'          => __('Search Agents', 'propertyfinder'),
            'not_found'             => __('No agents found.', 'propertyfinder'),
            'not_found_in_trash'    => __('No agents found in Trash.', 'propertyfinder'),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => PropertyFinder_Config::get('agent_cpt_slug', 'agent')),
            'capability_type'    => 'post',
            'has_archive'         => true,
            'hierarchical'       => false,
            'menu_position'      => 21,
            'menu_icon'          => 'dashicons-groups',
            'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
            'show_in_rest'       => true,
        );

        register_post_type(PropertyFinder_Config::get_agent_cpt_name(), $args);
    }
}

