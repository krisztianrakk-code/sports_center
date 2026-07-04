<?php
/**
 * Shared Header Component
 * Path: components/header.php
 *
 * Include this with: require_once 'components/header.php';
 * The parent page must already have called session_start()
 * and must load Bootstrap CSS in its <head> (see note below).
 */

// Biztonsági háló: ha a beillesztő oldal elfelejtette elindítani a sessiont
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// $initial mindig itt számolódik ki, így egyetlen oldalnak sem kell
// külön-külön elkészítenie (ez okozta korábban a hiányzó/eltérő avatart)
$initial = (isset($_SESSION['user_id']) && !empty($_SESSION['user_name']))
    ? mb_strtoupper(mb_substr(trim($_SESSION['user_name']), 0, 1, 'UTF-8'), 'UTF-8')
    : "";
?>
<style>
    :root {
        --c-football: #10a36b;     /* zöld */
        --c-basketball: #f5a623;   /* narancssárga */
        --c-volleyball: #2f6fed;   /* kék */
        --c-tennis: #c2660d;       /* salak / barna */
        --c-default: #5b4cf5;      /* márka lila (header, alapértelmezett) */
    }

    .sport-btn {
        color: #fff !important;
        border: none;
    }
    .sport-btn:hover {
        filter: brightness(0.92);
        color: #fff !important;
    }

    /* Header profil-avatar: színes kör + a név első betűje, minden oldalon ugyanígy */
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

<header class="d-flex justify-content-between align-items-center bg-white border-bottom px-4 px-sm-5" style="height:70px;">
    <div class="d-flex align-items-center gap-2">
      <img src="assets/img/logo.png" alt="ACE logo" style="height:32px;object-fit:contain;">
      <a href="index.php" class="fw-bold text-dark text-decoration-none">ACE</a>
    </div>
    <div>
      <?php if (isset($_SESSION['user_id'])): ?>

        <a href="profile.php" class="d-flex align-items-center gap-2 text-decoration-none text-dark fw-semibold">
          <div class="avatar-badge">
            <?php echo htmlspecialchars($initial); ?>
          </div>
          <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        </a>

      <?php else: ?>

        <a href="login.php" class="text-secondary text-decoration-none small px-2">Login</a>
        <a href="register.php" class="btn btn-sm sport-btn" style="background-color:var(--c-default) !important;">Sign Up</a>

      <?php endif; ?>
    </div>
</header>