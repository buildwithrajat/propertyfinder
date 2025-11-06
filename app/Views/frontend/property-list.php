<?php
/**
 * Frontend property list view
 *
 * @package PropertyFinder
 * @subpackage Views
 * 
 * THEME OVERRIDE:
 * To override this template in your theme, copy this file to:
 * wp-content/themes/{your-theme}/propertyfinder/frontend/property-list.php
 * 
 * USED BY:
 * [propertyfinder_list] shortcode
 * 
 * AVAILABLE VARIABLES:
 * @var array $properties Array of property objects/posts
 * @var array $atts Shortcode attributes (limit, order, status)
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// If properties are empty, fetch from WordPress posts
if (empty($properties)) {
    $cpt_name = PropertyFinder_Config::get_cpt_name();
    $limit = isset($atts['limit']) ? intval($atts['limit']) : 10;
    $order = isset($atts['order']) ? $atts['order'] : 'desc';
    $status = isset($atts['status']) ? $atts['status'] : 'publish';
    
    $args = array(
        'post_type' => $cpt_name,
        'posts_per_page' => $limit,
        'orderby' => 'date',
        'order' => $order,
        'post_status' => $status,
    );
    
    $properties = get_posts($args);
}
?>
<div class="propertyfinder-properties" data-view="grid">
    <?php if (!empty($properties)): ?>
        <div class="propertyfinder-view-controls">
            <div class="propertyfinder-view-toggle">
                <button type="button" class="propertyfinder-view-btn propertyfinder-view-grid active" data-view="grid" title="<?php esc_attr_e('Grid View', 'propertyfinder'); ?>">
                    <span class="dashicons dashicons-grid-view"></span>
                    <span class="view-label"><?php _e('Grid', 'propertyfinder'); ?></span>
                </button>
                <button type="button" class="propertyfinder-view-btn propertyfinder-view-list" data-view="list" title="<?php esc_attr_e('List View', 'propertyfinder'); ?>">
                    <span class="dashicons dashicons-list-view"></span>
                    <span class="view-label"><?php _e('List', 'propertyfinder'); ?></span>
                </button>
            </div>
        </div>
        <div class="propertyfinder-grid propertyfinder-properties-container">
            <?php foreach ($properties as $property): 
                // Get post object if it's an ID
                $post = is_object($property) ? $property : get_post($property);
                if (!$post) continue;
                
                // Get post meta
                $meta = get_post_meta($post->ID);
                
                // Get featured image
                $featured_image = get_the_post_thumbnail_url($post->ID, 'large');
                if (!$featured_image) {
                    $featured_image = get_the_post_thumbnail_url($post->ID, 'medium');
                }
                
                // Get price
                $price_type = isset($meta['_pf_price_type'][0]) ? $meta['_pf_price_type'][0] : '';
                $price_amount = isset($meta['_pf_price_amount'][0]) ? floatval($meta['_pf_price_amount'][0]) : 0;
                $price_on_request = isset($meta['_pf_price_on_request'][0]) ? $meta['_pf_price_on_request'][0] : '';
                
                // Format price
                $display_price = '';
                if ($price_on_request === 'yes') {
                    $display_price = __('On Request', 'propertyfinder');
                } elseif ($price_amount > 0) {
                    $display_price = 'AED ' . number_format($price_amount, 0, '.', ',');
                }
                
                // Get property details
                $bedrooms = isset($meta['_pf_bedrooms'][0]) ? intval($meta['_pf_bedrooms'][0]) : 0;
                $bathrooms = isset($meta['_pf_bathrooms'][0]) ? intval($meta['_pf_bathrooms'][0]) : 0;
                $size = isset($meta['_pf_size'][0]) ? floatval($meta['_pf_size'][0]) : 0;
                $size_unit = isset($meta['_pf_size_unit'][0]) ? $meta['_pf_size_unit'][0] : 'sqft';
                
                // Format size
                $display_size = '';
                if ($size > 0) {
                    $display_size = number_format($size, 0, '.', ',') . ' ' . ($size_unit === 'sqft' ? __('Sq. Ft.', 'propertyfinder') : $size_unit);
                }
                
                // Get location
                $location_name = isset($meta['_pf_location_name'][0]) ? $meta['_pf_location_name'][0] : '';
                $location_path = isset($meta['_pf_location_path'][0]) ? $meta['_pf_location_path'][0] : '';
                $display_location = $location_name ?: $location_path;
                
                // Get category/type
                $category_terms = wp_get_post_terms($post->ID, 'pf_category', array('fields' => 'names'));
                $category = !empty($category_terms) ? $category_terms[0] : '';
                
                // Get property type
                $type_terms = wp_get_post_terms($post->ID, 'pf_type', array('fields' => 'names'));
                $type = !empty($type_terms) ? $type_terms[0] : '';
                
                // Build title
                $title_parts = array();
                if ($category) $title_parts[] = $category;
                if ($type) $title_parts[] = $type;
                $display_title = !empty($title_parts) ? implode(' | ', $title_parts) : get_the_title($post->ID);
            ?>
                <div class="propertyfinder-card propertyfinder-property-item">
                    <div class="propertyfinder-card-image">
                        <?php if ($featured_image): ?>
                            <a href="<?php echo esc_url(get_permalink($post->ID)); ?>">
                                <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr(get_the_title($post->ID)); ?>" />
                            </a>
                        <?php else: ?>
                            <div class="propertyfinder-card-placeholder">
                                <span class="dashicons dashicons-admin-home"></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="propertyfinder-card-content">
                        <?php if ($display_price): ?>
                            <div class="propertyfinder-card-price">
                                <?php echo esc_html($display_price); ?>
                            </div>
                        <?php endif; ?>
                        
                        <h3 class="propertyfinder-card-title">
                            <a href="<?php echo esc_url(get_permalink($post->ID)); ?>">
                                <?php echo esc_html($display_title); ?>
                            </a>
                        </h3>
                        
                        <?php if ($display_location): ?>
                            <div class="propertyfinder-card-location">
                                <span class="dashicons dashicons-location"></span>
                                <?php echo esc_html($display_location); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="propertyfinder-card-details">
                            <?php if ($bedrooms > 0): ?>
                                <span class="propertyfinder-detail-item">
                                    <span class="propertyfinder-icon propertyfinder-icon-bed"></span>
                                    <span class="detail-value"><?php echo esc_html($bedrooms); ?> <?php _e('Bed', 'propertyfinder'); ?></span>
                                </span>
                            <?php endif; ?>
                            
                            <?php if ($bathrooms > 0): ?>
                                <span class="propertyfinder-detail-item">
                                    <span class="propertyfinder-icon propertyfinder-icon-bath"></span>
                                    <span class="detail-value"><?php echo esc_html($bathrooms); ?> <?php _e('Bath', 'propertyfinder'); ?></span>
                                </span>
                            <?php endif; ?>
                            
                            <?php if ($display_size): ?>
                                <span class="propertyfinder-detail-item">
                                    <span class="propertyfinder-icon propertyfinder-icon-size"></span>
                                    <span class="detail-value"><?php echo esc_html($display_size); ?></span>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="propertyfinder-no-properties"><?php _e('No properties found.', 'propertyfinder'); ?></p>
    <?php endif; ?>
</div>

