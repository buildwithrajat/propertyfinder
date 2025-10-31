# PropertyFinder Plugin - Implementation Guide

## Overview

This guide explains how the PropertyFinder integration works with Custom Post Types (CPT), taxonomies, and API synchronization.

## Architecture

### 1. Custom Post Type: `pf_listing`

The plugin creates a custom post type for listings with the following capabilities:

- **Public**: `true` - Visible to visitors
- **Publicly Queryable**: `true` - Can be queried
- **Supports**: `title`, `editor`, `thumbnail`, `excerpt`, `custom-fields`
- **Show in REST**: `true` - Available in WP REST API
- **Archive**: `true` - Has archive page

### 2. Taxonomies

The following taxonomies are registered for the `pf_listing` CPT:

1. **`pf_category`** - Property Category (Sale/Rent)
2. **`pf_property_type`** - Property Type (Apartment, Villa, etc.)
3. **`pf_amenity`** - Amenities (Pool, Parking, etc.)
4. **`pf_location`** - Location/Community
5. **`pf_transaction_type`** - Transaction Type
6. **`pf_furnishing_status`** - Furnishing Status

All taxonomies are:
- Hierarchical: `true`
- Show in admin column: `true`
- REST API enabled: `true`

### 3. API Integration

Based on [PropertyFinder API documentation](https://api-docs.propertyfinder.net/enterprise-api/):

```php
// API Endpoint
https://atlas.propertyfinder.com/v1

// Authentication
POST /v1/auth/token
Headers: Content-Type: application/json
Body: { "apiKey": "...", "apiSecret": "..." }

// Get Listings
GET /v1/listings
Headers: Authorization: Bearer <TOKEN>
Query: page=1&perPage=50&status=published
```

## Import Flow

### 1. User Triggers Import

From **PropertyFinder → Import Listings** page:

```javascript
// User clicks "Import Listings" button
// AJAX Request sent
action: 'propertyfinder_import'
status: 'published'
page: 1
perPage: 50
```

### 2. Importer Class Handles Request

```php
// PropertyFinder_Importer::handle_import_ajax()
// 1. Validates nonce and permissions
// 2. Calls API to fetch listings
// 3. Loops through each listing
// 4. Creates or updates CPT
```

### 3. Single Listing Import Process

For each listing:

1. **Check if exists** by `_pf_api_id` meta
2. **Create post** if new, **Update post** if exists
3. **Set meta fields** (price, bedrooms, location, etc.)
4. **Set taxonomies** (category, type, amenities, etc.)
5. **Fire action hooks** for customization

### 4. Meta Fields Mapping

```php
_pf_api_id           → API listing ID
_pf_reference         → Listing reference number
_pf_price             → Property price
_pf_currency          → Currency (AED, USD, etc.)
_pf_bedrooms          → Number of bedrooms
_pf_bathrooms         → Number of bathrooms
_pf_area              → Property area
_pf_area_unit         → Area unit (sqft, sqm)
_pf_status            → Status (published/unpublished)
_pf_location_lat      → Latitude
_pf_location_lng      → Longitude
_pf_location_name      → Location name
_pf_agent_name         → Agent name
_pf_agent_email        → Agent email
_pf_agent_phone        → Agent phone
_pf_last_synced        → Last sync timestamp
```

### 5. Taxonomies Set Automatically

From API data:

```php
// Category
$listing_data['category'] → pf_category taxonomy

// Property Type
$listing_data['propertyType'] → pf_property_type taxonomy

// Location
$listing_data['location']['name'] → pf_location taxonomy

// Transaction Type
$listing_data['transactionType'] → pf_transaction_type taxonomy

// Furnishing
$listing_data['furnishingStatus'] → pf_furnishing_status taxonomy

// Amenities (array)
$listing_data['amenities'] → pf_amenity taxonomy (each item)
```

## Customization Hooks

The plugin provides extensive hooks for customization:

### Actions

```php
// Before import starts
do_action('propertyfinder_import_start', $params);

// After single listing imported
do_action('propertyfinder_listing_imported', $post_id, $listing_data);

// After single listing updated
do_action('propertyfinder_listing_updated', $post_id, $listing_data);

// After meta fields set
do_action('propertyfinder_listing_meta_set', $post_id, $listing_data);

// After taxonomies set
do_action('propertyfinder_listing_taxonomies_set', $post_id, $listing_data);

// After import completes
do_action('propertyfinder_import_complete', $results);

// On API error
do_action('propertyfinder_api_error', $type, $data);

// On rate limit exceeded
do_action('propertyfinder_rate_limit_exceeded');
```

### Filters

```php
// Filter API response
apply_filters('propertyfinder_api_response', $data, $endpoint);

// Filter listing params
apply_filters('propertyfinder_listings_params', $params);

// Filter listing data before import
apply_filters('propertyfinder_listing_before_import', $listing_data);

// Filter post data
apply_filters('propertyfinder_listing_post_data', $post_data, $listing_data);

// Filter update post data
apply_filters('propertyfinder_listing_update_post_data', $post_data, $listing_data);

// Filter meta fields
apply_filters('propertyfinder_listing_meta_fields', $meta_fields, $listing_data);

// Filter default post status
apply_filters('propertyfinder_default_post_status', 'publish');

// Filter CPT arguments
apply_filters('propertyfinder_cpt_args', $args);

// Filter taxonomy arguments
apply_filters('propertyfinder_taxonomy_args', $args, $taxonomy);
```

## Usage Examples

### Example 1: Custom Import Mapping

```php
// functions.php or custom plugin

// Custom field mapping
add_filter('propertyfinder_listing_meta_fields', function($meta_fields, $listing_data) {
    // Add custom field
    $meta_fields['_pf_custom_field'] = $listing_data['customValue'];
    return $meta_fields;
}, 10, 2);
```

### Example 2: Set Default Post Status

```php
// Set all imports as draft
add_filter('propertyfinder_default_post_status', function($status) {
    return 'draft';
});
```

### Example 3: Modify Import Data

```php
// Add or modify data before import
add_filter('propertyfinder_listing_before_import', function($listing_data) {
    // Add custom data
    $listing_data['custom_field'] = 'custom_value';
    return $listing_data;
});
```

### Example 4: Custom Taxonomy Term Creation

```php
// Set custom term before taxonomy assignment
add_action('propertyfinder_listing_taxonomies_set', function($post_id, $listing_data) {
    // Set custom taxonomy
    wp_set_object_terms($post_id, 'Custom Term', 'custom_taxonomy');
}, 10, 2);
```

### Example 5: API Request Logging

```php
// Log all API requests
add_action('propertyfinder_api_error', function($type, $data) {
    error_log('PropertyFinder API Error: ' . $type);
    error_log(print_r($data, true));
});
```

### Example 6: Rate Limit Handling

```php
// Handle rate limit with retry
add_action('propertyfinder_rate_limit_exceeded', function() {
    // Wait and retry
    sleep(60);
    // Trigger retry
    wp_schedule_single_event(time() + 60, 'propertyfinder_sync_listings');
});
```

## Admin Interface

### Settings Page
Location: **PropertyFinder → Settings**

- API Key configuration
- API Secret configuration
- API Endpoint configuration
- Test Connection button
- Save settings

### Listings Page
Location: **PropertyFinder → Listings**

- View all imported listings
- Statistics dashboard
- Quick access to edit listings
- Sync individual listings

### Import Page
Location: **PropertyFinder → Import Listings**

- Set import parameters:
  - Status filter (published/unpublished/draft)
  - Page number
  - Items per page (1-100)
- Import Listings button
- Sync All Listings button
- Progress indicator
- Results display

## Synchronization

### Manual Sync
1. Go to **PropertyFinder → Import Listings**
2. Set parameters
3. Click **Import Listings**

### Automatic Sync
Enable auto-sync in settings:

```php
update_option('propertyfinder_auto_sync_enabled', true);
```

Set sync interval:

```php
update_option('propertyfinder_sync_interval', 3600); // 1 hour
```

Schedule cron:

```php
if (!wp_next_scheduled('propertyfinder_sync_listings')) {
    wp_schedule_event(time(), 'hourly', 'propertyfinder_sync_listings');
}
```

## Querying Listings

### Get All Published Listings

```php
$listings = get_posts(array(
    'post_type' => 'pf_listing',
    'post_status' => 'publish',
    'posts_per_page' => -1,
));
```

### Get Listings by Taxonomy

```php
// By category
$listings = get_posts(array(
    'post_type' => 'pf_listing',
    'tax_query' => array(
        array(
            'taxonomy' => 'pf_category',
            'field' => 'slug',
            'terms' => 'villa',
        ),
    ),
));
```

### Get Listings by Meta

```php
$listings = get_posts(array(
    'post_type' => 'pf_listing',
    'meta_query' => array(
        array(
            'key' => '_pf_price',
            'value' => 1000000,
            'compare' => '>=',
            'type' => 'NUMERIC',
        ),
        array(
            'key' => '_pf_bedrooms',
            'value' => 3,
            'compare' => '>=',
        ),
    ),
));
```

## Best Practices

1. **Test API Connection** before importing
2. **Start with small batches** (10-20 listings)
3. **Monitor rate limits** (650 requests/minute)
4. **Use caching** for frequently accessed data
5. **Log errors** for debugging
6. **Use hooks** for customization
7. **Regular sync** to keep data updated
8. **Backup** before large imports

## Troubleshooting

### Import Fails
- Check API credentials
- Verify API connection
- Check rate limits
- Review error logs

### Missing Data
- Verify API response structure
- Check field mappings
- Review filter hooks

### Taxonomy Terms Not Created
- Check taxonomy registration
- Verify term slugs
- Check for duplicate terms

## Additional Resources

- [PropertyFinder API Documentation](https://api-docs.propertyfinder.net/enterprise-api/)
- [WordPress CPT Plugin Handbook](https://developer.wordpress.org/plugins/post-types/)
- [WordPress Hooks Reference](https://developer.wordpress.org/reference/hooks/)

