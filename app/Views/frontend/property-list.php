<?php
/**
 * Frontend property list view
 *
 * @package PropertyFinder
 * @subpackage Views
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>
<div class="propertyfinder-properties">
    <?php if (!empty($properties)): ?>
        <div class="propertyfinder-grid">
            <?php foreach ($properties as $property): ?>
                <div class="propertyfinder-item">
                    <h3><?php echo esc_html($property->title); ?></h3>
                    <p><?php _e('Property details...', 'propertyfinder'); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p><?php _e('No properties found.', 'propertyfinder'); ?></p>
    <?php endif; ?>
</div>

