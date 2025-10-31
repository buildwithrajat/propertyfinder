# PropertyFinder Plugin - Quick Start Guide

## Your API Credentials

```
API Key: nxpEG.q0OMYGl9ABrVJuMgHflOctxjR6dO3GkD2W
API Secret: y6Qf5mbr0JQbWzO0HsVnCX752FdqovCJ
API Endpoint: https://atlas.propertyfinder.com/v1
```

## Step-by-Step Setup

### 1. Enable Debug Mode (Important!)

**Edit `wp-config.php`** in your WordPress root directory:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### 2. Activate Plugin

Go to: **WordPress Admin → Plugins → Activate "PropertyFinder CRM Integration"**

### 3. Save Settings

Go to: **WordPress Admin → PropertyFinder → Settings**

1. Verify API credentials are pre-filled
2. Click **"Save Settings"** button
3. Wait for success message: "Settings saved successfully!"

### 4. Test Connection

1. Click **"Test Connection"** button
2. Expected result: "Connection successful! Access token obtained."
3. If failed: Check error message for status code

### 5. View Logs

Go to: **WordPress Admin → PropertyFinder → Logs**

You'll see:
- API configuration status
- Recent log entries with status codes
- Test connection button

**Example logs:**
```
PropertyFinder: Testing connection with endpoint: https://atlas.propertyfinder.com/v1
PropertyFinder: Token obtained successfully
PropertyFinder: API request successful - Endpoint: https://atlas.propertyfinder.com/v1/listings, Status: 200
PropertyFinder: Retrieved 50 listings from API
```

### 6. Import Listings

1. Click **"Import Listings Now"** button
2. Confirm: "Are you sure you want to import listings now?"
3. Wait for: "Import completed: X imported, X updated, X skipped"

### 7. View Imported Listings

Go to: **WordPress Admin → PropertyFinder → Listings**

Or: **WordPress Admin → Posts → pf_listing**

## Error Status Codes Reference

| Code | Status | Meaning | Solution |
|------|--------|---------|----------|
| 200 | ✅ Success | Request successful | Continue |
| 400 | ❌ Bad Request | Invalid parameters | Check request data |
| 401 | ❌ Unauthorized | Invalid API credentials | Check API key/secret |
| 403 | ❌ Forbidden | Insufficient permissions | Check API access |
| 404 | ❌ Not Found | Invalid endpoint | Check endpoint URL |
| 422 | ❌ Validation Error | Invalid data format | Check API spec |
| 429 | ⚠️ Rate Limit | Too many requests | Wait 1 minute |
| 500 | ❌ Server Error | API server issue | Contact PropertyFinder |
| 502 | ❌ Bad Gateway | Network issue | Check connection |

## Common Issues & Solutions

### Settings Not Saving

**Problem:** "Settings saved successfully!" message doesn't appear

**Solution:**
1. Check browser console for JavaScript errors
2. Verify you're logged in as Administrator
3. Check WordPress debug log

### Connection Test Fails (401)

**Problem:** "Connection failed. Please check your API credentials."

**Solution:**
1. Verify API key and secret are correct
2. Check for extra spaces in credentials
3. Save settings again
4. Check logs for detailed error

**Check logs:**
```bash
tail -f wp-content/debug.log | grep PropertyFinder
```

### No Listings Imported (0 imported)

**Problem:** Import says "0 imported, 0 updated"

**Possible Causes:**
1. No listings in your account
2. API credentials invalid
3. Listings are in draft/archived state

**Solution:**
1. Check PropertyFinder dashboard for listings
2. Try different import parameters
3. Check logs for API response

### Import Shows Errors

**Problem:** "Import failed" message

**Check logs for:**
```
PropertyFinder: API Request Failed - Status Code: 403
PropertyFinder: Failed to fetch listings from API
```

**Solution:**
1. Check API credentials are correct
2. Verify API has proper permissions
3. Check rate limits
4. Contact PropertyFinder support

## Viewing Detailed Logs

### In Admin Panel
1. Go to **PropertyFinder → Logs**
2. See real-time log entries
3. API configuration status
4. Test connection from logs page

### In Debug Log File
Open `wp-content/debug.log` and search for "PropertyFinder"

### Via Terminal
```bash
# View PropertyFinder logs
grep PropertyFinder wp-content/debug.log

# Follow live logs
tail -f wp-content/debug.log | grep PropertyFinder

# Last 50 lines
tail -n 50 wp-content/debug.log | grep PropertyFinder
```

## Plugin Features

✅ Settings save working  
✅ Test connection with status codes  
✅ Import listings from API  
✅ View logs with error details  
✅ Real-time import progress  
✅ Success/error notifications  

## Next Actions

1. **Enable debug mode** in wp-config.php
2. **Save settings** (credentials pre-filled)
3. **Test connection** (verify API works)
4. **Import listings** (fetch properties)
5. **View logs** (monitor progress)

All errors with HTTP status codes will be logged!

