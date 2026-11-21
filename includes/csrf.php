<?php
/* Name: Srijan Shandilya | Roll Number: 20231BCA0248 */

// Make sure a session is running (index.php and others may already have started it)
if (session_status() === PHP_SESSION_NONE) {       // PHP_SESSION_NONE means no session has been started yet
    session_start();                               // start one so we can use $_SESSION
}

// Returns the secret token for this session, creating it the first time
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {                        // no token yet in this session?
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));     // 32 random bytes turned into 64 hex characters (unpredictable)
    }
    return $_SESSION['csrf_token'];                              // hand the token back to the caller
}

// Returns a ready-made hidden input to place inside every form
function csrf_field(): string {
    // htmlspecialchars keeps the value safe inside the HTML attribute
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

// Checks the token that arrived with a POST request. Returns true only if it matches the session's token
function csrf_verify(): bool {
    $sent = $_POST['csrf_token'] ?? '';                          // token from the submitted form (empty if missing)
    $real = $_SESSION['csrf_token'] ?? '';                       // token stored in the session
    // hash_equals compares in constant time, so attackers cannot guess the token by measuring response speed
    return is_string($sent) && $real !== '' && hash_equals($real, $sent);
}