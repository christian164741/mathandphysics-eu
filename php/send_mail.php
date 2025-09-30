<?php
// ===============================
// send_mail.php  (STRATO-ready)
// ===============================
// This script sends form submissions from kontakt.html to your inbox
// without requiring SMTP credentials. Place this file in the same
// directory as kontakt.html on your webspace.
//
// Expected POST fields: name, email, message
//
// SECURITY NOTES:
// - "From:" header is fixed to your domain address to avoid spam flags.
// - "Reply-To:" uses the visitor's email so you can reply directly.
// - Basic sanitation and header-injection protection included.
// - Optionally enable a simple honeypot (see $honeypotField).
// ===============================

// 1) CHANGE THIS to your receiving address (must belong to your domain)
$recipient = "info@mathandphysics.de";

// Optional: simple honeypot (add a hidden input with this name in your form)
// <input type="text" name="website" style="display:none">
$honeypotField = "website";

// --- Helper: deny header injection ---
function contains_header_injection($str) {
    return preg_match("/[\r\n]|%0A|%0D|Content-Type:|bcc:|cc:/i", $str);
}

// --- Collect and sanitize ---
$name     = isset($_POST['name'])    ? trim($_POST['name'])    : "";
$email    = isset($_POST['email'])   ? trim($_POST['email'])   : "";
$message  = isset($_POST['message']) ? trim($_POST['message']) : "";
$honeypot = isset($_POST[$honeypotField]) ? trim($_POST[$honeypotField]) : "";

// Basic validation
if ($honeypot !== "") {
    // Bot filled the hidden field -> silently pretend success
    http_response_code(200);
    echo "<!doctype html><html><body style='font-family:Arial;color:green'>Danke, Ihre Nachricht wurde gesendet.</body></html>";
    exit;
}
if ($name === "" || $email === "" || $message === "") {
    http_response_code(400);
    echo "<!doctype html><html><body style='font-family:Arial;color:red'>Fehler: Bitte alle Felder ausfüllen.</body></html>";
    exit;
}

// Prevent header injection
if (contains_header_injection($name) || contains_header_injection($email)) {
    http_response_code(400);
    echo "<!doctype html><html><body style='font-family:Arial;color:red'>Fehler: Ungültige Eingabe.</body></html>";
    exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo "<!doctype html><html><body style='font-family:Arial;color:red'>Fehler: Ungültige E-Mail-Adresse.</body></html>";
    exit;
}

// Optional: length limits
if (mb_strlen($name) > 200 || mb_strlen($email) > 200 || mb_strlen($message) > 5000) {
    http_response_code(400);
    echo "<!doctype html><html><body style='font-family:Arial;color:red'>Fehler: Eingaben zu lang.</body></html>";
    exit;
}

// Build email
$subject = "Neue Kontaktanfrage – Math and Physics";
$body  = "Name: {$name}\n";
$body .= "E-Mail: {$email}\n";
$body .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unbekannt') . "\n";
$body .= "Datum: " . date('Y-m-d H:i:s') . "\n";
$body .= "----------------------------------------\n\n";
$body .= $message . "\n";

$headers = [];
$headers[] = "From: info@mathandphysics.de"; // fixed sender from your domain
$headers[] = "Reply-To: {$email}";
$headers[] = "Content-Type: text/plain; charset=UTF-8";
$headers[] = "X-Mailer: PHP/" . phpversion();

// Send
$ok = @mail($recipient, $subject, $body, implode("\r\n", $headers));

if ($ok) {
    // Weiterleitung zur Danke-Seite
    header("Location: ../danke.html");
    exit;
} else {
    // Weiterleitung zur Fehler-Seite (optional erstellen)
    header("Location: ../fehler.html");
    exit;
}

