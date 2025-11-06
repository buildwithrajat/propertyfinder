<?php
/**
 * API Configuration Section
 *
 * @package PropertyFinder
 * @subpackage Views
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>
<div class="propertyfinder-settings-section propertyfinder-card">
    <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=propertyfinder-settings')); ?>" class="propertyfinder-settings-form">
        <?php wp_nonce_field('propertyfinder_settings_nonce'); ?>
        <input type="hidden" name="propertyfinder_save_section" value="api_config" />
        
        <div class="propertyfinder-section-header">
            <div class="propertyfinder-section-icon">
                <span class="dashicons dashicons-admin-network"></span>
            </div>
            <div class="propertyfinder-section-title-group">
                <h2><?php _e('API Configuration', 'propertyfinder'); ?></h2>
                <p class="propertyfinder-section-description"><?php _e('Configure your PropertyFinder API credentials', 'propertyfinder'); ?></p>
            </div>
        </div>
        
        <div class="propertyfinder-form-section">
            <div class="propertyfinder-form-row">
                <div class="propertyfinder-form-label">
                    <label for="propertyfinder_api_key">
                        <?php _e('API Key', 'propertyfinder'); ?>
                        <span class="propertyfinder-required">*</span>
                    </label>
                </div>
                <div class="propertyfinder-form-field">
                    <div class="propertyfinder-input-wrapper">
                        <input type="text" 
                               id="propertyfinder_api_key" 
                               name="propertyfinder_api_key" 
                               value="<?php echo esc_attr(get_option('propertyfinder_api_key', '')); ?>" 
                               class="propertyfinder-input" 
                               placeholder="<?php _e('Enter your API Key', 'propertyfinder'); ?>" />
                        <span class="propertyfinder-input-icon dashicons dashicons-admin-network"></span>
                    </div>
                    <p class="propertyfinder-field-description"><?php _e('Enter your PropertyFinder API Key', 'propertyfinder'); ?></p>
                </div>
            </div>
            
            <div class="propertyfinder-form-row">
                <div class="propertyfinder-form-label">
                    <label for="propertyfinder_api_secret">
                        <?php _e('API Secret', 'propertyfinder'); ?>
                        <span class="propertyfinder-required">*</span>
                    </label>
                </div>
                <div class="propertyfinder-form-field">
                    <div class="propertyfinder-input-wrapper">
                        <input type="password" 
                               id="propertyfinder_api_secret" 
                               name="propertyfinder_api_secret" 
                               value="<?php echo esc_attr(get_option('propertyfinder_api_secret', '')); ?>" 
                               class="propertyfinder-input" 
                               placeholder="<?php _e('Enter your API Secret', 'propertyfinder'); ?>" />
                        <span class="propertyfinder-input-icon dashicons dashicons-lock"></span>
                        <button type="button" class="propertyfinder-toggle-password" aria-label="<?php _e('Show password', 'propertyfinder'); ?>">
                            <span class="dashicons dashicons-visibility"></span>
                        </button>
                    </div>
                    <p class="propertyfinder-field-description"><?php _e('Enter your PropertyFinder API Secret', 'propertyfinder'); ?></p>
                </div>
            </div>
            
            <div class="propertyfinder-form-row">
                <div class="propertyfinder-form-label">
                    <label for="propertyfinder_api_endpoint">
                        <?php _e('API Endpoint', 'propertyfinder'); ?>
                        <span class="propertyfinder-required">*</span>
                    </label>
                </div>
                <div class="propertyfinder-form-field">
                    <div class="propertyfinder-input-wrapper">
                        <input type="url" 
                               id="propertyfinder_api_endpoint" 
                               name="propertyfinder_api_endpoint" 
                               value="<?php echo esc_attr(get_option('propertyfinder_api_endpoint', 'https://atlas.propertyfinder.com/v1')); ?>" 
                               class="propertyfinder-input" 
                               placeholder="<?php _e('https://atlas.propertyfinder.com/v1', 'propertyfinder'); ?>" />
                        <span class="propertyfinder-input-icon dashicons dashicons-admin-links"></span>
                    </div>
                    <p class="propertyfinder-field-description"><?php _e('API endpoint URL', 'propertyfinder'); ?></p>
                </div>
            </div>
            
            <div class="propertyfinder-form-row" style="display: none !important;">
                <div class="propertyfinder-form-label">
                    <label for="propertyfinder_webhook_secret"><?php _e('Webhook Secret', 'propertyfinder'); ?></label>
                </div>
                <div class="propertyfinder-form-field">
                    <input type="text" 
                           id="propertyfinder_webhook_secret" 
                           name="propertyfinder_webhook_secret" 
                           value="<?php echo esc_attr(get_option('propertyfinder_webhook_secret', '')); ?>" 
                           class="propertyfinder-input" />
                    <p class="propertyfinder-field-description"><?php _e('HMAC secret for webhook signature verification (optional)', 'propertyfinder'); ?></p>
                </div>
            </div>
        </div>
        
        <div class="propertyfinder-form-actions">
            <?php submit_button(__('Save API Settings', 'propertyfinder'), 'primary propertyfinder-btn-primary propertyfinder-btn-small', 'propertyfinder_save_settings', false); ?>
        </div>
    </form>
</div>
