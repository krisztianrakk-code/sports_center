<?php
/**
 * Password Reset Request Handler
 * Path: forgot_password.php
 */
session_start();
require_once 'config/db_config.php';
require_once 'classes/User.php';
require_once 'config/email_config.php'; // Ezt szükséges beolvasni az e-mail küldéshez

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['request_reset_action'])) {
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));

    if (!$email) {
        $message = "<div class='alert alert-danger'>Error: Please provide a valid email format.</div>";
    } else {
        $userObj = new User($pdo);
        $result = $userObj->requestPasswordReset($email);

        if ($result['success']) {
            $token = $result['token'];
            $reset_link = "https://bt.stud.vts.su.ac.rs/reset_password.php?token=" . $token;

            try {
                // Globális $mail objektum használata a config-ból
                global $mail;
                $mail->clearAddresses(); // Előző címzettek törlése
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Reset your ACE Sports Center Password';
                $mail->Body = "
                    <h2>Password Reset Request</h2>
                    <p>We received a request to reset your password. Click the link below to set a new one:</p>
                    <a href='$reset_link' style='padding: 10px 20px; background-color: #5b4cf5; color: white; text-decoration: none; border-radius: 5px;'>Reset My Password</a>
                    <p>If you did not request this, please ignore this email.</p>";

                $mail->send();
                $message = "<div class='alert alert-success'>SUCCESS: A password reset link has been sent to your email address.</div>";
            } catch (Exception $e) {
                $message = "<div class='alert alert-danger'>Error: Email could not be sent. " . $mail->ErrorInfo . "</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>Error: " . $result['message'] . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - ACE Sports Center</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">

    <div class="card shadow-sm p-4" style="max-width: 400px; width: 100%; border-radius: 12px;">
        <h2 class="fw-bold mb-3 text-center">Reset Password</h2>
        
        <?php if (!empty($message)) echo $message; ?>

        <form action="forgot_password.php" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Enter your registered Email:</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            
            <button type="submit" name="request_reset_action" class="btn btn-primary w-100" style="background-color: #5b4cf5; border: none;">Generate Reset Link</button>
        </form>
        
        <div class="mt-3 text-center">
            <a href="login.php" class="text-decoration-none">&larr; Back to Login</a>
        </div>
    </div>

</body>
</html>