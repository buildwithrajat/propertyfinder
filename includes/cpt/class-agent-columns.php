<?php
/**
 * Agent Admin Columns
 *
 * @package PropertyFinder
 * @subpackage CPT
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Agent Admin Columns Handler
 */
class PropertyFinder_Agent_Columns {

    /**
     * Initialize
     */
    public function __construct() {
        $agent_cpt = PropertyFinder_Config::get_agent_cpt_name();
        add_filter("manage_{$agent_cpt}_posts_columns", array($this, 'add_columns'));
        add_action("manage_{$agent_cpt}_posts_custom_column", array($this, 'render_columns'), 10, 2);
    }

    /**
     * Add custom columns
     *
     * @param array $columns Existing columns
     * @return array Modified columns
     */
    public function add_columns($columns) {
        $new_columns = array();
        
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            
            if ($key === 'cb') {
                $new_columns['agent_image'] = __('Image', 'propertyfinder');
            }
        }
        
        $new_columns['agent_api_id'] = __('API ID', 'propertyfinder');
        $new_columns['agent_email'] = __('Email', 'propertyfinder');
        $new_columns['agent_phone'] = __('Phone', 'propertyfinder');
        
        return $new_columns;
    }

    /**
     * Render column content
     *
     * @param string $column Column name
     * @param int $post_id Post ID
     */
    public function render_columns($column, $post_id) {
        $meta = get_post_meta($post_id);
        
        switch ($column) {
            case 'agent_image':
                $thumbnail_id = get_post_thumbnail_id($post_id);
                if ($thumbnail_id) {
                    $thumbnail_url = get_the_post_thumbnail_url($post_id, 'thumbnail');
                    echo '<a href="' . esc_url(get_edit_post_link($post_id)) . '">';
                    echo '<img src="' . esc_url($thumbnail_url) . '" alt="' . esc_attr(get_the_title($post_id)) . '" style="width: 60px; height: 60px; object-fit: cover; border-radius: 3px; border: 1px solid #ddd;" />';
                    echo '</a>';
                } else {
                    echo '<span class="dashicons dashicons-groups" style="font-size: 50px; color: #ddd; display: block; text-align: center;"></span>';
                }
                break;
                
            case 'agent_api_id':
                $api_id = isset($meta['_pf_api_id'][0]) ? $meta['_pf_api_id'][0] : '';
                if ($api_id) {
                    echo '<code>' . esc_html($api_id) . '</code>';
                } else {
                    echo '<span style="color: #999;">' . __('Not synced', 'propertyfinder') . '</span>';
                }
                break;
                
            case 'agent_email':
                $email = isset($meta['_pf_email'][0]) ? $meta['_pf_email'][0] : '';
                if ($email) {
                    echo '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>';
                } else {
                    echo '-';
                }
                break;
                
            case 'agent_phone':
                $phone = isset($meta['_pf_phone'][0]) ? $meta['_pf_phone'][0] : '';
                if ($phone) {
                    echo '<a href="tel:' . esc_attr($phone) . '">' . esc_html($phone) . '</a>';
                } else {
                    echo '-';
                }
                break;
        }
    }
}

