# PropertyFinder Webhook Integration

## Overview

The PropertyFinder plugin now includes full webhook integration to automatically sync property listings when they are created, updated, or deleted in PropertyFinder.

## Features

### 1. Webhook Handler
- Automatically receives webhook events from PropertyFinder
- Supports both REST API and custom endpoint routes
- HMAC signature verification for security
- Handles `listing.published` and `listing.unpublished` events

### 2. Agent Assignment
- Stores agent/public profile information when properties are imported
- Includes `assignedTo`, `createdBy`, and `updatedBy` fields
- Helper functions available to retrieve agent data

### 3. Amenities & Categories
- Automatically extracts and stores amenities and categories
- Available via taxonomy terms and meta fields
- Helper functions for easy retrieval

## Webhook Endpoints

### REST API Endpoint (Recommended)
```
POST /wp-json/propertyfinder/v1/webhook
```

### Custom Rewrite Endpoint
```
POST /propertyfinder-webhook
```

Both endpoints accept the same webhook payload format from PropertyFinder.

## Webhook Events Supported

### listing.published
When a listing is published in PropertyFinder:
- Fetches full listing data from API
- Creates or updates WordPress post
- Updates all meta fields and taxonomies
- Fires `propertyfinder_webhook_listing_published` action

### listing.unpublished
When a listing is unpublished in PropertyFinder:
- Finds corresponding WordPress post
- Updates post status to draft (or trash based on settings)
- Fires `propertyfinder_webhook_listing_unpublished` action

## Configuration

### Setting Webhook Secret
```php
update_option('propertyfinder_webhook_secret', 'your-secret-key');
```

### Setting Unpublish Action
```php
// Options: 'draft' or 'trash'
update_option('propertyfinder_unpublish_action', 'draft');
```

## Subscription Management

### Subscribe to Webhook Events
```php
$webhook = new PropertyFinder_Webhook();
$callback_url = home_url('/wp-json/propertyfinder/v1/webhook');
$secret = 'your-hmac-secret';

$events = array('listing.published', 'listing.unpublished');
$results = $webhook->subscribe_to_events($events, $callback_url, $secret);
```

### Get Subscribed Webhooks
```php
$api = new PropertyFinder_API();
$webhooks = $api->get_webhooks();
```

### Unsubscribe from Event
```php
$api = new PropertyFinder_API();
$api->delete_webhook('listing.published');
```

## Helper Functions

### Get Amenities
```php
// Get amenities for a property
$amenities = propertyfinder_get_amenities($post_id);
// Returns: array('terms' => array(), 'meta' => array())

// Get all amenities in system
$all_amenities = propertyfinder_get_all_amenities();
```

### Get Categories
```php
// Get categories for a property
$categories = propertyfinder_get_categories($post_id);

// Get all categories
$all_categories = propertyfinder_get_all_categories();
```

### Get Assigned Agent
```php
// Get agent assigned to property
$agent = propertyfinder_get_assigned_agent($post_id);
// Returns: array('id' => 123, 'name' => 'John Doe', 'data' => array()) or false
```

### Get Agents from API
```php
// Get all agents/public profiles
$agents = propertyfinder_get_agents(array('perPage' => 100));
```

## Webhook Payload Format

PropertyFinder sends webhooks in this format:

```json
{
  "id": "554f3eaf-814a-4068-80b8-7beaaedb7194",
  "type": "listing.published",
  "timestamp": "2025-01-01T00:00:00Z",
  "entity": {
    "id": "01K0YB4HEKM08V901DVJ5ATVYF",
    "type": "listing"
  },
  "payload": {}
}
```

## Security

### HMAC Signature Verification
The webhook handler verifies HMAC-SHA256 signatures if a secret is configured:
- Header: `X-Signature`
- Algorithm: HMAC-SHA256
- Secret: Configured in `propertyfinder_webhook_secret` option

If no secret is configured, verification is skipped (useful for testing).

## Actions & Filters

### Actions
```php
// Fired when listing is published via webhook
do_action('propertyfinder_webhook_listing_published', $listing_id, $listing_data);

// Fired when listing is unpublished via webhook
do_action('propertyfinder_webhook_listing_unpublished', $listing_id, $post_id);
```

### Filters
```php
// Filter webhook payload before processing
apply_filters('propertyfinder_webhook_payload', $payload);
```

## Meta Fields for Agents

The following meta fields are stored for agent information:

- `_pf_assigned_to_id` - Public profile ID of assigned agent
- `_pf_assigned_to_name` - Name of assigned agent
- `_pf_assigned_to_data` - Full assigned agent data (serialized)
- `_pf_created_by_id` - Public profile ID who created listing
- `_pf_created_by_name` - Name of creator
- `_pf_updated_by_id` - Public profile ID who updated listing
- `_pf_updated_by_name` - Name of updater

## Example Usage

### Subscribe to Webhooks on Plugin Activation
```php
add_action('propertyfinder_activated', function() {
    $webhook = new PropertyFinder_Webhook();
    $callback_url = home_url('/wp-json/propertyfinder/v1/webhook');
    
    $events = array(
        'listing.published',
        'listing.unpublished'
    );
    
    $secret = wp_generate_password(32, false);
    update_option('propertyfinder_webhook_secret', $secret);
    
    $webhook->subscribe_to_events($events, $callback_url, $secret);
});
```

### Display Agent Information
```php
$agent = propertyfinder_get_assigned_agent($post_id);
if ($agent) {
    echo 'Assigned Agent: ' . esc_html($agent['name']);
    echo 'Agent ID: ' . esc_html($agent['id']);
}
```

### Display Amenities
```php
$amenities = propertyfinder_get_amenities($post_id);
if (!empty($amenities['meta'])) {
    foreach ($amenities['meta'] as $amenity) {
        echo esc_html($amenity) . ', ';
    }
}
```


