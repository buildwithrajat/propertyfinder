# PropertyFinder API Setup Guide

## Overview
This guide will help you set up and configure the PropertyFinder WordPress plugin with your API credentials.

## API Credentials

The plugin has been pre-configured with the following API credentials:

### API Key
```
nxpEG.q0OMYGl9ABrVJuMgHflOctxjR6dO3GkD2W
```

### API Secret
```
y6Qf5mbr0JQbWzO0HsVnCX752FdqovCJ
```

### API Endpoint
```
https://atlas.propertyfinder.com/v1
```

## Plugin Setup Instructions

### 1. Install and Activate the Plugin
1. Upload the plugin to `/wp-content/plugins/propertyfinder/`
2. Activate the plugin through the WordPress Plugins menu
3. The plugin will automatically create necessary database tables and default settings

### 2. Configure API Settings

#### Access Settings Page
1. Go to **WordPress Admin → PropertyFinder → Settings**
2. The API credentials are pre-filled with the values above
3. Click **Save Settings** to store the credentials in WordPress options

#### Verify Settings
- The settings are saved in `wp_options` table with these keys:
  - `propertyfinder_api_key`
  - `propertyfinder_api_secret`
  - `propertyfinder_api_endpoint`

### 3. Test API Connection

#### Test Connection Button
1. On the Settings page, click **Test Connection**
2. This will:
   - Connect to PropertyFinder API
   - Request an access token
   - Verify credentials are valid
   - Display success/error message

#### Expected Response
- **Success**: "Connection successful! Access token obtained."
- **Failure**: "Connection failed. Please check your API credentials."

### 4. Import Listings

#### Import First Page
1. Click **Import Listings Now** button
2. This imports the first 50 listings (page 1)
3. Listings are created as Custom Post Types (`pf_listing`)
4. Progress and results are displayed

#### Import All Pages
1. Click **Sync All Pages** button
2. This imports ALL listings from all pages
3. May take several minutes depending on total listings
4. Shows import progress and final results

### 5. View and Manage Listings

#### All Listings
- Go to **PropertyFinder → Listings**
- Shows statistics: Total, Published, Draft
- Displays recent listings with details
- Click "Manage All Listings" to open WordPress Posts editor

#### WordPress Posts Editor
- Listing CPT appears in WordPress admin menu
- Can edit, delete, or update listings manually
- Custom fields store API data

## Custom Post Type Details

### Post Type: `pf_listing`

#### Features
- Publicly queryable
- Supports: Title, Editor, Thumbnail, Excerpt, Custom Fields
- Has archive page at `/listing`
- REST API enabled (`show_in_rest: true`)

#### Taxonomies
- **pf_category** - Property Category
- **pf_property_type** - Property Type
- **pf_amenity** - Amenities
- **pf_location** - Location/Community
- **pf_transaction_type** - Sale/Rent
- **pf_furnishing_status** - Furnishing Status

### Custom Fields (Post Meta)

#### Basic Info
- `_pf_api_id` - Unique API listing ID
- `_pf_reference` - Reference number
- `_pf_category` - Category
- `_pf_type` - Property type

#### Multilingual Content
- `_pf_title_en` - English title
- `_pf_title_ar` - Arabic title
- `_pf_description_en` - English description
- `_pf_description_ar` - Arabic description

#### Property Details
- `_pf_bedrooms` - Number of bedrooms
- `_pf_bathrooms` - Number of bathrooms
- `_pf_size` - Property size
- `_pf_floor_number` - Floor number
- `_pf_unit_number` - Unit number
- `_pf_plot_size` - Plot size
- `_pf_parking_slots` - Parking spaces

#### Financial
- `_pf_price_type` - Price type
- `_pf_price_sale` - Sale price
- `_pf_price_monthly` - Monthly rent
- `_pf_price_yearly` - Yearly rent
- `_pf_price_structure` - Full price structure (serialized JSON)

#### Location
- `_pf_location_id` - Location ID
- `_pf_uae_emirate` - UAE emirate
- `_pf_street_direction` - Street direction
- `_pf_street_width` - Street width

#### Features
- `_pf_has_garden` - Has garden (yes/no)
- `_pf_has_kitchen` - Has kitchen (yes/no)
- `_pf_has_parking` - Has parking (yes/no)

#### Media
- `_pf_media_images` - Property images (serialized JSON)
- `_pf_media_videos` - Property videos (serialized JSON)

#### Status
- `_pf_last_synced` - Last sync timestamp
- `_pf_project_status` - Project status
- `_pf_available_from` - Available from date
- `_pf_age` - Property age

#### Furnishing & Compliance
- `_pf_furnishing_type` - Furnishing type
- `_pf_finishing_type` - Finishing type
- `_pf_compliance_type` - Compliance type
- `_pf_compliance_number` - Compliance number

## Database Tables

### Table: `wp_propertyfinder_properties`
Stores imported property data for quick access.

Columns:
- `id` - Primary key
- `property_id` - API property ID (unique)
- `title` - Property title
- `data` - Serialized property data
- `status` - Status (active/inactive)
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

## API Integration

### Authentication
1. Plugin requests access token from `/auth/token` endpoint
2. Sends API key and secret
3. Receives access token with expiration
4. Token cached in WordPress transients for 30 minutes

### API Endpoints Used

#### Authentication
```
POST /auth/token
Body: {
  "apiKey": "your-api-key",
  "apiSecret": "your-api-secret"
}
```

#### Get Listings
```
GET /listings?page=1&perPage=50&status=published
Headers: {
  "Authorization": "Bearer {access_token}"
}
```

#### Get Single Listing
```
GET /listings/{listing_id}
Headers: {
  "Authorization": "Bearer {access_token}"
}
```

## Troubleshooting

### Connection Failed
1. Verify API credentials are correct
2. Check API endpoint URL
3. Verify server can make HTTPS requests
4. Check PHP `curl` extension is enabled
5. Review WordPress debug log

### Import Failed
1. Check API connection first
2. Verify WordPress can create posts
3. Check user permissions (manage_options)
4. Review browser console for JavaScript errors
5. Check server error logs

### Listings Not Appearing
1. Verify CPT is registered (`pf_listing`)
2. Check posts exist in database
3. Flush rewrite rules (Settings → Permalinks)
4. Clear any caching plugins

### Token Expired
- Tokens expire after 30 minutes
- Plugin automatically refreshes token
- If issues persist, click "Test Connection"

## Advanced Configuration

### Custom Import Parameters
Edit `app/Controllers/AdminController.php` in `handle_sync_ajax()` method:

```php
$params = array(
    'page' => 1,
    'perPage' => 50,
    'status' => 'published' // or 'unpublished', 'draft'
);
```

### Auto Sync
Enable automatic syncing:

```php
// In WordPress options
update_option('propertyfinder_auto_sync_enabled', true);
update_option('propertyfinder_sync_interval', 3600); // 1 hour
```

### Sync Interval
WordPress Cron scheduled event: `propertyfinder_sync_listings`

View/Edit in: `wp_options` → `cron` option

## Security Notes

1. **API Credentials**: Stored in WordPress options database
2. **Access Token**: Cached in WordPress transients (30 min TTL)
3. **Permissions**: Requires `manage_options` capability
4. **Nonces**: All AJAX requests use WordPress nonces
5. **Sanitization**: All user input is sanitized
6. **SQL Injection**: Uses `$wpdb->prepare()` for all queries

## Support

For issues or questions:
1. Check WordPress debug log: `wp-content/debug.log`
2. Check PropertyFinder plugin logs in admin
3. Review error messages on Settings page
4. Test API connection to isolate issues

## Additional Resources

- PropertyFinder Developer Documentation
- WordPress Plugin Development Best Practices
- Custom Post Type API Reference

