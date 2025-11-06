# PropertyFinder Global Configuration Guide

## Overview

All plugin settings, constants, and configuration values are now centralized in `includes/config.php`. This makes it easy to manage everything from one place.

## Configuration File

**Location**: `includes/config.php`

The `PropertyFinder_Config` class provides:

1. **Centralized Configuration** - All settings in one place
2. **Helper Methods** - Easy access to common values
3. **Type Safety** - Consistent data types across the plugin
4. **Easy Maintenance** - Change once, affects everywhere

## Using the Config

### Basic Usage

```php
// Get any config value
$api_endpoint = PropertyFinder_Config::get('api_endpoint');

// Get with default value
$cpt_name = PropertyFinder_Config::get('cpt_name', 'pf_listing');

// Use helper functions
$cpt_name = propertyfinder_get_cpt_name();
$taxonomy = propertyfinder_get_taxonomy('category');
$meta_field = propertyfinder_get_meta_field('api_id');
```

### Available Methods

#### Get Configuration Values
```php
PropertyFinder_Config::get($key, $default = null)
PropertyFinder_Config::get_all()
```

#### Specific Getters
```php
PropertyFinder_Config::get_cpt_name()
PropertyFinder_Config::get_api_endpoint()
PropertyFinder_Config::get_api_key()
PropertyFinder_Config::get_api_secret()
PropertyFinder_Config::get_taxonomy($type)
PropertyFinder_Config::get_meta_field($field)
PropertyFinder_Config::get_webhook_url($use_rest = true)
PropertyFinder_Config::get_option_name($option)
```

#### Helper Functions
```php
propertyfinder_config($key, $default = null)
propertyfinder_get_cpt_name()
propertyfinder_get_taxonomy($type)
propertyfinder_get_meta_field($field)
```

## Configuration Keys

### Custom Post Type
- `cpt_name` - Post type name (default: `pf_listing`)
- `cpt_singular` - Singular label
- `cpt_plural` - Plural label
- `cpt_slug` - URL slug

### Taxonomies
- `taxonomy_category` - Category taxonomy name
- `taxonomy_property_type` - Property type taxonomy
- `taxonomy_amenity` - Amenity taxonomy
- `taxonomy_location` - Location taxonomy
- `taxonomy_transaction_type` - Transaction type taxonomy
- `taxonomy_furnishing_status` - Furnishing status taxonomy

### API Settings
- `api_endpoint` - API base URL (from options)
- `api_key` - API key (from options)
- `api_secret` - API secret (from options)

### Webhook Settings
- `webhook_secret` - HMAC secret (from options)
- `webhook_url` - REST API webhook URL
- `webhook_url_alt` - Alternative webhook URL

### Sync Settings
- `sync_enabled` - Enable automatic sync (from options)
- `sync_interval` - Sync interval (from options)
- `sync_time` - Daily sync time (from options)
- `sync_cron_hook` - WordPress cron hook name

### Meta Fields
- `meta_prefix` - Prefix for all meta fields (default: `_pf_`)

### Database
- `db_table` - Database table name

### Cron Intervals
- `cron_interval_4hours` - 4-hour interval name
- `cron_interval_6hours` - 6-hour interval name

### Option Names
All WordPress option names are available:
- `option_api_key`
- `option_api_secret`
- `option_api_endpoint`
- `option_webhook_secret`
- `option_sync_enabled`
- `option_sync_interval`
- `option_sync_time`

## Changing Default Values

To change default values, edit `includes/config.php`:

```php
public static function get_all() {
    return array(
        'cpt_name' => 'your_custom_cpt', // Change here
        'meta_prefix' => '_custom_',     // Change prefix
        // ... etc
    );
}
```

## Dynamic Configuration

Some config values are loaded from WordPress options (like API credentials). These are read dynamically:

```php
'api_endpoint' => get_option('propertyfinder_api_endpoint', 'https://atlas.propertyfinder.com/v1'),
'api_key' => get_option('propertyfinder_api_key', ''),
```

## Examples

### Change CPT Name Globally

```php
// In config.php, change:
'cpt_name' => 'property_listing', // instead of 'pf_listing'
```

### Change Meta Field Prefix

```php
// In config.php, change:
'meta_prefix' => '_prop_', // instead of '_pf_'
```

### Access Configuration Anywhere

```php
// Get API endpoint
$endpoint = PropertyFinder_Config::get_api_endpoint();

// Get taxonomy name
$taxonomy = PropertyFinder_Config::get_taxonomy('category');

// Get meta field name
$meta_key = PropertyFinder_Config::get_meta_field('api_id');
// Returns: '_pf_api_id'
```

## Benefits

1. **Single Source of Truth** - All configuration in one file
2. **Easy Refactoring** - Change names without searching codebase
3. **Type Safety** - Consistent return types
4. **Maintainability** - Update once, affects everywhere
5. **Documentation** - Clear list of all available settings

## Migration Notes

All hardcoded values have been replaced with config calls:
- `'pf_listing'` → `PropertyFinder_Config::get_cpt_name()`
- `'pf_category'` → `PropertyFinder_Config::get_taxonomy('category')`
- `'_pf_api_id'` → `PropertyFinder_Config::get_meta_field('api_id')`
- `'propertyfinder_sync_listings'` → `PropertyFinder_Config::get('sync_cron_hook')`

