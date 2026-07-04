<?php
/**
 * PHPMailer Configuration for Brevo SMTP
 * Path: config/email_config.php
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// A fájlok betöltése közvetlenül a libs/PHPMailer mappából
require_once __DIR__ . '/../libs/PHPMailer/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer/SMTP.php';

// Létrehozzuk a globális $mail objektumot, amit a register.php vár
$mail = new PHPMailer(true);

try {
    // SMTP Szerver beállítások a Brevo fiókod alapján
    $mail->isSMTP();
    $mail->Host       = 'smtp-relay.brevo.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'b0b233001@smtp-brevo.com'; 
    
    // A Brevóban generált SMTP kulcsod
    $mail->Password   = 'xsmtpsib-1c6a5a7881d798ae1d0eea200a4eb121db13754f74176615e03aacffd7a57cb7-IJpEwglfckmK60Ml'; 
    
    $mail->SMTPSecure = 'tls'; 
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    // Feladó adatai - ÁTÍRVA A TE HITELLESÍTETT EMAIL CÍMEDRE:
    $mail->setFrom('krisztian.rakk@gmail.com', 'ACE Sports Center');

} catch (Exception $e) {
    // A hibákat a register.php fogja kezelni
}