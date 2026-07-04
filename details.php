<?php
/**
 * Core Operational View - Facility Details, AJAX Trigger and Transaction Processing
 * Path: details.php
 */
session_start();
require_once 'config/db_config.php';
require_once 'classes/Booking.php';
require_once 'config/email_config.php'; // E-mail küldéshez szükséges

$court_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$court_id) {
    die("Error: Critical routing parameter missing.");
}

$bookingObj = new Booking($pdo);
$court = $bookingObj->getCourtDetails($court_id);

if (!$court) {
    die("Error: Target facility infrastructure not found.");
}

// Felhasználó nevének első betűje az avatarhoz (ha be van lépve)
$initial = (isset($_SESSION['user_id']) && !empty($_SESSION['user_name'])) ? mb_strtoupper(mb_substr(trim($_SESSION['user_name']), 0, 1, 'UTF-8'), 'UTF-8') : "";

$message = "";

// --- FOGLALÁS MENTÉSÉNEK FELDOLGOZÁSA (POST) ---
// --- FOGLALÁS MENTÉSÉNEK FELDOLGOZÁSA (POST) ---
// --- FOGLALÁS MENTÉSÉNEK FELDOLGOZÁSA (POST) ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['commit_booking'])) {
    
    // Biztosítjuk a megfelelő időzónát a szerveren
    date_default_timezone_set('Europe/Belgrade');

    if (!isset($_SESSION['user_id'])) {
        $message = "Error: You must be logged in to execute a booking transaction.";
    } else {
        $date = filter_input(INPUT_POST, 'booking_date', FILTER_SANITIZE_SPECIAL_CHARS);
        $time = filter_input(INPUT_POST, 'selected_time', FILTER_SANITIZE_SPECIAL_CHARS);
        $duration = filter_input(INPUT_POST, 'duration', FILTER_SANITIZE_NUMBER_INT) ?? 60;

        if (empty($date) || empty($time)) {
            $message = "Error: Please choose both a valid date and an available timeslot.";
        } else {
            $start_time = $date . ' ' . $time;
            $end_time = date('Y-m-d H:i:s', strtotime($start_time . " +{$duration} minutes"));

            // 1. VÉDELMI VONAL: Múltbeli időpont ellenőrzése
            // A strtotime($start_time) átalakítja a választott dátumot időbélyeggé
            // A time() pedig a szerver mostani pontos idejét adja
            if (strtotime($start_time) < (time() - 60)) { // 60 másodperc türelmi idő a hálózati késés miatt
                $message = "Error: You cannot book a timeslot in the past.";
            } 
            // 2. VÉDELMI VONAL: Adatbázis szintű szabad sáv ellenőrzés
            elseif (!$bookingObj->isSlotAvailable($court_id, $start_time, $end_time)) {
                $message = "Error: This specific timeslot has just been reserved by someone else.";
            } 
            else {
                // Minden rendben, mehet a foglalás
                $booking_code = bin2hex(random_bytes(16));
                $user_id = $_SESSION['user_id'];

                $sql = "INSERT INTO bookings (booking_code, user_id, court_id, start_time, end_time, duration_minutes, status)
                        VALUES (?, ?, ?, ?, ?, ?, 'pending')";
                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute([$booking_code, $user_id, $court_id, $start_time, $end_time, $duration])) {
                    $message = "SUCCESS: Your booking is registered! Code: " . $booking_code;
                    
                    // E-mail küldése
                    try {
                        global $mail;
                        $mail->clearAddresses();
                        $mail->addAddress($_SESSION['user_email'], $_SESSION['user_name']);
                        $mail->isHTML(true);
                        $mail->Subject = 'Foglalási kérelem beérkezett - ACE Sports Center';
                        $mail->Body = "
                            <h2>Kedves {$_SESSION['user_name']}!</h2>
                            <p>Köszönjük a foglalásodat. A kérelem beérkezett, jelenleg <strong>függőben</strong> van.</p>
                            <p><strong>Foglalási kód:</strong> $booking_code</p>
                            <p><strong>Időpont:</strong> $start_time</p>";
                        $mail->send();
                    } catch (Exception $e) {
                        // Az email hiba nem érvényteleníti a foglalást
                    }
                } else {
                    $message = "Error: Database insertion anomaly detected.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ACE - <?php echo htmlspecialchars($court['name']); ?></title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>

<body class="bg-gray-50 text-gray-800 font-sans antialiased min-h-screen flex flex-col justify-between">



  <header class="flex justify-between items-center bg-white border-b border-gray-200 px-4 sm:px-8 h-[70px]">
    <div class="flex items-center gap-2">
      <img src="assets/img/logo.png" alt="ACE logo" class="h-8 object-contain">
      <span class="font-bold text-gray-900">ACE</span>
    </div>
    <div>
      <?php if (isset($_SESSION['user_id'])): ?>

        <a href="profile.php" class="flex items-center gap-2 no-underline text-gray-900 font-semibold">
          <div class="rounded-full bg-[#5b4cf5] text-white flex items-center justify-center font-bold" style="width:35px;height:35px;font-size:15px;">
            <?php echo htmlspecialchars($initial); ?>
          </div>
          <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        </a>

      <?php else: ?>

        <a href="login.php" class="text-gray-500 no-underline text-sm px-2 hover:text-gray-800 transition">Login</a>
        <a href="register.php" class="inline-block bg-[#5b4cf5] hover:bg-[#4636d6] text-white text-sm font-medium px-4 py-2 rounded-lg no-underline transition">Sign Up</a>

      <?php endif; ?>
    </div>
  </header>



    <main class="max-w-7xl mx-auto px-4 py-6 w-full flex-grow">

       

        <a href="fields.php?type=<?php echo $court['type']; ?>" class="text-sm text-gray-500 hover:text-gray-800 inline-flex items-center gap-2 mb-6 transition">

            <i class="fa-solid fa-arrow-left"></i> Back to <?php echo htmlspecialchars($court['type'] ?? 'football'); ?> fields

        </a>



        <?php if (!empty($message)): ?>

            <div class="mb-6 p-4 rounded-xl border <?php echo strpos($message, 'SUCCESS') !== false ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'; ?>">

                <p class="font-semibold"><?php echo $message; ?></p>

            </div>

        <?php endif; ?>



        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

           

            <div class="lg:col-span-2 space-y-6">

                <div class="w-full h-[450px] overflow-hidden rounded-2xl shadow-sm bg-gray-200">

                    <?php
                    // --- KÉP ELÉRÉSI ÚT MEGHATÁROZÁSA ---
                    // Elsődlegesen az adatbázisban tárolt image_path mezőt használjuk.
                    // Csak akkor esünk vissza a sport típus alapú alapértelmezett képre,
                    // ha az image_path üres, vagy a fájl ténylegesen nem létezik a szerveren.

                    $db_image = trim($court['image_path'] ?? '');

                    if (!empty($db_image) && file_exists($db_image)) {
                        $local_image_path = $db_image;
                    } else {
                        $court_type = !empty($court['type']) ? strtolower($court['type']) : 'default';
                        $local_image_path = 'assets/img/' . $court_type . '.png';

                        if (!file_exists($local_image_path)) {
                            $local_image_path = 'assets/img/' . $court_type . '.jpg';
                            if (!file_exists($local_image_path)) {
                                $local_image_path = 'assets/img/default.jpg';
                            }
                        }
                    }
                    ?>

                    <img src="<?php echo htmlspecialchars($local_image_path); ?>"

                         alt="<?php echo htmlspecialchars($court['name']); ?>"

                         class="w-full h-full object-cover">

                </div>



                <div class="flex justify-between items-start border-b border-gray-100 pb-6">

                    <div>

                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight"><?php echo htmlspecialchars($court['name']); ?></h1>

                        <div class="flex items-center gap-3 mt-2">

                            <span class="flex items-center text-amber-500 font-semibold text-sm gap-1">

                                <i class="fa-solid fa-star"></i> 4.5

                            </span>

                            <span class="bg-emerald-100 text-emerald-800 text-xs font-semibold px-2.5 py-1 rounded-full capitalize">

                                <?php echo htmlspecialchars($court['type'] ?? 'Football'); ?>

                            </span>

                        </div>

                        <p class="text-gray-500 text-sm mt-3 inline-flex items-center gap-1.5">

                            <i class="fa-solid fa-location-dot text-gray-400"></i> <?php echo htmlspecialchars($court['location']); ?>

                        </p>

                    </div>

                    <div class="text-right">

                        <p class="text-2xl font-bold text-gray-900">$<?php echo htmlspecialchars($court['price_per_hour']); ?></p>

                        <p class="text-xs text-gray-400 font-medium">per hour</p>

                    </div>

                </div>



                <div>

                    <h3 class="text-lg font-bold text-gray-900 mb-2">Description</h3>

                    <p class="text-gray-600 leading-relaxed text-sm">

                        <?php echo htmlspecialchars($court['description'] ?? 'Well-maintained field with premium infrastructure. Includes changing rooms and parking facilities.'); ?>

                    </p>

                </div>



                <div class="border-t border-gray-100 pt-6">

                    <h3 class="text-lg font-bold text-gray-900 mb-4">Amenities</h3>

                    <div class="grid grid-cols-2 gap-y-3 gap-x-4">

                        <div class="flex items-center gap-2 text-sm text-gray-700">

                            <i class="fa-regular fa-circle-check text-emerald-500 text-lg"></i> Changing Rooms

                        </div>

                        <div class="flex items-center gap-2 text-sm text-gray-700">

                            <i class="fa-regular fa-circle-check text-emerald-500 text-lg"></i> Parking Available

                        </div>

                        <div class="flex items-center gap-2 text-sm text-gray-700">

                            <i class="fa-regular fa-circle-check text-emerald-500 text-lg"></i> Restrooms

                        </div>

                        <div class="flex items-center gap-2 text-sm text-gray-700">

                            <i class="fa-regular fa-circle-check text-emerald-500 text-lg"></i> Equipment Storage

                        </div>

                        <div class="flex items-center gap-2 text-sm text-gray-700">

                            <i class="fa-regular fa-circle-check text-emerald-500 text-lg"></i> Lighting

                        </div>

                        <div class="flex items-center gap-2 text-sm text-gray-700">

                            <i class="fa-regular fa-circle-check text-emerald-500 text-lg"></i> Water Fountains

                        </div>

                    </div>

                </div>

            </div>



            <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm lg:sticky lg:top-24">

                <h3 class="text-lg font-bold text-gray-900 mb-4">Available Time Slots</h3>

               

                <input type="hidden" id="court_id" value="<?php echo $court['id']; ?>">



                <form action="details.php?id=<?php echo $court['id']; ?>" method="POST" class="space-y-4">

                    <div>

                        <label for="booking_date" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Select Date</label>

                        <input type="date" id="booking_date" name="booking_date" required

                               class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">

                    </div>



                    <div>

                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Select Time</label>

                        <div id="slots_container" 
                            class="max-h-60 overflow-y-auto pr-1 grid grid-cols-2 gap-2 text-center"
                            data-server-now="<?php echo date('Y-m-d H:i:s'); ?>">
                            <p class="col-span-2 text-xs text-gray-400 italic py-4">Select a date to pull schedule matrices...</p>
                        </div>

                        <input type="hidden" id="selected_time" name="selected_time" required>

                    </div>



                    <div>

                        <label for="duration" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Select Match Duration</label>

                        <select name="duration" id="duration"

                                class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">

                            <option value="60">1 Hour Block</option>

                            <option value="120">2 Hour Block</option>

                            <option value="180">3 Hour Block</option>

                        </select>

                    </div>



                    <button type="submit" name="commit_booking"

                            class="w-full bg-[#00a862] hover:bg-[#008f52] text-white font-medium py-3 rounded-xl transition shadow-sm mt-2 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2">

                        Book Now

                    </button>

                </form>

            </div>



        </div>

    </main>



   <footer class="bg-[#0b1329] text-gray-400 text-xs py-6 border-t border-slate-800">

        <div class="w-full px-6 grid grid-cols-1 sm:grid-cols-3 items-center gap-4">

           

            <div class="flex items-center gap-3 font-bold text-white text-sm justify-start">

                <div class="flex items-center gap-2 p-1 bg-white/10 rounded-lg">

                    <img src="assets/img/logo.png" alt="ACE Logo" class="w-6 h-6 object-cover rounded">

                </div>

                <span class="tracking-wider font-extrabold text-white">ACE</span>

            </div>

           

            <div class="flex gap-6 justify-center">

                <a href="about.php" class="hover:text-white transition">About</a>

                <a href="contact.php" class="hover:text-white transition">Contact</a>

                <a href="#" class="hover:text-white transition">Terms</a>

                <a href="#" class="hover:text-white transition">Privacy</a>

            </div>

           

            <div class="text-gray-500 text-center sm:text-right">

                &copy; 2026 SportFields. All rights reserved.

            </div>

           

        </div>

    </footer>



    <script src="assets/js/main.js"></script>

   <script>
dateInput.addEventListener('change', function() {
    const selectedDate = this.value;
    // A szerver aktuális órája, amit a PHP-ból kiveszünk (csak az órát, pl. 13)
    const currentHour = <?php echo (int)date('H'); ?>;
    const currentFullDate = "<?php echo date('Y-m-d'); ?>";

    fetch(`get_slots.php?court_id=${courtId}&date=${selectedDate}`)
        .then(response => response.json())
        .then(bookedSlots => {
            slotsContainer.innerHTML = '';
            
            for (let hour = 8; hour < 20; hour++) {
                const timeString = `${hour.toString().padStart(2, '0')}:00:00`;
                const fullDateTime = `${selectedDate} ${timeString}`;
                
                // Múltbeli ellenőrzés:
                // Ha a választott dátum ma van ÉS az óra kisebb, mint a mostani óra
                const isPast = (selectedDate === currentFullDate && hour < currentHour);
                const isBooked = bookedSlots.includes(fullDateTime);
                
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = `p-2 border rounded-lg text-sm transition ${
                    (isPast || isBooked) 
                    ? 'bg-gray-100 text-gray-400 cursor-not-allowed border-gray-100' 
                    : 'bg-white text-gray-700 border-gray-200 hover:border-emerald-500'
                }`;
                btn.innerText = `${hour.toString().padStart(2, '0')}:00`;
                
                if (!(isPast || isBooked)) {
                    btn.onclick = function() {
                        document.querySelectorAll('#slots_container button').forEach(b => b.classList.remove('bg-emerald-500', 'text-white'));
                        btn.classList.add('bg-emerald-500', 'text-white');
                        document.getElementById('selected_time').value = timeString;
                    };
                } else {
                    btn.disabled = true;
                }
                slotsContainer.appendChild(btn);
            }
        });
});
</script>

</body>

</html>