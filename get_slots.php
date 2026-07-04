<?php
require_once 'config/db_config.php';
$court_id = $_GET['court_id'];
$date = $_GET['date'];

// Lekérdezzük a már foglalt időpontokat erre a napra
$stmt = $pdo->prepare("SELECT start_time FROM bookings WHERE court_id = ? AND DATE(start_time) = ? AND status != 'cancelled'");
$stmt->execute([$court_id, $date]);
$booked_slots = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($booked_slots);