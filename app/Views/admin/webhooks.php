<?php
/**
 * Admin webhooks page view
 *
 * @package PropertyFinder
 * @subpackage Views
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>
<div class="wrap propertyfinder-modern">
    <h1><?php echo esc_html($page_title); ?></h1>
    
    <div class="propertyfinder-webhooks-section">
        <h2><?php _e('Webhook Configuration', 'propertyfinder'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label><?php _e('Webhook URL', 'propertyfinder'); ?></label>
                </th>
                <td>
                    <code id="webhook-url" style="padding: 8px; background: #f5f5f5; display: inline-block; margin-bottom: 10px;"><?php echo esc_html($webhook_url); ?></code>
                    <button type="button" class="button button-small" onclick="copyToClipboard('webhook-url')">
                        <span class="dashicons dashicons-admin-page"></span> <?php _e('Copy', 'propertyfinder'); ?>
                    </button>
                    <p class="description"><?php _e('Use this URL when subscribing to PropertyFinder webhook events.', 'propertyfinder'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label><?php _e('Webhook Secret', 'propertyfinder'); ?></label>
                </th>
                <td>
                    <?php if (empty($webhook_secret)): ?>
                        <button type="button" class="button" id="generate-webhook-secret">
                            <?php _e('Generate Secret', 'propertyfinder'); ?>
                        </button>
                        <p class="description"><?php _e('Generate a secret key for HMAC signature verification. Save it in your PropertyFinder webhook settings.', 'propertyfinder'); ?></p>
                    <?php else: ?>
                        <code style="padding: 8px; background: #f5f5f5; display: inline-block;"><?php echo esc_html($webhook_secret); ?></code>
                        <p class="description"><?php _e('Current webhook secret. Configure this in PropertyFinder webhook settings.', 'propertyfinder'); ?></p>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="propertyfinder-webhooks-section" style="margin-top: 30px;">
        <h2><?php _e('Subscribe to Webhook Events', 'propertyfinder'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="webhook-event-id"><?php _e('Event Type', 'propertyfinder'); ?></label>
                </th>
                <td>
                    <select id="webhook-event-id" class="regular-text">
                        <option value="listing.published"><?php _e('Listing Published', 'propertyfinder'); ?></option>
                        <option value="listing.unpublished"><?php _e('Listing Unpublished', 'propertyfinder'); ?></option>
                        <option value="lead.created"><?php _e('Lead Created', 'propertyfinder'); ?></option>
                        <option value="lead.updated"><?php _e('Lead Updated', 'propertyfinder'); ?></option>
                        <option value="user.created"><?php _e('User Created', 'propertyfinder'); ?></option>
                        <option value="user.updated"><?php _e('User Updated', 'propertyfinder'); ?></option>
                    </select>
                    <p class="description"><?php _e('Select the event type to subscribe to.', 'propertyfinder'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="webhook-callback-url"><?php _e('Callback URL', 'propertyfinder'); ?></label>
                </th>
                <td>
                    <input type="url" 
                           id="webhook-callback-url" 
                           value="<?php echo esc_attr($webhook_url); ?>" 
                           class="regular-text" />
                    <p class="description"><?php _e('The URL where webhook events will be sent.', 'propertyfinder'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="webhook-secret-input"><?php _e('Secret (Optional)', 'propertyfinder'); ?></label>
                </th>
                <td>
                    <input type="text" 
                           id="webhook-secret-input" 
                           value="<?php echo esc_attr($webhook_secret); ?>" 
                           class="regular-text" 
                           placeholder="<?php _e('Leave empty to use default secret', 'propertyfinder'); ?>" />
                    <p class="description"><?php _e('HMAC secret for signature verification (optional).', 'propertyfinder'); ?></p>
                </td>
            </tr>
        </table>
        <p class="submit">
            <button type="button" class="button button-primary" id="subscribe-webhook">
                <span class="dashicons dashicons-yes-alt"></span> <?php _e('Subscribe', 'propertyfinder'); ?>
            </button>
        </p>
    </div>

    <div class="propertyfinder-webhooks-section" style="margin-top: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2><?php _e('Registered Webhooks', 'propertyfinder'); ?></h2>
            <button type="button" class="button button-secondary" id="refresh-webhooks">
                <span class="dashicons dashicons-update"></span> <?php _e('Refresh', 'propertyfinder'); ?>
            </button>
        </div>
        
        <div id="webhooks-list">
            <?php if (empty($webhooks)): ?>
                <p><?php _e('No webhooks registered yet.', 'propertyfinder'); ?></p>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
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
                                            class="button button-small button-link-delete unsubscribe-webhook" 
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

<script>
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    const text = element.textContent || element.innerText;
    
    navigator.clipboard.writeText(text).then(function() {
        alert('<?php _e('Copied to clipboard!', 'propertyfinder'); ?>');
    }, function(err) {
        console.error('Failed to copy: ', err);
    });
}

jQuery(document).ready(function($) {
    // Subscribe webhook
    $('#subscribe-webhook').on('click', function() {
        const button = $(this);
        const eventId = $('#webhook-event-id').val();
        const callbackUrl = $('#webhook-callback-url').val();
        const secret = $('#webhook-secret-input').val();
        
        if (!eventId || !callbackUrl) {
            alert('<?php _e('Please fill in all required fields.', 'propertyfinder'); ?>');
            return;
        }
        
        button.prop('disabled', true).text('<?php _e('Subscribing...', 'propertyfinder'); ?>');
        
        $.ajax({
            url: propertyfinderAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'propertyfinder_subscribe_webhook',
                nonce: propertyfinderAdmin.nonce,
                event_id: eventId,
                callback_url: callbackUrl,
                secret: secret
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message || '<?php _e('Webhook subscribed successfully!', 'propertyfinder'); ?>');
                    location.reload();
                } else {
                    alert(response.data.message || '<?php _e('Failed to subscribe webhook.', 'propertyfinder'); ?>');
                    button.prop('disabled', false).html('<span class="dashicons dashicons-yes-alt"></span> <?php _e('Subscribe', 'propertyfinder'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('An error occurred. Please try again.', 'propertyfinder'); ?>');
                button.prop('disabled', false).html('<span class="dashicons dashicons-yes-alt"></span> <?php _e('Subscribe', 'propertyfinder'); ?>');
            }
        });
    });
    
    // Unsubscribe webhook
    $('.unsubscribe-webhook').on('click', function() {
        if (!confirm('<?php _e('Are you sure you want to unsubscribe from this webhook?', 'propertyfinder'); ?>')) {
            return;
        }
        
        const button = $(this);
        const eventId = button.data('event-id');
        
        button.prop('disabled', true).text('<?php _e('Unsubscribing...', 'propertyfinder'); ?>');
        
        $.ajax({
            url: propertyfinderAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'propertyfinder_unsubscribe_webhook',
                nonce: propertyfinderAdmin.nonce,
                event_id: eventId
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php _e('Webhook unsubscribed successfully!', 'propertyfinder'); ?>');
                    location.reload();
                } else {
                    alert(response.data.message || '<?php _e('Failed to unsubscribe webhook.', 'propertyfinder'); ?>');
                    button.prop('disabled', false).text('<?php _e('Unsubscribe', 'propertyfinder'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('An error occurred. Please try again.', 'propertyfinder'); ?>');
                button.prop('disabled', false).text('<?php _e('Unsubscribe', 'propertyfinder'); ?>');
            }
        });
    });
    
    // Refresh webhooks
    $('#refresh-webhooks').on('click', function() {
        const button = $(this);
        button.prop('disabled', true);
        
        $.ajax({
            url: propertyfinderAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'propertyfinder_refresh_webhooks',
                nonce: propertyfinderAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message || '<?php _e('Failed to refresh webhooks.', 'propertyfinder'); ?>');
                    button.prop('disabled', false);
                }
            },
            error: function() {
                alert('<?php _e('An error occurred. Please try again.', 'propertyfinder'); ?>');
                button.prop('disabled', false);
            }
        });
    });
    
    // Generate webhook secret
    $('#generate-webhook-secret').on('click', function() {
        const secret = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
        $('#webhook-secret-input').val(secret);
        alert('<?php _e('Secret generated. Please save your settings to store it.', 'propertyfinder'); ?>');
    });
});
</script>


