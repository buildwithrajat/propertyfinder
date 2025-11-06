<?php
/**
 * Sync Scheduler Section
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
        <input type="hidden" name="propertyfinder_save_section" value="scheduler" />
        
        <div class="propertyfinder-section-header">
            <div class="propertyfinder-section-icon">
                <span class="dashicons dashicons-clock"></span>
            </div>
            <div class="propertyfinder-section-title-group">
                <h2><?php _e('Sync Scheduler', 'propertyfinder'); ?></h2>
                <p class="propertyfinder-section-description"><?php _e('Configure automatic synchronization', 'propertyfinder'); ?></p>
            </div>
        </div>
        
        <div class="propertyfinder-form-section">
            <div class="propertyfinder-form-row">
                <div class="propertyfinder-form-label">
                    <label for="propertyfinder_sync_enabled"><?php _e('Enable Automatic Sync', 'propertyfinder'); ?></label>
                </div>
                <div class="propertyfinder-form-field">
                    <label class="propertyfinder-switch">
                        <input type="checkbox" 
                               id="propertyfinder_sync_enabled" 
                               name="propertyfinder_sync_enabled" 
                               value="1" 
                               <?php checked(get_option('propertyfinder_sync_enabled', false), true); ?> />
                        <span class="propertyfinder-slider"></span>
                        <span class="propertyfinder-switch-label"><?php _e('Enable automatic synchronization of listings', 'propertyfinder'); ?></span>
                    </label>
                </div>
            </div>
            
            <div class="propertyfinder-form-row">
                <div class="propertyfinder-form-label">
                    <label for="propertyfinder_sync_interval"><?php _e('Sync Interval', 'propertyfinder'); ?></label>
                </div>
                <div class="propertyfinder-form-field">
                    <div class="propertyfinder-select-wrapper">
                        <select id="propertyfinder_sync_interval" name="propertyfinder_sync_interval" class="propertyfinder-select">
                            <option value="hourly" <?php selected(get_option('propertyfinder_sync_interval', 'hourly'), 'hourly'); ?>>
                                <?php _e('Every Hour', 'propertyfinder'); ?>
                            </option>
                            <option value="4hours" <?php selected(get_option('propertyfinder_sync_interval', 'hourly'), '4hours'); ?>>
                                <?php _e('Every 4 Hours', 'propertyfinder'); ?>
                            </option>
                            <option value="6hours" <?php selected(get_option('propertyfinder_sync_interval', 'hourly'), '6hours'); ?>>
                                <?php _e('Every 6 Hours', 'propertyfinder'); ?>
                            </option>
                            <option value="daily" <?php selected(get_option('propertyfinder_sync_interval', 'hourly'), 'daily'); ?>>
                                <?php _e('Daily (24 Hours)', 'propertyfinder'); ?>
                            </option>
                            <option value="daily_12am" <?php selected(get_option('propertyfinder_sync_interval', 'hourly'), 'daily_12am'); ?>>
                                <?php _e('Daily at 12:00 AM', 'propertyfinder'); ?>
                            </option>
                            <option value="weekly" <?php selected(get_option('propertyfinder_sync_interval', 'hourly'), 'weekly'); ?>>
                                <?php _e('Weekly', 'propertyfinder'); ?>
                            </option>
                        </select>
                        <span class="propertyfinder-select-icon dashicons dashicons-arrow-down-alt2"></span>
                    </div>
                    <p class="propertyfinder-field-description"><?php _e('How often to automatically sync listings from PropertyFinder API', 'propertyfinder'); ?></p>
                </div>
            </div>
            
            <div class="propertyfinder-form-row" id="sync-time-row" style="<?php echo in_array(get_option('propertyfinder_sync_interval', 'hourly'), array('daily', 'weekly')) ? '' : 'display:none;'; ?>">
                <div class="propertyfinder-form-label">
                    <label for="propertyfinder_sync_time"><?php _e('Sync Time', 'propertyfinder'); ?></label>
                </div>
                <div class="propertyfinder-form-field">
                    <div class="propertyfinder-input-wrapper">
                        <input type="time" 
                               id="propertyfinder_sync_time" 
                               name="propertyfinder_sync_time" 
                               value="<?php echo esc_attr(get_option('propertyfinder_sync_time', '00:00')); ?>" 
                               class="propertyfinder-input" />
                        <span class="propertyfinder-input-icon dashicons dashicons-clock"></span>
                    </div>
                    <p class="propertyfinder-field-description"><?php _e('Time of day to run sync (for daily and weekly intervals)', 'propertyfinder'); ?></p>
                </div>
            </div>
            
            <?php
            $cron_hook = \PropertyFinder_Config::get('sync_cron_hook', 'propertyfinder_sync_listings');
            $next_sync = wp_next_scheduled($cron_hook);
            if ($next_sync):
                $next_sync_time = get_date_from_gmt(date('Y-m-d H:i:s', $next_sync), get_option('date_format') . ' ' . get_option('time_format'));
            ?>
            <div class="propertyfinder-form-row propertyfinder-info-row">
                <div class="propertyfinder-form-label">
                    <span class="dashicons dashicons-calendar-alt"></span>
                    <?php _e('Next Sync', 'propertyfinder'); ?>
                </div>
                <div class="propertyfinder-form-field">
                    <div class="propertyfinder-info-badge">
                        <strong><?php echo esc_html($next_sync_time); ?></strong>
                    </div>
                    <p class="propertyfinder-field-description"><?php _e('Next scheduled synchronization time', 'propertyfinder'); ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="propertyfinder-form-actions">
            <?php submit_button(__('Save Scheduler', 'propertyfinder'), 'primary propertyfinder-btn-primary propertyfinder-btn-small', 'propertyfinder_save_settings', false); ?>
        </div>
    </form>
</div>
