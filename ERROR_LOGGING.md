# PropertyFinder Error Logging System

## Overview

A comprehensive error logging system has been added to the PropertyFinder plugin to help diagnose and fix issues during activation and runtime.

## Features

### 1. **Logger Class** (`includes/class-propertyfinder-logger.php`)
- 4 log levels: debug, info, warning, error
- Logs to file: `wp-content/propertyfinder-debug.log`
- Also logs to WordPress debug.log if WP_DEBUG is enabled
- Email notifications on fatal errors (optional)

### 2. **Activation Error Handling**
- Try-catch blocks around all critical operations
- Requirement checks (PHP 7.2+, WordPress 5.0+)
- Detailed error messages with stack traces
- User-friendly error page on activation failure
- Automatic deactivation on fatal errors

### 3. **Log Viewer** (Admin Page)
- Location: **PropertyFinder → Logs**
- View log file content
- Clear logs button
- Download log file
- Real-time error viewing

## Log Locations

1. **Debug Log**: `wp-content/propertyfinder-debug.log`
2. **Activation Error Log**: `wp-content/propertyfinder-activation-error.log`
3. **WordPress Debug Log**: `wp-content/debug.log` (if WP_DEBUG enabled)

## Usage

### In Code

```php
// Debug level
PropertyFinder_Logger::debug('Processing data', array('count' => 10));

// Info level
PropertyFinder_Logger::info('Import started');

// Warning level
PropertyFinder_Logger::warning('Rate limit approaching');

// Error level
PropertyFinder_Logger::error('Import failed', array('error' => $e->getMessage()));

// Fatal error
PropertyFinder_Logger::fatal('Critical failure', array('error' => $message));
```

### Log Levels

- **debug**: Detailed debugging information
- **info**: General information messages
- **warning**: Warnings that don't stop execution
- **error**: Errors that may affect functionality
- **fatal**: Critical errors that stop execution

### Configuration

Set log level in settings or via constant:
```php
define('PROPERTYFINDER_LOG_LEVEL', 'error'); // debug, info, warning, error
```

Enable fatal error email notifications:
```php
update_option('propertyfinder_notify_on_fatal', true);
```

## Activation Error Handling

On activation failure:
1. Error is logged to file
2. Error is logged to WordPress debug.log
3. User sees friendly error page with:
   - Error message
   - File path to error log
   - PHP version
   - WordPress version
4. Plugin is automatically deactivated

## Common Errors

### 1. PHP Version Too Low
**Error**: `PropertyFinder requires PHP 7.2 or higher`
**Fix**: Upgrade PHP to 7.2+

### 2. WordPress Version Too Low
**Error**: `PropertyFinder requires WordPress 5.0 or higher`
**Fix**: Upgrade WordPress to 5.0+

### 3. Database Table Creation Failed
**Error**: Failed to create database tables
**Fix**: Check database permissions

### 4. Memory Exhausted
**Error**: PHP memory limit exceeded
**Fix**: Increase PHP memory limit

## Debugging Activation Issues

1. **Check Logs**
   - Go to PropertyFinder → Logs
   - Or check: `wp-content/propertyfinder-debug.log`

2. **Check Error File**
   - Read: `wp-content/propertyfinder-activation-error.log`
   - Contains stack trace and error details

3. **Check WordPress Debug Log**
   - Enable: `define('WP_DEBUG', true);`
   - Check: `wp-content/debug.log`

4. **Enable Debug Mode**
   ```php
   // In wp-config.php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   ```

## Best Practices

1. **Enable Logging in Development**
   ```php
   define('PROPERTYFINDER_LOG_LEVEL', 'debug');
   ```

2. **Disable in Production**
   ```php
   define('PROPERTYFINDER_LOG_LEVEL', 'error');
   ```

3. **Regularly Check Logs**
   - Monitor log file size
   - Clear old logs
   - Investigate errors

4. **Use Appropriate Log Levels**
   - Use `debug` for development
   - Use `info` for important events
   - Use `warning` for potential issues
   - Use `error` for actual errors
   - Use `fatal` for critical failures

## Error Examples

### Activation Error
```
[2025-01-27 14:15:22] [ERROR] PropertyFinder: Plugin activation failed
Context: error: PropertyFinder requires PHP 7.2 or higher. Current version: 7.0.33
```

### API Error
```
[2025-01-27 14:15:22] [ERROR] PropertyFinder: API request failed
Context: endpoint: /listings, status: 401
```

### Database Error
```
[2025-01-27 14:15:22] [ERROR] PropertyFinder: Database query failed
Context: query: SELECT * FROM wp_pf_listings, error: Table doesn't exist
```

## Log File Format

```
[2025-01-27 14:15:22] [INFO] PropertyFinder: Plugin activated | Context: {version: "1.0.0"}
[2025-01-27 14:15:23] [DEBUG] PropertyFinder: Import started | Context: {count: 10}
[2025-01-27 14:15:24] [ERROR] PropertyFinder: API request failed | Context: {endpoint: "/listings", status: 429}
```

## Admin Interface

### Viewing Logs
1. Go to **PropertyFinder → Logs**
2. View log file content in the admin
3. Download log file
4. Clear logs

### Log Display
- Shows last 100 lines by default
- Scrollable view
- Code-formatted for readability
- Timestamps included

## Security

- Log files are NOT publicly accessible
- Only administrators can view logs
- Logs contain no sensitive data by default
- Configure log retention in settings

## Performance

- Logging has minimal performance impact
- Disabled log levels are not processed
- File writes are buffered
- Consider log rotation for large files

## Troubleshooting

### Log File Not Created
- Check directory permissions
- Check wp-content directory is writable
- Check PHP error_log setting

### Logs Not Appearing
- Check log level setting
- Check error_log PHP directive
- Check disk space

### Too Many Logs
- Adjust log level to 'error'
- Implement log rotation
- Clear logs regularly

## Integration Points

Logging is integrated into:
- Plugin activation
- Plugin deactivation
- API requests
- Import operations
- Database operations
- Admin actions

## Support

For help with errors:
1. Check the log files
2. Review error messages
3. Check WordPress debug log
4. Contact support with error details

