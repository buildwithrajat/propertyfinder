<?php
/**
 * Admin import page view
 *
 * @package PropertyFinder
 * @subpackage Views
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>
<div class="wrap propertyfinder-modern propertyfinder-import-page">
    <h1><?php echo esc_html($page_title); ?></h1>
    
    <div class="propertyfinder-import-container">
        <h2><?php _e('Import Parameters', 'propertyfinder'); ?></h2>
        
        <form id="propertyfinder-import-form" method="post">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="import-status"><?php _e('Listing Status', 'propertyfinder'); ?></label>
                    </th>
                    <td>
                        <select id="import-status" name="status" class="regular-text">
                            <option value="published" selected><?php _e('Published', 'propertyfinder'); ?></option>
                            <option value="unpublished"><?php _e('Unpublished', 'propertyfinder'); ?></option>
                            <option value="draft"><?php _e('Draft', 'propertyfinder'); ?></option>
                        </select>
                        <p class="description"><?php _e('Select which listings to import based on their status.', 'propertyfinder'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="import-page"><?php _e('Page', 'propertyfinder'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="import-page" name="page" value="1" min="1" class="small-text" />
                        <p class="description"><?php _e('Page number to start importing from.', 'propertyfinder'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="import-per-page"><?php _e('Items Per Page', 'propertyfinder'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="import-per-page" name="perPage" value="50" min="1" max="100" class="small-text" />
                        <p class="description"><?php _e('Number of listings to import per request (1-100).', 'propertyfinder'); ?></p>
                    </td>
                </tr>
            </table>
        </form>
        
        <div class="propertyfinder-import-actions">
            <button type="button" class="button button-primary button-large" id="propertyfinder-import-now">
                <?php _e('Import Listings', 'propertyfinder'); ?>
            </button>
            <button type="button" class="button button-secondary button-large" id="propertyfinder-sync-all">
                <?php _e('Sync All Listings', 'propertyfinder'); ?>
            </button>
        </div>
    </div>
    
    <div id="import-progress-container" class="propertyfinder-progress-container" style="display: none; margin: 20px 0; padding: 20px; background: #fff; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
            <h3 style="margin: 0; color: #2271b1; display: flex; align-items: center; gap: 10px;">
                <span class="dashicons dashicons-download" style="animation: spin 1s linear infinite;"></span>
                <?php _e('Importing Listings...', 'propertyfinder'); ?>
            </h3>
            <span id="import-progress-status" style="font-size: 12px; color: #666;"><?php _e('Initializing...', 'propertyfinder'); ?></span>
        </div>
        <div class="propertyfinder-progress-bar" style="width: 100%; height: 8px; background: #f0f0f0; border-radius: 4px; overflow: hidden; margin-bottom: 15px;">
            <div id="import-progress-fill" class="propertyfinder-progress-fill" style="height: 100%; background: linear-gradient(90deg, #2271b1, #135e96); width: 0%; transition: width 0.3s ease; border-radius: 4px;"></div>
        </div>
        <div id="import-progress-counter" style="margin-bottom: 15px; font-size: 14px; color: #666;">
            <strong><?php _e('Progress:', 'propertyfinder'); ?></strong> 
            <span id="imported-count">0</span> <?php _e('imported', 'propertyfinder'); ?> / 
            <span id="total-count">0</span> <?php _e('total', 'propertyfinder'); ?> 
            (<span id="remaining-count">0</span> <?php _e('remaining', 'propertyfinder'); ?>)
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
    </div>
    
    <div class="propertyfinder-import-results" style="display: none;">
        <h2><?php _e('Import Results', 'propertyfinder'); ?></h2>
        <div class="propertyfinder-results-content"></div>
    </div>
    
    <style>
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .propertyfinder-progress-container.success {
        border-color: #00a32a !important;
    }
    .propertyfinder-progress-container.error {
        border-color: #d63638 !important;
    }
    </style>
</div>