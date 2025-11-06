<?php
/**
 * Actions Section
 *
 * @package PropertyFinder
 * @subpackage Views
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>
<div class="propertyfinder-settings-section propertyfinder-card propertyfinder-actions-card">
    <div class="propertyfinder-section-header">
        <div class="propertyfinder-section-icon">
            <span class="dashicons dashicons-controls-play"></span>
        </div>
        <div class="propertyfinder-section-title-group">
            <h2><?php _e('Actions', 'propertyfinder'); ?></h2>
            <p class="propertyfinder-section-description"><?php _e('Test your API connection and import listings', 'propertyfinder'); ?></p>
        </div>
    </div>
    
    <div class="propertyfinder-actions-content">
        <div class="propertyfinder-action-group">
            <div class="propertyfinder-action-item">
                <div class="propertyfinder-action-icon">
                    <span class="dashicons dashicons-admin-plugins"></span>
                </div>
                <div class="propertyfinder-action-content">
                    <h3><?php _e('Test Connection', 'propertyfinder'); ?></h3>
                    <p><?php _e('Verify your API credentials are working correctly', 'propertyfinder'); ?></p>
                </div>
                <div class="propertyfinder-action-button">
                    <button type="button" class="button button-secondary propertyfinder-btn propertyfinder-btn-secondary" id="propertyfinder-test-connection">
                        <span class="dashicons dashicons-admin-plugins"></span>
                        <?php _e('Test Connection', 'propertyfinder'); ?>
                    </button>
                    <span class="spinner propertyfinder-spinner"></span>
                </div>
                <div class="propertyfinder-action-status connection-status"></div>
            </div>
            
            <div class="propertyfinder-action-item propertyfinder-action-primary">
                <div class="propertyfinder-action-icon">
                    <span class="dashicons dashicons-update"></span>
                </div>
                <div class="propertyfinder-action-content">
                    <h3><?php _e('Import Listings', 'propertyfinder'); ?></h3>
                    <p><?php _e('Manually sync listings from PropertyFinder API', 'propertyfinder'); ?></p>
                </div>
                <div class="propertyfinder-action-buttons">
                    <button type="button" class="button button-primary propertyfinder-btn propertyfinder-btn-primary" id="propertyfinder-sync-now">
                        <span class="dashicons dashicons-update"></span>
                        <?php _e('Import Listings Now', 'propertyfinder'); ?>
                    </button>
                    <button type="button" class="button button-secondary propertyfinder-btn propertyfinder-btn-secondary" id="propertyfinder-sync-all">
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Sync All Pages', 'propertyfinder'); ?>
                    </button>
                </div>
                <div class="propertyfinder-action-status sync-status"></div>
            </div>
        </div>
    </div>
</div>
