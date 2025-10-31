<?php
/**
 * Base Model
 *
 * @package PropertyFinder
 * @subpackage Models
 */

namespace PropertyFinder\Models;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Base Model class
 */
class BaseModel {
    
    /**
     * Database instance
     */
    protected $db;
    
    /**
     * Table name
     */
    protected $table;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
    }

    /**
     * Get all records
     *
     * @return array
     */
    public function getAll() {
        if (!$this->table) {
            return [];
        }
        return $this->db->get_results("SELECT * FROM {$this->db->prefix}{$this->table}");
    }

    /**
     * Get record by ID
     *
     * @param int $id Record ID
     * @return object|null
     */
    public function getById($id) {
        if (!$this->table) {
            return null;
        }
        return $this->db->get_row(
            $this->db->prepare("SELECT * FROM {$this->db->prefix}{$this->table} WHERE id = %d", $id)
        );
    }
}