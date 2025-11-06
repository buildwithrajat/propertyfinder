<?php
/**
 * Logger class for debugging and error tracking
 *
 * @package PropertyFinder
 * @subpackage Includes
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Logger class
 */
class PropertyFinder_Logger {

    /**
     * Log file path
     */
    private static $log_file;

    /**
     * Log directory
     */
    private static $log_dir;

    /**
     * Log level
     */
    private static $log_level = 'debug';

    /**
     * Initialize logger
     */
    public static function init() {
        // Set log directory to plugin folder
        $plugin_dir = dirname(dirname(__FILE__));
        self::$log_dir = $plugin_dir . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
        
        // Normalize path for Windows
        self::$log_dir = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, self::$log_dir);
        
        // Create log directory if it doesn't exist
        if (!file_exists(self::$log_dir)) {
            $created = wp_mkdir_p(self::$log_dir);
            if (!$created) {
                // Fallback: use mkdir
                @mkdir(self::$log_dir, 0755, true);
            }
            // Add .htaccess to protect log directory
            $htaccess_file = self::$log_dir . '.htaccess';
            if (!file_exists($htaccess_file)) {
                @file_put_contents($htaccess_file, "deny from all\n");
            }
            // Add index.php to prevent directory listing
            $index_file = self::$log_dir . 'index.php';
            if (!file_exists($index_file)) {
                @file_put_contents($index_file, "<?php\n// Silence is golden.\n");
            }
        }
        
        // Verify directory is writable
        if (!is_writable(self::$log_dir)) {
            // Try to make it writable
            @chmod(self::$log_dir, 0755);
            // Try again
            if (!is_writable(self::$log_dir)) {
                @chmod(self::$log_dir, 0777);
            }
        }
        
        // Test write capability and create initial log entry
        $test_file = self::$log_dir . 'test-write-' . time() . '.tmp';
        $test_result = @file_put_contents($test_file, 'test');
        if ($test_result !== false) {
            @unlink($test_file);
        }
        
        // Create today's log file immediately with a test entry
        $today_log = self::$log_dir . 'propertyfinder-' . date('Y-m-d') . '.log';
        $init_message = '[' . date('Y-m-d H:i:s') . '] [INFO] PropertyFinder: Logger initialized | Context: Array ( [log_dir] => ' . self::$log_dir . ' [test_write] => ' . ($test_result !== false ? 'success' : 'failed') . ' )' . "\n";
        @file_put_contents($today_log, $init_message, FILE_APPEND);
        
        // Set daily log file based on current date (don't call get_daily_log_file to avoid circular dependency)
        $date = date('Y-m-d');
        self::$log_file = self::$log_dir . 'propertyfinder-' . $date . '.log';
        
        // Set log level from constant or option - default to 'info' to capture all import logs
        if (defined('PROPERTYFINDER_LOG_LEVEL')) {
            self::$log_level = PROPERTYFINDER_LOG_LEVEL;
        } else {
            // Get from option, default to 'info' if not set
            $saved_level = get_option('propertyfinder_log_level', 'info');
            self::$log_level = !empty($saved_level) ? $saved_level : 'info';
        }
        
        // Ensure logging is enabled by default (unless explicitly disabled)
        if (self::$log_level === 'disabled' || self::$log_level === 'off') {
            // If disabled, still allow error and warning logs
            self::$log_level = 'warning';
        }
    }

    /**
     * Get daily log file path
     * 
     * @param string $date Date in Y-m-d format (optional, defaults to today)
     * @return string Log file path
     */
    public static function get_daily_log_file($date = null) {
        // Ensure logger is initialized
        if (self::$log_dir === null) {
            self::init();
        }
        
        if ($date === null) {
            $date = date('Y-m-d');
        }
        $log_file = self::$log_dir . 'propertyfinder-' . $date . '.log';
        // Normalize path
        $log_file = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $log_file);
        return $log_file;
    }

    /**
     * Get log directory
     * 
     * @return string Log directory path
     */
    public static function get_log_dir() {
        // Ensure logger is initialized
        if (self::$log_dir === null) {
            self::init();
        }
        return self::$log_dir;
    }

    /**
     * Log message
     *
     * @param string $level Log level
     * @param string $message Message to log
     * @param mixed $context Additional context
     * @param string $module Module name (optional, e.g., 'sync', 'import', 'update', 'api', 'webhook')
     */
    public static function log($level, $message, $context = null, $module = null) {
        // Ensure logger is initialized
        if (self::$log_dir === null) {
            self::init();
        }
        
        // Always log if log level is set (don't skip if level check fails - log everything by default)
        // Only skip if explicitly disabled
        $log_level = get_option('propertyfinder_log_level', 'info');
        if ($log_level === 'disabled' || $log_level === 'off') {
            return;
        }
        
        // FORCE log for import module regardless of level
        if ($module === 'import') {
            // Always log import operations
        } else {
            // Check log level for other modules
            if (!self::should_log($level)) {
                return;
            }
        }

        // Get log file based on module
        $log_file = self::get_module_log_file($module);
        $general_log_file = self::get_daily_log_file();

        $timestamp = date('Y-m-d H:i:s');
        $context_str = $context ? ' | Context: ' . print_r($context, true) : '';
        $module_str = $module ? '[' . strtoupper($module) . '] ' : '';
        $log_message = sprintf(
            "[%s] [%s] PropertyFinder: %s%s%s\n",
            $timestamp,
            strtoupper($level),
            $module_str,
            $message,
            $context_str
        );

        // ALWAYS write to general log file (for debugging)
        $general_write_result = self::write_to_file($general_log_file, $log_message);
        
        // Also write to module-specific file if module is enabled
        if ($module && self::is_module_enabled($module) && $log_file !== $general_log_file) {
            self::write_to_file($log_file, $log_message);
        }
        
        // If general log write failed, log to WordPress debug.log as fallback
        if (!$general_write_result && defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[PropertyFinder Log Write Failed] ' . $message . ($context ? ' | Context: ' . print_r($context, true) : ''));
        }
    }

    /**
     * Get module-specific log file path
     * 
     * @param string $module Module name (e.g., 'sync', 'import', 'update', 'api', 'webhook')
     * @return string Log file path
     */
    public static function get_module_log_file($module = null) {
        // Ensure logger is initialized
        if (self::$log_dir === null) {
            self::init();
        }
        
        $date = date('Y-m-d');
        
        if ($module) {
            $module = sanitize_file_name(strtolower($module));
            
            // Check if this module is enabled for logging
            if (!self::is_module_enabled($module)) {
                // Return general log file if module is disabled
                $log_file = self::$log_dir . 'propertyfinder-' . $date . '.log';
            } else {
                $log_file = self::$log_dir . 'propertyfinder-' . $module . '-' . $date . '.log';
            }
        } else {
            $log_file = self::$log_dir . 'propertyfinder-' . $date . '.log';
        }
        
        // Normalize path for Windows
        $log_file = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $log_file);
        return $log_file;
    }

    /**
     * Check if a module is enabled for logging
     * 
     * @param string $module Module name
     * @return bool True if module is enabled
     */
    private static function is_module_enabled($module) {
        // Get enabled modules from option
        $enabled_modules = get_option('propertyfinder_log_modules', null);
        
        // If option doesn't exist (first time), default to enabling import, sync, and update
        if ($enabled_modules === null || $enabled_modules === false) {
            // Default enabled modules
            return in_array($module, array('import', 'sync', 'update'));
        }
        
        // If empty array, default to import module (most important)
        if (empty($enabled_modules) || !is_array($enabled_modules)) {
            return $module === 'import';
        }
        
        return in_array($module, $enabled_modules);
    }

    /**
     * Log sync operation
     */
    public static function sync($message, $context = null) {
        self::log('info', $message, $context, 'sync');
    }

    /**
     * Log import operation
     */
    public static function import($message, $context = null) {
        self::log('info', $message, $context, 'import');
    }

    /**
     * Log update operation
     */
    public static function update($message, $context = null) {
        self::log('info', $message, $context, 'update');
    }

    /**
     * Log API operation
     */
    public static function api($message, $context = null) {
        self::log('info', $message, $context, 'api');
    }

    /**
     * Log webhook operation
     */
    public static function webhook($message, $context = null) {
        self::log('info', $message, $context, 'webhook');
    }

    /**
     * Log location operation
     */
    public static function location($message, $context = null) {
        self::log('info', $message, $context, 'location');
    }

    /**
     * Log agent operation
     */
    public static function agent($message, $context = null) {
        self::log('info', $message, $context, 'agent');
    }

    /**
     * Custom function to write to log file
     * 
     * @param string $file_path Full path to log file
     * @param string $message Log message to write
     * @return bool True on success, false on failure
     */
    private static function write_to_file($file_path, $message) {
        // Ensure directory exists
        $dir = dirname($file_path);
        if (!file_exists($dir)) {
            $created = wp_mkdir_p($dir);
            if (!$created) {
                // Fallback: try to create with mkdir
                @mkdir($dir, 0755, true);
            }
        }
        
        // Create file if it doesn't exist - use file_put_contents which creates file automatically
        if (!file_exists($file_path)) {
            // Try to create empty file first
            $created = @file_put_contents($file_path, '');
            if ($created !== false) {
                @chmod($file_path, 0666); // More permissive for Windows
            }
        }
        
        // Check if directory is writable
        if (!is_writable($dir)) {
            // Try to make it writable
            @chmod($dir, 0755);
            // Try even more permissive
            if (!is_writable($dir)) {
                @chmod($dir, 0777);
            }
        }
        
        // Make file writable if it exists
        if (file_exists($file_path) && !is_writable($file_path)) {
            @chmod($file_path, 0666);
        }
        
        // Write to file using file_put_contents with locking
        $result = @file_put_contents($file_path, $message, FILE_APPEND | LOCK_EX);
        
        // If file_put_contents fails, try without LOCK_EX
        if ($result === false) {
            $result = @file_put_contents($file_path, $message, FILE_APPEND);
        }
        
        // If still fails, try with fopen/fwrite
        if ($result === false) {
            $handle = @fopen($file_path, 'a');
            if ($handle) {
                $result = @fwrite($handle, $message);
                @fclose($handle);
            }
        }
        
        // Last resort: try without append mode (creates new file)
        if ($result === false && !file_exists($file_path)) {
            $result = @file_put_contents($file_path, $message);
        }
        
        return $result !== false;
    }

    /**
     * Check if should log based on level
     *
     * @param string $level Log level
     * @return bool
     */
    private static function should_log($level) {
        $levels = array(
            'debug' => 0,
            'info' => 1,
            'warning' => 2,
            'error' => 3,
        );

        $current_level = isset($levels[self::$log_level]) ? $levels[self::$log_level] : 2;
        $message_level = isset($levels[$level]) ? $levels[$level] : 2;

        return $message_level >= $current_level;
    }

    /**
     * Log debug message
     */
    public static function debug($message, $context = null) {
        self::log('debug', $message, $context);
    }

    /**
     * Log info message
     */
    public static function info($message, $context = null) {
        self::log('info', $message, $context);
    }

    /**
     * Log warning message
     */
    public static function warning($message, $context = null) {
        self::log('warning', $message, $context);
    }

    /**
     * Log error message
     */
    public static function error($message, $context = null) {
        self::log('error', $message, $context);
    }

    /**
     * Log fatal error
     */
    public static function fatal($message, $context = null) {
        self::log('error', 'FATAL: ' . $message, $context);
        
        // Send admin email on fatal errors
        if (get_option('propertyfinder_notify_on_fatal', false)) {
            wp_mail(
                get_option('admin_email'),
                '[PropertyFinder] Fatal Error',
                $message . "\n\nContext: " . print_r($context, true)
            );
        }
    }

    /**
     * Clear current log file
     */
    public static function clear_log() {
        if (file_exists(self::$log_file)) {
            file_put_contents(self::$log_file, '');
        }
    }

    /**
     * Delete a specific log file
     * 
     * @param string $filename Log filename (e.g., 'propertyfinder-2025-01-27.log')
     * @return bool Success status
     */
    public static function delete_log_file($filename) {
        // Ensure logger is initialized
        if (self::$log_dir === null) {
            self::init();
        }
        
        // Security: Only allow deleting log files in the log directory
        $filename = basename($filename);
        if (strpos($filename, 'propertyfinder-') !== 0 || strpos($filename, '.log') === false) {
            return false;
        }
        
        $file_path = self::$log_dir . $filename;
        if (file_exists($file_path)) {
            return unlink($file_path);
        }
        return false;
    }

    /**
     * Delete all log files
     * 
     * @return int Number of files deleted
     */
    public static function delete_all_logs() {
        $files = self::get_log_files();
        $deleted = 0;
        
        foreach ($files as $file) {
            if (self::delete_log_file($file['filename'])) {
                $deleted++;
            }
        }
        
        return $deleted;
    }

    /**
     * Get all log files
     * 
     * @param string $module Filter by module (optional)
     * @return array Array of log file information
     */
    public static function get_log_files($module = null) {
        // Ensure logger is initialized
        if (self::$log_dir === null) {
            self::init();
        }
        
        $files = array();
        
        if (!is_dir(self::$log_dir)) {
            return $files;
        }
        
        $pattern = $module ? self::$log_dir . 'propertyfinder-' . sanitize_file_name($module) . '-*.log' : self::$log_dir . 'propertyfinder-*.log';
        $dir_files = glob($pattern);
        
        foreach ($dir_files as $file_path) {
            $filename = basename($file_path);
            $file_info = array(
                'filename' => $filename,
                'path' => $file_path,
                'size' => filesize($file_path),
                'size_formatted' => size_format(filesize($file_path)),
                'date' => date('Y-m-d', filemtime($file_path)),
                'modified' => filemtime($file_path),
                'modified_formatted' => date('Y-m-d H:i:s', filemtime($file_path)),
                'module' => null,
            );
            
            // Extract module and date from filename
            // Pattern: propertyfinder-[module]-YYYY-MM-DD.log or propertyfinder-YYYY-MM-DD.log
            if (preg_match('/propertyfinder-([a-z]+)-(\d{4}-\d{2}-\d{2})\.log/', $filename, $matches)) {
                $file_info['module'] = $matches[1];
                $file_info['log_date'] = $matches[2];
            } elseif (preg_match('/propertyfinder-(\d{4}-\d{2}-\d{2})\.log/', $filename, $matches)) {
                $file_info['log_date'] = $matches[1];
                $file_info['module'] = 'general';
            }
            
            $files[] = $file_info;
        }
        
        // Sort by date descending, then by module
        usort($files, function($a, $b) {
            $date_cmp = strcmp($b['log_date'] ?? '', $a['log_date'] ?? '');
            if ($date_cmp !== 0) {
                return $date_cmp;
            }
            return strcmp($a['module'] ?? '', $b['module'] ?? '');
        });
        
        return $files;
    }

    /**
     * Get available modules from log files
     * 
     * @return array Array of module names
     */
    public static function get_modules() {
        $files = self::get_log_files();
        $modules = array('general' => __('General', 'propertyfinder'));
        
        foreach ($files as $file) {
            if (!empty($file['module']) && $file['module'] !== 'general') {
                $module_name = ucfirst($file['module']);
                $modules[$file['module']] = $module_name;
            }
        }
        
        return array_unique($modules, SORT_REGULAR);
    }

    /**
     * Get log content for a specific file
     *
     * @param string $filename Log filename (optional, defaults to current log)
     * @param int $lines Number of lines to return (0 for all)
     * @return string
     */
    public static function get_log($filename = null, $lines = 100) {
        // Ensure logger is initialized
        if (self::$log_dir === null) {
            self::init();
        }
        
        $log_file = $filename ? self::$log_dir . basename($filename) : self::$log_file;
        
        if (!file_exists($log_file)) {
            return 'Log file does not exist.';
        }

        $content = file_get_contents($log_file);
        
        if ($lines > 0) {
            $log_lines = explode("\n", $content);
            $last_lines = array_slice($log_lines, -$lines);
            return implode("\n", $last_lines);
        }
        
        return $content;
    }

    /**
     * Get log file size
     * 
     * @param string $filename Log filename (optional)
     * @return int File size in bytes
     */
    public static function get_log_size($filename = null) {
        $log_file = $filename ? self::$log_dir . basename($filename) : self::$log_file;
        
        if (file_exists($log_file)) {
            return filesize($log_file);
        }
        
        return 0;
    }

    /**
     * Log activation
     */
    public static function log_activation() {
        self::info('Plugin activated', array(
            'version' => PROPERTYFINDER_VERSION,
            'php_version' => PHP_VERSION,
            'wp_version' => get_bloginfo('version'),
        ));
    }

    /**
     * Log deactivation
     */
    public static function log_deactivation() {
        self::info('Plugin deactivated');
    }

    /**
     * Log uninstall
     */
    public static function log_uninstall() {
        self::info('Plugin uninstalled');
    }
}

