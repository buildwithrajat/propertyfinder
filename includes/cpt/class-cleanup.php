<?php
/**
 * Cleanup Operations
 *
 * @package PropertyFinder
 * @subpackage CPT
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Cleanup Class
 * Handles cleanup of taxonomies and posts
 */
class PropertyFinder_Cleanup {

    /**
     * Constructor
     */
    public function __construct() {
        // Hook cleanup methods if needed
    }

    /**
     * Clean up all taxonomies
     * 
     * @return array Results of cleanup
     */
    public function cleanup_taxonomies() {
        $results = array();
        
        $taxonomies = array(
            PropertyFinder_Config::get_taxonomy('category'),
            PropertyFinder_Config::get_taxonomy('property_type'),
            PropertyFinder_Config::get_taxonomy('amenity'),
        );
        
        foreach ($taxonomies as $taxonomy) {
            if (taxonomy_exists($taxonomy)) {
                $terms = get_terms(array(
                    'taxonomy' => $taxonomy,
                    'hide_empty' => false,
                ));
                
                if (!is_wp_error($terms) && !empty($terms)) {
                    foreach ($terms as $term) {
                        wp_delete_term($term->term_id, $taxonomy);
                    }
                    $results[$taxonomy] = count($terms) . ' terms deleted';
                } else {
                    $results[$taxonomy] = 'No terms found';
                }
            }
        }
        
        return $results;
    }

    /**
     * Clean up all properties
     * 
     * @return int Number of properties deleted
     */
    public function cleanup_properties() {
        $properties = get_posts(array(
            'post_type' => PropertyFinder_Config::get_cpt_name(),
            'posts_per_page' => -1,
            'post_status' => 'any',
        ));
        
        $deleted = 0;
        foreach ($properties as $property) {
            wp_delete_post($property->ID, true);
            $deleted++;
        }
        
        return $deleted;
    }

    /**
     * Clean up all agents
     * 
     * @return int Number of agents deleted
     */
    public function cleanup_agents() {
        $agents = get_posts(array(
            'post_type' => PropertyFinder_Config::get_agent_cpt_name(),
            'posts_per_page' => -1,
            'post_status' => 'any',
        ));
        
        $deleted = 0;
        foreach ($agents as $agent) {
            wp_delete_post($agent->ID, true);
            $deleted++;
        }
        
        return $deleted;
    }
}

