/**
 * Property Editor JavaScript
 *
 * @package PropertyFinder
 */

(function($) {
    'use strict';

    // Tab navigation is already in PHP inline script
    // Additional JavaScript can be added here for advanced interactions

    // Location creation
    $(document).on('click', '#create-location', function(e) {
        e.preventDefault();
        var locationName = prompt('Enter location name:');
        if (locationName) {
            var apiId = prompt('Enter API Location ID (optional, leave blank for manual):');
            // Simple creation - reload page to show in dropdown
            // In production, use AJAX for better UX
            alert('Location "' + locationName + '" will be created. Please save the property first, then assign it.');
        }
    });

    // Property type creation
    $(document).on('click', '#create-property-type', function(e) {
        e.preventDefault();
        var typeName = prompt('Enter property type name:');
        if (typeName) {
            alert('Property type "' + typeName + '" will be created. Please save the property first, then assign it.');
        }
    });

    // Amenity creation
    $(document).on('click', '#create-amenity', function(e) {
        e.preventDefault();
        var amenityName = prompt('Enter amenity name:');
        if (amenityName) {
            alert('Amenity "' + amenityName + '" will be created. Please save the property first, then assign it.');
        }
    });

    // Map initialization
    var propertyMap = null;
    var propertyMarker = null;

    function initPropertyMap() {
        var latInput = $('#pf-location-lat');
        var lngInput = $('#pf-location-lng');
        var mapContainer = $('#propertyfinder-map-container');
        
        if (mapContainer.length === 0) {
            return;
        }

        var lat = parseFloat(latInput.val()) || 25.276987; // Default Dubai
        var lng = parseFloat(lngInput.val()) || 55.296249;

        // Load Google Maps if not already loaded
        if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
            // Note: You'll need to add Google Maps API key in settings
            console.warn('Google Maps API not loaded. Add API key to enable map functionality.');
            return;
        }

        // Initialize map
        var mapOptions = {
            center: { lat: lat, lng: lng },
            zoom: 13,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        propertyMap = new google.maps.Map(mapContainer[0], mapOptions);

        // Add marker
        if (lat && lng) {
            propertyMarker = new google.maps.Marker({
                position: { lat: lat, lng: lng },
                map: propertyMap,
                draggable: true
            });

            // Update inputs when marker is dragged
            google.maps.event.addListener(propertyMarker, 'dragend', function() {
                var pos = propertyMarker.getPosition();
                latInput.val(pos.lat());
                lngInput.val(pos.lng());
            });
        }

        // Update marker when coordinates change
        latInput.add(lngInput).on('change', function() {
            var newLat = parseFloat(latInput.val());
            var newLng = parseFloat(lngInput.val());
            
            if (newLat && newLng && propertyMap) {
                var newPos = { lat: newLat, lng: newLng };
                
                if (propertyMarker) {
                    propertyMarker.setPosition(newPos);
                } else {
                    propertyMarker = new google.maps.Marker({
                        position: newPos,
                        map: propertyMap,
                        draggable: true
                    });
                }
                
                propertyMap.setCenter(newPos);
            }
        });
    }

    // Initialize map when map tab is shown
    $(document).on('click', '.propertyfinder-tab-btn[data-tab="map"]', function() {
        if (!propertyMap) {
            setTimeout(initPropertyMap, 100);
        }
    });

    // Load coordinates from location term when location changes
    $('#propertyfinder_location').on('change', function() {
        var locationId = $(this).val();
        if (!locationId) {
            return;
        }

        // Fetch location coordinates via AJAX
        $.ajax({
            url: propertyfinderEditor.ajaxUrl,
            type: 'POST',
            data: {
                action: 'propertyfinder_get_location_coords',
                location_id: locationId,
                nonce: propertyfinderEditor.nonce
            },
            success: function(response) {
                if (response.success && response.data.lat && response.data.lng) {
                    $('#pf-location-lat').val(response.data.lat);
                    $('#pf-location-lng').val(response.data.lng);
                    
                    // Update map if initialized
                    if (propertyMap && propertyMarker) {
                        var newPos = { lat: response.data.lat, lng: response.data.lng };
                        propertyMarker.setPosition(newPos);
                        propertyMap.setCenter(newPos);
                    }
                }
            }
        });
    });

})(jQuery);
