/**
 * Admin Settings Page JavaScript
 *
 * @package PropertyFinder
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Sync interval handler
        $('#propertyfinder_sync_interval').on('change', function() {
            const interval = $(this).val();
            const timeRow = $('#sync-time-row');
            const timeInput = $('#propertyfinder_sync_time');
            
            if (interval === 'daily' || interval === 'weekly' || interval === 'daily_12am') {
                timeRow.slideDown(200);
                if (interval === 'daily_12am') {
                    timeInput.val('00:00').prop('disabled', true);
                } else {
                    timeInput.prop('disabled', false);
                }
            } else {
                timeRow.slideUp(200);
            }
        });
        
        // Trigger on page load
        $('#propertyfinder_sync_interval').trigger('change');
        
        // Toggle password visibility
        $('.propertyfinder-toggle-password').on('click', function() {
            const button = $(this);
            const input = button.siblings('input[type="password"], input[type="text"]');
            const icon = button.find('.dashicons');
            
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('dashicons-visibility').addClass('dashicons-hidden');
            } else {
                input.attr('type', 'password');
                icon.removeClass('dashicons-hidden').addClass('dashicons-visibility');
            }
        });
        
        // Test connection button
        $('#propertyfinder-test-connection').on('click', function(e) {
            e.preventDefault();
            
            const button = $(this);
            const statusDiv = $('.connection-status');
            const spinner = button.siblings('.spinner');
            
            button.prop('disabled', true).addClass('is-loading');
            statusDiv.html('');
            spinner.addClass('is-active');
            
            $.ajax({
                url: propertyfinderAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'propertyfinder_test_connection',
                    nonce: propertyfinderAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        PropertyFinderToast.success(response.data.message || 'Connection successful!');
                        statusDiv.html(
                            '<div class="propertyfinder-status-message propertyfinder-status-success">' +
                            '<span class="dashicons dashicons-yes-alt"></span>' +
                            '<strong>Success:</strong> ' + response.data.message +
                            '</div>'
                        );
                    } else {
                        PropertyFinderToast.error(response.data.message || 'Connection failed!');
                        statusDiv.html(
                            '<div class="propertyfinder-status-message propertyfinder-status-error">' +
                            '<span class="dashicons dashicons-dismiss"></span>' +
                            '<strong>Error:</strong> ' + response.data.message +
                            '</div>'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    PropertyFinderToast.error('An error occurred. Please try again.');
                    statusDiv.html(
                        '<div class="propertyfinder-status-message propertyfinder-status-error">' +
                        '<span class="dashicons dashicons-dismiss"></span>' +
                        '<strong>Error:</strong> An error occurred. Please try again.' +
                        '</div>'
                    );
                },
                complete: function() {
                    button.prop('disabled', false).removeClass('is-loading');
                    spinner.removeClass('is-active');
                }
            });
        });
        
        // Sync now button
        $('#propertyfinder-sync-now').on('click', function(e) {
            e.preventDefault();
            
            const button = $(this);
            const statusDiv = $('.sync-status');
            const progressContainer = $('#import-progress-container');
            const originalHtml = button.html();
            
            PropertyFinderConfirm.show('Are you sure you want to import listings now? This may take a few minutes.', function(confirmed) {
                if (!confirmed) return;
                
                button.prop('disabled', true).addClass('is-loading')
                    .html('<span class="dashicons dashicons-update"></span> Importing...');
                
                // Show and reset progress container
                progressContainer.slideDown(300);
                $('#import-progress-fill').css('width', '0%').css('background', 'linear-gradient(90deg, #2271b1, #135e96)');
                $('#import-progress-status').text('Starting import...');
                $('#imported-count, #total-count, #remaining-count').text('0');
                $('#stat-imported, #stat-updated, #stat-skipped, #stat-errors').text('0');
                progressContainer.removeClass('success error');
                
                // Update progress to show we're starting
                setTimeout(function() {
                    $('#import-progress-fill').css('width', '10%');
                    $('#import-progress-status').text('Connecting to API...');
                }, 100);
                
                $.ajax({
                    url: propertyfinderAdmin.ajaxUrl,
                    type: 'POST',
                    timeout: 300000, // 5 minutes timeout
                    data: {
                        action: 'propertyfinder_sync',
                        nonce: propertyfinderAdmin.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            const results = response.data.results || {};
                            const imported = results.imported || 0;
                            const updated = results.updated || 0;
                            const skipped = results.skipped || 0;
                            const errors = results.errors || 0;
                            const total = results.total || (imported + updated + skipped + errors);
                            const totalProcessed = imported + updated + skipped + errors;
                            
                            // Update progress bar to 100%
                            $('#import-progress-fill').css('width', '100%')
                                .css('background', 'linear-gradient(90deg, #00a32a, #008a00)');
                            $('#import-progress-status').html('<span style="color: #00a32a;">✓ Import Completed!</span>');
                            progressContainer.addClass('success');
                            
                            // Update stats
                            $('#imported-count').text(totalProcessed);
                            $('#total-count').text(total);
                            $('#remaining-count').text('0');
                            $('#stat-imported').text(imported);
                            $('#stat-updated').text(updated);
                            $('#stat-skipped').text(skipped);
                            $('#stat-errors').text(errors);
                            
                            PropertyFinderToast.success(response.data.message || 'Import completed successfully!');
                            statusDiv.html(
                                '<div class="propertyfinder-status-message propertyfinder-status-success">' +
                                '<span class="dashicons dashicons-yes-alt"></span>' +
                                '<strong>Import Completed!</strong> ' + response.data.message +
                                '</div>'
                            );
                            
                            // Reload page after 3 seconds
                            setTimeout(function() {
                                location.reload();
                            }, 3000);
                        } else {
                            $('#import-progress-status').html('<span style="color: #d63638;">✗ Import Failed</span>');
                            progressContainer.addClass('error');
                            PropertyFinderToast.error('Import failed: ' + (response.data.message || 'Unknown error'));
                            statusDiv.html(
                                '<div class="propertyfinder-status-message propertyfinder-status-error">' +
                                '<span class="dashicons dashicons-dismiss"></span>' +
                                '<strong>Import Failed:</strong> ' + response.data.message +
                                '</div>'
                            );
                            button.prop('disabled', false).removeClass('is-loading').html(originalHtml);
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#import-progress-status').html('<span style="color: #d63638;">✗ Error Occurred</span>');
                        progressContainer.addClass('error');
                        PropertyFinderToast.error('An error occurred during import.');
                        statusDiv.html(
                            '<div class="propertyfinder-status-message propertyfinder-status-error">' +
                            '<span class="dashicons dashicons-dismiss"></span>' +
                            '<strong>Error:</strong> An error occurred during import.' +
                            '</div>'
                        );
                        button.prop('disabled', false).removeClass('is-loading').html(originalHtml);
                    }
                });
            });
        });
        
        // Sync All Listings
        $('#propertyfinder-sync-all').on('click', function(e) {
            e.preventDefault();
            
            const button = $(this);
            const progressContainer = $('#import-progress-container');
            const originalHtml = button.html();
            
            PropertyFinderConfirm.show('This will import ALL listings from PropertyFinder (all pages). This may take several minutes. Continue?', function(confirmed) {
                if (!confirmed) return;
                
                button.prop('disabled', true).addClass('is-loading')
                    .html('<span class="dashicons dashicons-download"></span> Importing All Pages...');
                progressContainer.slideDown(300);
                
                // Reset progress
                $('#import-progress-fill').css('width', '0%')
                    .css('background', 'linear-gradient(90deg, #2271b1, #135e96)');
                $('#import-progress-status').text('Starting full import...');
                $('#imported-count, #total-count, #remaining-count').text('0');
                $('#stat-imported, #stat-updated, #stat-skipped, #stat-errors').text('0');
                progressContainer.removeClass('success error');
                
                let totalImported = 0;
                let totalUpdated = 0;
                let totalSkipped = 0;
                let totalErrors = 0;
                let currentPage = 1;
                let isImporting = true;
                
                // Function to update progress
                function updateProgress(results) {
                    if (results) {
                        totalImported += results.imported || 0;
                        totalUpdated += results.updated || 0;
                        totalSkipped += results.skipped || 0;
                        totalErrors += results.errors || 0;
                        
                        const totalProcessed = totalImported + totalUpdated + totalSkipped + totalErrors;
                        const estimatedTotal = results.total || (totalProcessed * 2);
                        const remaining = Math.max(0, estimatedTotal - totalProcessed);
                        const percentage = estimatedTotal > 0 ? Math.min((totalProcessed / estimatedTotal) * 100, 95) : 50;
                        
                        $('#imported-count').text(totalProcessed);
                        $('#total-count').text(estimatedTotal);
                        $('#remaining-count').text(remaining);
                        $('#stat-imported').text(totalImported);
                        $('#stat-updated').text(totalUpdated);
                        $('#stat-skipped').text(totalSkipped);
                        $('#stat-errors').text(totalErrors);
                        
                        $('#import-progress-fill').css('width', percentage + '%');
                        $('#import-progress-status').text('Processing page ' + currentPage + ' (' + totalProcessed + ' processed)');
                    }
                }
                
                // Function to import next page
                function importNextPage() {
                    if (!isImporting) return;
                    
                    $.ajax({
                        url: propertyfinderAdmin.ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'propertyfinder_import',
                            nonce: propertyfinderAdmin.nonce,
                            status: $('#import-status').val() || 'published',
                            page: currentPage,
                            perPage: 50
                        },
                        success: function(response) {
                            if (response.success) {
                                const results = response.data.results || response.data;
                                updateProgress(results);
                                
                                // Check if there are more pages to import
                                if ((results.imported > 0 || results.updated > 0) && results.total > 0) {
                                    const totalProcessed = totalImported + totalUpdated + totalSkipped + totalErrors;
                                    if (totalProcessed < results.total) {
                                        currentPage++;
                                        setTimeout(importNextPage, 1000);
                                    } else {
                                        completeImport();
                                    }
                                } else {
                                    completeImport();
                                }
                            } else {
                                completeImport(true);
                            }
                        },
                        error: function() {
                            completeImport(true);
                        }
                    });
                }
                
                // Function to complete import
                function completeImport(isError) {
                    isImporting = false;
                    
                    if (isError) {
                        $('#import-progress-status').html('<span style="color: #d63638;">✗ Import Failed</span>');
                        progressContainer.addClass('error');
                        PropertyFinderToast.error('Import failed. Some pages may have been imported.');
                    } else {
                        $('#import-progress-status').html('<span style="color: #00a32a;">✓ Full Import Completed!</span>');
                        $('#import-progress-fill').css('width', '100%')
                            .css('background', 'linear-gradient(90deg, #00a32a, #008a00)');
                        progressContainer.addClass('success');
                        
                        const message = 'Full import completed! Imported: ' + totalImported + ', Updated: ' + totalUpdated;
                        PropertyFinderToast.success(message);
                        
                        setTimeout(function() {
                            location.reload();
                        }, 3000);
                    }
                    
                    button.prop('disabled', false).removeClass('is-loading').html(originalHtml);
                }
                
                // Start importing
                importNextPage();
            });
        });
        
        // Webhooks functionality
        // Copy to clipboard
        window.PropertyFinderSettings = {
            copyToClipboard: function(elementId) {
                const element = document.getElementById(elementId);
                const text = element.textContent || element.innerText;
                
                navigator.clipboard.writeText(text).then(function() {
                    PropertyFinderToast.success('Copied to clipboard!');
                }, function(err) {
                    console.error('Failed to copy: ', err);
                    PropertyFinderToast.error('Failed to copy to clipboard.');
                });
            }
        };
        
        // Subscribe webhook
        $('#subscribe-webhook').on('click', function(e) {
            e.preventDefault();
            
            const button = $(this);
            const eventId = $('#webhook-event-id').val();
            const callbackUrl = $('#webhook-callback-url').val();
            const secret = $('#webhook-secret-input').val();
            
            if (!eventId || !callbackUrl) {
                PropertyFinderToast.warning('Please fill in all required fields (Event Type and Callback URL).');
                return;
            }
            
            // Validate URL format
            try {
                new URL(callbackUrl);
            } catch (e) {
                PropertyFinderToast.error('Please enter a valid URL for the callback.');
                return;
            }
            
            const originalHtml = button.html();
            button.prop('disabled', true).addClass('is-loading')
                .html('<span class="dashicons dashicons-update" style="animation: spin 1s linear infinite;"></span> Subscribing...');
            
            $.ajax({
                url: propertyfinderAdmin.ajaxUrl,
                type: 'POST',
                timeout: 30000,
                data: {
                    action: 'propertyfinder_subscribe_webhook',
                    nonce: propertyfinderAdmin.nonce,
                    event_id: eventId,
                    callback_url: callbackUrl,
                    secret: secret || ''
                },
                success: function(response) {
                    if (response.success) {
                        PropertyFinderToast.success(response.data.message || 'Webhook subscribed successfully!');
                        $('#webhook-secret-input').val('');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        const errorMsg = response.data && response.data.message ? response.data.message : 'Failed to subscribe webhook. Please check your API credentials and try again.';
                        PropertyFinderToast.error(errorMsg);
                        button.prop('disabled', false).removeClass('is-loading').html(originalHtml);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Webhook subscription error:', xhr, status, error);
                    let errorMsg = 'An error occurred. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                        errorMsg = xhr.responseJSON.data.message;
                    } else if (xhr.responseText) {
                        try {
                            const errorData = JSON.parse(xhr.responseText);
                            if (errorData.data && errorData.data.message) {
                                errorMsg = errorData.data.message;
                            }
                        } catch (e) {
                            // Keep default message
                        }
                    }
                    PropertyFinderToast.error(errorMsg);
                    button.prop('disabled', false).removeClass('is-loading').html(originalHtml);
                }
            });
        });
        
        // Unsubscribe webhook
        $(document).on('click', '.unsubscribe-webhook', function() {
            const button = $(this);
            const eventId = button.data('event-id');
            
            PropertyFinderConfirm.show('Are you sure you want to unsubscribe from this webhook?', function(confirmed) {
                if (!confirmed) return;
                
                button.prop('disabled', true).addClass('is-loading').text('Unsubscribing...');
                
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
                            PropertyFinderToast.success('Webhook unsubscribed successfully!');
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            PropertyFinderToast.error(response.data.message || 'Failed to unsubscribe webhook.');
                            button.prop('disabled', false).removeClass('is-loading').text('Unsubscribe');
                        }
                    },
                    error: function() {
                        PropertyFinderToast.error('An error occurred. Please try again.');
                        button.prop('disabled', false).removeClass('is-loading').text('Unsubscribe');
                    }
                });
            });
        });
        
        // Refresh webhooks
        $('#refresh-webhooks').on('click', function() {
            const button = $(this);
            button.prop('disabled', true).addClass('is-loading');
            
            $.ajax({
                url: propertyfinderAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'propertyfinder_refresh_webhooks',
                    nonce: propertyfinderAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        PropertyFinderToast.success('Webhooks refreshed successfully!');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        PropertyFinderToast.error(response.data.message || 'Failed to refresh webhooks.');
                        button.prop('disabled', false).removeClass('is-loading');
                    }
                },
                error: function() {
                    PropertyFinderToast.error('An error occurred. Please try again.');
                    button.prop('disabled', false).removeClass('is-loading');
                }
            });
        });
    });
    
})(jQuery);
