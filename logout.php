<?php
/**
 * Global Session Destruction / Sign Out Handler
 * Path: logout.php
 * Compliance: coding_style_guide_hu.pdf
 */

session_start();

// 1. Összes session változó kiürítése
$_SESSION = array();

// 2. Ha a session cookie-t is törölni akarjuk a böngészőből
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Munkamenet teljes megsemmisítése a szerveren
session_destroy();

// 4. Biztonságos visszairányítás a főoldalra, immár Guest-ként
header("Location: index.php");
exit;