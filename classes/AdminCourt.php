<?php
/**
 * Administrative Facility Inventory Class
 * Path: classes/AdminCourt.php
 */

class AdminCourt {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    /**
     * Admin Operation: Insert new infrastructure field assets
     */
    public function addCourt($name, $type, $isIndoor, $pricePerHour, $location, $equipment, $description = "") {
        $sql = "INSERT INTO courts (name, type, is_indoor, price_per_hour, location, equipment_included, description) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$name, $type, $isIndoor, $pricePerHour, $location, $equipment, $description]);
    }

    /**
     * Admin Operation: Modify operational details of an active court
     */
    public function updateCourt($courtId, $name, $type, $isIndoor, $pricePerHour, $location, $equipment, $description) {
        $sql = "UPDATE courts SET name = ?, type = ?, is_indoor = ?, price_per_hour = ?, location = ?, equipment_included = ?, description = ? 
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$name, $type, $isIndoor, $pricePerHour, $location, $equipment, $description, $courtId]);
    }

    /**
     * Admin Operation: Delete field structure completely (triggers cascade cascading references on foreign tables)
     */
    public function deleteCourt($courtId) {
        $stmt = $this->db->prepare("DELETE FROM courts WHERE id = ?");
        return $stmt->execute([$courtId]);
    }
}