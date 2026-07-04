<?php
/**
 * Main Application Hub - Clean Category Entry
 * Path: index.php
 */
session_start();
require_once 'config/db_config.php';
?>
<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ACE Sports Center - Home</title>
<link rel="stylesheet" href="assets/css/style.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php require_once 'components/header.php'; ?>

<div class="layout">
    <aside class="sidebar">
        <h4>Available Fields</h4>
        <button class="field-item active" data-field="football">Football</button>
        <button class="field-item" data-field="basketball">Basketball</button>
        <button class="field-item" data-field="volleyball">Volleyball</button>
        <button class="field-item" data-field="tennis">Tennis</button>
    </aside>

    <section class="hero">
        <div class="hero-text">
            <span class="badge" id="badge">Football</span>

            <h1 id="title">Book Premium Football Fields</h1>

            <p id="desc">
                Professional-grade football pitches available for hourly rental.
                Perfect for matches and training sessions.
            </p>

            <a class="book-btn" id="book-now-btn" href="fields.php?type=football">
                Book Now
            </a>

            <div class="dots">
                <button class="dot active" data-i="0"><span class="fill"></span></button>
                <button class="dot" data-i="1"><span class="fill"></span></button>
                <button class="dot" data-i="2"><span class="fill"></span></button>
                <button class="dot" data-i="3"><span class="fill"></span></button>
            </div>
        </div>

        <div class="hero-image-wrap">
            <span class="badge-overlay" id="badge2">Football</span>
            <img id="hero-img" src="assets/img/football.jpg" alt="Football field">
        </div>
    </section>
</div>

<?php require_once 'components/footer.php'; ?>

<div class="help-btn">?</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>

</body>
</html>