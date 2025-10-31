<?php
/**
 * Define internationalization functionality
 *
 * @package PropertyFinder
 * @subpackage Includes
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Internationalization class
 */
class PropertyFinder_i18n {

    /**
     * Load the plugin text domain for translation
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'propertyfinder',
            false,
            dirname(PROPERTYFINDER_PLUGIN_BASENAME) . '/languages/'
        );
    }
}

