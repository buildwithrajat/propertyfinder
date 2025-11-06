/**
 * Admin area JavaScript
 *
 * @package PropertyFinder
 */

(function($) {
    'use strict';

    // WordPress Confirmation Dialog
    window.PropertyFinderConfirm = {
        /**
         * Show WordPress-style confirmation dialog
         * @param {string} message - Message to display
         * @param {function} callback - Callback function (receives true/false)
         */
        show: function(message, callback) {
            // Use WordPress's built-in confirm but style it better
            if (typeof wp !== 'undefined' && wp.data && wp.data.select('core/editor')) {
                // If in block editor, use WordPress confirm
                if (confirm(message)) {
                    callback(true);
                } else {
                    callback(false);
                }
            } else {
                // Create custom modal for better UX
                var modal = $('<div>')
                    .addClass('propertyfinder-confirm-modal')
                    .html(
                        '<div class="propertyfinder-confirm-overlay"></div>' +
                        '<div class="propertyfinder-confirm-dialog">' +
                        '<div class="propertyfinder-confirm-header">' +
                        '<span class="dashicons dashicons-warning"></span>' +
                        '<h3>' + this.escapeHtml('Confirm Action') + '</h3>' +
                        '</div>' +
                        '<div class="propertyfinder-confirm-body">' +
                        '<p>' + this.escapeHtml(message) + '</p>' +
                        '</div>' +
                        '<div class="propertyfinder-confirm-footer">' +
                        '<button type="button" class="button button-primary propertyfinder-confirm-yes">' + this.escapeHtml('Yes') + '</button> ' +
                        '<button type="button" class="button propertyfinder-confirm-no">' + this.escapeHtml('Cancel') + '</button>' +
                        '</div>' +
                        '</div>'
                    );

                $('body').append(modal);
                modal.fadeIn(200);

                // Handle yes button
                modal.find('.propertyfinder-confirm-yes').on('click', function() {
                    modal.fadeOut(200, function() {
                        $(this).remove();
                    });
                    callback(true);
                });

                // Handle no button
                modal.find('.propertyfinder-confirm-no, .propertyfinder-confirm-overlay').on('click', function() {
                    modal.fadeOut(200, function() {
                        $(this).remove();
                    });
                    callback(false);
                });
            }
        },
        escapeHtml: function(text) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    };

    // Toast Notification System
    window.PropertyFinderToast = {
        /**
         * Show toast notification
         * @param {string} message - Message to display
         * @param {string} type - Type: success, error, warning, info
         * @param {number} duration - Auto-dismiss duration in ms (0 = no auto-dismiss)
         */
        show: function(message, type, duration) {
            type = type || 'info';
            duration = duration || 4000;

            // Create container if it doesn't exist
            if (!$('#propertyfinder-toast-container').length) {
                $('body').append('<div id="propertyfinder-toast-container" class="propertyfinder-toast-container"></div>');
            }

            // Icon mapping
            var icons = {
                success: '<span class="dashicons dashicons-yes-alt"></span>',
                error: '<span class="dashicons dashicons-dismiss"></span>',
                warning: '<span class="dashicons dashicons-warning"></span>',
                info: '<span class="dashicons dashicons-info"></span>'
            };

            // Create toast element
            var toast = $('<div>')
                .addClass('propertyfinder-toast ' + type)
                .html(
                    '<span class="propertyfinder-toast-icon">' + (icons[type] || icons.info) + '</span>' +
                    '<span class="propertyfinder-toast-content">' + this.escapeHtml(message) + '</span>' +
                    '<button type="button" class="propertyfinder-toast-close" aria-label="Close">×</button>'
                );

            // Append to container
            $('#propertyfinder-toast-container').append(toast);

            // Auto-dismiss
            if (duration > 0) {
                setTimeout(function() {
                    PropertyFinderToast.remove(toast);
                }, duration);
            }

            // Close button handler
            toast.find('.propertyfinder-toast-close').on('click', function() {
                PropertyFinderToast.remove(toast);
            });

            return toast;
        },

        /**
         * Remove toast
         */
        remove: function(toast) {
            toast.addClass('fade-out');
            setTimeout(function() {
                toast.remove();
                // Remove container if empty
                if ($('#propertyfinder-toast-container').children().length === 0) {
                    $('#propertyfinder-toast-container').remove();
                }
            }, 300);
        },

        /**
         * Success toast
         */
        success: function(message, duration) {
            return this.show(message, 'success', duration);
        },

        /**
         * Error toast
         */
        error: function(message, duration) {
            return this.show(message, 'error', duration);
        },

        /**
         * Warning toast
         */
        warning: function(message, duration) {
            return this.show(message, 'warning', duration);
        },

        /**
         * Info toast
         */
        info: function(message, duration) {
            return this.show(message, 'info', duration);
        },

        /**
         * Escape HTML to prevent XSS
         */
        escapeHtml: function(text) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    };

    $(document).ready(function() {
        // Test connection button
        $('#propertyfinder-test-connection').on('click', function(e) {
            e.preventDefault();
            
            var button = $(this);
            var statusDiv = $('.connection-status');
            var spinner = button.next('.spinner');
            
            button.prop('disabled', true);
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
                        statusDiv.html('<div class="notice notice-success inline"><p><strong>✓ Success:</strong> ' + response.data.message + '</p></div>');
                    } else {
                        PropertyFinderToast.error(response.data.message || 'Connection failed!');
                        statusDiv.html('<div class="notice notice-error inline"><p><strong>✗ Error:</strong> ' + response.data.message + '</p></div>');
                    }
                },
                error: function(xhr, status, error) {
                    PropertyFinderToast.error('An error occurred. Please try again.');
                    statusDiv.html('<div class="notice notice-error inline"><p><strong>✗ Error:</strong> An error occurred. Please try again.</p></div>');
                },
                complete: function() {
                    button.prop('disabled', false);
                    spinner.removeClass('is-active');
                }
            });
        });
        
        // Sync now button
        $('#propertyfinder-sync-now').on('click', function(e) {
            e.preventDefault();
            
            var button = $(this);
            var statusDiv = $('.sync-status');
            var progressContainer = $('#import-progress-container');
            var originalHtml = button.html();
            
            PropertyFinderConfirm.show('Are you sure you want to import listings now? This may take a few minutes.', function(confirmed) {
                if (!confirmed) return;
                
                button.prop('disabled', true).html('<span class="dashicons dashicons-update" style="vertical-align: middle; animation: spin 1s linear infinite;"></span> Importing...');
                
                // Show and reset progress container
                progressContainer.show();
                $('#import-progress-fill').css('width', '0%').css('background', 'linear-gradient(90deg, #2271b1, #135e96)');
                $('#import-progress-status').text('Starting import...');
                $('#imported-count, #total-count, #remaining-count').text('0');
                $('#stat-imported, #stat-updated, #stat-skipped, #stat-errors').text('0');
                progressContainer.removeClass('success error').css('border-color', '#ddd');
                
                // Update progress to show we're starting
                $('#import-progress-fill').css('width', '10%');
                $('#import-progress-status').text('Connecting to API...');
                
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
                            var results = response.data.results || {};
                            var imported = results.imported || 0;
                            var updated = results.updated || 0;
                            var skipped = results.skipped || 0;
                            var errors = results.errors || 0;
                            var total = results.total || (imported + updated + skipped + errors);
                            var totalProcessed = imported + updated + skipped + errors;
                            
                            // Update progress bar to 100%
                            $('#import-progress-fill').css('width', '100%').css('background', 'linear-gradient(90deg, #00a32a, #008a00)');
                            $('#import-progress-status').html('<span style="color: #00a32a;">✓ Import Completed!</span>');
                            progressContainer.addClass('success').css('border-color', '#00a32a');
                            
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
                                '<div class="notice notice-success inline">' +
                                '<p><strong>✓ Import Completed!</strong></p>' +
                                '<p>' + response.data.message + '</p>' +
                                '</div>'
                            );
                            
                            // Reload page after 3 seconds to see updated listings
                            setTimeout(function() {
                                location.reload();
                            }, 3000);
                        } else {
                            $('#import-progress-status').html('<span style="color: #d63638;">✗ Import Failed</span>');
                            progressContainer.addClass('error').css('border-color', '#d63638');
                            PropertyFinderToast.error('Import failed: ' + (response.data.message || 'Unknown error'));
                            statusDiv.html('<div class="notice notice-error inline"><p><strong>✗ Import Failed:</strong> ' + response.data.message + '</p></div>');
                            button.prop('disabled', false).html(originalHtml);
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#import-progress-status').html('<span style="color: #d63638;">✗ Error Occurred</span>');
                        progressContainer.addClass('error').css('border-color', '#d63638');
                        PropertyFinderToast.error('An error occurred during import.');
                        statusDiv.html('<div class="notice notice-error inline"><p><strong>✗ Error:</strong> An error occurred during import.</p></div>');
                        button.prop('disabled', false).html(originalHtml);
                    }
                });
            });
        });
        
        // Import Listings
        $('#propertyfinder-import-now').on('click', function(e) {
            e.preventDefault();
            
            var button = $(this);
            var originalText = button.text();
            
            PropertyFinderConfirm.show('Start importing listings?', function(confirmed) {
                if (!confirmed) return;
                
                button.prop('disabled', true).text('Importing...');
                var progressContainer = $('#import-progress-container');
                progressContainer.show();
                
                // Reset progress
                $('#import-progress-fill').css('width', '0%').css('background', 'linear-gradient(90deg, #2271b1, #135e96)');
                $('#import-progress-status').text('Starting import...');
                $('#imported-count, #total-count, #remaining-count').text('0');
                $('#stat-imported, #stat-updated, #stat-skipped, #stat-errors').text('0');
                progressContainer.removeClass('success error').css('border-color', '#ddd');
                
                var perPage = parseInt($('#import-per-page').val()) || 50;
                var totalProcessed = 0;
                var totalImported = 0;
                var totalUpdated = 0;
                var totalSkipped = 0;
                var totalErrors = 0;
                var estimatedTotal = perPage; // Initial estimate
                
                // Function to update progress
                function updateProgress(results, isComplete) {
                    if (results) {
                        totalProcessed += (results.imported || 0) + (results.updated || 0) + (results.skipped || 0) + (results.errors || 0);
                        totalImported += results.imported || 0;
                        totalUpdated += results.updated || 0;
                        totalSkipped += results.skipped || 0;
                        totalErrors += results.errors || 0;
                        
                        if (results.total) {
                            estimatedTotal = results.total;
                        }
                    }
                    
                    var remaining = Math.max(0, estimatedTotal - totalProcessed);
                    var percentage = estimatedTotal > 0 ? Math.min((totalProcessed / estimatedTotal) * 100, 100) : 0;
                    
                    $('#imported-count').text(totalProcessed);
                    $('#total-count').text(estimatedTotal);
                    $('#remaining-count').text(remaining);
                    $('#stat-imported').text(totalImported);
                    $('#stat-updated').text(totalUpdated);
                    $('#stat-skipped').text(totalSkipped);
                    $('#stat-errors').text(totalErrors);
                    
                    $('#import-progress-fill').css('width', percentage + '%');
                    
                    if (isComplete) {
                        $('#import-progress-status').html('<span style="color: #00a32a;">✓ Import Completed!</span>');
                        $('#import-progress-fill').css('background', 'linear-gradient(90deg, #00a32a, #008a00)');
                        progressContainer.addClass('success').css('border-color', '#00a32a');
                    } else {
                        $('#import-progress-status').text('Processing... (' + totalProcessed + '/' + estimatedTotal + ')');
                    }
                }
                
                // Start import
                $.ajax({
                    url: propertyfinderAdmin.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'propertyfinder_import',
                        nonce: propertyfinderAdmin.nonce,
                        status: $('#import-status').val(),
                        page: $('#import-page').val(),
                        perPage: perPage
                    },
                    success: function(response) {
                        if (response.success) {
                            var results = response.data.results || response.data;
                            updateProgress(results, true);
                            
                            PropertyFinderToast.success('Import completed! Imported: ' + totalImported + ', Updated: ' + totalUpdated);
                            
                            // Reload page after 2 seconds
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            $('#import-progress-status').html('<span style="color: #d63638;">✗ Import Failed</span>');
                            progressContainer.addClass('error').css('border-color', '#d63638');
                            PropertyFinderToast.error('Import failed: ' + (response.data.message || 'Unknown error'));
                            button.prop('disabled', false).text(originalText);
                        }
                    },
                    error: function() {
                        $('#import-progress-status').html('<span style="color: #d63638;">✗ Error Occurred</span>');
                        progressContainer.addClass('error').css('border-color', '#d63638');
                        PropertyFinderToast.error('An error occurred during import.');
                        button.prop('disabled', false).text(originalText);
                    }
                });
            });
        });
        
        // Sync All Listings
        $('#propertyfinder-sync-all').on('click', function(e) {
            e.preventDefault();
            
            var button = $(this);
            var progressContainer = $('#import-progress-container');
            
            PropertyFinderConfirm.show('This will import ALL listings from PropertyFinder (all pages). This may take several minutes. Continue?', function(confirmed) {
                if (!confirmed) return;
                
                button.prop('disabled', true).html('<span class="dashicons dashicons-update" style="vertical-align: middle;"></span> Importing All Pages...');
                progressContainer.show();
                
                // Reset progress
                $('#import-progress-fill').css('width', '0%').css('background', 'linear-gradient(90deg, #2271b1, #135e96)');
                $('#import-progress-status').text('Starting full import...');
                $('#imported-count, #total-count, #remaining-count').text('0');
                $('#stat-imported, #stat-updated, #stat-skipped, #stat-errors').text('0');
                progressContainer.removeClass('success error').css('border-color', '#ddd');
                
                var totalImported = 0;
                var totalUpdated = 0;
                var totalSkipped = 0;
                var totalErrors = 0;
                var currentPage = 1;
                var isImporting = true;
                
                // Function to update progress
                function updateProgress(results) {
                    if (results) {
                        totalImported += results.imported || 0;
                        totalUpdated += results.updated || 0;
                        totalSkipped += results.skipped || 0;
                        totalErrors += results.errors || 0;
                        
                        var totalProcessed = totalImported + totalUpdated + totalSkipped + totalErrors;
                        var estimatedTotal = results.total || (totalProcessed * 2); // Estimate if total not available
                        var remaining = Math.max(0, estimatedTotal - totalProcessed);
                        var percentage = estimatedTotal > 0 ? Math.min((totalProcessed / estimatedTotal) * 100, 95) : 50;
                        
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
                            status: $('#import-status').val(),
                            page: currentPage,
                            perPage: 50
                        },
                        success: function(response) {
                            if (response.success) {
                                var results = response.data.results || response.data;
                                updateProgress(results);
                                
                                // Check if there are more pages to import
                                if ((results.imported > 0 || results.updated > 0) && results.total > 0) {
                                    var totalProcessed = totalImported + totalUpdated + totalSkipped + totalErrors;
                                    if (totalProcessed < results.total) {
                                        currentPage++;
                                        setTimeout(importNextPage, 1000); // Wait 1 second before next page
                                    } else {
                                        completeImport();
                                    }
                                } else {
                                    // No more results, complete
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
                        progressContainer.addClass('error').css('border-color', '#d63638');
                        PropertyFinderToast.error('Import failed. Some pages may have been imported.');
                    } else {
                        $('#import-progress-status').html('<span style="color: #00a32a;">✓ Full Import Completed!</span>');
                        $('#import-progress-fill').css('width', '100%').css('background', 'linear-gradient(90deg, #00a32a, #008a00)');
                        progressContainer.addClass('success').css('border-color', '#00a32a');
                        
                        var message = 'Full import completed! Imported: ' + totalImported + ', Updated: ' + totalUpdated;
                        PropertyFinderToast.success(message);
                        
                        // Reload page after 3 seconds
                        setTimeout(function() {
                            location.reload();
                        }, 3000);
                    }
                    
                    button.prop('disabled', false).html('<span class="dashicons dashicons-download" style="vertical-align: middle;"></span> Sync All Listings');
                }
                
                // Start importing
                importNextPage();
            });
        });
        
        // Test connection from logs page
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
                        PropertyFinderToast.success('Connection successful! Check logs for details.');
                    } else {
                        PropertyFinderToast.error('Connection failed: ' + (response.data.message || 'Unknown error'));
                    }
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                },
                error: function() {
                    PropertyFinderToast.error('Error occurred during test.');
                    button.prop('disabled', false).text('Test Connection');
                }
            });
        });
    });
    
})(jQuery);