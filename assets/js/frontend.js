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
        
        // Initialize gallery slider
        initializeGallerySlider();
    });

    function initializePropertyList($container) {
        // Initialize view toggle
        initViewToggle($container);
    }
    
    /**
     * Initialize view toggle (Grid/List)
     */
    function initViewToggle($container) {
        // Get saved view preference or default to grid
        var savedView = localStorage.getItem('propertyfinder_view_preference') || 'grid';
        
        // Set initial view
        if (savedView) {
            $container.attr('data-view', savedView);
            $container.find('.propertyfinder-view-btn').removeClass('active');
            $container.find('.propertyfinder-view-btn[data-view="' + savedView + '"]').addClass('active');
        }
        
        // Handle view toggle clicks
        $container.find('.propertyfinder-view-btn').on('click', function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var view = $btn.data('view');
            
            if (!view) return;
            
            // Update container view
            $container.attr('data-view', view);
            
            // Update button states
            $container.find('.propertyfinder-view-btn').removeClass('active');
            $btn.addClass('active');
            
            // Save preference
            localStorage.setItem('propertyfinder_view_preference', view);
        });
    }
    
    /**
     * Initialize gallery slider for property single page
     */
    function initializeGallerySlider() {
        var $slider = $('.propertyfinder-gallery-slider');
        if (!$slider.length) return;
        
        var $slides = $slider.find('.propertyfinder-gallery-slide');
        var $thumbs = $slider.find('.propertyfinder-gallery-thumb');
        var $prevBtn = $slider.find('.propertyfinder-gallery-prev');
        var $nextBtn = $slider.find('.propertyfinder-gallery-next');
        var $current = $slider.find('.propertyfinder-gallery-current');
        var $total = $slider.find('.propertyfinder-gallery-total');
        var currentSlide = 0;
        var totalSlides = $slides.length;
        
        // Update total count
        if ($total.length) {
            $total.text(totalSlides);
        }
        
        // Function to show slide
        function showSlide(index) {
            if (index < 0) {
                index = totalSlides - 1;
            } else if (index >= totalSlides) {
                index = 0;
            }
            
            currentSlide = index;
            
            // Update slides
            $slides.removeClass('active');
            $slides.eq(currentSlide).addClass('active');
            
            // Update thumbnails
            $thumbs.removeClass('active');
            $thumbs.eq(currentSlide).addClass('active');
            
            // Update counter
            if ($current.length) {
                $current.text(currentSlide + 1);
            }
            
            // Scroll thumbnail into view
            var $activeThumb = $thumbs.eq(currentSlide);
            if ($activeThumb.length) {
                var thumbContainer = $activeThumb.parent()[0];
                var thumbOffset = $activeThumb.position().left + thumbContainer.scrollLeft;
                var thumbWidth = $activeThumb.outerWidth();
                var containerWidth = $(thumbContainer).width();
                
                thumbContainer.scrollTo({
                    left: thumbOffset - (containerWidth / 2) + (thumbWidth / 2),
                    behavior: 'smooth'
                });
            }
        }
        
        // Previous button
        $prevBtn.on('click', function(e) {
            e.preventDefault();
            showSlide(currentSlide - 1);
        });
        
        // Next button
        $nextBtn.on('click', function(e) {
            e.preventDefault();
            showSlide(currentSlide + 1);
        });
        
        // Thumbnail click
        $thumbs.on('click', function(e) {
            e.preventDefault();
            var index = $(this).data('slide');
            if (typeof index !== 'undefined') {
                showSlide(index);
            }
        });
        
        // Keyboard navigation
        $(document).on('keydown', function(e) {
            if (!$slider.is(':visible')) return;
            
            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                showSlide(currentSlide - 1);
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                showSlide(currentSlide + 1);
            }
        });
        
        // Auto-play (optional - can be disabled)
        var autoPlayInterval = null;
        function startAutoPlay() {
            if (totalSlides <= 1) return;
            autoPlayInterval = setInterval(function() {
                showSlide(currentSlide + 1);
            }, 5000); // Change slide every 5 seconds
        }
        
        function stopAutoPlay() {
            if (autoPlayInterval) {
                clearInterval(autoPlayInterval);
                autoPlayInterval = null;
            }
        }
        
        // Pause on hover
        $slider.on('mouseenter', stopAutoPlay);
        $slider.on('mouseleave', startAutoPlay);
        
        // Start auto-play
        startAutoPlay();
    }
    
})(jQuery);

