<?php
/**
 * Logging Settings Section
 *
 * @package PropertyFinder
 * @subpackage Views
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>
<div class="propertyfinder-settings-section propertyfinder-card">
    <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=propertyfinder-settings')); ?>" class="propertyfinder-settings-form">
        <?php wp_nonce_field('propertyfinder_settings_nonce'); ?>
        <input type="hidden" name="propertyfinder_save_section" value="logging" />
        
        <div class="propertyfinder-section-header">
            <div class="propertyfinder-section-icon">
                <span class="dashicons dashicons-admin-tools"></span>
            </div>
            <div class="propertyfinder-section-title-group">
                <h2><?php _e('Logging', 'propertyfinder'); ?></h2>
                <p class="propertyfinder-section-description"><?php _e('Configure log settings and modules', 'propertyfinder'); ?></p>
            </div>
        </div>
        
        <div class="propertyfinder-form-section">
            <div class="propertyfinder-form-row">
                <div class="propertyfinder-form-label">
                    <label for="propertyfinder_log_level"><?php _e('Log Level', 'propertyfinder'); ?></label>
                </div>
                <div class="propertyfinder-form-field">
                    <div class="propertyfinder-select-wrapper">
                        <select id="propertyfinder_log_level" name="propertyfinder_log_level" class="propertyfinder-select">
                            <option value="debug" <?php selected(get_option('propertyfinder_log_level', 'info'), 'debug'); ?>>
                                <?php _e('Debug (All logs)', 'propertyfinder'); ?>
                            </option>
                            <option value="info" <?php selected(get_option('propertyfinder_log_level', 'info'), 'info'); ?>>
                                <?php _e('Info (Recommended)', 'propertyfinder'); ?>
                            </option>
                            <option value="warning" <?php selected(get_option('propertyfinder_log_level', 'info'), 'warning'); ?>>
                                <?php _e('Warning & Errors Only', 'propertyfinder'); ?>
                            </option>
                            <option value="error" <?php selected(get_option('propertyfinder_log_level', 'info'), 'error'); ?>>
                                <?php _e('Errors Only', 'propertyfinder'); ?>
                            </option>
                        </select>
                        <span class="propertyfinder-select-icon dashicons dashicons-arrow-down-alt2"></span>
                    </div>
                    <p class="propertyfinder-field-description"><?php _e('Minimum log level to record. Info level is recommended for property imports.', 'propertyfinder'); ?></p>
                </div>
            </div>
            
            <div class="propertyfinder-form-row">
                <div class="propertyfinder-form-label">
                    <label><?php _e('Enable Logging for Modules', 'propertyfinder'); ?></label>
                </div>
                <div class="propertyfinder-form-field">
                    <div class="propertyfinder-checkbox-group">
                        <?php
                        $enabled_modules = get_option('propertyfinder_log_modules', array('import', 'sync', 'update'));
                        $available_modules = array(
                            'import' => __('Import', 'propertyfinder') . ' - ' . __('Property import operations', 'propertyfinder'),
                            'sync' => __('Sync', 'propertyfinder') . ' - ' . __('Synchronization operations', 'propertyfinder'),
                            'update' => __('Update', 'propertyfinder') . ' - ' . __('Update operations', 'propertyfinder'),
                            'api' => __('API', 'propertyfinder') . ' - ' . __('API requests and responses', 'propertyfinder'),
                            'agent' => __('Agent', 'propertyfinder') . ' - ' . __('Agent operations', 'propertyfinder'),
                        );
                        foreach ($available_modules as $module_key => $module_label):
                        ?>
                            <label class="propertyfinder-checkbox-item">
                                <input type="checkbox" 
                                       name="propertyfinder_log_modules[]" 
                                       value="<?php echo esc_attr($module_key); ?>" 
                                       <?php checked(in_array($module_key, $enabled_modules), true); ?> />
                                <span class="propertyfinder-checkbox-label">
                                    <strong><?php echo esc_html($module_label); ?></strong>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="propertyfinder-field-description"><?php _e('Only selected modules will create log files. You can view logs in PropertyFinder → Logs.', 'propertyfinder'); ?></p>
                </div>
            </div>
        </div>
        
        <div class="propertyfinder-form-actions">
            <?php submit_button(__('Save Logging', 'propertyfinder'), 'primary propertyfinder-btn-primary propertyfinder-btn-small', 'propertyfinder_save_settings', false); ?>
        </div>
    </form>
</div>
