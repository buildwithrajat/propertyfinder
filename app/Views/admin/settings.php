<?php
/**
 * Admin settings page view
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
    
    <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=propertyfinder-settings')); ?>">
        <?php wp_nonce_field('propertyfinder_settings_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="propertyfinder_api_key"><?php _e('API Key', 'propertyfinder'); ?></label>
                </th>
                <td>
                    <input type="text" 
                           id="propertyfinder_api_key" 
                           name="propertyfinder_api_key" 
                           value="<?php echo esc_attr(get_option('propertyfinder_api_key', 'nxpEG.q0OMYGl9ABrVJuMgHflOctxjR6dO3GkD2W')); ?>" 
                           class="regular-text" />
                    <p class="description"><?php _e('Enter your PropertyFinder API Key', 'propertyfinder'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="propertyfinder_api_secret"><?php _e('API Secret', 'propertyfinder'); ?></label>
                </th>
                <td>
                    <input type="password" 
                           id="propertyfinder_api_secret" 
                           name="propertyfinder_api_secret" 
                           value="<?php echo esc_attr(get_option('propertyfinder_api_secret', 'y6Qf5mbr0JQbWzO0HsVnCX752FdqovCJ')); ?>" 
                           class="regular-text" />
                    <p class="description"><?php _e('Enter your PropertyFinder API Secret', 'propertyfinder'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="propertyfinder_api_endpoint"><?php _e('API Endpoint', 'propertyfinder'); ?></label>
                </th>
                <td>
                    <input type="url" 
                           id="propertyfinder_api_endpoint" 
                           name="propertyfinder_api_endpoint" 
                           value="<?php echo esc_attr(get_option('propertyfinder_api_endpoint', 'https://atlas.propertyfinder.com/v1')); ?>" 
                           class="regular-text" />
                    <p class="description"><?php _e('API endpoint URL', 'propertyfinder'); ?></p>
                </td>
            </tr>
        </table>
        
        <div class="submit">
            <?php submit_button(__('Save Settings', 'propertyfinder'), 'primary', 'propertyfinder_save_settings', false); ?>
        </div>
    </form>
    
    <div class="propertyfinder-actions">
        <h2><?php _e('Actions', 'propertyfinder'); ?></h2>
        <p class="description"><?php _e('Test your API connection and import listings', 'propertyfinder'); ?></p>
        
        <table class="form-table">
            <tr>
                <td>
                    <button type="button" class="button button-secondary" id="propertyfinder-test-connection">
                        <span class="dashicons dashicons-admin-plugins" style="vertical-align: middle;"></span>
                        <?php _e('Test Connection', 'propertyfinder'); ?>
                    </button>
                    <span class="spinner" style="float: none;"></span>
                    <div class="connection-status" style="margin-top: 10px;"></div>
                </td>
            </tr>
            <tr>
                <td>
                    <button type="button" class="button button-primary" id="propertyfinder-sync-now">
                        <span class="dashicons dashicons-update" style="vertical-align: middle;"></span>
                        <?php _e('Import Listings Now', 'propertyfinder'); ?>
                    </button>
                    <button type="button" class="button button-secondary" id="propertyfinder-sync-all" style="margin-left: 10px;">
                        <span class="dashicons dashicons-download" style="vertical-align: middle;"></span>
                        <?php _e('Sync All Pages', 'propertyfinder'); ?>
                    </button>
                    <div class="sync-status" style="margin-top: 10px;"></div>
                </td>
            </tr>
        </table>
    </div>
</div>