<?php
/**
 * Handle Booking Cancellation (User & Admin)
 * Path: actions/cancel_booking.php
 */
session_start();
require_once '../config/db_config.php';
require_once '../config/email_config.php'; // E-mail küldéshez szükséges

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['cancel_booking_action'])) {
    $bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_SANITIZE_NUMBER_INT);
    $userId = $_SESSION['user_id'];
    $userRole = $_SESSION['role']; // Admin/Employee felülbírálhatja a 6 órás szabályt

    // 1. Lekérjük a foglalást és a felhasználó adatait JOIN-nal
    $stmt = $pdo->prepare("SELECT b.*, u.email, u.name 
                           FROM bookings b 
                           JOIN users u ON b.user_id = u.id 
                           WHERE b.id = ?");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();

    if (!$booking) {
        $_SESSION['msg'] = "Error: Booking not found.";
        header("Location: ../profile.php");
        exit();
    }

    // 2. Jogosultság és szabályok ellenőrzése
    $canCancel = ($userRole === 'admin' || $userRole === 'employee') || ($booking['user_id'] == $userId);
    
    // 6 órás szabály csak userekre vonatkozik
    if ($userRole === 'registered' && (strtotime($booking['start_time']) - time()) < 21600) {
        $_SESSION['msg'] = "Cancellation denied: Must be done at least 6 hours before.";
        header("Location: ../profile.php");
        exit();
    }

    // 3. Törlés (státusz váltás)
    if ($canCancel) {
        $update = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
        if ($update->execute([$bookingId])) {
            
            // 4. E-mail küldés értesítésként
            try {
                global $mail;
                $mail->clearAddresses();
                $mail->addAddress($booking['email'], $booking['name']);
                $mail->isHTML(true);
                $mail->Subject = 'Foglalás lemondva - ACE Sports Center';
                $mail->Body = "
                    <h2>Foglalás lemondva</h2>
                    <p>Kedves {$booking['name']}!</p>
                    <p>Tájékoztatunk, hogy a <strong>{$booking['start_time']}</strong> időpontra szóló foglalásod törlésre került.</p>
                    <p>Üdvözlettel, ACE Sports Center</p>";
                $mail->send();
            } catch (Exception $e) {
                // Email hiba nem állítja meg a folyamatot
            }

            $_SESSION['msg'] = "SUCCESS: Booking has been cancelled.";
        }
    }
}

header("Location: ../profile.php");
exit();