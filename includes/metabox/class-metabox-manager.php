<?php
/**
 * Metabox Manager - Main coordinator for all metabox functionality
 *
 * This class coordinates all property metabox functionality:
 * - Property Metabox (UI and data display)
 * - AJAX Handlers (API interactions)
 * - API Sync Handler (sync to PropertyFinder API)
 *
 * @package PropertyFinder
 * @subpackage Metabox
 */

if (!defined('WPINC')) {
    die;
}

// Load all metabox components
require_once dirname(__FILE__) . '/class-property-metabox.php';
require_once dirname(__FILE__) . '/handlers/class-ajax-handlers.php';
require_once dirname(__FILE__) . '/handlers/class-api-sync.php';

/**
 * Metabox Manager Class
 * 
 * How it works:
 * 1. Initializes AJAX handlers (for API interactions like fetch, sync)
 * 2. Initializes Property Metabox (for UI display and data saving)
 * 
 * Flow:
 * propertyfinder.php → init_hooks() → PropertyFinder_Admin_Hooks → new PropertyFinder_Metabox()
 *                                                                     ↓
 *                                    ┌────────────────────────────────┴────────────────────────────┐
 *                                    ↓                                                              ↓
 *                      PropertyFinder_Property_Ajax_Handlers              PropertyFinder_Property_Metabox
 *                      (Handles AJAX requests)                          (Displays metabox UI)
 *                                    ↓                                                              ↓
 *                      - Fetch from API                                 - Render property data
 *                      - Download images                                - Save property data
 *                      - Download gallery                                - Agent assignment
 *                      - View JSON                                      - Enqueue scripts
 */
class PropertyFinder_Metabox {

    /**
     * Property metabox instance (UI and data saving)
     */
    private $property_metabox;

    /**
     * AJAX handlers instance (API interactions)
     */
    private $ajax_handlers;

    /**
     * Constructor
     * Initializes all metabox components
     */
    public function __construct() {
        // Initialize AJAX handlers first (register their hooks in constructor)
        $this->ajax_handlers = new PropertyFinder_Property_Ajax_Handlers();
        
        // Initialize property metabox (registers metabox UI hooks)
        $this->property_metabox = new PropertyFinder_Property_Metabox();
    }
}

