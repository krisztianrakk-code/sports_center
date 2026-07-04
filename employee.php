<?php
/**
 * Staff Reception Desk - UI Panel for Booking Verifier & Trackers
 * Path: employee.php
 */
session_start();
require_once 'config/db_config.php';

// Biztonsági kapu
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'employee' && $_SESSION['role'] !== 'admin')) {
    die("ACCESS DENIED: Staff privileges required.");
}

$message = "";
$message_type = "info";
if (isset($_SESSION['employee_msg'])) {
    $message = $_SESSION['employee_msg'];
    $message_type = $_SESSION['employee_msg_type'] ?? 'info';
    unset($_SESSION['employee_msg'], $_SESSION['employee_msg_type']);
}

$searchResult = null;
$searchQuery = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['search_code_action'])) {
    $searchQuery = strtoupper(trim(filter_input(INPUT_POST, 'booking_code', FILTER_SANITIZE_SPECIAL_CHARS)));
    
    if (!empty($searchQuery)) {
        $searchStmt = $pdo->prepare("
            SELECT b.*, c.name AS court_name, u.name AS user_name, u.email AS user_email 
            FROM bookings b
            JOIN courts c ON b.court_id = c.id
            JOIN users u ON b.user_id = u.id
            WHERE b.booking_code = ? LIMIT 1
        ");
        $searchStmt->execute([$searchQuery]);
        $searchResult = $searchStmt->fetch();
        
        if (!$searchResult) {
            $message = "ERROR: Booking code [ $searchQuery ] not found.";
            $message_type = "error";
        }
    }
}

// 1. PENDING (FÜGGŐBEN LÉVŐ)
$pendingStmt = $pdo->query("
    SELECT b.*, c.name AS court_name, u.name AS user_name 
    FROM bookings b
    JOIN courts c ON b.court_id = c.id
    JOIN users u ON b.user_id = u.id
    WHERE b.status = 'pending'
    ORDER BY b.start_time ASC
");
$pendingBookings = $pendingStmt->fetchAll();

// 2. MAI FOGLALÁSOK (Approved vagy Attended, függetlenül az időtől)
$activeStmt = $pdo->query("
    SELECT b.*, c.name AS court_name, u.name AS user_name 
    FROM bookings b
    JOIN courts c ON b.court_id = c.id
    JOIN users u ON b.user_id = u.id
    WHERE (b.status = 'approved' OR b.status = 'attended') 
    AND DATE(b.start_time) = CURDATE()
    ORDER BY b.start_time ASC
");
$activeBookings = $activeStmt->fetchAll();

$user_name = $_SESSION['user_name'] ?? 'Staff';
$user_role = $_SESSION['role'] ?? 'employee';
$initial = !empty($user_name) ? mb_strtoupper(mb_substr(trim($user_name), 0, 1, 'UTF-8'), 'UTF-8') : "S";
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Reception Desk - ACE Sports Center</title>
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

  <main class="container my-5 flex-fill">
    <div class="row g-4">

      <aside class="col-lg-3">
        <div class="card shadow-sm rounded-4 p-4 h-auto">
          <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold mx-auto mb-3" style="width:80px;height:80px;font-size:32px;">
            <?php echo htmlspecialchars($initial); ?>
          </div>
          <div class="text-center mb-3">
            <h3 class="h5 mb-1"><?php echo htmlspecialchars($user_name); ?></h3>
            <span class="badge rounded-pill bg-danger-subtle text-danger text-uppercase"><?php echo ($user_role === 'admin') ? 'System Admin' : 'Reception Staff'; ?></span>
          </div>
          <a href="profile.php" class="btn btn-dark w-100">👤 Back to My Profile</a>
        </div>
      </aside>

      <section class="col-lg-9">

        <?php if (!empty($message)): ?>
            <?php
                $alertClass = 'alert-warning';
                if ($message_type === 'success') $alertClass = 'alert-success';
                elseif ($message_type === 'error') $alertClass = 'alert-danger';
            ?>
            <div class="alert <?php echo $alertClass; ?> fw-semibold">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="card border-primary border-2 shadow-sm rounded-4 p-4 mb-4">
            <h3 class="h5 text-primary mb-1">🔍 Code Check-in Verifier</h3>
            <p class="text-secondary small mb-3">Enter the verification code provided by the player upon arrival:</p>

            <form action="employee.php" method="POST" class="d-flex flex-column flex-sm-row gap-2">
                <input type="text" name="booking_code" placeholder="e.g. 1A2B3C4D5E6F70819203A4B5C6D7E8F9" value="<?php echo htmlspecialchars($searchQuery); ?>" required maxlength="32"
                       class="form-control font-monospace text-uppercase fw-bold" style="letter-spacing:1px;">
                <button type="submit" name="search_code_action" class="btn btn-primary">Verify Code</button>
            </form>

            <?php if ($searchResult): ?>
                <div class="border-top pt-3 mt-3">
                    <h4 class="h6 mb-3">Result for Code: <code class="bg-light text-danger px-2 py-1 rounded"><?php echo $searchResult['booking_code']; ?></code></h4>

                    <table class="table table-borderless table-sm mb-3">
                        <tr><td class="fw-bold" style="width:140px;">Player Name:</td><td><strong><?php echo htmlspecialchars($searchResult['user_name']); ?></strong> (<?php echo htmlspecialchars($searchResult['user_email']); ?>)</td></tr>
                        <tr><td class="fw-bold">Court/Field:</td><td><?php echo htmlspecialchars($searchResult['court_name']); ?></td></tr>
                        <tr><td class="fw-bold">Time Window:</td><td><code><?php echo htmlspecialchars($searchResult['start_time']); ?></code> (<?php echo $searchResult['duration_minutes']; ?> mins)</td></tr>
                        <tr><td class="fw-bold">Current Status:</td><td><span class="badge bg-primary-subtle text-primary text-uppercase"><?php echo $searchResult['status']; ?></span></td></tr>
                    </table>

                    <form action="actions/process_employee_action.php" method="POST" class="bg-light border rounded-3 p-3">
                        <input type="hidden" name="booking_id" value="<?php echo $searchResult['id']; ?>">

                        <div class="mb-3">
                            <label for="search_note" class="form-label small fw-semibold">Internal Staff Notes / Comments</label>
                            <input type="text" name="staff_note" id="search_note" value="<?php echo htmlspecialchars($searchResult['staff_notes'] ?? ''); ?>" class="form-control" placeholder="e.g. Borrowed 2 rackets, Late arrival...">
                        </div>

                        <div class="d-flex align-items-end gap-3 flex-wrap">
                            <div>
                                <label for="search_status" class="form-label small fw-semibold">Update Status / Attendance</label>
                                <select name="new_status" id="search_status" class="form-select">
                                    <option value="approved" <?php if($searchResult['status']=='approved') echo 'selected'; ?>>Approved (Jóváhagyva)</option>
                                    <option value="attended" <?php if($searchResult['status']=='attended') echo 'selected'; ?>>Attended (Megérkezett)</option>
                                    <option value="no_show" <?php if($searchResult['status']=='no_show') echo 'selected'; ?>>No-Show (+1 Strike point)</option>
                                    <option value="rejected" <?php if($searchResult['status']=='rejected') echo 'selected'; ?>>Rejected (Elutasítva)</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <div class="row g-4">

            <div class="col-md-6">
              <div class="card shadow-sm rounded-4 p-4 border-top border-4 border-warning h-100">
                <h3 class="h6 mb-1">⏳ Incoming Requests</h3>
                <p class="small text-secondary mb-3">Review and either approve or decline new pending reservations.</p>

                <?php if (empty($pendingBookings)): ?>
                    <p class="text-success fw-semibold small bg-success-subtle p-3 rounded-3">No pending requests to process.</p>
                <?php else: ?>
                    <?php foreach ($pendingBookings as $pb): ?>
                        <div class="card bg-light border-start border-4 border-warning rounded-3 p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-warning-subtle text-warning small">CODE: <?php echo $pb['booking_code']; ?></span>
                            </div>
                            <div class="small text-secondary lh-lg">
                                User: <strong class="text-dark"><?php echo htmlspecialchars($pb['user_name']); ?></strong><br>
                                Facility: <strong class="text-dark"><?php echo htmlspecialchars($pb['court_name']); ?></strong><br>
                                Time: <code class="text-danger"><?php echo htmlspecialchars($pb['start_time']); ?></code> (<?php echo $pb['duration_minutes']; ?> mins)
                            </div>

                            <div class="mt-3">
                                <input type="text" id="note_<?php echo $pb['id']; ?>" placeholder="Optional staff note..." class="form-control form-control-sm mb-2" onchange="document.getElementById('hidden_note_app_<?php echo $pb['id']; ?>').value = this.value; document.getElementById('hidden_note_rej_<?php echo $pb['id']; ?>').value = this.value;">

                                <div class="d-flex gap-2">
                                    <form action="actions/process_employee_action.php" method="POST" class="flex-fill m-0" onsubmit="return confirm('Confirm approval?');">
                                        <input type="hidden" name="booking_id" value="<?php echo $pb['id']; ?>">
                                        <input type="hidden" name="new_status" value="approved">
                                        <input type="hidden" name="staff_note" id="hidden_note_app_<?php echo $pb['id']; ?>" value="">
                                        <button type="submit" class="btn btn-success btn-sm w-100">✔ Approve</button>
                                    </form>

                                    <form action="actions/process_employee_action.php" method="POST" class="flex-fill m-0" onsubmit="return confirm('Confirm rejection?');">
                                        <input type="hidden" name="booking_id" value="<?php echo $pb['id']; ?>">
                                        <input type="hidden" name="new_status" value="rejected">
                                        <input type="hidden" name="staff_note" id="hidden_note_rej_<?php echo $pb['id']; ?>" value="">
                                        <button type="submit" class="btn btn-danger btn-sm w-100">✖ Reject</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>

            <div class="col-md-6">
              <div class="card shadow-sm rounded-4 p-4 border-top border-4 border-success h-100">
                <h3 class="h6 mb-1">📅 Today's Schedule</h3>
                <p class="small text-secondary mb-3">Quick-access counter checklist to record arrivals as players walk in.</p>

                <?php if (empty($activeBookings)): ?>
                    <p class="text-secondary small bg-light p-3 rounded-3">No approved sessions found on the monitor.</p>
                <?php else: ?>
                    <?php foreach ($activeBookings as $ab): ?>
                        <div class="card bg-light border-start border-4 border-success rounded-3 p-3 mb-3">
                            <div class="border-bottom pb-2 mb-3">
                                <div class="small text-dark mb-1">
                                    <strong><?php echo htmlspecialchars($ab['user_name']); ?></strong> - <?php echo htmlspecialchars($ab['court_name']); ?>
                                </div>
                                <div class="small text-secondary">
                                    Időpont: <code><?php echo htmlspecialchars($ab['start_time']); ?></code>
                                </div>
                            </div>

                            <div class="d-flex flex-column gap-2">
                                <?php if ($ab['status'] !== 'completed'): ?>
                                    <form action="actions/process_employee_action.php" method="POST" class="m-0" onsubmit="return confirm('Check-in player?');">
                                        <input type="hidden" name="booking_id" value="<?php echo $ab['id']; ?>">
                                        <input type="hidden" name="new_status" value="completed">
                                        <input type="hidden" name="staff_note" value="<?php echo htmlspecialchars($ab['staff_notes'] ?? ''); ?>">
                                        <button type="submit" class="btn btn-success w-100">👤 Check-in Player</button>
                                    </form>

                                    <form action="actions/process_employee_action.php" method="POST" class="m-0" onsubmit="return confirm('Are you sure?');">
                                        <input type="hidden" name="booking_id" value="<?php echo $ab['id']; ?>">
                                        <input type="hidden" name="new_status" value="no_show">
                                        <button type="submit" class="btn btn-warning text-white w-100">❌ No-Show Penalty</button>
                                    </form>
                                <?php else: ?>
                                    <div class="bg-success-subtle text-success text-center small fw-bold p-2 rounded-3">
                                        ✅ TELJESÍTVE
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>

        </div>

      </section>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>