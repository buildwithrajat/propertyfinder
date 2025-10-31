# How to Enable WordPress Debug Mode

This guide will help you enable error logging for the PropertyFinder plugin.

## Quick Setup

### Step 1: Edit wp-config.php

Open your `wp-config.php` file (located in the WordPress root directory).

### Step 2: Add Debug Constants

Find these lines (they might already exist):

```php
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);
```

**Replace them with:**

```php
// Enable WordPress debug mode
define('WP_DEBUG', true);

// Log errors to debug.log file
define('WP_DEBUG_LOG', true);

// Don't show errors on the frontend (recommended)
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', false);
@ini_set('display_errors', 0);
```

### Step 3: Save and Refresh

1. Save the `wp-config.php` file
2. Refresh your WordPress admin panel
3. Go to **PropertyFinder → Logs** to view debug logs

## What Gets Logged

The plugin will log:

### API Connection
- Token request attempts
- HTTP status codes
- API endpoint URLs
- Success/failure messages

### Import Process
- Listing fetch attempts
- Items processed
- Created/updated listings
- Error messages with details

### Example Log Entries

```
[Date/Time] PropertyFinder: Testing connection with endpoint: https://atlas.propertyfinder.com/v1
[Date/Time] PropertyFinder: API Key: nxpEG.q0OMYGl9ABrVJu...
[Date/Time] PropertyFinder: Token obtained successfully from https://atlas.propertyfinder.com/v1/auth/token
[Date/Time] PropertyFinder: Access token obtained successfully. Expires in: 1800 seconds
[Date/Time] PropertyFinder: Fetching listings from API with params: Array(...)
[Date/Time] PropertyFinder: API request successful - Endpoint: https://atlas.propertyfinder.com/v1/listings, Status: 200
[Date/Time] PropertyFinder: Retrieved 50 listings from API
[Date/Time] PropertyFinder: Processing listing ID: abc123def456
[Date/Time] PropertyFinder: Creating new listing for ID: abc123def456
[Date/Time] PropertyFinder: Creating listing with title: Luxury Apartment in Dubai Marina
[Date/Time] PropertyFinder: Successfully created listing - Post ID: 123
```

## Error Messages with Status Codes

### Successful Connection
```
PropertyFinder: Token obtained successfully from https://atlas.propertyfinder.com/v1/auth/token
PropertyFinder: API request successful - Endpoint: https://atlas.propertyfinder.com/v1/listings?page=1&perPage=50, Status: 200
```

### Authentication Error (401)
```
PropertyFinder: Token Request Failed - Status Code: 401, Response: {"title":"Unauthorized","detail":"Invalid credentials","type":"AUTHENTICATION"}
```

### Forbidden Error (403)
```
PropertyFinder: API Request Failed - Endpoint: https://atlas.propertyfinder.com/v1/listings, Status Code: 403, Response: {"title":"Forbidden","detail":"Access denied"}
```

### Rate Limit (429)
```
PropertyFinder: Rate limit exceeded for endpoint: https://atlas.propertyfinder.com/v1/listings
```

### Bad Request (400)
```
PropertyFinder: API Request Failed - Status Code: 400, Response: {"title":"Bad Request","errors":[{"detail":"Invalid parameter value","pointer":"/page"}]}
```

## Viewing Logs

### Method 1: Admin Panel (Recommended)
1. Go to **WordPress Admin → PropertyFinder → Logs**
2. View recent PropertyFinder log entries
3. See API configuration status
4. Test connection directly from logs page

### Method 2: Debug Log File
1. Open `wp-content/debug.log` in a text editor
2. Search for "PropertyFinder" entries
3. Look for error messages and status codes

### Method 3: Terminal/SSH
```bash
# View last 50 PropertyFinder log entries
tail -n 100 wp-content/debug.log | grep PropertyFinder

# Follow live logs
tail -f wp-content/debug.log | grep PropertyFinder
```

## Troubleshooting Common Issues

### Issue: No logs appear

**Solution:**
1. Check `wp-config.php` has `WP_DEBUG_LOG` set to `true`
2. Verify file permissions on `wp-content/` directory
3. Check disk space availability
4. Try clicking "Test Connection" to generate logs

### Issue: Connection fails with 401

**Solution:**
Check credentials:
1. Go to **PropertyFinder → Settings**
2. Verify API Key and Secret are correct
3. Click "Save Settings"
4. Click "Test Connection"

### Issue: Import returns no listings

**Solution:**
Check logs for API response:
1. Go to **PropertyFinder → Logs**
2. Look for "Retrieved X listings from API"
3. If 0 listings, check API response for pagination info
4. Verify `filter[state]` parameter if using filters

### Issue: HTTP 429 Rate Limit

**Solution:**
1. Wait 1 minute before retrying
2. Reduce import batch size (perPage parameter)
3. Implement request throttling

## Clearing Logs

### Via Admin Panel
1. Go to **PropertyFinder → Logs**
2. Click "Clear Logs" button
3. Confirm action

### Via File
```bash
# Delete debug log
rm wp-content/debug.log

# Or empty the file
echo '' > wp-content/debug.log
```

## Disabling Debug Mode (After Testing)

When done debugging, **turn off debug mode** for security:

```php
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);
```

## Next Steps

1. **Enable Debug Mode** (add to wp-config.php)
2. **Save Settings** (PropertyFinder → Settings)
3. **Test Connection** (Settings page)
4. **View Logs** (PropertyFinder → Logs)
5. **Import Listings** (Settings page)

All errors with HTTP status codes will now be logged and visible!

