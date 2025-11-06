<?php
/**
 * Progress Container Component
 *
 * @package PropertyFinder
 * @subpackage Views
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>
<div id="import-progress-container" class="propertyfinder-progress-container" style="display: none;">
    <div class="propertyfinder-progress-header">
        <div class="propertyfinder-progress-title">
            <span class="dashicons dashicons-download propertyfinder-progress-icon"></span>
            <h3><?php _e('Importing Listings...', 'propertyfinder'); ?></h3>
        </div>
        <span id="import-progress-status" class="propertyfinder-progress-status"><?php _e('Initializing...', 'propertyfinder'); ?></span>
    </div>
    
    <div class="propertyfinder-progress-bar-wrapper">
        <div class="propertyfinder-progress-bar">
            <div id="import-progress-fill" class="propertyfinder-progress-fill"></div>
        </div>
    </div>
    
    <div id="import-progress-counter" class="propertyfinder-progress-counter">
        <strong><?php _e('Progress:', 'propertyfinder'); ?></strong> 
        <span id="imported-count">0</span> <?php _e('imported', 'propertyfinder'); ?> / 
        <span id="total-count">0</span> <?php _e('total', 'propertyfinder'); ?> 
        (<span id="remaining-count">0</span> <?php _e('remaining', 'propertyfinder'); ?>)
    </div>
    
    <div id="import-progress-stats" class="propertyfinder-progress-stats">
        <div class="propertyfinder-stat-card propertyfinder-stat-imported">
            <div class="propertyfinder-stat-label"><?php _e('Imported', 'propertyfinder'); ?></div>
            <div id="stat-imported" class="propertyfinder-stat-value">0</div>
        </div>
        <div class="propertyfinder-stat-card propertyfinder-stat-updated">
            <div class="propertyfinder-stat-label"><?php _e('Updated', 'propertyfinder'); ?></div>
            <div id="stat-updated" class="propertyfinder-stat-value">0</div>
        </div>
        <div class="propertyfinder-stat-card propertyfinder-stat-skipped">
            <div class="propertyfinder-stat-label"><?php _e('Skipped', 'propertyfinder'); ?></div>
            <div id="stat-skipped" class="propertyfinder-stat-value">0</div>
        </div>
        <div class="propertyfinder-stat-card propertyfinder-stat-errors">
            <div class="propertyfinder-stat-label"><?php _e('Errors', 'propertyfinder'); ?></div>
            <div id="stat-errors" class="propertyfinder-stat-value">0</div>
        </div>
    </div>
</div>
