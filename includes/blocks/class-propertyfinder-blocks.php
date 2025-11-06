<?php
/**
 * PropertyFinder Gutenberg Blocks
 *
 * @package PropertyFinder
 * @subpackage Blocks
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Blocks class
 */
class PropertyFinder_Blocks {

    /**
     * Constructor
     */
    public function __construct() {
        // Register block category (WordPress 5.8+)
        add_filter('block_categories_all', array($this, 'register_block_category'), 10, 2);
        // Fallback for older WordPress versions
        add_filter('block_categories', array($this, 'register_block_category'), 10, 2);
        
        add_action('init', array($this, 'register_blocks'), 10);
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_editor_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_block_assets'));
    }

    /**
     * Register custom block category
     */
    public function register_block_category($categories, $editor_context = null) {
        return array_merge(
            array(
                array(
                    'slug'  => 'propertyfinder',
                    'title' => __('PropertyFinder', 'propertyfinder'),
                    'icon'  => 'admin-home',
                ),
            ),
            $categories
        );
    }

    /**
     * Register blocks
     */
    public function register_blocks() {
        // Check if Gutenberg is available
        if (!function_exists('register_block_type')) {
            return;
        }

        // Register agent listing block with all attributes
        register_block_type('propertyfinder/agent-listing', array(
            'title' => __('Agent Listing', 'propertyfinder'),
            'category' => 'propertyfinder',
            'icon' => 'groups',
            'description' => __('Display a list of agents with pagination and metadata options.', 'propertyfinder'),
            'attributes' => array(
                'postsPerPage' => array(
                    'type' => 'number',
                    'default' => 12,
                ),
                'columns' => array(
                    'type' => 'number',
                    'default' => 3,
                ),
                'showImage' => array(
                    'type' => 'boolean',
                    'default' => true,
                ),
                'showBio' => array(
                    'type' => 'boolean',
                    'default' => false,
                ),
                'showEmail' => array(
                    'type' => 'boolean',
                    'default' => true,
                ),
                'showPhone' => array(
                    'type' => 'boolean',
                    'default' => true,
                ),
                'showLinkedIn' => array(
                    'type' => 'boolean',
                    'default' => false,
                ),
                'showRole' => array(
                    'type' => 'boolean',
                    'default' => false,
                ),
                'showStatus' => array(
                    'type' => 'boolean',
                    'default' => false,
                ),
                'enablePagination' => array(
                    'type' => 'boolean',
                    'default' => true,
                ),
                'orderBy' => array(
                    'type' => 'string',
                    'default' => 'title',
                ),
                'order' => array(
                    'type' => 'string',
                    'default' => 'ASC',
                ),
            ),
            'render_callback' => array($this, 'render_agent_listing_block'),
            'editor_script' => 'propertyfinder-blocks-editor',
            'editor_style' => 'propertyfinder-blocks-editor',
            'style' => 'propertyfinder-blocks-style',
        ));

        // Register single agent block
        register_block_type('propertyfinder/single-agent', array(
            'title' => __('Single Agent', 'propertyfinder'),
            'category' => 'propertyfinder',
            'icon' => 'admin-users',
            'description' => __('Display a single agent profile with customizable metadata.', 'propertyfinder'),
            'attributes' => array(
                'agentId' => array(
                    'type' => 'number',
                    'default' => 0,
                ),
                'showImage' => array(
                    'type' => 'boolean',
                    'default' => true,
                ),
                'showBio' => array(
                    'type' => 'boolean',
                    'default' => true,
                ),
                'showContact' => array(
                    'type' => 'boolean',
                    'default' => true,
                ),
                'showSocial' => array(
                    'type' => 'boolean',
                    'default' => true,
                ),
                'showCompliances' => array(
                    'type' => 'boolean',
                    'default' => false,
                ),
            ),
            'render_callback' => array($this, 'render_single_agent_block'),
            'editor_script' => 'propertyfinder-blocks-editor',
            'editor_style' => 'propertyfinder-blocks-editor',
            'style' => 'propertyfinder-blocks-style',
        ));
    }

    /**
     * Enqueue block editor assets
     */
    public function enqueue_block_editor_assets() {
        // Check if block editor is available
        if (!function_exists('register_block_type')) {
            return;
        }

        // Enqueue editor script for block controls
        wp_enqueue_script(
            'propertyfinder-blocks-editor',
            PROPERTYFINDER_PLUGIN_URL . 'assets/js/blocks-editor.js',
            array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-server-side-render'),
            PROPERTYFINDER_VERSION,
            true
        );

        wp_localize_script('propertyfinder-blocks-editor', 'propertyfinderBlocks', array(
            'agents' => $this->get_agents_for_select(),
        ));

        wp_enqueue_style(
            'propertyfinder-blocks-editor',
            PROPERTYFINDER_PLUGIN_URL . 'assets/css/blocks-editor.css',
            array('wp-edit-blocks'),
            PROPERTYFINDER_VERSION
        );

        // Enqueue block styles
        wp_enqueue_style(
            'propertyfinder-blocks-style',
            PROPERTYFINDER_PLUGIN_URL . 'assets/css/blocks.css',
            array(),
            PROPERTYFINDER_VERSION
        );
    }

    /**
     * Enqueue block frontend assets
     */
    public function enqueue_block_assets() {
        if (has_block('propertyfinder/agent-listing') || has_block('propertyfinder/single-agent')) {
            wp_enqueue_style(
                'propertyfinder-blocks',
                PROPERTYFINDER_PLUGIN_URL . 'assets/css/blocks.css',
                array(),
                PROPERTYFINDER_VERSION
            );

            wp_enqueue_script(
                'propertyfinder-blocks-frontend',
                PROPERTYFINDER_PLUGIN_URL . 'assets/js/blocks-frontend.js',
                array('jquery'),
                PROPERTYFINDER_VERSION,
                true
            );

            wp_localize_script('propertyfinder-blocks-frontend', 'propertyfinderBlocksData', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('propertyfinder_blocks_nonce'),
            ));
        }
    }

    /**
     * Get agents for select dropdown
     */
    private function get_agents_for_select() {
        $agent_model = new \PropertyFinder\Models\AgentModel();
        $agents = $agent_model->getAll(array(
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC',
        ));

        $options = array();
        foreach ($agents as $agent) {
            $options[] = array(
                'value' => $agent->ID,
                'label' => $agent->post_title,
            );
        }

        return $options;
    }

    /**
     * Render agent listing block
     */
    public function render_agent_listing_block($attributes) {
        $posts_per_page = isset($attributes['postsPerPage']) ? intval($attributes['postsPerPage']) : 12;
        $columns = isset($attributes['columns']) ? intval($attributes['columns']) : 3;
        $enable_pagination = isset($attributes['enablePagination']) ? $attributes['enablePagination'] : true;
        $order_by = isset($attributes['orderBy']) ? $attributes['orderBy'] : 'title';
        $order = isset($attributes['order']) ? $attributes['order'] : 'ASC';

        // Get current page
        $paged = get_query_var('paged') ? get_query_var('paged') : 1;

        // Query agents
        $agent_model = new \PropertyFinder\Models\AgentModel();
        $agents = $agent_model->getAll(array(
            'posts_per_page' => $posts_per_page,
            'post_status' => 'publish',
            'orderby' => $order_by,
            'order' => $order,
            'paged' => $paged,
        ));

        // Get total for pagination
        $total_agents = wp_count_posts(PropertyFinder_Config::get_agent_cpt_name());
        $total = isset($total_agents->publish) ? intval($total_agents->publish) : 0;
        $total_pages = $enable_pagination ? ceil($total / $posts_per_page) : 1;

        ob_start();
        ?>
        <div class="propertyfinder-agent-listing-block" data-posts-per-page="<?php echo esc_attr($posts_per_page); ?>" data-columns="<?php echo esc_attr($columns); ?>">
            <div class="propertyfinder-agent-loader" style="display: none;">
                <div class="spinner"></div>
                <p><?php _e('Loading agents...', 'propertyfinder'); ?></p>
            </div>
            
            <div class="propertyfinder-agent-grid" style="display: grid; grid-template-columns: repeat(<?php echo esc_attr($columns); ?>, 1fr); gap: 30px;">
                <?php if (empty($agents)): ?>
                    <div class="propertyfinder-no-agents" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                        <p><?php _e('No agents found.', 'propertyfinder'); ?></p>
                    </div>
                <?php else: ?>
                    <?php foreach ($agents as $agent): ?>
                        <?php
                        $meta = get_post_meta($agent->ID);
                        $featured_image = get_the_post_thumbnail_url($agent->ID, 'medium');
                        
                        $first_name = isset($meta['_pf_first_name'][0]) ? $meta['_pf_first_name'][0] : '';
                        $last_name = isset($meta['_pf_last_name'][0]) ? $meta['_pf_last_name'][0] : '';
                        $public_name = isset($meta['_pf_public_profile_name'][0]) ? $meta['_pf_public_profile_name'][0] : '';
                        
                        $display_name = '';
                        if (!empty($public_name)) {
                            $display_name = $public_name;
                        } elseif (!empty($first_name) || !empty($last_name)) {
                            $display_name = trim($first_name . ' ' . $last_name);
                        } else {
                            $display_name = $agent->post_title;
                        }
                        
                        $email = isset($meta['_pf_public_profile_email'][0]) ? $meta['_pf_public_profile_email'][0] : (isset($meta['_pf_email'][0]) ? $meta['_pf_email'][0] : '');
                        $phone = isset($meta['_pf_public_profile_phone'][0]) ? $meta['_pf_public_profile_phone'][0] : (isset($meta['_pf_mobile'][0]) ? $meta['_pf_mobile'][0] : '');
                        $linkedin = isset($meta['_pf_linkedin_address'][0]) ? $meta['_pf_linkedin_address'][0] : '';
                        $role_name = isset($meta['_pf_role_name'][0]) ? $meta['_pf_role_name'][0] : '';
                        $status = isset($meta['_pf_status'][0]) ? $meta['_pf_status'][0] : '';
                        $bio_primary = isset($meta['_pf_bio_primary'][0]) ? wp_trim_words($meta['_pf_bio_primary'][0], 20) : '';
                        $position_primary = isset($meta['_pf_position_primary'][0]) ? $meta['_pf_position_primary'][0] : '';
                        $is_super_agent = isset($meta['_pf_is_super_agent'][0]) ? $meta['_pf_is_super_agent'][0] : '0';
                        ?>
                        <div class="propertyfinder-agent-card">
                            <?php if (isset($attributes['showImage']) && $attributes['showImage'] && $featured_image): ?>
                                <div class="propertyfinder-agent-card-image">
                                    <a href="<?php echo esc_url(get_permalink($agent->ID)); ?>">
                                        <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr($display_name); ?>" />
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <div class="propertyfinder-agent-card-content">
                                <h3 class="propertyfinder-agent-card-name">
                                    <a href="<?php echo esc_url(get_permalink($agent->ID)); ?>">
                                        <?php echo esc_html($display_name); ?>
                                        <?php if ($is_super_agent === '1'): ?>
                                            <span class="propertyfinder-super-agent-badge"><?php _e('Super Agent', 'propertyfinder'); ?></span>
                                        <?php endif; ?>
                                    </a>
                                </h3>
                                
                                <?php if ($position_primary && isset($attributes['showRole']) && $attributes['showRole']): ?>
                                    <p class="propertyfinder-agent-card-position"><?php echo esc_html($position_primary); ?></p>
                                <?php endif; ?>
                                
                                <?php if (isset($attributes['showBio']) && $attributes['showBio'] && $bio_primary): ?>
                                    <div class="propertyfinder-agent-card-bio">
                                        <?php echo wp_kses_post($bio_primary); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="propertyfinder-agent-card-meta">
                                    <?php if (isset($attributes['showEmail']) && $attributes['showEmail'] && $email): ?>
                                        <div class="propertyfinder-agent-meta-item">
                                            <span class="dashicons dashicons-email"></span>
                                            <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($attributes['showPhone']) && $attributes['showPhone'] && $phone): ?>
                                        <div class="propertyfinder-agent-meta-item">
                                            <span class="dashicons dashicons-phone"></span>
                                            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($attributes['showLinkedIn']) && $attributes['showLinkedIn'] && $linkedin): ?>
                                        <div class="propertyfinder-agent-meta-item">
                                            <span class="dashicons dashicons-linkedin"></span>
                                            <a href="<?php echo esc_url($linkedin); ?>" target="_blank" rel="noopener"><?php _e('LinkedIn', 'propertyfinder'); ?></a>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($attributes['showRole']) && $attributes['showRole'] && $role_name): ?>
                                        <div class="propertyfinder-agent-meta-item">
                                            <span class="dashicons dashicons-admin-users"></span>
                                            <span><?php echo esc_html($role_name); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($attributes['showStatus']) && $attributes['showStatus'] && $status): ?>
                                        <div class="propertyfinder-agent-meta-item">
                                            <span class="status-badge status-<?php echo esc_attr($status); ?>">
                                                <?php echo esc_html(ucfirst($status)); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="propertyfinder-agent-card-footer">
                                    <a href="<?php echo esc_url(get_permalink($agent->ID)); ?>" class="propertyfinder-agent-view-btn">
                                        <?php _e('View Profile', 'propertyfinder'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <?php if ($enable_pagination && $total_pages > 1): ?>
                <div class="propertyfinder-agent-pagination">
                    <?php
                    echo paginate_links(array(
                        'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                        'format' => '?paged=%#%',
                        'current' => max(1, $paged),
                        'total' => $total_pages,
                        'prev_text' => __('← Previous', 'propertyfinder'),
                        'next_text' => __('Next →', 'propertyfinder'),
                        'type' => 'list',
                        'end_size' => 2,
                        'mid_size' => 1,
                    ));
                    ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render single agent block
     */
    public function render_single_agent_block($attributes) {
        $agent_id = isset($attributes['agentId']) ? intval($attributes['agentId']) : 0;
        
        // If no agent ID, try to get from current post context
        if (empty($agent_id) && is_singular(PropertyFinder_Config::get_agent_cpt_name())) {
            $agent_id = get_the_ID();
        }
        
        if (empty($agent_id)) {
            return '<p>' . __('Please select an agent to display.', 'propertyfinder') . '</p>';
        }

        $agent = get_post($agent_id);
        if (!$agent || $agent->post_type !== PropertyFinder_Config::get_agent_cpt_name()) {
            return '<p>' . __('Agent not found.', 'propertyfinder') . '</p>';
        }

        $meta = get_post_meta($agent_id);
        $featured_image = get_the_post_thumbnail_url($agent_id, 'large');
        
        // Get agent data
        $first_name = isset($meta['_pf_first_name'][0]) ? $meta['_pf_first_name'][0] : '';
        $last_name = isset($meta['_pf_last_name'][0]) ? $meta['_pf_last_name'][0] : '';
        $public_name = isset($meta['_pf_public_profile_name'][0]) ? $meta['_pf_public_profile_name'][0] : '';
        
        $display_name = '';
        if (!empty($public_name)) {
            $display_name = $public_name;
        } elseif (!empty($first_name) || !empty($last_name)) {
            $display_name = trim($first_name . ' ' . $last_name);
        } else {
            $display_name = $agent->post_title;
        }

        $public_email = isset($meta['_pf_public_profile_email'][0]) ? $meta['_pf_public_profile_email'][0] : '';
        $email = isset($meta['_pf_email'][0]) ? $meta['_pf_email'][0] : '';
        $public_phone = isset($meta['_pf_public_profile_phone'][0]) ? $meta['_pf_public_profile_phone'][0] : '';
        $mobile = isset($meta['_pf_mobile'][0]) ? $meta['_pf_mobile'][0] : '';
        $whatsapp = isset($meta['_pf_public_profile_whatsapp'][0]) ? $meta['_pf_public_profile_whatsapp'][0] : '';
        $linkedin = isset($meta['_pf_linkedin_address'][0]) ? $meta['_pf_linkedin_address'][0] : '';
        $bio_primary = isset($meta['_pf_bio_primary'][0]) ? $meta['_pf_bio_primary'][0] : '';
        $bio_secondary = isset($meta['_pf_bio_secondary'][0]) ? $meta['_pf_bio_secondary'][0] : '';
        $position_primary = isset($meta['_pf_position_primary'][0]) ? $meta['_pf_position_primary'][0] : '';
        $verification_status = isset($meta['_pf_verification_status'][0]) ? $meta['_pf_verification_status'][0] : '';
        $is_super_agent = isset($meta['_pf_is_super_agent'][0]) ? $meta['_pf_is_super_agent'][0] : '0';
        $compliances = isset($meta['_pf_compliances'][0]) ? maybe_unserialize($meta['_pf_compliances'][0]) : array();

        ob_start();
        ?>
        <div class="propertyfinder-single-agent-block">
            <div class="propertyfinder-single-agent-content">
                <?php if (isset($attributes['showImage']) && $attributes['showImage'] && $featured_image): ?>
                    <div class="propertyfinder-single-agent-image">
                        <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr($display_name); ?>" />
                    </div>
                <?php endif; ?>
                
                <div class="propertyfinder-single-agent-info">
                    <h2 class="propertyfinder-single-agent-name">
                        <?php echo esc_html($display_name); ?>
                        <?php if ($is_super_agent === '1'): ?>
                            <span class="propertyfinder-super-agent-badge"><?php _e('Super Agent', 'propertyfinder'); ?></span>
                        <?php endif; ?>
                    </h2>
                    
                    <?php if ($position_primary): ?>
                        <p class="propertyfinder-single-agent-position"><?php echo esc_html($position_primary); ?></p>
                    <?php endif; ?>
                    
                    <?php if ($verification_status === 'verified'): ?>
                        <span class="propertyfinder-verification-badge verified">
                            <span class="dashicons dashicons-yes-alt"></span> <?php _e('Verified', 'propertyfinder'); ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if (isset($attributes['showBio']) && $attributes['showBio'] && ($bio_primary || $bio_secondary)): ?>
                        <div class="propertyfinder-single-agent-bio">
                            <?php if ($bio_primary): ?>
                                <div class="propertyfinder-bio-primary"><?php echo wp_kses_post($bio_primary); ?></div>
                            <?php endif; ?>
                            <?php if ($bio_secondary): ?>
                                <div class="propertyfinder-bio-secondary"><?php echo wp_kses_post($bio_secondary); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($attributes['showContact']) && $attributes['showContact']): ?>
                        <div class="propertyfinder-single-agent-contact">
                            <?php if ($public_phone ?: $mobile): ?>
                                <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $public_phone ?: $mobile)); ?>" class="propertyfinder-contact-btn phone">
                                    <span class="dashicons dashicons-phone"></span>
                                    <?php echo esc_html($public_phone ?: $mobile); ?>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($whatsapp): ?>
                                <a href="https://wa.me/<?php echo esc_attr(preg_replace('/[^0-9]/', '', $whatsapp)); ?>" class="propertyfinder-contact-btn whatsapp" target="_blank">
                                    <span class="dashicons dashicons-whatsapp"></span>
                                    <?php _e('WhatsApp', 'propertyfinder'); ?>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($public_email ?: $email): ?>
                                <a href="mailto:<?php echo esc_attr($public_email ?: $email); ?>" class="propertyfinder-contact-btn email">
                                    <span class="dashicons dashicons-email"></span>
                                    <?php _e('Email', 'propertyfinder'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($attributes['showSocial']) && $attributes['showSocial'] && $linkedin): ?>
                        <div class="propertyfinder-single-agent-social">
                            <a href="<?php echo esc_url($linkedin); ?>" target="_blank" rel="noopener" class="propertyfinder-social-link linkedin">
                                <span class="dashicons dashicons-linkedin"></span>
                                <?php _e('LinkedIn', 'propertyfinder'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($attributes['showCompliances']) && $attributes['showCompliances'] && !empty($compliances) && is_array($compliances)): ?>
                        <div class="propertyfinder-single-agent-compliances">
                            <h3><?php _e('Compliances', 'propertyfinder'); ?></h3>
                            <table class="propertyfinder-compliances-table">
                                <thead>
                                    <tr>
                                        <th><?php _e('Type', 'propertyfinder'); ?></th>
                                        <th><?php _e('Value', 'propertyfinder'); ?></th>
                                        <th><?php _e('Status', 'propertyfinder'); ?></th>
                                        <th><?php _e('Expiry Date', 'propertyfinder'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($compliances as $compliance): ?>
                                        <tr>
                                            <td><?php echo esc_html(isset($compliance['type']) ? strtoupper($compliance['type']) : '-'); ?></td>
                                            <td><?php echo esc_html(isset($compliance['value']) ? $compliance['value'] : '-'); ?></td>
                                            <td>
                                                <span class="compliance-status compliance-<?php echo esc_attr(isset($compliance['status']) ? $compliance['status'] : ''); ?>">
                                                    <?php echo esc_html(isset($compliance['status']) ? ucfirst($compliance['status']) : '-'); ?>
                                                </span>
                                            </td>
                                            <td><?php echo esc_html(isset($compliance['expiryDate']) ? $compliance['expiryDate'] : '-'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

