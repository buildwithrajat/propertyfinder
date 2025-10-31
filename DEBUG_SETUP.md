# Enable WordPress Debug Mode

To debug the PropertyFinder plugin, enable WordPress debug mode by adding these lines to your `wp-config.php` file:

## Enable WordPress Debug

Add or modify these lines in `wp-config.php`:

```php
// Enable WP_DEBUG
define('WP_DEBUG', true);

// Enable debug logging to file
define('WP_DEBUG_LOG', true);

// Disable display of errors on frontend (optional)
define('WP_DEBUG_DISPLAY', false);

// Hide errors from visitors (recommended)
@ini_set('display_errors', 0);

// Log errors to file
@ini_set('log_errors', 1);
```

## Debug Log Location

After enabling debug mode, WordPress will log errors to:
```
wp-content/debug.log
```

## PropertyFinder Logging

The plugin logs all API interactions to the WordPress debug log. You'll see entries like:

```
PropertyFinder: Testing connection with endpoint: https://atlas.propertyfinder.com/v1
PropertyFinder: Token obtained successfully
PropertyFinder: Fetching listings from API with params: Array(...)
PropertyFinder: Retrieved 50 listings from API
PropertyFinder: Processing listing ID: abc123
PropertyFinder: Creating new listing for ID: abc123
PropertyFinder: Successfully created listing - Post ID: 123
```

## Error Examples

You'll see errors with HTTP status codes:

```
PropertyFinder: Token Request Failed - Status Code: 401, Response: {"detail":"Unauthorized"}
PropertyFinder: API Request Failed - Endpoint: https://atlas.propertyfinder.com/v1/listings, Status Code: 403, Response: {...}
```

## Viewing Logs

1. **Via Admin Panel**: WordPress Admin → PropertyFinder → Logs
2. **Via File**: Open `wp-content/debug.log` in a text editor
3. **Via Terminal**: `tail -f wp-content/debug.log`

## Clearing Logs

1. **Via Admin**: PropertyFinder → Logs → Clear Logs button
2. **Via File**: Delete or empty `wp-content/debug.log`

## Common Error Status Codes

| Code | Meaning | Solution |
|------|---------|----------|
| 200 | Success | Everything working |
| 400 | Bad Request | Check request parameters |
| 401 | Unauthorized | Invalid API credentials |
| 403 | Forbidden | Check API permissions |
| 404 | Not Found | Invalid endpoint URL |
| 422 | Validation Error | Check request data format |
| 429 | Rate Limit | Too many requests, wait and retry |
| 500 | Server Error | PropertyFinder API issue |
| 502 | Bad Gateway | Network/server issue |

## Testing

After enabling debug mode:

1. Save `wp-config.php`
2. Go to WordPress Admin → PropertyFinder → Settings
3. Click "Save Settings"
4. Click "Test Connection"
5. Check the Logs page for detailed error information

All API calls, errors, and status codes will be logged for debugging.

