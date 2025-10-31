/**
 * Admin area JavaScript
 *
 * @package PropertyFinder
 */

(function($) {
    'use strict';

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
                        statusDiv.html('<div class="notice notice-success inline"><p><strong>✓ Success:</strong> ' + response.data.message + '</p></div>');
                    } else {
                        statusDiv.html('<div class="notice notice-error inline"><p><strong>✗ Error:</strong> ' + response.data.message + '</p></div>');
                    }
                },
                error: function(xhr, status, error) {
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
            
            if (!confirm('Are you sure you want to import listings now? This may take a few minutes.')) {
                return;
            }
            
            button.prop('disabled', true).text('Importing...');
            statusDiv.html('<div class="notice notice-info inline"><p>Importing listings from PropertyFinder API...</p></div>');
            
            $.ajax({
                url: propertyfinderAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'propertyfinder_sync',
                    nonce: propertyfinderAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
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
                        statusDiv.html('<div class="notice notice-error inline"><p><strong>✗ Import Failed:</strong> ' + response.data.message + '</p></div>');
                        button.prop('disabled', false).html('<span class="dashicons dashicons-update" style="vertical-align: middle;"></span> Import Listings Now');
                    }
                },
                error: function() {
                    statusDiv.html('<div class="notice notice-error inline"><p><strong>✗ Error:</strong> An error occurred during import.</p></div>');
                    button.prop('disabled', false).html('<span class="dashicons dashicons-update" style="vertical-align: middle;"></span> Import Listings Now');
                }
            });
        });
        
        // Import Listings
        $('#propertyfinder-import-now').on('click', function(e) {
            e.preventDefault();
            
            var button = $(this);
            var originalText = button.text();
            
            if (!confirm('Start importing listings?')) {
                return;
            }
            
            button.prop('disabled', true).text('Importing...');
            $('.propertyfinder-import-progress').show();
            
            var formData = $('#propertyfinder-import-form').serialize();
            
            $.ajax({
                url: propertyfinderAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'propertyfinder_import',
                    nonce: propertyfinderAdmin.nonce,
                    status: $('#import-status').val(),
                    page: $('#import-page').val(),
                    perPage: $('#import-per-page').val()
                },
                success: function(response) {
                    if (response.success) {
                        $('.propertyfinder-import-results').show();
                        $('.propertyfinder-results-content').html(
                            '<p><strong>Imported:</strong> ' + response.data.imported + '</p>' +
                            '<p><strong>Updated:</strong> ' + response.data.updated + '</p>' +
                            '<p><strong>Skipped:</strong> ' + response.data.skipped + '</p>' +
                            '<p><strong>Errors:</strong> ' + response.data.errors + '</p>'
                        );
                    } else {
                        alert('Import failed: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('An error occurred during import.');
                },
                complete: function() {
                    button.prop('disabled', false).text(originalText);
                    $('.propertyfinder-import-progress').hide();
                }
            });
        });
        
        // Sync All Listings
        $('#propertyfinder-sync-all').on('click', function(e) {
            e.preventDefault();
            
            var button = $(this);
            var statusDiv = $('.sync-status');
            
            if (!confirm('This will import ALL listings from PropertyFinder (all pages). This may take several minutes. Continue?')) {
                return;
            }
            
            button.prop('disabled', true).html('<span class="dashicons dashicons-update" style="vertical-align: middle;"></span> Importing All Pages...');
            statusDiv.html('<div class="notice notice-info inline"><p>Starting full import of all listings from PropertyFinder. Please wait...</p></div>');
            
            $.ajax({
                url: propertyfinderAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'propertyfinder_sync_all',
                    nonce: propertyfinderAdmin.nonce
                },
                timeout: 300000, // 5 minutes timeout
                success: function(response) {
                    if (response.success) {
                        statusDiv.html(
                            '<div class="notice notice-success inline">' +
                            '<p><strong>✓ Full Import Completed!</strong></p>' +
                            '<p><strong>Total Imported:</strong> ' + response.data.imported + '</p>' +
                            '<p><strong>Total Updated:</strong> ' + response.data.updated + '</p>' +
                            '</div>'
                        );
                        // Reload page after 3 seconds
                        setTimeout(function() {
                            location.reload();
                        }, 3000);
                    } else {
                        statusDiv.html('<div class="notice notice-error inline"><p><strong>✗ Import Failed:</strong> ' + response.data.message + '</p></div>');
                        button.prop('disabled', false).html('<span class="dashicons dashicons-download" style="vertical-align: middle;"></span> Sync All Pages');
                    }
                },
                error: function(xhr, status, error) {
                    statusDiv.html('<div class="notice notice-error inline"><p><strong>✗ Error:</strong> An error occurred during import. Please try again.</p></div>');
                    button.prop('disabled', false).html('<span class="dashicons dashicons-download" style="vertical-align: middle;"></span> Sync All Pages');
                }
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
    
})(jQuery);