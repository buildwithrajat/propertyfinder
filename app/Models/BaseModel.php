<?php
namespace PropertyFinder\Models;

class BaseModel {
    protected $db;
    protected $table;
    
    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
    }

    public function getAll() {
        if (!$this->table) {
            return [];
        }
        return $this->db->get_results("SELECT * FROM {$this->table}");
    }

    public function getById($id) {
        if (!$this->table) {
            return null;
        }
        return $this->db->get_row(
            $this->db->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id)
        );
    }
}