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
     * Log level
     */
    private static $log_level = 'debug';

    /**
     * Initialize logger
     */
    public static function init() {
        self::$log_file = WP_CONTENT_DIR . '/propertyfinder-debug.log';
        
        // Set log level from constant or option
        if (defined('PROPERTYFINDER_LOG_LEVEL')) {
            self::$log_level = PROPERTYFINDER_LOG_LEVEL;
        } else {
            self::$log_level = get_option('propertyfinder_log_level', 'error');
        }
    }

    /**
     * Log message
     *
     * @param string $level Log level
     * @param string $message Message to log
     * @param mixed $context Additional context
     */
    public static function log($level, $message, $context = null) {
        if (!self::should_log($level)) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $context_str = $context ? ' | Context: ' . print_r($context, true) : '';
        $log_message = sprintf(
            "[%s] [%s] PropertyFinder: %s%s\n",
            $timestamp,
            strtoupper($level),
            $message,
            $context_str
        );

        // Log to file
        error_log($log_message, 3, self::$log_file);

        // Also log to WordPress debug.log if WP_DEBUG is enabled
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('PropertyFinder: ' . $message . $context_str);
        }
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
     * Clear log file
     */
    public static function clear_log() {
        if (file_exists(self::$log_file)) {
            unlink(self::$log_file);
        }
    }

    /**
     * Get log content
     *
     * @param int $lines Number of lines to return
     * @return string
     */
    public static function get_log($lines = 100) {
        if (!file_exists(self::$log_file)) {
            return 'Log file does not exist.';
        }

        $content = file_get_contents(self::$log_file);
        $log_lines = explode("\n", $content);
        $last_lines = array_slice($log_lines, -$lines);

        return implode("\n", $last_lines);
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

