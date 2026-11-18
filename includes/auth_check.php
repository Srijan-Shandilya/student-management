<?php
/* Name: Srijan Shandilya | Roll Number: 20231BCA0248 */

// Start (or resume) the session so we can read $_SESSION
session_start();

// If no user id is stored in the session, the visitor is NOT logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_error'] = 'Please log in to continue.';  // message to show on the login page
    header('Location: index.php');                            // send the browser to the login page
    exit;                                                     // stop the script: without exit, the protected page would still run
}