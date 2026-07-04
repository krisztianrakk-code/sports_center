<?php
/**
 * Court Facility Inventory Listing - Search, Filter, Extended Sort & Sport Selector Edition
 * Path: fields.php
 */
session_start();
require_once 'config/db_config.php';

// Lekérjük a sportág típusát (football, basketball, volleyball, tennis)
$sport_type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'football';

// --- FILTEREK, RENDEZÉS ÉS KERESÉS FOGADÁSA ---
$search_query = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$environment  = filter_input(INPUT_GET, 'environment', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'all';
$sort         = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'default';

// Felhasználó nevének első betűje az avatarhoz (ha be van lépve)
$initial = (isset($_SESSION['user_id']) && !empty($_SESSION['user_name'])) ? mb_strtoupper(mb_substr(trim($_SESSION['user_name']), 0, 1, 'UTF-8'), 'UTF-8') : "";

// --- DINAMIKUS SPORT SZÍN BEÁLLÍTÁSA ---
// Ugyanazok az értékek, mint a --c-football / --c-basketball / --c-volleyball / --c-tennis
// CSS változók, csak PHP oldalon is elérhetővé téve a query/logika számára.
switch ($sport_type) {
    case 'football':
        $current_color = '#10a36b'; // Zöld
        break;
    case 'basketball':
        $current_color = '#f5a623'; // Narancs
        break;
    case 'volleyball':
        $current_color = '#2f6fed'; // Kék
        break;
    case 'tennis':
        $current_color = '#c2660d'; // Salak / Barna
        break;
    default:
        $current_color = '#5b4cf5'; // Alapértelmezett lila
        break;
}

// --- DINAMIKUS SQL LEKÉRDEZÉS FELÉPÍTÉSE ---
$query = "SELECT * FROM courts WHERE type = ?";
$params = [$sport_type];

// 1. Szabadszöveges keresés (Név vagy Helyszín alapján)
if (!empty($search_query)) {
    $query .= " AND (name LIKE ? OR location LIKE ?)";
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
}

// 2. Környezet szűrés (A DB alapján: 1 = indoor, 0 = outdoor)
if ($environment === 'indoor') {
    $query .= " AND is_indoor = 1";
} elseif ($environment === 'outdoor') {
    $query .= " AND is_indoor = 0";
}

// 3. Kibővített rendezés hozzáadása az SQL végéhez
if ($sort === 'price_asc') {
    $query .= " ORDER BY price_per_hour ASC";
} elseif ($sort === 'price_desc') {
    $query .= " ORDER BY price_per_hour DESC";
} elseif ($sort === 'popular') {
    $query .= " ORDER BY booking_count DESC";
} elseif ($sort === 'latest') {
    $query .= " ORDER BY created_at DESC";
} else {
    $query .= " ORDER BY id DESC";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$courts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars(ucfirst($sport_type)); ?> Fields - ACE</title>

<link rel="stylesheet" href="assets/css/style.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    :root {
        --c-football: #10a36b;     /* zöld */
        --c-basketball: #f5a623;   /* narancssárga */
        --c-volleyball: #2f6fed;   /* kék */
        --c-tennis: #c2660d;       /* salak / barna */
        --c-default: #5b4cf5;      /* márka lila (header, alapértelmezett) */

        --bs-body-font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    body { background-color: #f9fafb; }

    .facility-card { transition: transform 0.2s ease; }
    .facility-card:hover { transform: translateY(-4px); }
    .facility-img { height: 180px; object-fit: cover; }

    /* Sportáganként színezett gombok - a --c-<sport> változókból */
    .sport-btn {
        background-color: var(--c-<?php echo $sport_type; ?>, var(--c-default));
        color: #fff !important;
        border: none;
    }
    .sport-btn:hover {
        filter: brightness(0.92);
        color: #fff !important;
    }

    /* Header profil-avatar: színes kör + a név első betűje */
    .avatar-badge {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background-color: var(--c-default) !important;
        color: #fff !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 15px;
    }
</style>
</head>
<body class="d-flex flex-column min-vh-100">

  <?php require_once 'components/header.php'; ?>

  <main class="container my-5 flex-fill">
    <a href="index.php" class="text-secondary text-decoration-none small d-inline-block mb-2">&larr; Back to Home</a>
    <h1 class="display-6 fw-bold mb-1"><?php echo htmlspecialchars(ucfirst($sport_type)); ?> Fields</h1>
    <p class="text-secondary mb-4">Choose from our premium <?php echo htmlspecialchars($sport_type); ?> facilities</p>

    <div class="card shadow-sm rounded-4 p-4 mb-4">
        <form method="GET" action="fields.php" class="row g-3 align-items-end">

            <div class="col-md-6 col-lg-2">
                <label for="filter_sport_type" class="form-label small fw-semibold">Sport Type</label>
                <select name="type" id="filter_sport_type" class="form-select">
                    <option value="football" <?php echo $sport_type === 'football' ? 'selected' : ''; ?>>⚽ Football</option>
                    <option value="basketball" <?php echo $sport_type === 'basketball' ? 'selected' : ''; ?>>🏀 Basketball</option>
                    <option value="volleyball" <?php echo $sport_type === 'volleyball' ? 'selected' : ''; ?>>🏐 Volleyball</option>
                    <option value="tennis" <?php echo $sport_type === 'tennis' ? 'selected' : ''; ?>>🎾 Tennis</option>
                </select>
            </div>

            <div class="col-md-6 col-lg-4">
                <label for="search_input" class="form-label small fw-semibold">Search Facility</label>
                <input type="text" name="search" id="search_input" class="form-control" placeholder="Search by name or location..." value="<?php echo htmlspecialchars($search_query); ?>">
            </div>

            <div class="col-md-6 col-lg-2">
                <label for="filter_environment" class="form-label small fw-semibold">Court Venue</label>
                <select name="environment" id="filter_environment" class="form-select">
                    <option value="all" <?php echo $environment === 'all' ? 'selected' : ''; ?>>All Venues</option>
                    <option value="indoor" <?php echo $environment === 'indoor' ? 'selected' : ''; ?>>Indoor Only</option>
                    <option value="outdoor" <?php echo $environment === 'outdoor' ? 'selected' : ''; ?>>Outdoor Only</option>
                </select>
            </div>

            <div class="col-md-6 col-lg-2">
                <label for="sort_price" class="form-label small fw-semibold">Sort By</label>
                <select name="sort" id="sort_price" class="form-select">
                    <option value="default" <?php echo $sort === 'default' ? 'selected' : ''; ?>>Default Sorting</option>
                    <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>🔥 Most Popular</option>
                    <option value="latest" <?php echo $sort === 'latest' ? 'selected' : ''; ?>>✨ Latest Courts</option>
                    <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                </select>
            </div>

            <div class="col-lg-2">
                <button type="submit" class="btn sport-btn w-100">Apply Filters</button>
            </div>
        </form>
    </div>

    <?php if (empty($courts)): ?>
        <div class="card shadow-sm rounded-4 p-5 text-center text-secondary">
            <p class="mb-0">No facilities found matching your search or filters.</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($courts as $court): ?>
                <div class="col-sm-6 col-lg-4 col-xl-3">
                  <div class="facility-card card shadow-sm rounded-4 overflow-hidden h-100">
                   <img src="<?php echo htmlspecialchars($court['image_path']); ?>" class="facility-img card-img-top" alt="<?php echo htmlspecialchars($court['name']); ?>" onerror="this.src='assets/img/football.jpg'">

                    <div class="card-body d-flex flex-column">
                        <h3 class="h5 fw-bold mb-1"><?php echo htmlspecialchars($court['name']); ?></h3>
                        <p class="text-secondary small mb-2">📍 <?php echo htmlspecialchars($court['location']); ?></p>

                        <?php if (isset($court['is_indoor'])): ?>
                            <p class="text-secondary small mb-3">
                                🏟️ <?php echo ($court['is_indoor'] == 1) ? 'Indoor' : 'Outdoor'; ?>
                            </p>
                        <?php endif; ?>

                        <div class="mt-auto">
                            <div class="mb-3 text-secondary small">
                                <span class="fs-4 fw-bold text-dark">$<?php echo htmlspecialchars($court['price_per_hour']); ?></span> /hour
                            </div>
                            <a href="details.php?id=<?php echo $court['id']; ?>" class="btn sport-btn w-100">View Details</a>
                        </div>
                    </div>
                  </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
  </main>

<?php require_once 'components/footer.php'; ?>

  <div class="help-btn">?</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>