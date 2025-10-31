<?php
/**
 * Admin properties page view
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
    
    <div style="background: #fff; padding: 30px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05); border-radius: 4px; margin-top: 0;">
        <h2><?php _e('Properties', 'propertyfinder'); ?></h2>
        <p><?php _e('Properties will be displayed here.', 'propertyfinder'); ?></p>
    </div>
</div>