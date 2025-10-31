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
    
    <div class="propertyfinder-import-progress" style="display: none;">
        <h2><?php _e('Import Progress', 'propertyfinder'); ?></h2>
        <div class="propertyfinder-progress-bar">
            <div class="propertyfinder-progress-fill"></div>
        </div>
        <div class="propertyfinder-progress-text"></div>
        <div class="propertyfinder-progress-details"></div>
    </div>
    
    <div class="propertyfinder-import-results" style="display: none;">
        <h2><?php _e('Import Results', 'propertyfinder'); ?></h2>
        <div class="propertyfinder-results-content"></div>
    </div>
</div>