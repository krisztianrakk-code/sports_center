<?php
/**
 * Profile Update Processor - Session & ID Sync Edition
 * Path: actions/update_profile.php
 */
session_start();
require_once '../config/db_config.php'; // Két ponttal visszalépünk az actions mappából

// Biztonsági ellenőrzés: ha nincs belépve, azonnali visszadobás
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $session_user_id = $_SESSION['user_id'];
    
    // Bemeneti adatok tisztítása és fogadása
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Alapvető validációk
    if (empty($name) || empty($email)) {
        $_SESSION['profile_msg'] = "❌ Name and email fields cannot be empty!";
        $_SESSION['profile_msg_type'] = "error";
        header("Location: ../profile.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['profile_msg'] = "❌ Invalid email format!";
        $_SESSION['profile_msg_type'] = "error";
        header("Location: ../profile.php");
        exit();
    }

    try {
        // --- ID RECONCILIATION CRITICAL FIX ---
        // Megkeressük a felhasználó VALÓDI ID-ját az adatbázisban az e-mail címe alapján.
        // Ez orvosolja azt a hibát, ha a login.php rossz ID-t rakott volna a sessionbe.
        $findRealUser = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $findRealUser->execute([$email]);
        $realUserRow = $findRealUser->fetch();
        
        if ($realUserRow) {
            // Ha megtaláltuk az e-mail alapján, akkor az adatbázisban lévő pontos ID-t használjuk!
            $user_id = $realUserRow['id'];
        } else {
            // Ha az e-mail alapján nincs meg (mert épp most akarja átírni egy újra), 
            // akkor használjuk a munkamenet ID-t
            $user_id = $session_user_id;
        }

        // 1. Biztonsági ellenőrzés: Létezik-e ez az email BÁRKI MÁSNÁL (id != talált user_id)
        $emailCheckStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $emailCheckStmt->execute([$email, $user_id]);
        
        if ($emailCheckStmt->fetch()) {
            $_SESSION['profile_msg'] = "❌ This email address is already in use by another account.";
            $_SESSION['profile_msg_type'] = "error";
            header("Location: ../profile.php");
            exit();
        }

        // 2. Alapadatok frissítése (Név és Email) a közös 'users' táblában
        $updateQuery = "UPDATE users SET name = ?, email = ? WHERE id = ?";
        $params = [$name, $email, $user_id];

        // 3. Jelszó módosítás kezelése
        if (!empty($new_password)) {
            if ($new_password !== $confirm_password) {
                $_SESSION['profile_msg'] = "❌ The new passwords do not match!";
                $_SESSION['profile_msg_type'] = "error";
                header("Location: ../profile.php");
                exit();
            }
            
            if (strlen($new_password) < 6) {
                $_SESSION['profile_msg'] = "❌ Password must be at least 6 characters long!";
                $_SESSION['profile_msg_type'] = "error";
                header("Location: ../profile.php");
                exit();
            }

            // Új jelszó titkosítása és hozzáadása a lekérdezéshez
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $updateQuery = "UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?";
            $params = [$name, $email, $hashed_password, $user_id];
        }

        // Végrehajtás az adatbázisban a GARANTÁLTAN JÓ ID-val
        $stmt = $pdo->prepare($updateQuery);
        $stmt->execute($params);

        // 4. MUNKAMENET (SESSION) KÉNYSZERÍTETT SZINKRONIZÁCIÓJA
        // Felülírjuk a session értékeit a valós adatokkal, így a profile.php is jól fog működni
        $_SESSION['user_id'] = $user_id; // Helyretesszük a hibás session ID-t!
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;

        $_SESSION['profile_msg'] = "✅ Profile updated successfully!";
        $_SESSION['profile_msg_type'] = "success";

    } catch (PDOException $e) {
        $_SESSION['profile_msg'] = "❌ Database error occurred. Please try again later.";
        $_SESSION['profile_msg_type'] = "error";
    }
}

// Visszairányítás a profil oldalra
header("Location: ../profile.php");
exit();