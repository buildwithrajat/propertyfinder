<?php
/**
 * Property Metabox
 *
 * @package PropertyFinder
 * @subpackage Metabox
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Property Metabox Class
 */
class PropertyFinder_Property_Metabox {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_property_data'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        $cpt_name = PropertyFinder_Config::get_cpt_name();
        add_meta_box(
            'propertyfinder_property_data',
            __('Property Data', 'propertyfinder'),
            array($this, 'render_property_metabox'),
            $cpt_name,
            'normal',
            'high'
        );
        
        // Agent Assignment Meta Box
        add_meta_box(
            'propertyfinder_agent_assignment',
            __('Assign Agent', 'propertyfinder'),
            array($this, 'render_agent_assignment_metabox'),
            $cpt_name,
            'side',
            'default'
        );
    }

    /**
     * Enqueue scripts
     */
    public function enqueue_scripts($hook) {
        global $post_type;
        
        if ($post_type !== PropertyFinder_Config::get_cpt_name() || !in_array($hook, array('post.php', 'post-new.php'))) {
            return;
        }

        // Admin JS (includes toast system)
        wp_enqueue_script(
            'propertyfinder-admin',
            PROPERTYFINDER_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            PROPERTYFINDER_VERSION,
            true
        );
        
        // Property editor JavaScript
        wp_enqueue_script(
            'propertyfinder-property-editor',
            PROPERTYFINDER_PLUGIN_URL . 'assets/js/property-editor.js',
            array('jquery', 'propertyfinder-admin'),
            PROPERTYFINDER_VERSION,
            true
        );
        
        wp_enqueue_media();

        wp_localize_script('propertyfinder-property-editor', 'propertyfinderEditor', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('propertyfinder_property_nonce'),
        ));
    }

    /**
     * Render property metabox
     */
    public function render_property_metabox($post) {
        wp_nonce_field('propertyfinder_property_metabox', 'propertyfinder_property_nonce');
        
        $meta = get_post_meta($post->ID);
        
        // Get agents
        $agent_posts = get_posts(array(
            'post_type' => PropertyFinder_Config::get_agent_cpt_name(),
            'posts_per_page' => -1,
            'post_status' => 'any',
            'meta_key' => '_pf_api_id',
            'orderby' => 'title',
            'order' => 'ASC',
        ));
        
        $agents = array();
        foreach ($agent_posts as $agent_post) {
            $api_id = get_post_meta($agent_post->ID, '_pf_api_id', true);
            if ($api_id) {
                $agent_meta = get_post_meta($agent_post->ID);
                $featured_image_id = get_post_thumbnail_id($agent_post->ID);
                $agents[] = array(
                    'id' => $api_id,
                    'wp_id' => $agent_post->ID,
                    'name' => $agent_post->post_title,
                    'email' => isset($agent_meta['_pf_email'][0]) ? $agent_meta['_pf_email'][0] : '',
                    'phone' => isset($agent_meta['_pf_phone'][0]) ? $agent_meta['_pf_phone'][0] : '',
                    'photo_url' => $featured_image_id ? wp_get_attachment_image_url($featured_image_id, 'thumbnail') : '',
                );
            }
        }
        
        // Get assigned agent
        $assigned_agent_id = isset($meta['_pf_assigned_to_id'][0]) ? $meta['_pf_assigned_to_id'][0] : '';
        $assigned_agent = null;
        if ($assigned_agent_id) {
            foreach ($agents as $agent) {
                if ($agent['id'] == $assigned_agent_id) {
                    $assigned_agent = $agent;
                    break;
                }
            }
            if (!$assigned_agent) {
                $assigned_agent = array(
                    'id' => $assigned_agent_id,
                    'name' => isset($meta['_pf_assigned_to_name'][0]) ? $meta['_pf_assigned_to_name'][0] : '',
                    'email' => isset($meta['_pf_assigned_to_email'][0]) ? $meta['_pf_assigned_to_email'][0] : '',
                    'phone' => isset($meta['_pf_assigned_to_phone'][0]) ? $meta['_pf_assigned_to_phone'][0] : '',
                    'photo_url' => isset($meta['_pf_assigned_to_photo'][0]) ? $meta['_pf_assigned_to_photo'][0] : '',
                );
            }
        }
        
        // Get location from post meta (not taxonomy - taxonomy removed)
        $location_id = isset($meta['_pf_location_id'][0]) ? $meta['_pf_location_id'][0] : '';
        $location_name = isset($meta['_pf_location_name'][0]) ? $meta['_pf_location_name'][0] : '';
        $location_type = isset($meta['_pf_location_type'][0]) ? $meta['_pf_location_type'][0] : '';
        $location_lat = isset($meta['_pf_location_lat'][0]) ? $meta['_pf_location_lat'][0] : '';
        $location_lng = isset($meta['_pf_location_lng'][0]) ? $meta['_pf_location_lng'][0] : '';
        
        // Get location tree/path for parent display
        $location_tree = array();
        $location_path = '';
        if (isset($meta['_pf_location_tree'][0])) {
            $location_tree = maybe_unserialize($meta['_pf_location_tree'][0]);
            if (!is_array($location_tree)) {
                $location_tree = array();
            }
        }
        if (isset($meta['_pf_location_path'][0])) {
            $location_path = $meta['_pf_location_path'][0];
        } elseif (!empty($location_tree) && is_array($location_tree)) {
            // Build path from tree if path not available
            $path_parts = array();
            foreach ($location_tree as $tree_item) {
                if (isset($tree_item['name'])) {
                    $path_parts[] = $tree_item['name'];
                }
            }
            $location_path = implode(' > ', $path_parts);
        }
        
        // Get full location data
        $location_data = array();
        if (isset($meta['_pf_location_data'][0])) {
            $location_data = maybe_unserialize($meta['_pf_location_data'][0]);
            if (!is_array($location_data)) {
                $location_data = array();
            }
        }
        
        $data = array(
            'post' => $post,
            'meta' => $meta,
            'agents' => $agents,
            'assigned_agent' => $assigned_agent,
            'has_json' => !empty(get_post_meta($post->ID, '_pf_imported_json', true)),
            'last_sync' => get_post_meta($post->ID, '_pf_last_synced', true),
            'sync_status' => get_post_meta($post->ID, '_pf_sync_status', true),
            'location_id' => $location_id,
            'location_name' => $location_name,
            'location_type' => $location_type,
            'location_lat' => $location_lat,
            'location_lng' => $location_lng,
            'location_tree' => $location_tree,
            'location_path' => $location_path,
            'location_data' => $location_data,
        );
        
        $view_path = PROPERTYFINDER_PLUGIN_DIR . 'app/Views/admin/property-metabox.php';
        if (file_exists($view_path)) {
            include $view_path;
        } else {
            echo '<div class="propertyfinder-metabox"><p>' . __('Metabox template not found.', 'propertyfinder') . '</p></div>';
        }
    }

    /**
     * Save property data
     */
    public function save_property_data($post_id, $post) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!isset($_POST['propertyfinder_property_nonce']) || !wp_verify_nonce($_POST['propertyfinder_property_nonce'], 'propertyfinder_property_metabox')) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if ($post->post_type !== PropertyFinder_Config::get_cpt_name()) {
            return;
        }

        // Category and Property Type are handled by WordPress taxonomies - no need to save here
        
        // Save state/stage
        if (isset($_POST['propertyfinder_state'])) {
            $state = sanitize_text_field($_POST['propertyfinder_state']);
            if (!empty($state)) {
                $valid_states = array('draft', 'live', 'takendown', 'archived', 'unpublished', 'pending_approval', 'rejected', 'approved', 'failed');
                if (in_array($state, $valid_states)) {
                    // Save state (will be used as stage for API)
                    update_post_meta($post_id, '_pf_state', $state);
                    // Also save as state_type for more detailed states
                    update_post_meta($post_id, '_pf_state_type', $state);
                }
            }
        }
        
        // Save price type and amount directly from API structure
        if (isset($_POST['propertyfinder_price_type'])) {
            $price_type = sanitize_text_field($_POST['propertyfinder_price_type']);
            $price_amount = isset($_POST['propertyfinder_price_amount']) ? floatval($_POST['propertyfinder_price_amount']) : 0;
            
            // Determine offering type from price type
            $offering_type = ($price_type === 'sale') ? 'sale' : 'rent';
            update_post_meta($post_id, '_pf_offering_type', $offering_type);
            update_post_meta($post_id, '_pf_price_type', $price_type);
            
            // Build price structure
            $price_data = array(
                'type' => $price_type,
                'amounts' => array(
                    'daily' => 0,
                    'monthly' => 0,
                    'sale' => 0,
                    'weekly' => 0,
                    'yearly' => 0
                ),
                'onRequest' => false
            );
            
            // Set the amount based on price type
            if ($price_amount > 0) {
                $price_data['amounts'][$price_type] = $price_amount;
            }
            
            // Save single price amount field
            if ($price_amount > 0) {
                update_post_meta($post_id, '_pf_price_amount', $price_amount);
            } else {
                delete_post_meta($post_id, '_pf_price_amount');
            }
            
            // Update price type and structure (simplified - only type and amount)
            if (!empty($price_type)) {
                $price_data['type'] = $price_type;
                $price_data['onRequest'] = false; // Can be updated from API if needed
                
                update_post_meta($post_id, '_pf_price_type', $price_type);
                update_post_meta($post_id, '_pf_price_structure', maybe_serialize($price_data));
            }
        }
        
        // Save amenities (as post meta, not taxonomy)
        $amenities_array = array();
        
        // Get amenities from checkbox selection
        if (isset($_POST['propertyfinder_amenities_checkbox']) && is_array($_POST['propertyfinder_amenities_checkbox'])) {
            $amenities_array = array_map('sanitize_text_field', $_POST['propertyfinder_amenities_checkbox']);
        }
        
        // Get amenities from JSON hidden field (if JavaScript updated it)
        if (isset($_POST['propertyfinder_amenities']) && !empty($_POST['propertyfinder_amenities'])) {
            $amenities_json = stripslashes($_POST['propertyfinder_amenities']);
            $json_amenities = json_decode($amenities_json, true);
            if (is_array($json_amenities) && !empty($json_amenities)) {
                $amenities_array = array_unique(array_merge($amenities_array, array_map('sanitize_text_field', $json_amenities)));
            }
        }
        
        // Get custom amenities from textarea
        if (isset($_POST['propertyfinder_amenities_custom']) && !empty($_POST['propertyfinder_amenities_custom'])) {
            $custom_text = sanitize_textarea_field($_POST['propertyfinder_amenities_custom']);
            $custom_amenities = array_filter(array_map('trim', explode("\n", $custom_text)));
            $amenities_array = array_unique(array_merge($amenities_array, array_map('sanitize_text_field', $custom_amenities)));
        }
        
        // Save amenities array
        if (!empty($amenities_array)) {
            update_post_meta($post_id, '_pf_amenities', maybe_serialize(array_values($amenities_array)));
        } else {
            delete_post_meta($post_id, '_pf_amenities');
        }
        
        // Location is managed via API import or "Fetch from API" button - not editable through metabox

        // Save agent assignment
        if (isset($_POST['propertyfinder_assigned_agent_id'])) {
            $agent_id = sanitize_text_field($_POST['propertyfinder_assigned_agent_id']);
            
            if (empty($agent_id)) {
                // Remove agent assignment
                delete_post_meta($post_id, '_pf_assigned_to_id');
                delete_post_meta($post_id, '_pf_assigned_to_name');
                delete_post_meta($post_id, '_pf_assigned_to_email');
                delete_post_meta($post_id, '_pf_assigned_to_phone');
                delete_post_meta($post_id, '_pf_assigned_to_photo');
            } else {
                // Find agent by public profile ID or API ID
                $agent_posts = get_posts(array(
                    'post_type' => PropertyFinder_Config::get_agent_cpt_name(),
                    'posts_per_page' => 1,
                    'post_status' => 'any',
                    'meta_query' => array(
                        'relation' => 'OR',
                        array(
                            'key' => '_pf_public_profile_id',
                            'value' => $agent_id,
                            'compare' => '=',
                        ),
                        array(
                            'key' => '_pf_api_id',
                            'value' => $agent_id,
                            'compare' => '=',
                        ),
                    ),
                ));
                
                if (!empty($agent_posts)) {
                    $agent_post = $agent_posts[0];
                    $agent_meta = get_post_meta($agent_post->ID);
                    
                    // Get the public profile ID (preferred) or API ID
                    $profile_id = isset($agent_meta['_pf_public_profile_id'][0]) ? $agent_meta['_pf_public_profile_id'][0] : '';
                    if (empty($profile_id)) {
                        $profile_id = isset($agent_meta['_pf_api_id'][0]) ? $agent_meta['_pf_api_id'][0] : $agent_id;
                    }
                    
                    // Save agent assignment using public profile ID
                    update_post_meta($post_id, '_pf_assigned_to_id', $profile_id);
                    update_post_meta($post_id, '_pf_assigned_to_name', $agent_post->post_title);
                    update_post_meta($post_id, '_pf_assigned_to_email', isset($agent_meta['_pf_email'][0]) ? $agent_meta['_pf_email'][0] : '');
                    update_post_meta($post_id, '_pf_assigned_to_phone', isset($agent_meta['_pf_mobile'][0]) ? $agent_meta['_pf_mobile'][0] : '');
                    
                    // Get agent photo
                    $featured_image_id = get_post_thumbnail_id($agent_post->ID);
                    if ($featured_image_id) {
                        $photo_url = wp_get_attachment_image_url($featured_image_id, 'thumbnail');
                        if ($photo_url) {
                            update_post_meta($post_id, '_pf_assigned_to_photo', $photo_url);
                        }
                    }
                } else {
                    // Agent not found, but save the profile ID anyway
                    update_post_meta($post_id, '_pf_assigned_to_id', $agent_id);
                }
            }
        }

        // One-way sync: Manual sync only via "Sync to API" button - no automatic sync
    }

    /**
     * Render agent assignment metabox
     */
    public function render_agent_assignment_metabox($post) {
        wp_nonce_field('propertyfinder_property_metabox', 'propertyfinder_property_nonce');
        
        $meta = get_post_meta($post->ID);
        
        // Get all agents
        $agent_posts = get_posts(array(
            'post_type' => PropertyFinder_Config::get_agent_cpt_name(),
            'posts_per_page' => -1,
            'post_status' => 'any',
            'orderby' => 'title',
            'order' => 'ASC',
        ));
        
        $agents = array();
        foreach ($agent_posts as $agent_post) {
            $api_id = get_post_meta($agent_post->ID, '_pf_api_id', true);
            $public_profile_id = get_post_meta($agent_post->ID, '_pf_public_profile_id', true);
            
            // Use public profile ID if available, otherwise use API ID
            $profile_id = !empty($public_profile_id) ? $public_profile_id : $api_id;
            
            if ($profile_id) {
                $agent_meta = get_post_meta($agent_post->ID);
                $featured_image_id = get_post_thumbnail_id($agent_post->ID);
                
                $agents[] = array(
                    'profile_id' => $profile_id,
                    'api_id' => $api_id,
                    'wp_id' => $agent_post->ID,
                    'name' => $agent_post->post_title,
                    'email' => isset($agent_meta['_pf_email'][0]) ? $agent_meta['_pf_email'][0] : '',
                    'phone' => isset($agent_meta['_pf_mobile'][0]) ? $agent_meta['_pf_mobile'][0] : '',
                    'photo_url' => $featured_image_id ? wp_get_attachment_image_url($featured_image_id, 'thumbnail') : '',
                );
            }
        }
        
        // Get currently assigned agent
        $assigned_agent_id = isset($meta['_pf_assigned_to_id'][0]) ? $meta['_pf_assigned_to_id'][0] : '';
        $assigned_agent = null;
        if ($assigned_agent_id) {
            foreach ($agents as $agent) {
                if ($agent['profile_id'] == $assigned_agent_id || $agent['api_id'] == $assigned_agent_id) {
                    $assigned_agent = $agent;
                    break;
                }
            }
        }
        ?>
        <div class="propertyfinder-agent-assignment">
            <p class="description">
                <?php _e('Assign an agent to this property using their Public Profile ID from PropertyFinder API.', 'propertyfinder'); ?>
            </p>
            
            <label for="propertyfinder_assigned_agent_id" style="display: block; margin-bottom: 8px; font-weight: 600;">
                <?php _e('Select Agent by Public Profile ID:', 'propertyfinder'); ?>
            </label>
            
            <select name="propertyfinder_assigned_agent_id" id="propertyfinder_assigned_agent_id" class="regular-text" style="width: 100%;">
                <option value=""><?php _e('-- No Agent Assigned --', 'propertyfinder'); ?></option>
                <?php foreach ($agents as $agent): ?>
                    <option value="<?php echo esc_attr($agent['profile_id']); ?>" 
                            data-api-id="<?php echo esc_attr($agent['api_id']); ?>"
                            data-name="<?php echo esc_attr($agent['name']); ?>"
                            data-email="<?php echo esc_attr($agent['email']); ?>"
                            data-phone="<?php echo esc_attr($agent['phone']); ?>"
                            <?php selected($assigned_agent_id, $agent['profile_id']); ?>
                            <?php selected($assigned_agent_id, $agent['api_id']); ?>>
                        <?php echo esc_html($agent['name']); ?> 
                        (Profile ID: <?php echo esc_html($agent['profile_id']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            
            <?php if ($assigned_agent): ?>
                <div class="assigned-agent-info" style="margin-top: 15px; padding: 12px; background: #f0f7ff; border-left: 3px solid #2271b1; border-radius: 3px;">
                    <h4 style="margin: 0 0 10px 0; font-size: 14px;"><?php _e('Assigned Agent:', 'propertyfinder'); ?></h4>
                    <?php if ($assigned_agent['photo_url']): ?>
                        <img src="<?php echo esc_url($assigned_agent['photo_url']); ?>" 
                             alt="<?php echo esc_attr($assigned_agent['name']); ?>" 
                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 50%; float: left; margin-right: 12px; border: 2px solid #2271b1;" />
                    <?php endif; ?>
                    <div style="overflow: hidden;">
                        <strong style="display: block; margin-bottom: 5px;"><?php echo esc_html($assigned_agent['name']); ?></strong>
                        <?php if ($assigned_agent['email']): ?>
                            <div style="font-size: 12px; color: #666; margin-bottom: 3px;">
                                <span class="dashicons dashicons-email-alt" style="font-size: 14px; vertical-align: middle;"></span>
                                <?php echo esc_html($assigned_agent['email']); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($assigned_agent['phone']): ?>
                            <div style="font-size: 12px; color: #666;">
                                <span class="dashicons dashicons-phone" style="font-size: 14px; vertical-align: middle;"></span>
                                <?php echo esc_html($assigned_agent['phone']); ?>
                            </div>
                        <?php endif; ?>
                        <div style="font-size: 11px; color: #999; margin-top: 5px;">
                            <?php _e('Profile ID:', 'propertyfinder'); ?> <?php echo esc_html($assigned_agent['profile_id']); ?>
                        </div>
                    </div>
                    <div style="clear: both;"></div>
                </div>
            <?php else: ?>
                <p class="description" style="margin-top: 10px; color: #666;">
                    <?php _e('No agent assigned. Select an agent from the dropdown above.', 'propertyfinder'); ?>
                </p>
            <?php endif; ?>
            
            <?php if (empty($agents)): ?>
                <p class="description" style="margin-top: 10px; padding: 10px; background: #fff3cd; border-left: 3px solid #ffc107; border-radius: 3px;">
                    <strong><?php _e('No agents found.', 'propertyfinder'); ?></strong><br>
                    <?php _e('Please import agents from PropertyFinder API first.', 'propertyfinder'); ?>
                </p>
            <?php endif; ?>
        </div>
        
        <style>
        .propertyfinder-agent-assignment {
            padding: 5px 0;
        }
        .propertyfinder-agent-assignment select {
            margin-bottom: 10px;
        }
        .assigned-agent-info {
            clear: both;
        }
        </style>
        <?php
    }
}

