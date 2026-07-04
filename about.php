<?php
/**
 * About Page
 * Path: about.php
 */
?>
<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>About - ACE</title>

<!-- Bootstrap 5 -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">

<!-- Saját design tokenek -->
<link rel="stylesheet" href="style.css">

<style>
    body{
        min-height:100vh;
        display:flex;
        flex-direction:column;
        font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;
        background:#f7f8f9;
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
    .link-brand{
        color:var(--purple, #5b4cf5);
        font-weight:600;
        text-decoration:none;
    }
    .link-brand:hover{ color:var(--purple-dark, #4636d6); }

    /* Hero */
    .about-hero{
        background:linear-gradient(135deg, #6a5cf7 0%, var(--purple, #5b4cf5) 50%, var(--purple-dark, #4636d6) 100%);
        color:#fff;
        text-align:center;
        padding:70px 24px;
    }
    .about-hero h1{
        font-weight:800;
        font-size:2.8rem;
        margin-bottom:14px;
    }
    .about-hero p{
        font-size:1.1rem;
        max-width:760px;
        margin:0 auto;
        opacity:0.95;
    }

    /* Content cards */
    .content-section{
        padding:56px 24px;
    }
    .story-card, .mv-card, .why-card, .cta-card{
        background:#fff;
        border-radius:16px;
        box-shadow:0 6px 18px rgba(15,23,42,0.06);
        border:1px solid var(--border, #e5e7eb);
    }
    .story-card{
        padding:36px;
        max-width:900px;
        margin:0 auto 24px;
    }
    .story-card h2{
        font-weight:800;
        margin-bottom:18px;
    }
    .story-card p{
        color:var(--text-muted, #6b7280);
        line-height:1.7;
        margin-bottom:16px;
    }
    .mv-row{
        max-width:900px;
        margin:0 auto 24px;
    }
    .mv-card{
        padding:28px;
        height:100%;
    }
    .mv-icon{
        width:56px;
        height:56px;
        border-radius:14px;
        background:#ede9fe;
        color:var(--purple, #5b4cf5);
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:1.5rem;
        margin-bottom:16px;
    }
    .mv-card h3{
        font-weight:800;
        margin-bottom:10px;
    }
    .mv-card p{
        color:var(--text-muted, #6b7280);
        line-height:1.6;
        margin:0;
    }
    .why-card{
        padding:36px;
        max-width:900px;
        margin:0 auto 24px;
    }
    .why-card h2{
        font-weight:800;
        margin-bottom:24px;
    }
    .why-item{
        display:flex;
        gap:12px;
        margin-bottom:22px;
    }
    .why-item .check{
        color:var(--c-football, #10a36b);
        flex-shrink:0;
        font-size:1.2rem;
        line-height:1.5;
    }
    .why-item h4{
        font-weight:700;
        font-size:1.02rem;
        margin-bottom:4px;
    }
    .why-item p{
        color:var(--text-muted, #6b7280);
        font-size:0.93rem;
        line-height:1.55;
        margin:0;
    }

    .cta-card{
        background:linear-gradient(135deg, #6a5cf7 0%, var(--purple, #5b4cf5) 60%, var(--purple-dark, #4636d6) 100%);
        color:#fff;
        text-align:center;
        padding:48px 24px;
        max-width:900px;
        margin:0 auto;
        border:none;
    }
    .cta-card .cta-icon{
        font-size:2.4rem;
        margin-bottom:14px;
    }
    .cta-card h2{
        font-weight:800;
        margin-bottom:12px;
    }
    .cta-card p{
        opacity:0.95;
        max-width:560px;
        margin:0 auto 26px;
    }
    .btn-cta{
        background:#fff;
        color:var(--purple, #5b4cf5);
        font-weight:700;
        padding:12px 30px;
        border-radius:9px;
        border:none;
    }
    .btn-cta:hover{
        background:#f3f0ff;
        color:var(--purple-dark, #4636d6);
    }
</style>
</head>
<body>

<!-- Header -->
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

<!-- Hero -->
<section class="about-hero">
    <h1>About ACE</h1>
    <p>All Courts Elite - Your premier destination for booking professional sports facilities</p>
</section>

<!-- Content -->
<main class="content-section flex-grow-1">

    <!-- Our Story -->
    <div class="story-card">
        <h2>Our Story</h2>
        <p>Founded in 2020, ACE (All Courts Elite) was born from a simple idea: making it easier for athletes and sports enthusiasts to find and book quality sports facilities. We recognized that finding the right venue for your game shouldn't be a challenge.</p>
        <p>Today, we partner with premium sports facilities across the country to bring you the best courts and fields for football, basketball, volleyball, and tennis. Our platform connects passionate players with world-class venues, making sports more accessible to everyone.</p>
        <p class="mb-0">Whether you're organizing a competitive match, training session, or casual game with friends, ACE provides a seamless booking experience with transparent pricing and instant confirmation.</p>
    </div>

    <!-- Our Mission / Our Vision -->
    <div class="row g-4 mv-row">
        <div class="col-md-6">
            <div class="mv-card">
                <div class="mv-icon">&#127919;</div>
                <h3>Our Mission</h3>
                <p>To make quality sports facilities accessible to everyone by providing a simple, transparent, and reliable booking platform that connects players with the best venues.</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mv-card">
                <div class="mv-icon">&#127942;</div>
                <h3>Our Vision</h3>
                <p>To become the leading sports facility booking platform, fostering a community where every athlete can easily access professional-grade courts and fields.</p>
            </div>
        </div>
    </div>

    <!-- Why Choose ACE -->
    <div class="why-card">
        <h2>Why Choose ACE?</h2>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="why-item">
                    <span class="check">&#10003;</span>
                    <div>
                        <h4>Premium Facilities</h4>
                        <p>Access to top-tier sports venues with professional-grade equipment and amenities.</p>
                    </div>
                </div>
                <div class="why-item">
                    <span class="check">&#10003;</span>
                    <div>
                        <h4>Transparent Pricing</h4>
                        <p>No hidden fees. Clear, upfront pricing so you know exactly what you're paying.</p>
                    </div>
                </div>
                <div class="why-item mb-0">
                    <span class="check">&#10003;</span>
                    <div>
                        <h4>Verified Venues</h4>
                        <p>All facilities are vetted and regularly inspected to ensure quality standards.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="why-item">
                    <span class="check">&#10003;</span>
                    <div>
                        <h4>Easy Booking</h4>
                        <p>Simple, user-friendly platform with instant confirmation and flexible scheduling.</p>
                    </div>
                </div>
                <div class="why-item">
                    <span class="check">&#10003;</span>
                    <div>
                        <h4>24/7 Support</h4>
                        <p>Dedicated customer support team ready to assist you whenever you need help.</p>
                    </div>
                </div>
                <div class="why-item mb-0">
                    <span class="check">&#10003;</span>
                    <div>
                        <h4>Community Focused</h4>
                        <p>Building a vibrant community of athletes and sports enthusiasts.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Join Our Community CTA -->
    <div class="cta-card">
        <div class="cta-icon">&#128101;</div>
        <h2>Join Our Community</h2>
        <p>Over 50,000+ athletes trust ACE for their sports facility bookings. Start your journey with us today!</p>
        <a href="register.php" class="btn btn-cta">Get Started</a>
    </div>

</main>

<!-- Footer -->
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
    <div class="small" style="color:#94a3b8;">&copy; 2026 SportFields. All rights reserved.</div>
</footer>

<div class="help-btn">?</div>

<!-- Bootstrap JS bundle -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>

</body>
</html>
