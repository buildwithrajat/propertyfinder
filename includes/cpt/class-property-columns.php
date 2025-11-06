<?php
/**
 * Property Admin Columns
 *
 * @package PropertyFinder
 * @subpackage CPT
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Property Admin Columns Handler
 */
class PropertyFinder_Property_Columns {

    /**
     * Initialize
     */
    public function __construct() {
        $property_cpt = PropertyFinder_Config::get_cpt_name();
        add_filter("manage_{$property_cpt}_posts_columns", array($this, 'add_columns'));
        add_action("manage_{$property_cpt}_posts_custom_column", array($this, 'render_columns'), 10, 2);
        add_filter("manage_edit-{$property_cpt}_sortable_columns", array($this, 'make_sortable'));
        add_action('admin_head', array($this, 'add_styles'));
        add_action('add_meta_boxes', array($this, 'remove_meta_boxes'), 10);
        // Remove excerpt from title column display
        add_filter('post_row_actions', array($this, 'clean_title_column'), 10, 2);
        add_filter('the_excerpt', array($this, 'remove_excerpt_in_admin'), 10, 1);
        add_filter('get_the_excerpt', array($this, 'remove_excerpt_in_admin'), 10, 1);
        // Hook into admin table display to remove content
        add_filter('display_post_states', array($this, 'remove_post_excerpt_display'), 10, 2);
        // Format title display in admin table
        add_filter('the_title', array($this, 'format_title_display'), 10, 2);
    }

    /**
     * Add custom columns
     *
     * @param array $columns Existing columns
     * @return array Modified columns
     */
    public function add_columns($columns) {
        // Remove description column
        unset($columns['description']);
        
        $new_columns = array();
        
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            
            if ($key === 'cb') {
                $new_columns['property_image'] = __('Image', 'propertyfinder');
            }
        }
        
        // Keep only essential columns for cleaner look
        $new_columns['property_price'] = __('Price', 'propertyfinder');
        $new_columns['property_location'] = __('Location', 'propertyfinder');
        $new_columns['property_type'] = __('Type', 'propertyfinder');
        $new_columns['property_status'] = __('Status', 'propertyfinder');
        
        // Remove date column and add it back at the end (optional - you can remove this if you want date first)
        if (isset($new_columns['date'])) {
            $date_column = $new_columns['date'];
            unset($new_columns['date']);
            $new_columns['date'] = $date_column;
        }
        
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
            case 'property_image':
                $thumbnail_id = get_post_thumbnail_id($post_id);
                if ($thumbnail_id) {
                    $thumbnail_url = get_the_post_thumbnail_url($post_id, 'thumbnail');
                    echo '<a href="' . esc_url(get_edit_post_link($post_id)) . '">';
                    echo '<img src="' . esc_url($thumbnail_url) . '" alt="' . esc_attr(get_the_title($post_id)) . '" style="width: 60px; height: 60px; object-fit: cover; border-radius: 3px; border: 1px solid #ddd;" />';
                    echo '</a>';
                } else {
                    echo '<span class="dashicons dashicons-admin-home" style="font-size: 50px; color: #ddd; display: block; text-align: center;"></span>';
                }
                break;
                
            case 'property_price':
                $price_amount = isset($meta['_pf_price_amount'][0]) ? floatval($meta['_pf_price_amount'][0]) : 0;
                $price_type = isset($meta['_pf_price_type'][0]) ? $meta['_pf_price_type'][0] : '';
                
                if ($price_amount > 0 && !empty($price_type)) {
                    $price_label = ucfirst($price_type);
                    echo '<strong>' . esc_html($price_label) . ':</strong> ';
                    echo esc_html(number_format($price_amount, 2)) . ' AED';
                } else {
                    echo '-';
                }
                break;
                
            case 'property_location':
                // Get location from post meta (not taxonomy)
                $location_name = get_post_meta($post_id, '_pf_location_name', true);
                if (!empty($location_name)) {
                    echo esc_html($location_name);
                } else {
                    echo '-';
                }
                break;
                
            case 'property_type':
                $types = wp_get_post_terms($post_id, PropertyFinder_Config::get_taxonomy('property_type'));
                if (!empty($types) && !is_wp_error($types)) {
                    // Format property type - remove hyphens for display
                    $type_name = propertyfinder_format_property_type($types[0]->name);
                    echo esc_html($type_name);
                } else {
                    echo '-';
                }
                break;
                
            case 'property_status':
                $api_id = isset($meta['_pf_api_id'][0]) ? $meta['_pf_api_id'][0] : '';
                $state = isset($meta['_pf_state'][0]) ? $meta['_pf_state'][0] : '';
                if ($state) {
                    $state_class = ($state === 'live' || $state === 'published') ? 'status-active' : 'status-inactive';
                    echo '<span class="status-badge ' . esc_attr($state_class) . '">' . esc_html(ucfirst($state)) . '</span>';
                } elseif (!$api_id) {
                    echo '<span style="color: #999;">' . __('Not synced', 'propertyfinder') . '</span>';
                } else {
                    echo '-';
                }
                break;
        }
    }

    /**
     * Make columns sortable
     *
     * @param array $columns Sortable columns
     * @return array Modified sortable columns
     */
    public function make_sortable($columns) {
        $columns['property_status'] = 'property_status';
        $columns['property_type'] = 'property_type';
        return $columns;
    }

    /**
     * Add admin styles
     */
    public function add_styles() {
        $screen = get_current_screen();
        
        if ($screen && $screen->post_type === PropertyFinder_Config::get_cpt_name() && $screen->base === 'edit') {
            ?>
            <style>
                .column-property_image {
                    width: 80px;
                    text-align: center;
                }
                .column-property_image img {
                    border-radius: 3px;
                    border: 1px solid #ddd;
                }
                .column-property_price {
                    min-width: 120px;
                    font-weight: 600;
                    color: #2271b1;
                }
                .column-property_location,
                .column-property_type {
                    min-width: 120px;
                }
                .column-property_status {
                    min-width: 100px;
                }
                .status-badge {
                    display: inline-block;
                    padding: 4px 8px;
                    border-radius: 3px;
                    font-size: 11px;
                    font-weight: 600;
                    text-transform: uppercase;
                }
                .status-badge.status-active {
                    background: #00a32a;
                    color: #fff;
                }
                .status-badge.status-inactive {
                    background: #f0f0f0;
                    color: #666;
                }
                /* Hide description/excerpt under title - Keep row actions visible */
                .wp-list-table .column-title .post-excerpt,
                .wp-list-table .column-title .description,
                .wp-list-table .column-title .excerpt,
                .wp-list-table .column-title .post-content,
                .wp-list-table .column-title .entry-content,
                .column-title .post-excerpt,
                .column-title .description,
                .column-title .excerpt,
                .column-title p.description,
                .column-title p.excerpt {
                    display: none !important;
                    visibility: hidden !important;
                    height: 0 !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    font-size: 0 !important;
                    line-height: 0 !important;
                }
                /* Hide any paragraph elements that are NOT row actions */
                .wp-list-table .column-title p:not(.row-actions) {
                    display: none !important;
                }
                /* Ensure row actions are always visible */
                .wp-list-table .column-title .row-actions,
                .wp-list-table .column-title .row-actions a,
                .column-title .row-actions,
                .column-title .row-actions a {
                    display: inline !important;
                    visibility: visible !important;
                    height: auto !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    font-size: 12px !important;
                    line-height: 1.5 !important;
                }
                /* Ensure title is clean without extra spacing */
                .column-title strong {
                    display: block;
                    margin-bottom: 0;
                }
                .column-title .row-actions {
                    margin-top: 2px;
                }
                /* Remove any line breaks or spacing after title */
                .column-title .row-title {
                    margin-bottom: 0 !important;
                }
                .column-title {
                    line-height: 1.4;
                }
            </style>
            <?php
        }
    }

    /**
     * Remove default meta boxes
     */
    public function remove_meta_boxes() {
        $screen = get_current_screen();
        
        if ($screen && $screen->post_type === PropertyFinder_Config::get_cpt_name()) {
            // Keep content editor enabled - don't remove it
            // remove_meta_box('postdivrich', PropertyFinder_Config::get_cpt_name(), 'normal');
            remove_meta_box('postexcerpt', PropertyFinder_Config::get_cpt_name(), 'normal');
            remove_meta_box('postcustom', PropertyFinder_Config::get_cpt_name(), 'normal');
            
            // Keep editor support enabled - allow content editor
            // remove_post_type_support(PropertyFinder_Config::get_cpt_name(), 'editor');
            remove_post_type_support(PropertyFinder_Config::get_cpt_name(), 'excerpt');
            remove_post_type_support(PropertyFinder_Config::get_cpt_name(), 'custom-fields');
        }
    }

    /**
     * Clean title column - remove excerpt from display
     */
    public function clean_title_column($actions, $post) {
        if ($post->post_type === PropertyFinder_Config::get_cpt_name()) {
            // This filter runs but we mainly use CSS to hide excerpt
        }
        return $actions;
    }

    /**
     * Remove excerpt in admin listing
     */
    public function remove_excerpt_in_admin($excerpt) {
        $screen = get_current_screen();
        if ($screen && $screen->post_type === PropertyFinder_Config::get_cpt_name() && $screen->base === 'edit') {
            return '';
        }
        return $excerpt;
    }

    /**
     * Remove post excerpt display in admin table
     */
    public function remove_post_excerpt_display($post_states, $post) {
        if ($post && $post->post_type === PropertyFinder_Config::get_cpt_name()) {
            // This helps clean up the title column
        }
        return $post_states;
    }

    /**
     * Format title display in admin table
     */
    public function format_title_display($title, $post_id = null) {
        // Only run in admin area
        if (!is_admin()) {
            return $title;
        }

        // Check if get_current_screen() function exists (only available in admin)
        if (!function_exists('get_current_screen')) {
            return $title;
        }

        $screen = get_current_screen();
        if ($screen && $screen->post_type === PropertyFinder_Config::get_cpt_name() && $screen->base === 'edit') {
            if ($post_id) {
                $post = get_post($post_id);
                if ($post && $post->post_type === PropertyFinder_Config::get_cpt_name()) {
                    return propertyfinder_format_property_title($title);
                }
            }
        }
        return $title;
    }
}

