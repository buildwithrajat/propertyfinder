/**
 * Agent Editor JavaScript
 *
 * @package PropertyFinder
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Tab functionality is handled in the metabox view template
        // Additional JavaScript can be added here for agent-specific functionality
        
        // Handle form validation
        $('form#post').on('submit', function(e) {
            var email = $('input[name="_pf_email"]').val();
            if (email && !isValidEmail(email)) {
                alert('Please enter a valid email address.');
                e.preventDefault();
                return false;
            }
        });

        function isValidEmail(email) {
            var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
    });

})(jQuery);

