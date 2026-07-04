<?php
/**
 * Handle Staff/Employee Actions (Approve, Reject, Attended, No-Show)
 * Path: actions/process_employee_action.php
 */
session_start();
require_once '../config/db_config.php';
require_once '../config/email_config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'employee' && $_SESSION['role'] !== 'admin')) {
    $_SESSION['employee_msg'] = "❌ ACCESS DENIED: Staff privileges required.";
    $_SESSION['employee_msg_type'] = "error";
    header("Location: ../profile.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['booking_id'])) {
    $bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_SANITIZE_NUMBER_INT);
    $newStatus = filter_input(INPUT_POST, 'new_status', FILTER_SANITIZE_SPECIAL_CHARS);
    $staffNote = isset($_POST['staff_note']) ? trim(filter_input(INPUT_POST, 'staff_note', FILTER_SANITIZE_SPECIAL_CHARS)) : '';

    // 1. Módosított lekérdezés: JOIN a users ÉS a courts táblával (árral együtt)
    $stmt = $pdo->prepare("
        SELECT b.*, u.email, u.name, c.name AS court_name, c.price_per_hour 
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN courts c ON b.court_id = c.id 
        WHERE b.id = ?
    ");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();

    if (!$booking) {
        $_SESSION['employee_msg'] = "❌ ERROR: Booking not found.";
        $_SESSION['employee_msg_type'] = "error";
        header("Location: ../employee.php");
        exit();
    }

    $updateSql = "UPDATE bookings SET status = ?, staff_notes = ? WHERE id = ?";
    $updateStmt = $pdo->prepare($updateSql);
    
    if ($updateStmt->execute([$newStatus, $staffNote, $bookingId])) {
        
        // --- EMAIL KÜLDÉS RÉSZ (Árral kiegészítve) ---
        try {
            global $mail;
            $mail->clearAddresses();
            $mail->addAddress($booking['email'], $booking['name']);
            $mail->isHTML(true);
            $mail->Subject = 'Frissítés a foglalásod kapcsán - ACE Sports Center';
            
            // Számoljuk ki a végösszeget (időtartam percekben / 60 * óradíj)
            $totalPrice = ($booking['duration_minutes'] / 60) * $booking['price_per_hour'];
            
            $body = "<h2>Kedves {$booking['name']}!</h2>";
            $body .= "<p>A foglalásod státusza megváltozott: <strong>" . strtoupper($newStatus) . "</strong></p>";
            
            $body .= "<h3>Foglalás részletei:</h3>";
            $body .= "<ul>
                        <li><strong>Pálya:</strong> {$booking['court_name']}</li>
                        <li><strong>Időpont:</strong> {$booking['start_time']}</li>
                        <li><strong>Időtartam:</strong> {$booking['duration_minutes']} perc</li>
                        <li><strong>Végösszeg:</strong> " . number_format($totalPrice, 2) . " $</li>
                        <li><strong>Foglalási kód:</strong> {$booking['booking_code']}</li>
                      </ul>";

            if ($newStatus === 'rejected' && !empty($staffNote)) {
                $body .= "<p><strong>Indoklás:</strong> {$staffNote}</p>";
            }

            $mail->Body = $body . "<p>Üdvözlettel,<br>ACE Sports Center csapata</p>";
            $mail->send();
        } catch (Exception $e) {
            error_log("Email küldési hiba: " . $mail->ErrorInfo);
        }

        $_SESSION['employee_msg'] = "🎉 SUCCESS: Booking status updated to [" . strtoupper($newStatus) . "].";
        $_SESSION['employee_msg_type'] = "success";
        
        // --- NEGATÍV PONT RENDSZER ---
        if ($newStatus === 'no_show') {
            $userId = $booking['user_id'];
            $pointsStmt = $pdo->prepare("UPDATE users SET negative_points = negative_points + 1 WHERE id = ?");
            $pointsStmt->execute([$userId]);
            
            $checkUserStmt = $pdo->prepare("SELECT negative_points FROM users WHERE id = ?");
            $checkUserStmt->execute([$userId]);
            $uData = $checkUserStmt->fetch();
            
            $_SESSION['employee_msg'] = "❌ Marked as No-Show. Penalty applied (+1 Strike). Current strikes: " . ($uData['negative_points'] ?? 1);
            
            $lockStmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ? AND negative_points >= 3");
            $lockStmt->execute([$userId]);
            
            if ($lockStmt->rowCount() > 0) {
                $_SESSION['employee_msg'] .= " 🚨 PLAYER AUTOMATICALLY BANNED (Reached 3+ strikes).";
                $_SESSION['employee_msg_type'] = "error";
            }
        }
    } else {
        $_SESSION['employee_msg'] = "❌ ERROR: Database update failed.";
        $_SESSION['employee_msg_type'] = "error";
    }
} else {
    $_SESSION['employee_msg'] = "❌ Invalid request method or missing data.";
    $_SESSION['employee_msg_type'] = "error";
}


header("Location: ../employee.php");
exit();
?>