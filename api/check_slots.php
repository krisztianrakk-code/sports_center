<?php
/**
 * Asynchronous Slot Availability API
 * Path: api/check_slots.php
 */
header('Content-Type: application/json');
require_once '../config/db_config.php';
require_once '../classes/Booking.php';

$court_id = filter_input(INPUT_GET, 'court_id', FILTER_SANITIZE_NUMBER_INT);
$date = filter_input(INPUT_GET, 'date', FILTER_SANITIZE_SPECIAL_CHARS);

if (!$court_id || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(["success" => false, "message" => "Invalid parameters."]);
    exit;
}

$bookingObj = new Booking($pdo);

// A sportközpont fix, órás idősávjai (08:00 - 20:00)
$hours = [
    "08:00", "09:00", "10:00", "11:00", "12:00", "13:00", 
    "14:00", "15:00", "16:00", "17:00", "18:00", "19:00", "20:00"
];

$slotsStatus = [];

foreach ($hours as $hour) {
    $start_time = $date . ' ' . $hour . ':00';
    // Feltételezzük a minimális 1 órás (60 perces) alapegységet az ellenőrzéshez
    $end_time = date('Y-m-d H:i:s', strtotime($start_time . " +60 minutes"));

    // Lekérjük az OOP osztályból, hogy szabad-e
    $isAvailable = $bookingObj->isSlotAvailable($court_id, $start_time, $end_time);

    $slotsStatus[] = [
        "time" => $hour,
        "available" => $isAvailable
    ];
}

echo json_encode([
    "success" => true,
    "date" => $date,
    "slots" => $slotsStatus
]);