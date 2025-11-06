<?php
/**
 * Webhooks Section
 *
 * @package PropertyFinder
 * @subpackage Views
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="propertyfinder-webhooks-section" style="display: none !important;">
    <div class="propertyfinder-settings-section propertyfinder-card">
        <div class="propertyfinder-section-header">
            <div class="propertyfinder-section-icon">
                <span class="dashicons dashicons-admin-links"></span>
            </div>
            <div class="propertyfinder-section-title-group">
                <h2><?php _e('Webhook Configuration', 'propertyfinder'); ?></h2>
                <p class="propertyfinder-section-description"><?php _e('Configure webhook settings for PropertyFinder', 'propertyfinder'); ?></p>
            </div>
        </div>
        
        <div class="propertyfinder-form-section">
            <div class="propertyfinder-form-row">
                <div class="propertyfinder-form-label">
                    <label><?php _e('Webhook URL', 'propertyfinder'); ?></label>
                </div>
                <div class="propertyfinder-form-field">
                    <div class="propertyfinder-webhook-url-wrapper">
                        <code id="webhook-url" class="propertyfinder-webhook-url"><?php echo esc_html($webhook_url); ?></code>
                        <button type="button" class="button button-secondary propertyfinder-btn propertyfinder-btn-secondary" onclick="PropertyFinderSettings.copyToClipboard('webhook-url')">
                            <span class="dashicons dashicons-admin-page"></span> <?php _e('Copy URL', 'propertyfinder'); ?>
                        </button>
                    </div>
                    <p class="propertyfinder-field-description"><?php _e('Use this URL when subscribing to PropertyFinder webhook events.', 'propertyfinder'); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="propertyfinder-settings-section propertyfinder-card">
        <div class="propertyfinder-section-header">
            <div class="propertyfinder-section-icon">
                <span class="dashicons dashicons-rss"></span>
            </div>
            <div class="propertyfinder-section-title-group">
                <h2><?php _e('Subscribe to Webhook Events', 'propertyfinder'); ?></h2>
                <p class="propertyfinder-section-description"><?php _e('Subscribe to PropertyFinder webhook events', 'propertyfinder'); ?></p>
            </div>
        </div>
        
        <div class="propertyfinder-form-section">
            <div class="propertyfinder-form-row">
                <div class="propertyfinder-form-label">
                    <label for="webhook-event-id"><?php _e('Event Type', 'propertyfinder'); ?></label>
                </div>
                <div class="propertyfinder-form-field">
                    <div class="propertyfinder-select-wrapper">
                        <select id="webhook-event-id" class="propertyfinder-select">
                            <?php
                            $webhook_events = \PropertyFinder_Config::get_webhook_events();
                            foreach ($webhook_events as $event_id => $event_label):
                            ?>
                                <option value="<?php echo esc_attr($event_id); ?>"><?php echo esc_html($event_label); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="propertyfinder-select-icon dashicons dashicons-arrow-down-alt2"></span>
                    </div>
                    <p class="propertyfinder-field-description"><?php _e('Select the event type to subscribe to.', 'propertyfinder'); ?></p>
                </div>
            </div>
            
            <div class="propertyfinder-form-row">
                <div class="propertyfinder-form-label">
                    <label for="webhook-callback-url"><?php _e('Callback URL', 'propertyfinder'); ?></label>
                </div>
                <div class="propertyfinder-form-field">
                    <div class="propertyfinder-input-wrapper">
                        <input type="url" 
                               id="webhook-callback-url" 
                               value="<?php echo esc_attr($webhook_url); ?>" 
                               class="propertyfinder-input" />
                        <span class="propertyfinder-input-icon dashicons dashicons-admin-links"></span>
                    </div>
                    <p class="propertyfinder-field-description"><?php _e('The URL where webhook events will be sent.', 'propertyfinder'); ?></p>
                </div>
            </div>
            
            <div class="propertyfinder-form-row">
                <div class="propertyfinder-form-label">
                    <label for="webhook-secret-input"><?php _e('Secret (Optional)', 'propertyfinder'); ?></label>
                </div>
                <div class="propertyfinder-form-field">
                    <div class="propertyfinder-input-wrapper">
                        <input type="text" 
                               id="webhook-secret-input" 
                               value="<?php echo esc_attr($webhook_secret); ?>" 
                               class="propertyfinder-input" 
                               placeholder="<?php _e('Leave empty to use default secret', 'propertyfinder'); ?>" />
                        <span class="propertyfinder-input-icon dashicons dashicons-lock"></span>
                    </div>
                    <p class="propertyfinder-field-description"><?php _e('HMAC secret for signature verification (optional).', 'propertyfinder'); ?></p>
                </div>
            </div>
        </div>
        
        <div class="propertyfinder-form-actions">
            <button type="button" class="button button-primary propertyfinder-btn propertyfinder-btn-primary" id="subscribe-webhook">
                <span class="dashicons dashicons-yes-alt"></span> <?php _e('Subscribe to Webhook', 'propertyfinder'); ?>
            </button>
        </div>
    </div>
    
    <div class="propertyfinder-settings-section propertyfinder-card">
        <div class="propertyfinder-section-header">
            <div class="propertyfinder-section-icon">
                <span class="dashicons dashicons-list-view"></span>
            </div>
            <div class="propertyfinder-section-title-group">
                <h2><?php _e('Registered Webhooks', 'propertyfinder'); ?></h2>
                <p class="propertyfinder-section-description"><?php _e('Manage your webhook subscriptions', 'propertyfinder'); ?></p>
            </div>
            <div class="propertyfinder-section-actions">
                <button type="button" class="button button-secondary propertyfinder-btn propertyfinder-btn-secondary" id="refresh-webhooks">
                    <span class="dashicons dashicons-update"></span> <?php _e('Refresh List', 'propertyfinder'); ?>
                </button>
            </div>
        </div>
        
        <div id="webhooks-list" class="propertyfinder-webhooks-list">
        <?php if (empty($webhooks)): ?>
            <div class="propertyfinder-empty-state">
                <span class="dashicons dashicons-rss"></span>
                <p><?php _e('No webhooks registered yet.', 'propertyfinder'); ?></p>
                <p class="propertyfinder-empty-state-description"><?php _e('Use the form above to subscribe to webhook events.', 'propertyfinder'); ?></p>
            </div>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped propertyfinder-table">
                <thead>
                    <tr>
                        <th><?php _e('Event ID', 'propertyfinder'); ?></th>
                        <th><?php _e('Callback URL', 'propertyfinder'); ?></th>
                        <th><?php _e('Created', 'propertyfinder'); ?></th>
                        <th><?php _e('Actions', 'propertyfinder'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($webhooks as $webhook): ?>
                        <tr>
                            <td><strong><?php echo esc_html($webhook['eventId']); ?></strong></td>
                            <td><code><?php echo esc_html($webhook['url']); ?></code></td>
                            <td><?php echo isset($webhook['createdAt']) ? esc_html($webhook['createdAt']) : '-'; ?></td>
                            <td>
                                <button type="button" 
                                        class="button button-small button-link-delete propertyfinder-btn propertyfinder-btn-danger unsubscribe-webhook" 
                                        data-event-id="<?php echo esc_attr($webhook['eventId']); ?>">
                                    <?php _e('Unsubscribe', 'propertyfinder'); ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        </div>
    </div>
</div>

