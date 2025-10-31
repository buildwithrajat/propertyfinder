<?php
/**
 * Admin logs page view
 *
 * @package PropertyFinder
 * @subpackage Views
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get WordPress debug log
$debug_log_file = WP_CONTENT_DIR . '/debug.log';
$log_content = '';

if (file_exists($debug_log_file)) {
    // Get PropertyFinder-related entries
    $full_log = file_get_contents($debug_log_file);
    $lines = explode("\n", $full_log);
    
    // Filter for PropertyFinder entries only
    $propertyfinder_lines = array();
    foreach ($lines as $line) {
        if (stripos($line, 'PropertyFinder') !== false) {
            $propertyfinder_lines[] = $line;
        }
    }
    
    // Get last 200 lines
    $propertyfinder_lines = array_slice($propertyfinder_lines, -200);
    $log_content = implode("\n", $propertyfinder_lines);
}

// Get API status
$api_key = get_option('propertyfinder_api_key', '');
$api_secret = get_option('propertyfinder_api_secret', '');
$api_endpoint = get_option('propertyfinder_api_endpoint', 'https://atlas.propertyfinder.com/v1');
?>
<div class="wrap propertyfinder-modern propertyfinder-logs-page">
    <h1><?php echo esc_html($page_title); ?></h1>
    
    <div class="propertyfinder-logs-info">
        <h2><?php _e('API Configuration Status', 'propertyfinder'); ?></h2>
        <table class="form-table">
            <tr>
                <th><?php _e('API Endpoint:', 'propertyfinder'); ?></th>
                <td><code><?php echo esc_html($api_endpoint); ?></code></td>
            </tr>
            <tr>
                <th><?php _e('API Key:', 'propertyfinder'); ?></th>
                <td>
                    <?php if (!empty($api_key)): ?>
                        <span style="color: #4ab866;">✓ Configured</span> 
                        (<code><?php echo esc_html(substr($api_key, 0, 15)) . '...'; ?></code>)
                    <?php else: ?>
                        <span style="color: #d63638;">✗ Not configured</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th><?php _e('API Secret:', 'propertyfinder'); ?></th>
                <td>
                    <?php if (!empty($api_secret)): ?>
                        <span style="color: #4ab866;">✓ Configured</span>
                    <?php else: ?>
                        <span style="color: #d63638;">✗ Not configured</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th><?php _e('Cached Token:', 'propertyfinder'); ?></th>
                <td>
                    <?php 
                    $cached_token = get_transient('propertyfinder_access_token');
                    if ($cached_token): 
                    ?>
                        <span style="color: #4ab866;">✓ Token exists</span>
                    <?php else: ?>
                        <span style="color: #ffb900;">No cached token</span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="propertyfinder-logs-actions">
        <button type="button" class="button button-secondary" id="test-connection-from-logs">
            <?php _e('Test Connection', 'propertyfinder'); ?>
        </button>
        <button type="button" class="button button-secondary" onclick="location.reload()">
            <?php _e('Refresh Logs', 'propertyfinder'); ?>
        </button>
    </div>
    
    <div style="background: #fff; padding: 30px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05); border-radius: 4px; margin-top: 30px;">
        <h2><?php _e('Recent PropertyFinder Log Entries', 'propertyfinder'); ?></h2>
        <p><?php _e('Log file location:', 'propertyfinder'); ?> <code><?php echo esc_html($debug_log_file); ?></code></p>
        
        <?php if (!empty($log_content)): ?>
            <pre><?php echo esc_html($log_content); ?></pre>
        <?php else: ?>
            <p><?php _e('No PropertyFinder log entries found. Try clicking "Test Connection" to generate logs.', 'propertyfinder'); ?></p>
        <?php endif; ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#test-connection-from-logs').on('click', function() {
        var button = $(this);
        button.prop('disabled', true).text('Testing...');
        
        $.ajax({
            url: propertyfinderAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'propertyfinder_test_connection',
                nonce: propertyfinderAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Connection successful! Check logs for details.');
                } else {
                    alert('Connection failed: ' + response.data.message);
                }
                location.reload();
            },
            error: function() {
                alert('Error occurred during test.');
                button.prop('disabled', false).text('Test Connection');
            }
        });
    });
});
</script>