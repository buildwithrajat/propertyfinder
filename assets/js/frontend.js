/**
 * Frontend area JavaScript
 *
 * @package PropertyFinder
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize propertyfinder functionality
        $('.propertyfinder-properties').each(function() {
            initializePropertyList($(this));
        });
    });

    function initializePropertyList($container) {
        // Add any frontend functionality here
        console.log('PropertyFinder frontend initialized');
    }
    
})(jQuery);

