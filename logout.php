<?php
/* Name: Srijan Shandilya | Roll Number: 20231BCA0248 */

session_start();                                   // we must resume the session before we can destroy it

$_SESSION = [];                                    // empty all session data (user_id, username, ...)

// Also delete the session cookie stored in the browser
if (ini_get('session.use_cookies')) {              // only if sessions use cookies (the default)
    $p = session_get_cookie_params();              // read the cookie's current settings
    setcookie(session_name(), '', time() - 42000,  // set expiry in the past, which makes the browser delete it
        $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

session_destroy();                                 // remove the session on the server

header('Location: index.php?logout=1');            // back to login; ?logout=1 triggers the "logged out" message
exit;