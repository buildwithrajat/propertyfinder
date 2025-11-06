<?php
/**
 * Agent Model
 *
 * @package PropertyFinder
 * @subpackage Models
 */

namespace PropertyFinder\Models;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Agent Model class
 */
class AgentModel extends BaseModel {
    
    /**
     * Get agent by API ID
     *
     * @param string $api_id Agent API ID
     * @return WP_Post|null Post object or null if not found
     */
    public function getByApiId($api_id) {
        $posts = get_posts(array(
            'post_type' => \PropertyFinder_Config::get_agent_cpt_name(),
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
     * Get agents by status
     *
     * @param string $status Agent status
     * @return array
     */
    public function getByStatus($status = 'active') {
        $args = array(
            'post_type' => \PropertyFinder_Config::get_agent_cpt_name(),
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => '_pf_status',
                    'value' => $status,
                    'compare' => '=',
                ),
            ),
        );

        return get_posts($args);
    }
    
    /**
     * Get all agents
     *
     * @param array $args Query arguments
     * @return array
     */
    public function getAll($args = array()) {
        $default_args = array(
            'post_type' => \PropertyFinder_Config::get_agent_cpt_name(),
            'posts_per_page' => -1,
            'post_status' => 'any',
        );

        $args = array_merge($default_args, $args);

        return get_posts($args);
    }
    
    /**
     * Create agent post
     *
     * @param array $data Agent data
     * @return int|WP_Error Post ID or error
     */
    public function create($data) {
        $post_data = array(
            'post_type' => \PropertyFinder_Config::get_agent_cpt_name(),
            'post_title' => isset($data['title']) ? sanitize_text_field($data['title']) : '',
            'post_status' => isset($data['status']) ? sanitize_text_field($data['status']) : 'publish',
            'meta_input' => isset($data['meta']) ? $data['meta'] : array(),
        );

        if (isset($data['content'])) {
            $post_data['post_content'] = wp_kses_post($data['content']);
        }

        return wp_insert_post($post_data);
    }
    
    /**
     * Update agent post
     *
     * @param int $post_id Post ID
     * @param array $data Agent data
     * @return int|WP_Error Updated post ID or error
     */
    public function update($post_id, $data) {
        $post_data = array(
            'ID' => $post_id,
        );

        if (isset($data['title'])) {
            $post_data['post_title'] = sanitize_text_field($data['title']);
        }

        if (isset($data['content'])) {
            $post_data['post_content'] = wp_kses_post($data['content']);
        }

        if (isset($data['status'])) {
            $post_data['post_status'] = sanitize_text_field($data['status']);
        }

        $result = wp_update_post($post_data);

        if (!is_wp_error($result) && isset($data['meta'])) {
            foreach ($data['meta'] as $key => $value) {
                update_post_meta($post_id, $key, $value);
            }
        }

        return $result;
    }
    
    /**
     * Delete agent
     *
     * @param int $post_id Post ID
     * @return bool Success status
     */
    public function delete($post_id) {
        return wp_delete_post($post_id, true) !== false;
    }
}

