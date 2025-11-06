<?php
/**
 * CPT Manager - Main coordinator for all CPT functionality
 *
 * @package PropertyFinder
 * @subpackage CPT
 */

if (!defined('WPINC')) {
    die;
}

// Load all CPT module files
require_once dirname(__FILE__) . '/class-property-cpt.php';
require_once dirname(__FILE__) . '/class-agent-cpt.php';
require_once dirname(__FILE__) . '/class-taxonomies.php';
require_once dirname(__FILE__) . '/class-property-columns.php';
require_once dirname(__FILE__) . '/class-agent-columns.php';
require_once dirname(__FILE__) . '/class-agent-template.php';
require_once dirname(__FILE__) . '/class-property-template.php';
require_once dirname(__FILE__) . '/class-cleanup.php';

/**
 * CPT Manager Class
 * 
 * Coordinates all CPT and taxonomy functionality
 */
class PropertyFinder_CPT {

    /**
     * Property CPT instance
     */
    private $property_cpt;

    /**
     * Agent CPT instance
     */
    private $agent_cpt;

    /**
     * Taxonomies instance
     */
    private $taxonomies;

    /**
     * Property columns instance
     */
    private $property_columns;

    /**
     * Agent columns instance
     */
    private $agent_columns;

    /**
     * Agent template instance
     */
    private $agent_template;

    /**
     * Property template instance
     */
    private $property_template;

    /**
     * Cleanup instance
     */
    private $cleanup;

    /**
     * Constructor
     */
    public function __construct() {
        // Initialize components
        $this->property_cpt = new PropertyFinder_Property_CPT();
        $this->agent_cpt = new PropertyFinder_Agent_CPT();
        $this->taxonomies = new PropertyFinder_Taxonomies();
        $this->property_columns = new PropertyFinder_Property_Columns();
        $this->agent_columns = new PropertyFinder_Agent_Columns();
        $this->agent_template = new PropertyFinder_Agent_Template();
        $this->property_template = new PropertyFinder_Property_Template();
        $this->cleanup = new PropertyFinder_Cleanup();

        // Register taxonomies
        add_action('init', array($this->taxonomies, 'register'), 10);

        // Disable block editor for our CPTs
        add_filter('use_block_editor_for_post_type', array($this, 'disable_block_editor'), 10, 2);
    }

    /**
     * Disable block editor for listings CPT
     *
     * @param bool $use_block_editor Whether to use block editor
     * @param string $post_type Post type name
     * @return bool
     */
    public function disable_block_editor($use_block_editor, $post_type) {
        if ($post_type === PropertyFinder_Config::get_cpt_name() ||
            $post_type === PropertyFinder_Config::get_agent_cpt_name()) {
            return false;
        }
        return $use_block_editor;
    }
}

