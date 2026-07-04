<?php
/**
 * User Authentication Interface and Session Initiation
 * Path: login.php
 */
session_start();
require_once 'config/db_config.php';
require_once 'classes/User.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login_action'])) {
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
    $password = $_POST['password'] ?? '';

    if (!$email || empty($password)) {
        $message = "Error: Valid email and password are required.";
    } else {
        
        // --- HARDCODED TESZT KAPU A DOLGOZÓHOZ ÉS ADMINHOZ ---
        // Ha ezt a konkrét email/jelszó párost írod be, a rendszer az adatbázist megkerülve azonnal beléptet!
        if ($email === 'employee@sport.com' && $password === 'jelszo123') {
            $_SESSION['user_id'] = 9999; // Ideiglenes teszt ID
            $_SESSION['user_email'] = 'employee@sport.com';
            $_SESSION['user_name'] = 'Teszt Recepciós (Kényszerített)';
            $_SESSION['role'] = 'employee'; // Dolgozói jogkör

            header("Location: index.php");
            exit;
        }
        
        // Opcionális: Ha az admint is tesztelni akarod jelszó nélkül
        if ($email === 'admin@sport.com' && $password === 'jelszo123') {
            $_SESSION['user_id'] = 8888;
            $_SESSION['user_email'] = 'admin@sport.com';
            $_SESSION['user_name'] = 'Rendszer Adminisztrátor';
            $_SESSION['role'] = 'admin'; // Adminisztrátori jogkör

            header("Location: index.php");
            exit;
        }
        // --- TESZT KAPU VÉGE ---


        // NORMÁL MENET: Ha nem a teszt adatokat adtad meg, az eredeti rendszered fut le
        $userObj = new User($pdo);
        $auth = $userObj->login($email, $password);

        if ($auth['success']) {
            // Munkamenet változók beállítása
            $_SESSION['user_id'] = $auth['user']['id'];
            $_SESSION['user_email'] = $auth['user']['email'];
            $_SESSION['user_name'] = $auth['user']['name'];
            $_SESSION['role'] = $auth['user']['role'];

            header("Location: index.php");
            exit;
        } else {
            $message = "Error: " . $auth['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - ACE</title>

<!-- Bootstrap 5 -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">

<!-- Saját design tokenek -->
<link rel="stylesheet" href="style.css">

<style>
    /* Bootstrap felülírások a saját design tokenekkel (style.css :root változói) */
    body{
        min-height:100vh;
        display:flex;
        flex-direction:column;
        font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;
    }
    .login-wrap{
        flex:1;
        background:linear-gradient(135deg, var(--bg-hero-from), var(--bg-hero-to));
        display:flex;
        align-items:center;
        justify-content:center;
        padding:56px 16px;
    }
    .login-card{
        border-radius:16px;
        box-shadow:0 18px 40px -10px rgba(16,163,107,0.15);
        border:1px solid var(--border);
    }
    .btn-brand-primary{
        background:var(--green);
        border-color:var(--green);
        color:#fff;
        font-weight:700;
    }
    .btn-brand-primary:hover{
        background:var(--green-dark);
        border-color:var(--green-dark);
        color:#fff;
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
        border:1px solid var(--purple);
        color:var(--purple);
        font-weight:700;
    }
    .btn-outline-brand-secondary:hover{
        background:var(--purple);
        color:#fff;
    }
    .link-brand{
        color:var(--purple);
        font-weight:600;
        text-decoration:none;
    }
    .link-brand:hover{ color:var(--purple-dark); }
    .form-control:focus{
        border-color:var(--purple);
        box-shadow:0 0 0 0.2rem rgba(91,76,245,0.15);
    }
    .form-check-input:checked{
        background-color:#1f2937;
        border-color:#1f2937;
    }
</style>
</head>
<body>

<!-- Header (Bootstrap navbar) -->
<header class="navbar navbar-expand bg-white border-bottom px-4 py-3">
    <div class="container-fluid p-0">
        <a href="index.php" class="navbar-brand d-flex align-items-center gap-2 m-0">
            <img src="assets/img/logo.png" alt="ACE logo" width="36" height="36" class="rounded-3" onerror="this.style.display='none'">
            <span class="fw-bold">ACE</span>
        </a>
        <div class="d-flex align-items-center gap-3 ms-auto">
            <a href="login.php" class="btn btn-outline-brand-secondary d-flex align-items-center gap-1">
                &#8594; Login
            </a>
            <a href="register.php" class="btn btn-brand-secondary d-flex align-items-center gap-2">
                &#128100; Sign Up
            </a>
        </div>
    </div>
</header>

<!-- Login section -->
<main class="login-wrap">
    <div class="w-100" style="max-width:440px;">
        <div class="text-center mb-4">
            <h1 class="fw-bold display-6">Welcome Back</h1>
            <p class="text-muted">Sign in to your account to continue</p>
        </div>

        <div class="card login-card p-4">
            <?php if (!empty($message)): ?>
                <div class="alert alert-danger py-2 px-3 fw-semibold" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="card-body p-0">
                <div class="mb-3">
                    <label for="email" class="form-label fw-bold">&#9993; Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="you@example.com" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-bold">&#128274; Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                        <input type="checkbox" name="remember" id="remember" class="form-check-input">
                        <label for="remember" class="form-check-label">Remember me</label>
                    </div>
                    <a href="forgot_password.php" class="link-brand">Forgot password?</a>
                </div>

                <button type="submit" name="login_action" class="btn btn-brand-secondary w-100 py-2">
                    &#8594; Sign In
                </button>
            </form>

            <p class="text-center text-muted mt-3 mb-0">
                Don't have an account? <a href="register.php" class="link-brand">Sign up</a>
            </p>
        </div>

        <a href="index.php" class="d-block text-center text-muted mt-4 text-decoration-none">&#8592; Back to home</a>
    </div>
</main>

<!-- Footer -->
<footer class="d-flex align-items-center justify-content-between flex-wrap gap-3 px-4 py-4" style="background:var(--navy); color:#cbd5e1;">
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

<!-- Bootstrap JS bundle (dropdownökhöz, modalokhoz, ha kellenek később) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>

</body>
</html>
