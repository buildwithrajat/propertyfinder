<?php
/**
 * Agent Template Handler
 *
 * @package PropertyFinder
 * @subpackage CPT
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Agent Template Class
 * Handles agent single page template loading and content rendering
 */
class PropertyFinder_Agent_Template {

    /**
     * Constructor
     */
    public function __construct() {
        add_filter('single_template', array($this, 'load_agent_template'));
        add_filter('the_content', array($this, 'render_agent_content'), 20);
    }

    /**
     * Load custom template for agent single page
     *
     * @param string $template Current template path
     * @return string Template path
     */
    public function load_agent_template($template) {
        global $post;
        
        if ($post && $post->post_type === PropertyFinder_Config::get_agent_cpt_name()) {
            // Check for template in theme
            $theme_template = locate_template(array(
                'single-' . PropertyFinder_Config::get_agent_cpt_name() . '.php',
                'propertyfinder/single-agent.php',
            ));
            
            if ($theme_template) {
                return $theme_template;
            }
            
            // Use plugin template as fallback
            $plugin_template = PROPERTYFINDER_PLUGIN_DIR . 'templates/single-agent.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        
        return $template;
    }

    /**
     * Render agent content
     *
     * @param string $content Post content
     * @return string Modified content
     */
    public function render_agent_content($content) {
        global $post;
        
        if (!$post || $post->post_type !== PropertyFinder_Config::get_agent_cpt_name()) {
            return $content;
        }
        
        // Get agent meta
        $meta = get_post_meta($post->ID);
        $api_id = isset($meta['_pf_api_id'][0]) ? $meta['_pf_api_id'][0] : '';
        $email = isset($meta['_pf_email'][0]) ? $meta['_pf_email'][0] : '';
        $phone = isset($meta['_pf_phone'][0]) ? $meta['_pf_phone'][0] : '';
        
        // Build agent info HTML
        $agent_info = '<div class="propertyfinder-agent-info">';
        
        if ($email) {
            $agent_info .= '<p><strong>' . __('Email:', 'propertyfinder') . '</strong> <a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a></p>';
        }
        
        if ($phone) {
            $agent_info .= '<p><strong>' . __('Phone:', 'propertyfinder') . '</strong> <a href="tel:' . esc_attr($phone) . '">' . esc_html($phone) . '</a></p>';
        }
        
        if ($api_id) {
            $agent_info .= '<p><strong>' . __('API ID:', 'propertyfinder') . '</strong> ' . esc_html($api_id) . '</p>';
        }
        
        $agent_info .= '</div>';
        
        return $content . $agent_info;
    }
}

