<?php
/**
 * User Account Activation Verifier
 * Path: activation.php
 */
require_once 'config/db_config.php';

$message = "";
$status = "error";

if (isset($_GET['token'])) {
    $token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_SPECIAL_CHARS);

    // Megkeressük a felhasználót a token alapján
    $sql = "SELECT id FROM users WHERE activation_token = ? AND is_active = 0 LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        // Ha találtunk egyezést, aktiváljuk a fiókot és töröljük a tokent
        $updateSql = "UPDATE users SET is_active = 1, activation_token = NULL WHERE id = ?";
        $updateStmt = $pdo->prepare($updateSql);
        
        if ($updateStmt->execute([$user['id']])) {
            $message = "Your account has been successfully activated! You can now log in.";
            $status = "success";
        } else {
            $message = "An error occurred during activation. Please try again later.";
        }
    } else {
        $message = "Invalid or expired activation token. The account may already be active.";
    }
} else {
    $message = "No activation token provided.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Activation</title>
</head>
<body>

    <h2>Account Activation</h2>
    <hr>
    
    <?php if ($status === "success"): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border: 1px solid #c3e6cb;">
            <strong>Success!</strong> <?php echo $message; ?>
            <br><br>
            <a href="login.php" style="font-weight: bold;">Go to Login Page &rarr;</a>
        </div>
    <?php else: ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb;">
            <strong>Error:</strong> <?php echo $message; ?>
            <br><br>
            <a href="index.php">Back to Home</a>
        </div>
    <?php endif; ?>

</body>
</html>