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

// Include header
include __DIR__ . '/settings/header.php';

// Include sections
include __DIR__ . '/settings/api-config.php';
include __DIR__ . '/settings/logging.php';
include __DIR__ . '/settings/scheduler.php';
include __DIR__ . '/settings/actions.php';
?>

        </div><!-- .propertyfinder-settings-grid -->
    </div><!-- .propertyfinder-settings-wrapper -->
    
<?php
// Include Progress Container
include __DIR__ . '/settings/progress-container.php';

// Include Webhooks Section (hidden)
include __DIR__ . '/settings/webhooks.php';

// Include Footer with Scripts
include __DIR__ . '/settings/footer.php';
?>
