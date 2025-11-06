<?php
/**
 * Frontend single agent view
 *
 * @package PropertyFinder
 * @subpackage Views
 * 
 * THEME OVERRIDE:
 * To override this template in your theme, copy this file to:
 * wp-content/themes/{your-theme}/propertyfinder/frontend/agent-single.php
 * 
 * ALTERNATIVE OVERRIDE (for Agent CPT single page):
 * wp-content/themes/{your-theme}/single-pf_agent.php
 * OR
 * wp-content/themes/{your-theme}/propertyfinder/single-agent.php
 * 
 * AVAILABLE VARIABLES:
 * @var object $post WordPress post object (global)
 * @var array $meta Post meta array
 * @var string $first_name Agent first name
 * @var string $last_name Agent last name
 * @var string $email Agent email
 * @var string $mobile Agent mobile number
 * @var string $public_name Public profile name
 * @var string $public_email Public profile email
 * @var string $public_phone Public profile phone
 * @var string $bio_primary Primary bio
 * @var string $bio_secondary Secondary bio
 * @var string $position_primary Primary position
 * @var string $position_secondary Secondary position
 * @var string $linkedin LinkedIn URL
 * @var string $role_name Role name
 * @var string $verification_status Verification status
 * @var string $is_super_agent Is super agent (0 or 1)
 * @var string|false $featured_image Featured image URL
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

global $post;

// Get agent meta
$meta = get_post_meta($post->ID);
$featured_image = get_the_post_thumbnail_url($post->ID, 'large');

// Extract agent data
$first_name = isset($meta['_pf_first_name'][0]) ? $meta['_pf_first_name'][0] : '';
$last_name = isset($meta['_pf_last_name'][0]) ? $meta['_pf_last_name'][0] : '';
$email = isset($meta['_pf_email'][0]) ? $meta['_pf_email'][0] : '';
$mobile = isset($meta['_pf_mobile'][0]) ? $meta['_pf_mobile'][0] : '';
$status = isset($meta['_pf_status'][0]) ? $meta['_pf_status'][0] : '';

// Public Profile
$public_name = isset($meta['_pf_public_profile_name'][0]) ? $meta['_pf_public_profile_name'][0] : '';
$public_email = isset($meta['_pf_public_profile_email'][0]) ? $meta['_pf_public_profile_email'][0] : '';
$public_phone = isset($meta['_pf_public_profile_phone'][0]) ? $meta['_pf_public_profile_phone'][0] : '';
$public_phone_secondary = isset($meta['_pf_public_profile_phone_secondary'][0]) ? $meta['_pf_public_profile_phone_secondary'][0] : '';
$whatsapp_phone = isset($meta['_pf_public_profile_whatsapp'][0]) ? $meta['_pf_public_profile_whatsapp'][0] : '';

// Bio
$bio_primary = isset($meta['_pf_bio_primary'][0]) ? $meta['_pf_bio_primary'][0] : '';
$bio_secondary = isset($meta['_pf_bio_secondary'][0]) ? $meta['_pf_bio_secondary'][0] : '';

// Position
$position_primary = isset($meta['_pf_position_primary'][0]) ? $meta['_pf_position_primary'][0] : '';
$position_secondary = isset($meta['_pf_position_secondary'][0]) ? $meta['_pf_position_secondary'][0] : '';

// Social
$linkedin = isset($meta['_pf_linkedin_address'][0]) ? $meta['_pf_linkedin_address'][0] : '';

// Role
$role_name = isset($meta['_pf_role_name'][0]) ? $meta['_pf_role_name'][0] : '';

// Verification
$verification_status = isset($meta['_pf_verification_status'][0]) ? $meta['_pf_verification_status'][0] : '';
$is_super_agent = isset($meta['_pf_is_super_agent'][0]) ? $meta['_pf_is_super_agent'][0] : '0';

// Compliances
$compliances = isset($meta['_pf_compliances'][0]) ? maybe_unserialize($meta['_pf_compliances'][0]) : array();
?>

<div class="propertyfinder-agent-single">
    <div class="propertyfinder-agent-header">
        <div class="propertyfinder-agent-header-content">
            <div class="propertyfinder-agent-image">
                <?php if ($featured_image): ?>
                    <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" />
                <?php else: ?>
                    <div class="propertyfinder-agent-placeholder">
                        <span class="dashicons dashicons-admin-users"></span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="propertyfinder-agent-info">
                <h1 class="propertyfinder-agent-name">
                    <?php 
                    // Show public profile name first, if empty then use basic info (first name + last name)
                    $display_name = '';
                    if (!empty($public_name)) {
                        $display_name = $public_name;
                    } elseif (!empty($first_name) || !empty($last_name)) {
                        $display_name = trim($first_name . ' ' . $last_name);
                    } else {
                        $display_name = get_the_title();
                    }
                    echo esc_html($display_name); 
                    ?>
                    <?php if ($is_super_agent === '1'): ?>
                        <span class="propertyfinder-super-agent-badge"><?php _e('Super Agent', 'propertyfinder'); ?></span>
                    <?php endif; ?>
                </h1>
                
                <?php if ($position_primary): ?>
                    <p class="propertyfinder-agent-position"><?php echo esc_html($position_primary); ?></p>
                <?php endif; ?>
                
                <?php if ($verification_status === 'verified'): ?>
                    <span class="propertyfinder-verification-badge verified">
                        <span class="dashicons dashicons-yes-alt"></span> <?php _e('Verified', 'propertyfinder'); ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="propertyfinder-agent-contact">
            <?php if ($public_phone): ?>
                <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $public_phone)); ?>" class="propertyfinder-contact-btn phone">
                    <span class="dashicons dashicons-phone"></span>
                    <?php echo esc_html($public_phone); ?>
                </a>
            <?php elseif ($mobile): ?>
                <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $mobile)); ?>" class="propertyfinder-contact-btn phone">
                    <span class="dashicons dashicons-phone"></span>
                    <?php echo esc_html($mobile); ?>
                </a>
            <?php endif; ?>
            
            <?php if ($whatsapp_phone): ?>
                <a href="https://wa.me/<?php echo esc_attr(preg_replace('/[^0-9]/', '', $whatsapp_phone)); ?>" class="propertyfinder-contact-btn whatsapp" target="_blank">
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
    </div>
    
    <div class="propertyfinder-agent-content">
        <div class="propertyfinder-agent-main">
            <?php if ($bio_primary || $bio_secondary): ?>
                <section class="propertyfinder-agent-section propertyfinder-agent-bio">
                    <h2><?php _e('About', 'propertyfinder'); ?></h2>
                    <?php if ($bio_primary): ?>
                        <div class="propertyfinder-agent-bio-primary">
                            <?php echo wp_kses_post($bio_primary); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($bio_secondary): ?>
                        <div class="propertyfinder-agent-bio-secondary">
                            <?php echo wp_kses_post($bio_secondary); ?>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
            
            <?php if ($position_secondary || $linkedin): ?>
                <section class="propertyfinder-agent-section propertyfinder-agent-details">
                    <h2><?php _e('Details', 'propertyfinder'); ?></h2>
                    <dl class="propertyfinder-details-list">
                        <?php if ($position_secondary): ?>
                            <dt><?php _e('Position', 'propertyfinder'); ?></dt>
                            <dd><?php echo esc_html($position_secondary); ?></dd>
                        <?php endif; ?>
                        
                        <?php if ($linkedin): ?>
                            <dt><?php _e('LinkedIn', 'propertyfinder'); ?></dt>
                            <dd>
                                <a href="<?php echo esc_url($linkedin); ?>" target="_blank" rel="noopener">
                                    <?php echo esc_html($linkedin); ?>
                                    <span class="dashicons dashicons-external"></span>
                                </a>
                            </dd>
                        <?php endif; ?>
                    </dl>
                </section>
            <?php endif; ?>
            
            <?php if (!empty($compliances) && is_array($compliances)): ?>
                <section class="propertyfinder-agent-section propertyfinder-agent-compliances">
                    <h2><?php _e('Compliances', 'propertyfinder'); ?></h2>
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
                </section>
            <?php endif; ?>
        </div>
        
        <div class="propertyfinder-agent-sidebar">
            <div class="propertyfinder-agent-widget propertyfinder-contact-widget">
                <h3><?php _e('Contact Information', 'propertyfinder'); ?></h3>
                <ul class="propertyfinder-contact-list">
                    <?php if ($public_phone ?: $mobile): ?>
                        <li>
                            <span class="dashicons dashicons-phone"></span>
                            <strong><?php _e('Phone:', 'propertyfinder'); ?></strong>
                            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $public_phone ?: $mobile)); ?>">
                                <?php echo esc_html($public_phone ?: $mobile); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php if ($public_phone_secondary): ?>
                        <li>
                            <span class="dashicons dashicons-phone"></span>
                            <strong><?php _e('Phone (Secondary):', 'propertyfinder'); ?></strong>
                            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $public_phone_secondary)); ?>">
                                <?php echo esc_html($public_phone_secondary); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php if ($whatsapp_phone): ?>
                        <li>
                            <span class="dashicons dashicons-whatsapp"></span>
                            <strong><?php _e('WhatsApp:', 'propertyfinder'); ?></strong>
                            <a href="https://wa.me/<?php echo esc_attr(preg_replace('/[^0-9]/', '', $whatsapp_phone)); ?>" target="_blank">
                                <?php echo esc_html($whatsapp_phone); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php if ($public_email ?: $email): ?>
                        <li>
                            <span class="dashicons dashicons-email"></span>
                            <strong><?php _e('Email:', 'propertyfinder'); ?></strong>
                            <a href="mailto:<?php echo esc_attr($public_email ?: $email); ?>">
                                <?php echo esc_html($public_email ?: $email); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

