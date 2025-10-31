<?php
/**
 * Frontend single property view
 *
 * @package PropertyFinder
 * @subpackage Views
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

if (!$property):
?>
    <p><?php _e('Property not found.', 'propertyfinder'); ?></p>
<?php else: ?>
    <div class="propertyfinder-single-property">
        <h2><?php echo esc_html($property->title); ?></h2>
        <div class="propertyfinder-content">
            <?php echo wp_kses_post($property->content); ?>
        </div>
    </div>
<?php endif; ?>

