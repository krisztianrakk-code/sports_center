<?php
/**
 * Clean User Profile - Settings (Top), Reservations (Bottom, Clients Only) & Soft Cancellation
 * Path: profile.php
 */
session_start();
require_once 'config/db_config.php';

// Biztonsági ellenőrzés: ha nincs belépve, irány a login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Felhasználó alapadatainak lekérése az adatbázisból
$userStmt = $pdo->prepare("SELECT name, email, role FROM users WHERE id = ?");
$userStmt->execute([$_SESSION['user_id']]);
$userData = $userStmt->fetch();

$user_name = $userData['name'] ?? $_SESSION['user_name'] ?? 'User';

// --- EMAIL HANDLING FIX FOR EMPLOYEES ---
if (!empty($userData['email'])) {
    $user_email = $userData['email'];
} elseif (!empty($_SESSION['user_email'])) {
    $user_email = $_SESSION['user_email'];
} else {
    $user_email = 'No email provided';
}

// Felhasználó nevének első betűje az avatarhoz
$initial = !empty($user_name) ? mb_strtoupper(mb_substr(trim($user_name), 0, 1, 'UTF-8'), 'UTF-8') : "?";

// --- JOGOSULTSÁG MEGHATÁROZÁSA ---
// Kinyerjük a nyers szerepkört az adatbázisból vagy a munkamenetből
$raw_role = $userData['role'] ?? $_SESSION['role'] ?? $_SESSION['user_role'] ?? 'registered';

if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
    $raw_role = 'admin';
}

// Egységesítjük a szerepköröket az oldal logikájához
$user_role = 'client'; 
if ($raw_role === 'registered' || $raw_role === 'client') {
    $user_role = 'client'; // A 'registered' userek is 'client' felületet kapnak
} elseif ($raw_role === 'admin') {
    $user_role = 'admin';
} elseif ($raw_role === 'employee' || $raw_role === 'staff') {
    $user_role = 'employee';
}

// Gyorslinkek beállítása a belső panelekhez
$dashboard_url = "";
$dashboard_label = "";

if ($user_role === 'admin') {
    $dashboard_url = 'admin.php';
    $dashboard_label = '🛡️ Go to Admin Dashboard';
} elseif ($user_role === 'employee') {
    $dashboard_url = 'employee.php';
    $dashboard_label = '💼 Go to Employee Panel';
}

// Értesítési üzenetek kezelése
$message = "";
$message_type = "";
if (isset($_SESSION['profile_msg'])) {
    $message = $_SESSION['profile_msg'];
    $message_type = $_SESSION['profile_msg_type'] ?? 'info';
    unset($_SESSION['profile_msg'], $_SESSION['profile_msg_type']);
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile - ACE Sports Center</title>
<link rel="stylesheet" href="assets/css/style.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    /* Brand color override - lets Bootstrap's built-in utilities (btn-primary,
       text-primary, border-primary, bg-primary...) use the ACE brand purple
       instead of Bootstrap's default blue. No other custom CSS is used. */
    :root {
        --bs-primary: #5b4cf5;
        --bs-primary-rgb: 91, 76, 245;
        --bs-body-font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }
    body { background-color: #f9fafb; }
</style>
</head>
<body class="d-flex flex-column min-vh-100">
    
<?php require_once 'components/header.php'; ?>

  <main class="container my-5 flex-fill" style="max-width:1000px;">

    <?php if (!empty($message)): ?>
        <?php
            $alertClass = 'alert-info';
            if ($message_type === 'success') $alertClass = 'alert-success';
            elseif ($message_type === 'error') $alertClass = 'alert-danger';
        ?>
        <div class="alert <?php echo $alertClass; ?> fw-semibold">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">

      <aside class="col-md-4 col-lg-3">
        <div class="card shadow-sm rounded-4 p-4 h-auto">
          <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold mx-auto mb-4" style="width:80px;height:80px;font-size:32px;">
            <?php echo htmlspecialchars($initial); ?>
          </div>
          <div class="text-center mb-4">
            <h3 class="h5 mb-1"><?php echo htmlspecialchars($user_name); ?></h3>
            <p class="text-secondary small text-break mb-2"><?php echo htmlspecialchars($user_email); ?></p>
            <?php
                $roleBadgeClass = 'bg-light text-dark';
                if ($user_role === 'employee') $roleBadgeClass = 'bg-info-subtle text-info';
                elseif ($user_role === 'admin') $roleBadgeClass = 'bg-danger-subtle text-danger';
            ?>
            <span class="badge rounded-pill <?php echo $roleBadgeClass; ?> text-uppercase">
                <?php echo htmlspecialchars($raw_role); ?>
            </span>
          </div>

          <?php if (!empty($dashboard_url)): ?>
              <a href="<?php echo $dashboard_url; ?>" class="btn btn-dark w-100 mb-2">
                  <?php echo $dashboard_label; ?>
              </a>
          <?php endif; ?>

          <a href="logout.php" class="btn btn-danger w-100">Sign Out / Logout</a>
        </div>
      </aside>

      <section class="col-md-8 col-lg-9">

        <h2 class="h4 fw-bold mb-3">Account Settings</h2>
        <div class="card shadow-sm rounded-4 p-4 mb-5">
            <form action="actions/update_profile.php" method="POST" class="d-flex flex-column gap-3">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="profile_name" class="form-label fw-semibold small">Full Name</label>
                        <input type="text" id="profile_name" name="name" value="<?php echo htmlspecialchars($user_name); ?>" required class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label for="profile_email" class="form-label fw-semibold small">Email Address</label>
                        <input type="email" id="profile_email" name="email" value="<?php echo htmlspecialchars($user_email === 'No email provided' ? '' : $user_email); ?>" placeholder="example@domain.com" required class="form-control">
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="profile_password" class="form-label fw-semibold small">New Password (leave blank to keep current)</label>
                        <input type="password" id="profile_password" name="new_password" placeholder="••••••••" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label for="profile_confirm_password" class="form-label fw-semibold small">Confirm New Password</label>
                        <input type="password" id="profile_confirm_password" name="confirm_password" placeholder="••••••••" class="form-control">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary align-self-start">Save Changes</button>
            </form>
        </div>

        <?php if ($user_role === 'client'): ?>
            <h2 class="h4 fw-bold mb-3">Your Reservations</h2>
            <?php
            $bookingsStmt = $pdo->prepare("
                SELECT b.*, c.name AS court_name, c.type AS court_type, c.location 
                FROM bookings b
                JOIN courts c ON b.court_id = c.id
                WHERE b.user_id = ?
                ORDER BY b.start_time DESC
            ");
            $bookingsStmt->execute([$_SESSION['user_id']]);
            $userBookings = $bookingsStmt->fetchAll();
            ?>

            <?php if (empty($userBookings)): ?>
                <div class="card shadow-sm rounded-4 p-4 text-center text-secondary mb-4">
                    <p class="mb-0">You haven't made any court reservations yet.</p>
                </div>
            <?php else: ?>
                <div class="d-flex flex-column gap-3 mb-5">
                    <?php foreach ($userBookings as $booking): 
                        $startTimeStamp = strtotime($booking['start_time']);
                        $sixHoursInSeconds = 6 * 60 * 60;
                        $isBufferValid = ($startTimeStamp - time()) >= $sixHoursInSeconds;
                        
                        $isStatusCancellable = ($booking['status'] === 'pending' || $booking['status'] === 'approved');
                        $canCancel = ($isBufferValid && $isStatusCancellable);

                        $statusBadgeClass = 'bg-light text-dark';
                        switch ($booking['status']) {
                            case 'pending': $statusBadgeClass = 'bg-warning-subtle text-warning-emphasis'; break;
                            case 'approved': $statusBadgeClass = 'bg-success-subtle text-success'; break;
                            case 'attended': $statusBadgeClass = 'bg-info-subtle text-info'; break;
                            case 'rejected': $statusBadgeClass = 'bg-danger-subtle text-danger'; break;
                            case 'no_show': $statusBadgeClass = 'bg-light text-secondary border border-secondary-subtle'; break;
                            case 'cancelled': $statusBadgeClass = 'bg-secondary-subtle text-secondary'; break;
                        }
                    ?>
                        <div class="card shadow-sm rounded-3 p-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                            <div>
                                <h4 class="h6 mb-2"><?php echo htmlspecialchars($booking['court_name']); ?></h4>
                                <p class="small text-secondary mb-1">📍 <?php echo htmlspecialchars($booking['location']); ?> (<?php echo ucfirst($booking['court_type']); ?>)</p>
                                <p class="small text-secondary mb-1">📅 <strong>Time:</strong> <?php echo date('Y.m.d H:i', $startTimeStamp); ?></p>
                                <p class="small text-secondary mb-0">🔑 <strong>Code:</strong> <code><?php echo htmlspecialchars($booking['booking_code']); ?></code></p>
                            </div>

                            <div class="d-flex flex-row flex-sm-column align-items-center align-items-sm-end justify-content-between justify-content-sm-start gap-2 w-100 w-sm-auto">
                                <span class="badge rounded-pill <?php echo $statusBadgeClass; ?> text-uppercase">
                                    <?php echo htmlspecialchars($booking['status']); ?>
                                </span>

                                <?php if ($canCancel): ?>
                                    <form action="actions/cancel_booking.php" method="POST" onsubmit="return confirm('Are you sure you want to cancel this reservation?');" class="m-0">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Cancel Reservation</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

      </section>
    </div>
  </main>

  <footer>
    <div class="footer-brand"><img src="assets/img/logo.png" alt="ACE logo"> ACE</div>
    <div class="footer-links">
      <a href="about.php">About</a>
      <a href="contact.php">Contact</a>
      <a href="terms.php">Terms</a>
      <a href="privacy.php">Privacy</a>
    </div>
    <div class="footer-copy">© 2026 SportFields. All rights reserved.</div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
