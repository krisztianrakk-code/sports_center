<?php
/**
 * User Registration Processor with Real Email Activation
 * Path: register.php
 */

// Hibakeresés bekapcsolása - így ha bármi gond van, kiírja a pontos PHP hibaüzenetet
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config/db_config.php';
require_once 'classes/User.php';
// Beemeljük a PHPMailer beállításokat, amiket a Brevóhoz konfiguráltunk
require_once 'config/email_config.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['register_action'])) {
    $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
    $phone = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS));
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Alapvető szerveroldali ellenőrzések
    if (empty($name) || empty($email) || empty($password)) {
        $message = "Error: Name, Email and Password fields are mandatory.";
    } elseif (!$email) {
        $message = "Error: Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $message = "Error: Passwords do not match.";
    } else {
        $userObj = new User($pdo);
        $result = $userObj->register($name, $email, $phone, $password);

        if ($result['success']) {
            $token = $result['token'];
            
            // Dinamikusan összeállítjuk az aktivációs linket az egyetemi szerverhez
            $activation_link = "https://bt.stud.vts.su.ac.rs/activation.php?token=" . $token;

            try {
                // Kikényszerítjük, hogy a PHP az email_config.php-ból behúzott $mail objektumot használja
                global $mail; 

                // Ellenőrizzük, hogy az objektum sikeresen létrejött-e az email_config.php-ban
                if (!isset($mail) || $mail === null) {
                    throw new Exception("A PHPMailer (\$mail) objektum nem jött létre az email_config.php fájlban! Ellenőrizd a konfigurációs fájlt.");
                }

                // Címzettek beállítása (A regisztráló felhasználónak küldjük)
                $mail->clearAddresses(); // Biztonsági tisztítás a küldések előtt
                $mail->addAddress($email, $name);
                
                // Levél tartalma
                $mail->isHTML(true);
                $mail->Subject = 'Activate your ACE Sports Center Account';
                
                $mail->Body = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px;'>
                        <h2 style='color: #10a36b; text-align: center;'>Welcome to ACE, $name!</h2>
                        <p>Thank you for registering at ACE Sports Center. To complete your registration and activate your account, please click the button below:</p>
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='$activation_link' style='background-color: #5b4cf5; color: white; padding: 12px 24px; text-decoration: none; font-weight: bold; border-radius: 6px; display: inline-block;'>Activate Account</a>
                        </div>
                        <p style='color: #6b7280; font-size: 14px;'>If the button doesn't work, copy and paste this link into your browser:</p>
                        <p style='word-break: break-all; font-size: 14px;'><a href='$activation_link'>$activation_link</a></p>
                        <hr style='border: 0; border-top: 1px solid #e5e7eb; margin: 20px 0;'>
                        <p style='font-size: 12px; color: #9ca3af; text-align: center;'>&copy; 2026 ACE Sports Center. All rights reserved.</p>
                    </div>
                ";
                
                $mail->AltBody = "Hello $name,\n\nThank you for registering at ACE Sports Center. Please activate your account using the following link:\n$activation_link";

                // E-mail küldése
                $mail->send();
                $message = "Registration successful! Please check your email to activate your account.";
                
            } catch (Exception $e) {
                // Elkapjuk a hibát, de nem omlasztjuk össze a szervert, hanem kiírjuk a hiba üzenetet
                $message = "Registration saved, but email could not be sent. Error: " . $e->getMessage();
            }
        } else {
            $message = "Error: " . $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - ACE</title>

<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="style.css">

<style>
    body{
        min-height:100vh;
        display:flex;
        flex-direction:column;
        font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;
    }
    .register-wrap{
        flex:1;
        background:linear-gradient(135deg, var(--bg-hero-from, #eafaf1), var(--bg-hero-to, #f3fbf6));
        display:flex;
        align-items:center;
        justify-content:center;
        padding:56px 16px;
    }
    .register-card{
        border-radius:16px;
        box-shadow:0 18px 40px -10px rgba(16,163,107,0.15);
        border:1px solid var(--border, #e5e7eb);
    }
    .btn-brand-secondary{
        background:#5b4cf5;
        background:var(--purple, #5b4cf5);
        border-color:#5b4cf5;
        border-color:var(--purple, #5b4cf5);
        color:#fff;
        font-weight:700;
    }
    .btn-brand-secondary:hover{
        background:#4636d6;
        background:var(--purple-dark, #4636d6);
        border-color:#4636d6;
        border-color:var(--purple-dark, #4636d6);
        color:#fff;
    }
    .btn-outline-brand-secondary{
        background:transparent;
        border:1px solid var(--purple, #5b4cf5);
        color:var(--purple, #5b4cf5);
        font-weight:700;
    }
    .btn-outline-brand-secondary:hover{
        background:var(--purple, #5b4cf5);
        color:#fff;
    }
    .link-brand{
        color:var(--purple, #5b4cf5);
        font-weight:600;
        text-decoration:none;
    }
    .link-brand:hover{ color:var(--purple-dark, #4636d6); }
    .form-control:focus{
        border-color:var(--purple, #5b4cf5);
        box-shadow:0 0 0 0.2rem rgba(91,76,245,0.15);
    }
    .form-check-input:checked{
        background-color:#1f2937;
        border-color:#1f2937;
    }
</style>
</head>
<body>

<header class="navbar navbar-expand bg-white border-bottom px-4 py-3">
    <div class="container-fluid p-0">
        <a href="index.php" class="navbar-brand d-flex align-items-center gap-2 m-0">
            <img src="assets/img/logo.png" alt="ACE logo" width="36" height="36" class="rounded-3" onerror="this.style.display='none'">
            <span class="fw-bold">ACE</span>
        </a>
        <div class="d-flex align-items-center gap-3 ms-auto">
            <a href="login.php" class="d-flex align-items-center gap-1 fw-semibold text-dark text-decoration-none">
                &#8594; Login
            </a>
            <a href="register.php" class="btn btn-brand-secondary d-flex align-items-center gap-2">
                &#128100; Sign Up
            </a>
        </div>
    </div>
</header>

<main class="register-wrap">
    <div class="w-100" style="max-width:440px;">
        <div class="text-center mb-4">
            <h1 class="fw-bold display-6">Create Account</h1>
            <p class="text-muted">Join us and start booking your favorite fields</p>
        </div>

        <div class="card register-card p-4">
            <?php if (!empty($message)): ?>
                <div class="alert <?php echo (strpos($message, 'Error:') === 0) ? 'alert-danger' : 'alert-success'; ?> py-2 px-3 fw-semibold" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" class="card-body p-0">
                <div class="mb-3">
                    <label for="name" class="form-label fw-bold">&#128100; Full Name</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="John Doe" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-bold">&#9993; Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="you@example.com" required>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label fw-bold">&#9742; Phone Number</label>
                    <input type="text" name="phone" id="phone" class="form-control" placeholder="+36 30 123 4567">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-bold">&#128274; Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Create a password" required>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label fw-bold">&#128274; Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm your password" required>
                </div>

                <div class="form-check mb-4">
                    <input type="checkbox" name="terms" id="terms" class="form-check-input" required>
                    <label for="terms" class="form-check-label">
                        I agree to the <a href="terms.php" class="link-brand">Terms of Service</a> and <a href="privacy.php" class="link-brand">Privacy Policy</a>
                    </label>
                </div>

                <button type="submit" name="register_action" class="btn btn-brand-secondary w-100 py-2">
                    &#128100;&#43; Create Account
                </button>
            </form>

            <p class="text-center text-muted mt-3 mb-0">
                Already have an account? <a href="login.php" class="link-brand">Sign in</a>
            </p>
        </div>

        <a href="index.php" class="d-block text-center text-muted mt-4 text-decoration-none">&#8592; Back to home</a>
    </div>
</main>

<footer class="d-flex align-items-center justify-content-between flex-wrap gap-3 px-4 py-4" style="background:var(--navy, #0f172a); color:#cbd5e1;">
    <a href="index.php" class="d-flex align-items-center gap-2 text-white fw-bold text-decoration-none">
        <img src="assets/img/logo.png" alt="ACE logo" width="30" height="30" class="rounded-2" onerror="this.style.display='none'">
        ACE
    </a>
    <div class="d-flex gap-4">
        <a href="about.php" class="text-decoration-none" style="color:#cbd5e1;">About</a>
        <a href="contact.php" class="text-decoration-none" style="color:#cbd5e1;">Contact</a>
        <a href="terms.php" class="text-decoration-none" style="color:#cbd5e1;">Terms</a>
        <a href="privacy.php" class="text-decoration-none" style="color:#cbd5e1;">Privacy</a>
    </div>
    <div class="small" style="color:#94a3b8;">&copy; 2026 ACE. All rights reserved.</div>
</footer>

<div class="help-btn">?</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>

</body>
</html>