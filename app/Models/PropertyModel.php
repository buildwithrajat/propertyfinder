<?php
/**
 * Property Model
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
 * Property Model class
 */
class PropertyModel extends BaseModel {
    
    /**
     * Table name
     */
    protected $table = 'propertyfinder_properties';
    
    /**
     * Get properties by status
     *
     * @param string $status Property status
     * @return array
     */
    public function getByStatus($status = 'active') {
        return $this->db->get_results(
            $this->db->prepare(
                "SELECT * FROM {$this->db->prefix}{$this->table} WHERE status = %s ORDER BY created_at DESC",
                $status
            )
        );
    }
    
    /**
     * Get property by property ID
     *
     * @param string $property_id Property ID
     * @return object|null
     */
    public function getByPropertyId($property_id) {
        return $this->db->get_row(
            $this->db->prepare(
                "SELECT * FROM {$this->db->prefix}{$this->table} WHERE property_id = %s",
                $property_id
            )
        );
    }
    
    /**
     * Insert property
     *
     * @param array $data Property data
     * @return int|false ID on success, false on failure
     */
    public function insert($data) {
        return $this->db->insert(
            $this->db->prefix . $this->table,
            array(
                'property_id' => $data['property_id'],
                'title' => $data['title'],
                'data' => maybe_serialize($data['data']),
                'status' => isset($data['status']) ? $data['status'] : 'active',
            ),
            array('%s', '%s', '%s', '%s')
        );
    }
    
    /**
     * Update property
     *
     * @param int $id Property ID
     * @param array $data Property data
     * @return bool
     */
    public function update($id, $data) {
        return $this->db->update(
            $this->db->prefix . $this->table,
            array(
                'title' => $data['title'],
                'data' => maybe_serialize($data['data']),
                'status' => isset($data['status']) ? $data['status'] : 'active',
            ),
            array('id' => $id),
            array('%s', '%s', '%s'),
            array('%d')
        );
    }
    
    /**
     * Delete property
     *
     * @param int $id Property ID
     * @return bool
     */
    public function delete($id) {
        return $this->db->delete(
            $this->db->prefix . $this->table,
            array('id' => $id),
            array('%d')
        );
    }
}

