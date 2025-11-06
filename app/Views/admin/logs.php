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

// Get API status
$api_key = get_option('propertyfinder_api_key', '');
$api_secret = get_option('propertyfinder_api_secret', '');
$api_endpoint = get_option('propertyfinder_api_endpoint', 'https://atlas.propertyfinder.com/v1');
?>
<div class="wrap propertyfinder-modern propertyfinder-logs-page">
    <h1><?php echo esc_html($page_title); ?></h1>
    
    <div class="propertyfinder-logs-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
        <div>
            <p class="description" style="margin: 0;">
                <?php _e('Log directory:', 'propertyfinder'); ?> 
                <code><?php echo esc_html($log_dir); ?></code>
            </p>
        </div>
        <div class="propertyfinder-logs-actions" style="display: flex; gap: 10px;">
            <button type="button" class="button button-secondary" id="test-connection-from-logs">
                <?php _e('Test Connection', 'propertyfinder'); ?>
            </button>
            <?php if (!empty($selected_log)): ?>
                <button type="button" class="button button-secondary clear-log-btn" data-filename="<?php echo esc_attr($selected_log); ?>">
                    <?php _e('Clear Current Log', 'propertyfinder'); ?>
                </button>
            <?php endif; ?>
            <?php if (!empty($log_files)): ?>
                <button type="button" class="button button-link-delete delete-all-logs-btn">
                    <?php _e('Delete All Logs', 'propertyfinder'); ?>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="propertyfinder-logs-grid" style="display: grid; grid-template-columns: 20% 80%; gap: 20px; margin-top: 20px;">
        <!-- Log Files List -->
        <div class="propertyfinder-logs-sidebar" style="background: #fff; padding: 20px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05); border-radius: 4px; min-width: 0; overflow: hidden;">
            <h2 style="margin-top: 0;"><?php _e('Log Files', 'propertyfinder'); ?></h2>
            
            <!-- Module Filter -->
            <?php if (!empty($modules)): ?>
                <div style="margin-bottom: 15px;">
                    <label for="module-filter" style="display: block; margin-bottom: 5px; font-weight: 500;">
                        <?php _e('Filter by Module:', 'propertyfinder'); ?>
                    </label>
                    <select id="module-filter" class="regular-text" style="width: 100%;">
                        <option value=""><?php _e('All Modules', 'propertyfinder'); ?></option>
                        <?php foreach ($modules as $module_key => $module_name): ?>
                            <option value="<?php echo esc_attr($module_key); ?>" <?php selected($selected_module, $module_key); ?>>
                                <?php echo esc_html($module_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($log_files)): ?>
                <div class="propertyfinder-log-files-list" style="max-height: 600px; overflow-y: auto;">
                    <?php 
                    $current_module = null;
                    foreach ($log_files as $file): 
                        $file_module = $file['module'] ?? 'general';
                        if ($current_module !== $file_module && !$selected_module):
                            $current_module = $file_module;
                            $module_name = isset($modules[$file_module]) ? $modules[$file_module] : ucfirst($file_module);
                    ?>
                        <div style="padding: 8px 5px 5px; margin-top: 10px; margin-bottom: 5px; border-bottom: 2px solid #2271b1; font-weight: 600; color: #2271b1; text-transform: uppercase; font-size: 11px;">
                            <?php echo esc_html($module_name); ?>
                        </div>
                    <?php endif; ?>
                        <div class="propertyfinder-log-file-item" 
                             style="padding: 10px; margin-bottom: 5px; border: 1px solid #ddd; border-radius: 3px; cursor: pointer; transition: all 0.2s; <?php echo ($selected_log === $file['filename']) ? 'background: #f0f6fc; border-color: #2271b1;' : ''; ?>"
                             data-filename="<?php echo esc_attr($file['filename']); ?>">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 5px;">
                                <div style="flex: 1;">
                                    <strong style="display: block; margin-bottom: 3px;">
                                        <?php echo esc_html($file['log_date'] ?? $file['date']); ?>
                                        <?php if ($file_module !== 'general' && !$selected_module): ?>
                                            <span style="font-size: 10px; color: #666; font-weight: normal;">(<?php echo esc_html($file_module); ?>)</span>
                                        <?php endif; ?>
                                    </strong>
                                    <small style="color: #666; display: block;">
                                        <?php echo esc_html($file['size_formatted']); ?>
                                    </small>
                                </div>
                                <div class="log-file-actions" style="display: flex; gap: 5px;">
                                    <button type="button" 
                                            class="button button-small clear-log-btn" 
                                            data-filename="<?php echo esc_attr($file['filename']); ?>"
                                            title="<?php _e('Clear', 'propertyfinder'); ?>"
                                            style="padding: 2px 8px; min-height: auto;">
                                        <span class="dashicons dashicons-edit" style="font-size: 16px; width: 16px; height: 16px;"></span>
                                    </button>
                                    <button type="button" 
                                            class="button button-small delete-log-btn" 
                                            data-filename="<?php echo esc_attr($file['filename']); ?>"
                                            title="<?php _e('Delete', 'propertyfinder'); ?>"
                                            style="padding: 2px 8px; min-height: auto; color: #b32d2e;">
                                        <span class="dashicons dashicons-trash" style="font-size: 16px; width: 16px; height: 16px;"></span>
                                    </button>
                                </div>
                            </div>
                            <small style="color: #999; font-size: 11px;">
                                <?php echo esc_html($file['modified_formatted']); ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color: #666;"><?php _e('No log files found.', 'propertyfinder'); ?></p>
            <?php endif; ?>
        </div>

        <!-- Log Content -->
        <div class="propertyfinder-logs-content" style="background: #fff; padding: 20px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05); border-radius: 4px; min-width: 0; overflow: hidden;">
            <h2 style="margin-top: 0; word-wrap: break-word; overflow-wrap: break-word;">
                <?php if ($selected_log): ?>
                    <?php _e('Log Content:', 'propertyfinder'); ?> 
                    <code style="word-break: break-all; white-space: normal;"><?php echo esc_html($selected_log); ?></code>
                <?php else: ?>
                    <?php _e('Select a log file to view', 'propertyfinder'); ?>
                <?php endif; ?>
            </h2>
            
            <?php if (!empty($log_content)): ?>
                <div style="position: relative; width: 100%; overflow: hidden;">
                    <pre id="log-content" style="background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 4px; overflow-x: auto; overflow-y: auto; max-height: 600px; font-size: 12px; line-height: 1.5; margin: 0; width: 100%; box-sizing: border-box; word-wrap: break-word; white-space: pre-wrap; word-break: break-all;"><?php echo esc_html($log_content); ?></pre>
                </div>
            <?php else: ?>
                <p style="color: #666;"><?php _e('No log content available. Logs will appear here when sync operations occur.', 'propertyfinder'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Module filter change
    $('#module-filter').on('change', function() {
        var module = $(this).val();
        var url = new URL(window.location.href);
        if (module) {
            url.searchParams.set('module', module);
        } else {
            url.searchParams.delete('module');
        }
        url.searchParams.delete('log_file'); // Reset selected log
        window.location.href = url.toString();
    });

    // Load log file on click
    $('.propertyfinder-log-file-item').on('click', function(e) {
        if ($(e.target).closest('.log-file-actions').length) {
            return; // Don't load if clicking action buttons
        }
        
        var filename = $(this).data('filename');
        var url = new URL(window.location.href);
        url.searchParams.set('log_file', filename);
        // Preserve module filter
        var module = $('#module-filter').val();
        if (module) {
            url.searchParams.set('module', module);
        }
        window.location.href = url.toString();
    });

    // Clear log file
    $('.clear-log-btn').on('click', function(e) {
        e.stopPropagation();
        var button = $(this);
        var filename = button.data('filename');
        var originalText = button.html();
        
        if (!confirm('<?php _e('Are you sure you want to clear this log file?', 'propertyfinder'); ?>')) {
            return;
        }
        
        button.prop('disabled', true).html('<span class="dashicons dashicons-update" style="animation: spin 1s linear infinite;"></span>');
        
        $.ajax({
            url: propertyfinderAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'propertyfinder_clear_log',
                nonce: propertyfinderAdmin.nonce,
                filename: filename
            },
            success: function(response) {
                if (response.success) {
                    if (typeof PropertyFinderToast !== 'undefined') {
                        PropertyFinderToast.success(response.data.message || '<?php _e('Log cleared successfully.', 'propertyfinder'); ?>');
                    } else {
                        alert(response.data.message || '<?php _e('Log cleared successfully.', 'propertyfinder'); ?>');
                    }
                    location.reload();
                } else {
                    if (typeof PropertyFinderToast !== 'undefined') {
                        PropertyFinderToast.error(response.data.message || '<?php _e('Failed to clear log.', 'propertyfinder'); ?>');
                    } else {
                        alert(response.data.message || '<?php _e('Failed to clear log.', 'propertyfinder'); ?>');
                    }
                    button.prop('disabled', false).html(originalText);
                }
            },
            error: function() {
                alert('<?php _e('Error occurred while clearing log.', 'propertyfinder'); ?>');
                button.prop('disabled', false).html(originalText);
            }
        });
    });

    // Delete log file
    $('.delete-log-btn').on('click', function(e) {
        e.stopPropagation();
        var button = $(this);
        var filename = button.data('filename');
        
        if (!confirm('<?php _e('Are you sure you want to delete this log file? This action cannot be undone.', 'propertyfinder'); ?>')) {
            return;
        }
        
        button.prop('disabled', true);
        
        $.ajax({
            url: propertyfinderAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'propertyfinder_delete_log',
                nonce: propertyfinderAdmin.nonce,
                filename: filename
            },
            success: function(response) {
                if (response.success) {
                    if (typeof PropertyFinderToast !== 'undefined') {
                        PropertyFinderToast.success(response.data.message || '<?php _e('Log file deleted successfully.', 'propertyfinder'); ?>');
                    } else {
                        alert(response.data.message || '<?php _e('Log file deleted successfully.', 'propertyfinder'); ?>');
                    }
                    location.reload();
                } else {
                    if (typeof PropertyFinderToast !== 'undefined') {
                        PropertyFinderToast.error(response.data.message || '<?php _e('Failed to delete log file.', 'propertyfinder'); ?>');
                    } else {
                        alert(response.data.message || '<?php _e('Failed to delete log file.', 'propertyfinder'); ?>');
                    }
                    button.prop('disabled', false);
                }
            },
            error: function() {
                alert('<?php _e('Error occurred while deleting log file.', 'propertyfinder'); ?>');
                button.prop('disabled', false);
            }
        });
    });

    // Delete all logs
    $('.delete-all-logs-btn').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm('<?php _e('Are you sure you want to delete ALL log files? This action cannot be undone.', 'propertyfinder'); ?>')) {
            return;
        }
        
        if (!confirm('<?php _e('This will permanently delete all log files. Are you absolutely sure?', 'propertyfinder'); ?>')) {
            return;
        }
        
        var button = $(this);
        button.prop('disabled', true).text('<?php _e('Deleting...', 'propertyfinder'); ?>');
        
        $.ajax({
            url: propertyfinderAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'propertyfinder_delete_all_logs',
                nonce: propertyfinderAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    if (typeof PropertyFinderToast !== 'undefined') {
                        PropertyFinderToast.success(response.data.message || '<?php _e('All log files deleted successfully.', 'propertyfinder'); ?>');
                    } else {
                        alert(response.data.message || '<?php _e('All log files deleted successfully.', 'propertyfinder'); ?>');
                    }
                    location.reload();
                } else {
                    if (typeof PropertyFinderToast !== 'undefined') {
                        PropertyFinderToast.error(response.data.message || '<?php _e('Failed to delete log files.', 'propertyfinder'); ?>');
                    } else {
                        alert(response.data.message || '<?php _e('Failed to delete log files.', 'propertyfinder'); ?>');
                    }
                    button.prop('disabled', false).text('<?php _e('Delete All Logs', 'propertyfinder'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('Error occurred while deleting log files.', 'propertyfinder'); ?>');
                button.prop('disabled', false).text('<?php _e('Delete All Logs', 'propertyfinder'); ?>');
            }
        });
    });

    // Test connection
    $('#test-connection-from-logs').on('click', function() {
        var button = $(this);
        button.prop('disabled', true).text('<?php _e('Testing...', 'propertyfinder'); ?>');
        
        $.ajax({
            url: propertyfinderAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'propertyfinder_test_connection',
                nonce: propertyfinderAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    if (typeof PropertyFinderToast !== 'undefined') {
                        PropertyFinderToast.success('<?php _e('Connection successful! Check logs for details.', 'propertyfinder'); ?>');
                    } else {
                        alert('<?php _e('Connection successful! Check logs for details.', 'propertyfinder'); ?>');
                    }
                } else {
                    if (typeof PropertyFinderToast !== 'undefined') {
                        PropertyFinderToast.error(response.data.message || '<?php _e('Connection failed.', 'propertyfinder'); ?>');
                    } else {
                        alert('<?php _e('Connection failed: ', 'propertyfinder'); ?>' + response.data.message);
                    }
                }
                location.reload();
            },
            error: function() {
                alert('<?php _e('Error occurred during test.', 'propertyfinder'); ?>');
                button.prop('disabled', false).text('<?php _e('Test Connection', 'propertyfinder'); ?>');
            }
        });
    });
});
</script>

<style>
.propertyfinder-log-file-item:hover {
    background: #f0f6fc !important;
    border-color: #2271b1 !important;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Responsive grid layout */
.propertyfinder-logs-grid {
    display: grid;
    grid-template-columns: 20% 80%;
    gap: 20px;
}

/* Responsive breakpoints */
@media (max-width: 1200px) {
    .propertyfinder-logs-grid {
        grid-template-columns: 25% 75%;
    }
}

@media (max-width: 782px) {
    .propertyfinder-logs-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .propertyfinder-logs-sidebar {
        order: 2;
    }
    
    .propertyfinder-logs-content {
        order: 1;
    }
}

/* Prevent overflow */
.propertyfinder-logs-sidebar,
.propertyfinder-logs-content {
    min-width: 0;
    overflow: hidden;
}

.propertyfinder-logs-sidebar h2,
.propertyfinder-logs-content h2 {
    word-wrap: break-word;
    overflow-wrap: break-word;
    hyphens: auto;
}

.propertyfinder-log-files-list {
    max-height: 600px;
    overflow-y: auto;
    overflow-x: hidden;
}

/* Ensure log content doesn't overflow */
#log-content {
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    word-wrap: break-word;
    white-space: pre-wrap;
    word-break: break-all;
    overflow-wrap: break-word;
}

/* Fix code overflow */
code {
    word-break: break-all;
    white-space: normal;
    overflow-wrap: break-word;
}
</style>
