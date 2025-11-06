/**
 * PropertyFinder Blocks Frontend JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Handle pagination clicks
        $('.propertyfinder-agent-pagination .page-numbers a').on('click', function(e) {
            e.preventDefault();
            
            const $link = $(this);
            const $block = $link.closest('.propertyfinder-agent-listing-block');
            const url = $link.attr('href');
            
            if (!url) {
                return;
            }
            
            // Show loader
            $block.find('.propertyfinder-agent-loader').show();
            $block.find('.propertyfinder-agent-grid').hide();
            $block.find('.propertyfinder-agent-pagination').hide();
            
            // Load new page
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'html',
                success: function(response) {
                    // Extract the block content from response
                    const $response = $(response);
                    const $newBlock = $response.find('.propertyfinder-agent-listing-block');
                    
                    if ($newBlock.length) {
                        $block.find('.propertyfinder-agent-grid').html($newBlock.find('.propertyfinder-agent-grid').html());
                        $block.find('.propertyfinder-agent-pagination').html($newBlock.find('.propertyfinder-agent-pagination').html());
                    }
                    
                    // Hide loader and show content
                    $block.find('.propertyfinder-agent-loader').hide();
                    $block.find('.propertyfinder-agent-grid').show();
                    $block.find('.propertyfinder-agent-pagination').show();
                    
                    // Scroll to top of block
                    $('html, body').animate({
                        scrollTop: $block.offset().top - 100
                    }, 500);
                    
                    // Re-bind pagination links
                    $('.propertyfinder-agent-pagination .page-numbers a').on('click', arguments.callee);
                },
                error: function() {
                    $block.find('.propertyfinder-agent-loader').hide();
                    $block.find('.propertyfinder-agent-grid').show();
                    $block.find('.propertyfinder-agent-pagination').show();
                    alert('Failed to load agents. Please try again.');
                }
            });
        });
    });

})(jQuery);

