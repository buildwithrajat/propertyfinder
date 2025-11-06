<?php
/**
 * Settings page header
 *
 * @package PropertyFinder
 * @subpackage Views
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>
<div class="wrap propertyfinder-modern propertyfinder-settings-page">
    <div class="propertyfinder-settings-header">
        <div class="propertyfinder-header-content">
            <h1 class="propertyfinder-page-title">
                <span class="dashicons dashicons-admin-settings"></span>
                <?php echo esc_html($page_title); ?>
            </h1>
            <p class="propertyfinder-page-description"><?php _e('Configure your PropertyFinder plugin settings and manage imports', 'propertyfinder'); ?></p>
        </div>
    </div>
    
    <?php
    // Show success message
    $show_success = false;
    if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true') {
        $show_success = true;
    } elseif (get_transient('propertyfinder_settings_saved')) {
        $show_success = true;
        delete_transient('propertyfinder_settings_saved');
    }
    
    if ($show_success) {
        echo '<div class="notice notice-success is-dismissible propertyfinder-notice"><p><strong>' . __('Settings saved successfully!', 'propertyfinder') . '</strong></p></div>';
    }
    ?>
    
    <div class="propertyfinder-settings-wrapper">
        <div class="propertyfinder-settings-grid">
