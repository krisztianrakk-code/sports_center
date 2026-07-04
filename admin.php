<?php
/**
 * Administrative Infrastructure, Court Management & Global Booking Monitor
 * Path: admin.php
 */
session_start();
require_once 'config/db_config.php';
require_once 'classes/User.php';
require_once 'classes/AdminCourt.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("ACCESS DENIED: Administrative privileges required.");
}

$userObj = new User($pdo);
$adminCourtObj = new AdminCourt($pdo);

$courtMessage = "";
$userMessage = "";

// 1. ÚJ PÁLYA HOZZÁADÁSA
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_court_action'])) {
    $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
    $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_SPECIAL_CHARS);
    $is_indoor = filter_input(INPUT_POST, 'is_indoor', FILTER_SANITIZE_NUMBER_INT);
    $price = filter_input(INPUT_POST, 'price_per_hour', FILTER_VALIDATE_FLOAT);
    $location = trim(filter_input(INPUT_POST, 'location', FILTER_SANITIZE_SPECIAL_CHARS));
    $equipment = trim(filter_input(INPUT_POST, 'equipment_included', FILTER_SANITIZE_SPECIAL_CHARS));

    if (empty($name) || empty($location) || $price === false) {
        $courtMessage = "Error: Invalid facility parameters.";
    } else {
        if ($adminCourtObj->addCourt($name, $type, $is_indoor, $price, $location, $equipment)) {
            $courtMessage = "SUCCESS: New court asset deployed.";
        } else {
            $courtMessage = "Error: Failed to register court.";
        }
    }
}

// 2. PÁLYA TÖRLÉSE
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_court_action'])) {
    $courtId = filter_input(INPUT_POST, 'court_id', FILTER_SANITIZE_NUMBER_INT);
    if ($adminCourtObj->deleteCourt($courtId)) {
        $courtMessage = "SUCCESS: Court removed from the database.";
    } else {
        $courtMessage = "Error: Deletion constraint anomaly.";
    }
}

// 3. BLOKKOLT FELHASZNÁLÓ FELOLDÁSA
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['unblock_user_action'])) {
    $targetUserId = filter_input(INPUT_POST, 'blocked_user_id', FILTER_SANITIZE_NUMBER_INT);
    if ($userObj->unblockUser($targetUserId)) {
        $userMessage = "SUCCESS: Account reactivated.";
    } else {
        $userMessage = "Error: Reactivation failed.";
    }
}

// Adatok betöltése a felületre
$blockedUsers = $userObj->getBlockedUsers();

// --- KERESÉS ÉS LIMITÁLÁS LOGIKA (ÚJ) ---
$search_query = "";
if (isset($_GET['search_court']) && trim($_GET['search_court']) !== "") {
    $search_query = trim($_GET['search_court']);
    // Keresés esetén kilistázza a találatokat név alapján
    $courtStmt = $pdo->prepare("SELECT * FROM courts WHERE name LIKE ? ORDER BY id DESC");
    $courtStmt->execute(["%" . $search_query . "%"]);
} else {
    // Alapértelmezetten csak a legutóbbi 10 pályát kéri le
    $courtStmt = $pdo->query("SELECT * FROM courts ORDER BY id DESC LIMIT 10");
}
$allCourts = $courtStmt->fetchAll();

// --- AZ ÖSSZES FOGLALÁS LEKÉRDEZÉSE A RENDSZERBEN ---
$globalBookingsStmt = $pdo->query("
    SELECT b.*, c.name AS court_name, u.name AS user_name, u.email AS user_email 
    FROM bookings b
    JOIN courts c ON b.court_id = c.id
    JOIN users u ON b.user_id = u.id
    ORDER BY b.start_time DESC
");
$globalBookings = $globalBookingsStmt->fetchAll();

// Adminisztrátor alapadatok az avatarhoz
$user_name = $_SESSION['user_name'] ?? 'Admin';
$initial = !empty($user_name) ? mb_strtoupper(mb_substr(trim($user_name), 0, 1, 'UTF-8'), 'UTF-8') : "A";
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - ACE Sports Center</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Brand color override - lets Bootstrap's built-in utilities (btn-primary,
           text-primary, border-primary, bg-primary...) use the ACE brand colors
           instead of Bootstrap's defaults. No other custom CSS is used. */
        :root {
            --bs-primary: #5b4cf5;
            --bs-primary-rgb: 91, 76, 245;
            --bs-body-font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        body { background-color: #f9fafb; }
        .row-no_show { background-color: #fef2f2 !important; }
        .row-cancelled { background-color: #f3f4f6 !important; }
        .row-approved { background-color: #ecfdf5 !important; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

<?php require_once 'components/header.php'; ?>

  <main class="container my-5 flex-fill">
    <div class="row g-4">

      <aside class="col-lg-3">
        <div class="card shadow-sm rounded-4 p-4 h-auto">
          <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center fw-bold mx-auto mb-3" style="width:80px;height:80px;font-size:32px;">
            <?php echo htmlspecialchars($initial); ?>
          </div>
          <div class="text-center mb-3">
            <h3 class="h5 mb-1"><?php echo htmlspecialchars($user_name); ?></h3>
            <span class="badge rounded-pill bg-danger-subtle text-danger text-uppercase">System Admin</span>
          </div>
          <a href="profile.php" class="btn btn-dark w-100">👤 Back to My Profile</a>
        </div>
      </aside>

      <section class="col-lg-9">

        <div class="row g-4 mb-4">

            <div class="col-lg-7">
              <div class="card shadow-sm rounded-4 p-4 h-100">
                <h3 class="h5 mb-3">🏟️ Field & Court Inventory Manager</h3>

                <?php if (!empty($courtMessage)): ?>
                    <div class="alert <?php echo (strpos($courtMessage, 'Error') !== false) ? 'alert-danger' : 'alert-success'; ?> fw-semibold">
                        <?php echo $courtMessage; ?>
                    </div>
                <?php endif; ?>

                <form action="admin.php" method="POST" class="mb-4">
                    <input type="hidden" name="add_court_action" value="1">

                    <div class="mb-3">
                        <label for="c_name" class="form-label small fw-semibold">Field/Court Name</label>
                        <input type="text" name="name" id="c_name" required placeholder="e.g. Arena 1 Subotica" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="c_type" class="form-label small fw-semibold">Sport Category Type</label>
                        <select name="type" id="c_type" class="form-select">
                            <option value="basketball">Basketball</option>
                            <option value="football">Football</option>
                            <option value="volleyball">Volleyball</option>
                            <option value="tennis">Tennis</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="c_indoor" class="form-label small fw-semibold">Structure Setting</label>
                        <select name="is_indoor" id="c_indoor" class="form-select">
                            <option value="0">Outdoor (Kültéri)</option>
                            <option value="1">Indoor (Beltéri)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="c_price" class="form-label small fw-semibold">Price Per Hour ($)</label>
                        <input type="number" step="0.01" name="price_per_hour" id="c_price" required placeholder="25.00" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="c_loc" class="form-label small fw-semibold">Specific Location</label>
                        <input type="text" name="location" id="c_loc" required placeholder="e.g. Sector A Hall 2" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="c_equip" class="form-label small fw-semibold">Equipment Included</label>
                        <input type="text" name="equipment_included" id="c_equip" placeholder="e.g. Leather Balls, Professional Training Nets" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-success w-100">Deploy Court Infrastructure</button>
                </form>

                <hr>

                <h4 class="h6 mb-2">
                    <?php echo (!empty($search_query)) ? 'Search Results for: "' . htmlspecialchars($search_query) . '"' : 'Registered Facilities Matrix (Latest 10)'; ?>
                </h4>

                <form action="admin.php" method="GET" class="d-flex align-items-center gap-2 my-3">
                    <input type="text" name="search_court" placeholder="Search court by name..." value="<?php echo htmlspecialchars($search_query); ?>" class="form-control">
                    <button type="submit" class="btn btn-primary text-nowrap">Search</button>
                    <?php if (!empty($search_query)): ?>
                        <a href="admin.php" class="text-secondary text-nowrap small">Clear</a>
                    <?php endif; ?>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle small">
                        <thead>
                            <tr>
                                <th>Name (Type)</th>
                                <th>Setup</th>
                                <th>Rate</th>
                                <th>Location</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($allCourts)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-secondary py-4">No fields found matching the criteria.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($allCourts as $c): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($c['name']); ?></strong> <span class="text-secondary small">(<?php echo htmlspecialchars($c['type']); ?>)</span></td>
                                        <td><?php echo $c['is_indoor'] ? 'Indoor' : 'Outdoor'; ?></td>
                                        <td><strong>$<?php echo htmlspecialchars($c['price_per_hour']); ?></strong>/h</td>
                                        <td><?php echo htmlspecialchars($c['location']); ?></td>
                                        <td>
                                            <form action="admin.php" method="POST" onsubmit="return confirm('Completely purge this facility node?');" class="m-0">
                                                <input type="hidden" name="court_id" value="<?php echo $c['id']; ?>">
                                                <button type="submit" name="delete_court_action" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
              </div>
            </div>

            <div class="col-lg-5">
              <div class="card shadow-sm rounded-4 p-4 h-100">
                <h3 class="h5 mb-3">🔒 Blocked Accounts</h3>

                <?php if (!empty($userMessage)): ?>
                    <div class="alert alert-success fw-semibold">
                        <?php echo $userMessage; ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($blockedUsers)): ?>
                    <p class="text-success fw-semibold small bg-success-subtle p-3 rounded-3">No accounts are currently frozen.</p>
                <?php else: ?>
                    <?php foreach ($blockedUsers as $bu): ?>
                        <div class="card border-danger-subtle bg-danger-subtle bg-opacity-10 p-3 rounded-3 mb-3">
                            <strong class="text-dark"><?php echo htmlspecialchars($bu['name']); ?></strong>
                            <small class="text-secondary"><?php echo htmlspecialchars($bu['email']); ?></small>
                            <span class="text-danger small fw-semibold">Infractions: <?php echo $bu['negative_points']; ?> pts</span>

                            <form action="admin.php" method="POST" class="m-0">
                                <input type="hidden" name="blocked_user_id" value="<?php echo $bu['id']; ?>">
                                <button type="submit" name="unblock_user_action" class="btn btn-primary btn-sm w-100 mt-2">Lift Suspension</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-4 p-4">
            <h3 class="h5 text-primary mb-3">📋 Global Reservations Log (All Users)</h3>
            <div class="table-responsive">
                <table class="table table-hover align-middle small">
                    <thead>
                        <tr>
                            <th>Booking Code</th>
                            <th>Customer Details</th>
                            <th>Facility / Court</th>
                            <th>Time Window</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($globalBookings)): ?>
                            <tr>
                                <td colspan="5" class="text-center fw-bold text-secondary py-5">No reservations recorded in the system.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($globalBookings as $gb): 
                                $rowClass = '';
                                if($gb['status'] === 'no_show') $rowClass = 'row-no_show';
                                elseif($gb['status'] === 'cancelled') $rowClass = 'row-cancelled';
                                elseif($gb['status'] === 'approved') $rowClass = 'row-approved';
                            ?>
                                <tr class="<?php echo $rowClass; ?>">
                                    <td><code><strong><?php echo htmlspecialchars($gb['booking_code']); ?></strong></code></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($gb['user_name']); ?></strong><br>
                                        <small class="text-secondary"><?php echo htmlspecialchars($gb['user_email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($gb['court_name']); ?></td>
                                    <td>
                                        <code><?php echo htmlspecialchars($gb['start_time']); ?></code><br>
                                        <small class="text-secondary">Duration: <?php echo htmlspecialchars($gb['duration_minutes']); ?> mins</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary text-uppercase"><?php echo htmlspecialchars($gb['status']); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

      </section>
    </div>
  </main>

<?php require_once 'components/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
