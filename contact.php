<?php
/**
 * Contact Page
 * Path: contact.php
 */
?>
<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contact - ACE</title>

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
    .contact-hero{
        background:linear-gradient(135deg, #6a5cf7 0%, var(--purple, #5b4cf5) 50%, var(--purple-dark, #4636d6) 100%);
        color:#fff;
        text-align:center;
        padding:70px 24px;
    }
    .contact-hero h1{
        font-weight:800;
        font-size:2.8rem;
        margin-bottom:14px;
    }
    .contact-hero p{
        font-size:1.05rem;
        max-width:680px;
        margin:0 auto;
        opacity:0.95;
    }

    .content-section{
        padding:56px 24px;
    }
    .form-card, .info-card{
        background:#fff;
        border-radius:16px;
        box-shadow:0 6px 18px rgba(15,23,42,0.06);
        border:1px solid var(--border, #e5e7eb);
    }
    .form-card{
        padding:36px;
    }
    .form-card h2{
        font-weight:800;
        margin-bottom:24px;
    }
    .form-card label{
        font-weight:600;
        font-size:0.92rem;
        margin-bottom:6px;
    }
    .form-control:focus{
        border-color:var(--purple, #5b4cf5);
        box-shadow:0 0 0 0.2rem rgba(91,76,245,0.15);
    }

    .info-card{
        padding:24px;
    }
    .info-icon{
        width:48px;
        height:48px;
        border-radius:12px;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:1.3rem;
        margin-bottom:14px;
    }
    .icon-email{ background:#ede9fe; color:var(--purple, #5b4cf5); }
    .icon-phone{ background:#d1fae5; color:var(--green, #10a36b); }
    .icon-office{ background:#dbeafe; color:#2f6fed; }
    .icon-hours{ background:#fef3c7; color:#d97706; }

    .info-card h4{
        font-weight:800;
        margin-bottom:4px;
    }
    .info-card p{
        color:var(--text-muted, #6b7280);
        font-size:0.92rem;
        margin-bottom:6px;
    }
    .info-card a{
        color:var(--purple, #5b4cf5);
        font-weight:700;
        text-decoration:none;
        font-size:0.95rem;
    }
    .info-card a:hover{ color:var(--purple-dark, #4636d6); }

    .hours-row{
        display:flex;
        justify-content:space-between;
        font-size:0.9rem;
        margin-bottom:6px;
    }
    .hours-row span:first-child{ color:var(--text-muted, #6b7280); }
    .hours-row span:last-child{ font-weight:700; }
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
<section class="contact-hero">
    <h1>Contact Us</h1>
    <p>Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
</section>

<!-- Content -->
<main class="content-section flex-grow-1">
    <div class="row g-4" style="max-width:1180px; margin:0 auto;">

        <!-- Send us a Message -->
        <div class="col-lg-8">
            <div class="form-card">
                <h2>Send us a Message</h2>
                <form action="contact.php" method="POST">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="name">Your Name</label>
                            <input type="text" name="name" id="name" class="form-control" placeholder="John Doe" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email">Email Address</label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="john@example.com" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="subject">Subject</label>
                        <input type="text" name="subject" id="subject" class="form-control" placeholder="How can we help you?" required>
                    </div>
                    <div class="mb-4">
                        <label for="message">Message</label>
                        <textarea name="message" id="message" class="form-control" rows="6" placeholder="Tell us more about your inquiry..." required></textarea>
                    </div>
                    <button type="submit" name="contact_action" class="btn btn-brand-secondary w-100 py-2">
                        &#10148; Send Message
                    </button>
                </form>
            </div>
        </div>

        <!-- Contact info -->
        <div class="col-lg-4">
            <div class="d-flex flex-column gap-4">

                <div class="info-card">
                    <div class="info-icon icon-email">&#9993;</div>
                    <h4>Email</h4>
                    <p>Our team is here to help</p>
                    <a href="mailto:support@ace-sports.com">support@ace-sports.com</a>
                </div>

                <div class="info-card">
                    <div class="info-icon icon-phone">&#128222;</div>
                    <h4>Phone</h4>
                    <p>Mon-Fri from 8am to 8pm</p>
                    <a href="tel:+15551234567">+1 (555) 123-4567</a>
                </div>

                <div class="info-card">
                    <div class="info-icon icon-office">&#128205;</div>
                    <h4>Office</h4>
                    <p class="mb-0">123 Sports Avenue<br>Downtown District<br>New York, NY 10001</p>
                </div>

                <div class="info-card">
                    <div class="info-icon icon-hours">&#128337;</div>
                    <h4>Business Hours</h4>
                    <div class="hours-row"><span>Monday - Friday:</span><span>8am - 8pm</span></div>
                    <div class="hours-row"><span>Saturday:</span><span>9am - 6pm</span></div>
                    <div class="hours-row mb-0"><span>Sunday:</span><span>10am - 4pm</span></div>
                </div>

            </div>
        </div>

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

<div class="help-btn"></div>

<!-- Bootstrap JS bundle -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>

</body>
</html>
