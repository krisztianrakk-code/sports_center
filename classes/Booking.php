<?php
/**
 * Facility Booking Controller Class
 * Path: classes/Booking.php
 */

class Booking {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function getCourtDetails($courtId) {
        $stmt = $this->db->prepare("SELECT * FROM courts WHERE id = ?");
        $stmt->execute([$courtId]);
        return $stmt->fetch();
    }

    /**
     * Strict Overlap Time Frame Validator
     * Evaluates boundary parameters to protect slot alignment.
     */
    public function isSlotAvailable($courtId, $startTime, $endTime) {
        $sql = "SELECT COUNT(*) FROM bookings 
                WHERE court_id = ? 
                AND status NOT IN ('cancelled', 'rejected', 'no_show')
                AND (? < end_time AND ? > start_time)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$courtId, $startTime, $endTime]);
        return $stmt->fetchColumn() == 0;
    }

    /**
     * Registered User Dashboard View: Fetch user's own appointments
     */
    public function getUserBookings($userId) {
        $sql = "SELECT b.*, c.name as court_name, c.location FROM bookings b 
                JOIN courts c ON b.court_id = c.id 
                WHERE b.user_id = ? ORDER BY b.start_time DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Enforces the mandatory 6-hour cancellation restriction limit rule.
     */
    public function requestUserCancellation($bookingId, $userId) {
        $sql = "SELECT start_time, status FROM bookings WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$bookingId, $userId]);
        $booking = $stmt->fetch();

        if (!$booking) return ["success" => false, "message" => "Booking context allocation error."];
        if ($booking['status'] !== 'pending' && $booking['status'] !== 'approved') {
            return ["success" => false, "message" => "This appointment cannot be altered at this stage."];
        }

        // Mathematical variance verification (6 hours = 21600 seconds)
        $timeVariance = strtotime($booking['start_time']) - time();
        if ($timeVariance < 21600) {
            return ["success" => false, "message" => "Cancellation denied. Must be triggered at least 6 hours prior to game start."];
        }

        $update = $this->db->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
        if ($update->execute([$bookingId])) {
            return ["success" => true];
        }
        return ["success" => false, "message" => "Update operational breakdown."];
    }

    /**
     * Employee Controller: Log Arrival / No Show with automated penalty structure processing
     */
    public function updateStatusByStaff($bookingId, $userId, $targetStatus, $staffComment) {
        $this->db->beginTransaction();

        try {
            // Update booking row parameters
            $sql = "UPDATE bookings SET status = ?, employee_comment = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$targetStatus, $staffComment, $bookingId]);

            // If player triggers a No-Show, increment penalty flags automatically
            if ($targetStatus === 'no_show') {
                $updateUser = $this->db->prepare("UPDATE users SET negative_points = negative_points + 1 WHERE id = ?");
                $updateUser->execute([$userId]);

                // Check constraint threshold
                $checkUser = $this->db->prepare("SELECT negative_points FROM users WHERE id = ?");
                $checkUser->execute([$userId]);
                $points = $checkUser->fetchColumn();

                // If user hits exactly or crosses 3 penalty markers, freeze account state
                if ($points >= 3) {
                    $blockUser = $this->db->prepare("UPDATE users SET is_blocked = 1 WHERE id = ?");
                    $blockUser->execute([$userId]);
                }
            }

            $this->db->commit();
            return ["success" => true];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ["success" => false, "message" => "Transaction error: " . $e->getMessage()];
        }
    }
    public function sendBookingNotification($userEmail, $userName, $status) {
    global $mail;
    try {
        $mail->clearAddresses();
        $mail->addAddress($userEmail, $userName);
        $mail->Subject = 'Foglalási értesítő - ACE Sports Center';
        $mail->Body = "<h2>Szia $userName!</h2>
                       <p>Az új foglalásod státusza: <strong>$status</strong>.</p>";
        $mail->send();
    } catch (Exception $e) {
        // Log error
    }
}
}