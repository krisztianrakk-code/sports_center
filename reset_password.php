<?php
/**
 * Core Password Reset Execution
 * Path: reset_password.php
 */
session_start();
require_once 'config/db_config.php';

$message = "";
$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_SPECIAL_CHARS);

if (!$token) {
    die("Error: Missing secure token sequence identifier.");
}

// Ellenőrizzük, hogy létezik-e a token és nincs-e lejárva
$stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires_at > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die("Error: The password reset token is invalid or has expired.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['execute_reset_action'])) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($password) || strlen($password) < 6) {
        $message = "Error: Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $message = "Error: Passwords do not match.";
    } else {
        // Új jelszó hashelése Bcrypt-tel
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Frissítjük a jelszót és takarítjuk a visszaállító token mezőket
        $sql = "UPDATE users SET password = ?, reset_token = NULL, reset_expires_at = NULL WHERE id = ?";
        $updateStmt = $pdo->prepare($sql);
        
        if ($updateStmt->execute([$hashedPassword, $user['id']])) {
            $message = "SUCCESS: Your password has been reset. You can now <a href='login.php'>Login</a>.";
        } else {
            $message = "Error: Failed to execute password update.";
        }
    }
}
?>
<h2>Set New Password</h2>
<?php if (!empty($message)) echo "<p><strong>{$message}</strong></p>"; ?>

<form action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>" method="POST">
    <label for="password">New Password:</label><br>
    <input type="password" name="password" id="password" required><br><br>

    <label for="confirm_password">Confirm New Password:</label><br>
    <input type="password" name="confirm_password" id="confirm_password" required><br><br>

    <button type="submit" name="execute_reset_action">Update Password</button>
</form>