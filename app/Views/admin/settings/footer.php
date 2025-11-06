<?php
/**
 * Settings page footer with scripts
 *
 * @package PropertyFinder
 * @subpackage Views
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>
</div><!-- .wrap .propertyfinder-settings-page -->

<style>
/* Force grid display - inline to override everything */
.propertyfinder-settings-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)) !important;
    gap: 16px !important;
}

.propertyfinder-settings-grid > .propertyfinder-settings-section {
    display: flex !important;
    flex-direction: column !important;
}
</style>
