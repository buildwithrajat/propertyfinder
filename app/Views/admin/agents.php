<?php
/**
 * Agents page view
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
    
    <div class="propertyfinder-agents-section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <p class="description"><?php _e('Manage agents imported from PropertyFinder API', 'propertyfinder'); ?></p>
                <?php if (isset($stats)): ?>
                    <div style="margin-top: 10px;">
                        <span style="margin-right: 15px;"><strong><?php _e('Total:', 'propertyfinder'); ?></strong> <?php echo esc_html($stats['total']); ?></span>
                        <span style="margin-right: 15px; color: #00a32a;"><strong><?php _e('Published:', 'propertyfinder'); ?></strong> <?php echo esc_html($stats['published']); ?></span>
                        <span style="margin-right: 15px; color: #d63638;"><strong><?php _e('Draft:', 'propertyfinder'); ?></strong> <?php echo esc_html($stats['draft']); ?></span>
                        <?php 
                        $last_sync = get_option('propertyfinder_agent_last_sync', '');
                        $sync_enabled = get_option('propertyfinder_agent_sync_enabled', false);
                        $lock_status = get_transient('propertyfinder_agent_import_lock');
                        if ($last_sync): 
                            $last_sync_time = strtotime($last_sync);
                            $time_diff = human_time_diff($last_sync_time, current_time('timestamp'));
                        ?>
                            <span style="margin-right: 15px; color: #2271b1;">
                                <strong><?php _e('Last Sync:', 'propertyfinder'); ?></strong> 
                                <span id="last-sync-time"><?php echo esc_html($time_diff . ' ago'); ?></span>
                                <?php if ($sync_enabled): ?>
                                    <span class="dashicons dashicons-update" style="font-size: 14px; margin-left: 5px; animation: spin 2s linear infinite;" title="<?php _e('Auto-sync enabled', 'propertyfinder'); ?>"></span>
                                <?php endif; ?>
                                <?php if ($lock_status): ?>
                                    <span class="dashicons dashicons-lock" style="font-size: 14px; margin-left: 5px; color: #f56e28;" title="<?php _e('Sync in progress', 'propertyfinder'); ?>"></span>
                                <?php endif; ?>
                            </span>
                        <?php else: ?>
                            <span style="margin-right: 15px; color: #999;">
                                <strong><?php _e('Last Sync:', 'propertyfinder'); ?></strong> 
                                <span id="last-sync-time"><?php _e('Never', 'propertyfinder'); ?></span>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div>
                <a href="<?php echo admin_url('post-new.php?post_type=' . PropertyFinder_Config::get_agent_cpt_name()); ?>" class="button button-primary">
                    <span class="dashicons dashicons-plus-alt"></span> <?php _e('Add New Agent', 'propertyfinder'); ?>
                </a>
                <a href="<?php echo admin_url('edit.php?post_type=' . PropertyFinder_Config::get_agent_cpt_name()); ?>" class="button">
                    <span class="dashicons dashicons-list-view"></span> <?php _e('View All Agents', 'propertyfinder'); ?>
                </a>
                <button type="button" class="button button-primary" id="import-agents">
                    <span class="dashicons dashicons-download"></span> <?php _e('Import from API', 'propertyfinder'); ?>
                </button>
                <button type="button" class="button button-secondary" id="refresh-agents">
                    <span class="dashicons dashicons-update"></span> <?php _e('Refresh', 'propertyfinder'); ?>
                </button>
                <button type="button" class="button button-secondary" id="sync-agents">
                    <span class="dashicons dashicons-update"></span> <?php _e('Sync Now', 'propertyfinder'); ?>
                </button>
            </div>
        </div>
        
        <?php 
        $lock_status = get_transient('propertyfinder_agent_import_lock');
        if ($lock_status): 
        ?>
            <div id="sync-status" style="padding: 10px; background: #fff3cd; border-left: 4px solid #ffc107; margin-bottom: 20px; border-radius: 3px;">
                <span class="dashicons dashicons-update" style="animation: spin 1s linear infinite;"></span>
                <strong><?php _e('Agent sync is currently running...', 'propertyfinder'); ?></strong>
                <span id="sync-progress-text"></span>
            </div>
        <?php endif; ?>
        
        <?php if (empty($agents)): ?>
            <div style="padding: 20px; text-align: center; background: #f9f9f9; border: 1px dashed #ddd; border-radius: 4px;">
                <p style="margin: 0; color: #666;"><?php _e('No agents found in WordPress.', 'propertyfinder'); ?></p>
                <p style="margin: 10px 0 0 0; color: #999; font-size: 13px;">
                    <?php _e('Click "Import from API" to import agents from PropertyFinder API.', 'propertyfinder'); ?>
                </p>
            </div>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 50px;"><?php _e('Image', 'propertyfinder'); ?></th>
                        <th><?php _e('Name', 'propertyfinder'); ?></th>
                        <th><?php _e('API ID', 'propertyfinder'); ?></th>
                        <th><?php _e('Email', 'propertyfinder'); ?></th>
                        <th><?php _e('Phone', 'propertyfinder'); ?></th>
                        <th><?php _e('Status', 'propertyfinder'); ?></th>
                        <th><?php _e('Role', 'propertyfinder'); ?></th>
                        <th><?php _e('Actions', 'propertyfinder'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($agents as $agent): ?>
                        <tr>
                            <td>
                                <?php if (!empty($agent['featured_image'])): ?>
                                    <img src="<?php echo esc_url($agent['featured_image']); ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 3px;" />
                                <?php else: ?>
                                    <span class="dashicons dashicons-admin-users" style="font-size: 40px; color: #ddd;"></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong>
                                    <a href="<?php echo esc_url($agent['edit_link']); ?>">
                                        <?php 
                                        $name = trim(($agent['first_name'] ?? '') . ' ' . ($agent['last_name'] ?? ''));
                                        echo esc_html($name ?: $agent['post_title'] ?: '-'); 
                                        ?>
                                    </a>
                                </strong>
                                <br>
                                <small style="color: #666;">
                                    <?php 
                                    if ($agent['post_status'] === 'publish') {
                                        echo '<span style="color: #00a32a;">●</span> ' . __('Published', 'propertyfinder');
                                    } else {
                                        echo '<span style="color: #d63638;">●</span> ' . __('Draft', 'propertyfinder');
                                    }
                                    ?>
                                </small>
                            </td>
                            <td>
                                <?php if (!empty($agent['api_id'])): ?>
                                    <code><?php echo esc_html($agent['api_id']); ?></code>
                                <?php else: ?>
                                    <span style="color: #999;"><?php _e('Not synced', 'propertyfinder'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($agent['email'])): ?>
                                    <a href="mailto:<?php echo esc_attr($agent['email']); ?>"><?php echo esc_html($agent['email']); ?></a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $phone = $agent['public_profile_phone'] ?: $agent['mobile'];
                                echo $phone ? esc_html($phone) : '-'; 
                                ?>
                            </td>
                            <td>
                                <?php if (!empty($agent['status'])): ?>
                                    <span class="status-badge status-<?php echo esc_attr($agent['status']); ?>">
                                        <?php echo esc_html(ucfirst($agent['status'])); ?>
                                    </span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo !empty($agent['role_name']) ? esc_html($agent['role_name']) : '-'; ?>
                            </td>
                            <td>
                                <a href="<?php echo esc_url($agent['edit_link']); ?>" class="button button-small">
                                    <?php _e('Edit', 'propertyfinder'); ?>
                                </a>
                                <?php if (!empty($agent['view_link'])): ?>
                                    <a href="<?php echo esc_url($agent['view_link']); ?>" class="button button-small" target="_blank">
                                        <?php _e('View', 'propertyfinder'); ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<style>
.status-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}
.status-active {
    background: #d4edda;
    color: #155724;
}
.status-inactive {
    background: #f8d7da;
    color: #721c24;
}
</style>

<style>
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.sync-status-success {
    background: #d4edda !important;
    border-left-color: #28a745 !important;
    color: #155724;
}
.sync-status-error {
    background: #f8d7da !important;
    border-left-color: #dc3545 !important;
    color: #721c24;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Auto-refresh sync status every 5 seconds
    let syncStatusInterval = null;
    
    function checkSyncStatus() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'propertyfinder_check_agent_sync_status',
                nonce: '<?php echo wp_create_nonce('propertyfinder_admin_nonce'); ?>'
            },
            success: function(response) {
                if (response.success && response.data) {
                    const status = response.data;
                    
                    // Update last sync time
                    if (status.last_sync) {
                        $('#last-sync-time').text(status.last_sync + ' ago');
                    }
                    
                    // Update sync status indicator
                    if (status.is_running) {
                        if (!$('#sync-status').length) {
                            $('.propertyfinder-agents-section').prepend(
                                '<div id="sync-status" style="padding: 10px; background: #fff3cd; border-left: 4px solid #ffc107; margin-bottom: 20px; border-radius: 3px;">' +
                                '<span class="dashicons dashicons-update" style="animation: spin 1s linear infinite;"></span> ' +
                                '<strong><?php _e('Agent sync is currently running...', 'propertyfinder'); ?></strong>' +
                                '</div>'
                            );
                        }
                    } else {
                        $('#sync-status').remove();
                        if (syncStatusInterval) {
                            clearInterval(syncStatusInterval);
                            syncStatusInterval = null;
                        }
                    }
                }
            }
        });
    }
    
    // Start checking sync status if page loads while sync is running
    <?php if ($lock_status): ?>
    syncStatusInterval = setInterval(checkSyncStatus, 5000);
    <?php endif; ?>
    
    $('#refresh-agents').on('click', function() {
        const button = $(this);
        const originalHtml = button.html();
        button.prop('disabled', true).html('<span class="dashicons dashicons-update" style="animation: spin 1s linear infinite;"></span> <?php _e('Refreshing...', 'propertyfinder'); ?>');
        
        location.reload();
    });
    
    $('#sync-agents').on('click', function() {
        const button = $(this);
        const originalHtml = button.html();
        
        // Check if sync is already running
        if ($('#sync-status').length) {
            alert('<?php _e('Agent sync is already running. Please wait for it to complete.', 'propertyfinder'); ?>');
            return;
        }
        
        button.prop('disabled', true).html('<span class="dashicons dashicons-update" style="animation: spin 1s linear infinite;"></span> <?php _e('Syncing...', 'propertyfinder'); ?>');
        
        // Show progress message
        const progressMsg = $('<div id="sync-status" style="padding: 10px; background: #fff3cd; border-left: 4px solid #ffc107; margin-bottom: 20px; border-radius: 3px;"></div>');
        $('.propertyfinder-agents-section').prepend(progressMsg);
        progressMsg.html('<span class="dashicons dashicons-update" style="animation: spin 1s linear infinite;"></span> <strong><?php _e('Syncing agents from API... Please wait.', 'propertyfinder'); ?></strong>');
        
        // Start checking status
        if (!syncStatusInterval) {
            syncStatusInterval = setInterval(checkSyncStatus, 5000);
        }
        
        $.post(ajaxurl, {
            action: 'propertyfinder_sync_agents',
            nonce: '<?php echo wp_create_nonce('propertyfinder_admin_nonce'); ?>',
            page: 1,
            perPage: 50
        }, function(response) {
            button.prop('disabled', false).html(originalHtml);
            
            if (response.success) {
                const results = response.data.results;
                $('#sync-status').removeClass().addClass('sync-status-success').html(
                    '<span class="dashicons dashicons-yes-alt"></span> ' +
                    '<strong><?php _e('Sync completed!', 'propertyfinder'); ?></strong> ' +
                    '<?php _e('Imported:', 'propertyfinder'); ?> ' + (results.imported || 0) + ', ' +
                    '<?php _e('Updated:', 'propertyfinder'); ?> ' + (results.updated || 0) + ', ' +
                    '<?php _e('Skipped:', 'propertyfinder'); ?> ' + (results.skipped || 0)
                );
                
                // Show toast notification if available
                if (typeof PropertyFinderToast !== 'undefined') {
                    PropertyFinderToast.success(
                        '<?php _e('Sync completed!', 'propertyfinder'); ?> ' +
                        '<?php _e('Imported:', 'propertyfinder'); ?> ' + (results.imported || 0) + ' | ' +
                        '<?php _e('Updated:', 'propertyfinder'); ?> ' + (results.updated || 0) + ' | ' +
                        '<?php _e('Skipped:', 'propertyfinder'); ?> ' + (results.skipped || 0)
                    );
                }
                
                // Refresh last sync time
                checkSyncStatus();
                
                // Reload page after 2 seconds
                setTimeout(function() {
                    location.reload();
                }, 2000);
            } else {
                $('#sync-status').removeClass().addClass('sync-status-error').html(
                    '<span class="dashicons dashicons-warning"></span> ' +
                    '<strong><?php _e('Sync failed:', 'propertyfinder'); ?></strong> ' +
                    (response.data.message || '<?php _e('Unknown error', 'propertyfinder'); ?>')
                );
                
                // Show toast notification if available
                if (typeof PropertyFinderToast !== 'undefined') {
                    PropertyFinderToast.error(response.data.message || '<?php _e('Sync failed. Check logs for details.', 'propertyfinder'); ?>');
                }
            }
            
            if (syncStatusInterval) {
                clearInterval(syncStatusInterval);
                syncStatusInterval = null;
            }
        }).fail(function() {
            button.prop('disabled', false).html(originalHtml);
            $('#sync-status').removeClass().addClass('sync-status-error').html(
                '<span class="dashicons dashicons-warning"></span> ' +
                '<strong><?php _e('Request failed. Please try again.', 'propertyfinder'); ?></strong>'
            );
            
            // Show toast notification if available
            if (typeof PropertyFinderToast !== 'undefined') {
                PropertyFinderToast.error('<?php _e('Request failed. Please try again.', 'propertyfinder'); ?>');
            }
            
            if (syncStatusInterval) {
                clearInterval(syncStatusInterval);
                syncStatusInterval = null;
            }
        });
    });

    $('#import-agents').on('click', function() {
        if (!confirm('<?php _e('This will import all agents from the PropertyFinder API into WordPress. Existing agents will be updated. Continue?', 'propertyfinder'); ?>')) {
            return;
        }

        const button = $(this);
        const originalHtml = button.html();
        button.prop('disabled', true).html('<span class="dashicons dashicons-download" style="animation: spin 1s linear infinite;"></span> <?php _e('Importing...', 'propertyfinder'); ?>');
        
        // Create modern progress container
        const progressContainer = $('<div id="import-progress-container" class="propertyfinder-progress-container" style="margin: 20px 0; padding: 20px; background: #fff; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"></div>');
        $('.propertyfinder-agents-section').prepend(progressContainer);
        
        progressContainer.html(`
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                <h3 style="margin: 0; color: #2271b1; display: flex; align-items: center; gap: 10px;">
                    <span class="dashicons dashicons-download" style="animation: spin 1s linear infinite;"></span>
                    <?php _e('Importing Agents...', 'propertyfinder'); ?>
                </h3>
                <span id="import-progress-status" style="font-size: 12px; color: #666;"><?php _e('Initializing...', 'propertyfinder'); ?></span>
            </div>
            <div class="propertyfinder-progress-bar" style="width: 100%; height: 8px; background: #f0f0f0; border-radius: 4px; overflow: hidden; margin-bottom: 15px;">
                <div id="import-progress-fill" class="propertyfinder-progress-fill" style="height: 100%; background: linear-gradient(90deg, #2271b1, #135e96); width: 0%; transition: width 0.3s ease; border-radius: 4px;"></div>
            </div>
            <div id="import-progress-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-top: 15px;">
                <div class="stat-card" style="padding: 12px; background: #e7f5e7; border-left: 4px solid #00a32a; border-radius: 4px;">
                    <div style="font-size: 11px; color: #666; text-transform: uppercase; margin-bottom: 5px;"><?php _e('Imported', 'propertyfinder'); ?></div>
                    <div id="stat-imported" style="font-size: 24px; font-weight: bold; color: #00a32a;">0</div>
                </div>
                <div class="stat-card" style="padding: 12px; background: #e7f3ff; border-left: 4px solid #2271b1; border-radius: 4px;">
                    <div style="font-size: 11px; color: #666; text-transform: uppercase; margin-bottom: 5px;"><?php _e('Updated', 'propertyfinder'); ?></div>
                    <div id="stat-updated" style="font-size: 24px; font-weight: bold; color: #2271b1;">0</div>
                </div>
                <div class="stat-card" style="padding: 12px; background: #fff3cd; border-left: 4px solid #dba617; border-radius: 4px;">
                    <div style="font-size: 11px; color: #666; text-transform: uppercase; margin-bottom: 5px;"><?php _e('Skipped', 'propertyfinder'); ?></div>
                    <div id="stat-skipped" style="font-size: 24px; font-weight: bold; color: #dba617;">0</div>
                </div>
                <div class="stat-card" style="padding: 12px; background: #fcf0f1; border-left: 4px solid #d63638; border-radius: 4px;">
                    <div style="font-size: 11px; color: #666; text-transform: uppercase; margin-bottom: 5px;"><?php _e('Errors', 'propertyfinder'); ?></div>
                    <div id="stat-errors" style="font-size: 24px; font-weight: bold; color: #d63638;">0</div>
                </div>
            </div>
            <div id="import-progress-details" style="margin-top: 15px; padding: 10px; background: #f9f9f9; border-radius: 4px; font-size: 13px; color: #666; display: none;"></div>
        `);
        
        // Start checking progress
        if (!syncStatusInterval) {
            syncStatusInterval = setInterval(function() {
                checkImportProgress();
            }, 2000);
        }
        
        $.post(ajaxurl, {
            action: 'propertyfinder_import_agents',
            nonce: '<?php echo wp_create_nonce('propertyfinder_admin_nonce'); ?>',
            page: 1,
            perPage: 100
        }, function(response) {
            button.prop('disabled', false).html(originalHtml);
            
            if (syncStatusInterval) {
                clearInterval(syncStatusInterval);
                syncStatusInterval = null;
            }
            
            if (response.success) {
                const results = response.data.results;
                updateProgressStats(results, results.total || 100);
                $('#import-progress-status').html('<span style="color: #00a32a;">✓ <?php _e('Import Completed!', 'propertyfinder'); ?></span>');
                $('#import-progress-fill').css('width', '100%').css('background', 'linear-gradient(90deg, #00a32a, #008a00)');
                $('#import-progress-container').removeClass().addClass('propertyfinder-progress-container').css('border-color', '#00a32a');
                
                // Show success message
                setTimeout(function() {
                    const successMsg = '<?php _e('Import completed!', 'propertyfinder'); ?>\n\n' +
                        '<?php _e('Imported:', 'propertyfinder'); ?> ' + (results.imported || 0) + '\n' +
                        '<?php _e('Updated:', 'propertyfinder'); ?> ' + (results.updated || 0) + '\n' +
                        '<?php _e('Skipped:', 'propertyfinder'); ?> ' + (results.skipped || 0) + '\n' +
                        '<?php _e('Errors:', 'propertyfinder'); ?> ' + (results.errors || 0);
                    
                    // Use toast notification if available, otherwise use alert
                    if (typeof PropertyFinderToast !== 'undefined') {
                        PropertyFinderToast.success(
                            '<?php _e('Import completed!', 'propertyfinder'); ?> ' +
                            '<?php _e('Imported:', 'propertyfinder'); ?> ' + (results.imported || 0) + ' | ' +
                            '<?php _e('Updated:', 'propertyfinder'); ?> ' + (results.updated || 0) + ' | ' +
                            '<?php _e('Skipped:', 'propertyfinder'); ?> ' + (results.skipped || 0) + ' | ' +
                            '<?php _e('Errors:', 'propertyfinder'); ?> ' + (results.errors || 0)
                        );
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        alert(successMsg);
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    }
                }, 500);
            } else {
                $('#import-progress-status').html('<span style="color: #d63638;">✗ <?php _e('Import Failed', 'propertyfinder'); ?></span>');
                $('#import-progress-container').css('border-color', '#d63638');
                
                // Use toast notification if available, otherwise use alert
                if (typeof PropertyFinderToast !== 'undefined') {
                    PropertyFinderToast.error(response.data.message || '<?php _e('Import failed. Check logs for details.', 'propertyfinder'); ?>');
                } else {
                    alert(response.data.message || '<?php _e('Import failed. Check logs for details.', 'propertyfinder'); ?>');
                }
            }
        }).fail(function() {
            button.prop('disabled', false).html(originalHtml);
            if (syncStatusInterval) {
                clearInterval(syncStatusInterval);
                syncStatusInterval = null;
            }
            $('#import-progress-container').remove();
            
            // Use toast notification if available, otherwise use alert
            if (typeof PropertyFinderToast !== 'undefined') {
                PropertyFinderToast.error('<?php _e('Request failed. Please try again.', 'propertyfinder'); ?>');
            } else {
                alert('<?php _e('Request failed. Please try again.', 'propertyfinder'); ?>');
            }
        });
        
        function checkImportProgress() {
            // Check if import is still running
            const isRunning = $('#import-progress-container').length > 0;
            if (!isRunning) {
                if (syncStatusInterval) {
                    clearInterval(syncStatusInterval);
                    syncStatusInterval = null;
                }
                return;
            }
            
            // Update progress status (simulated progress)
            const currentWidth = parseInt($('#import-progress-fill').css('width')) || 0;
            const containerWidth = $('#import-progress-container').width() || 100;
            const percentage = Math.min((currentWidth / containerWidth) * 100 + 5, 95);
            $('#import-progress-fill').css('width', percentage + '%');
        }
        
        function updateProgressStats(results, total) {
            $('#stat-imported').text(results.imported || 0);
            $('#stat-updated').text(results.updated || 0);
            $('#stat-skipped').text(results.skipped || 0);
            $('#stat-errors').text(results.errors || 0);
        }
    });
});
</script>

