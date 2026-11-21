<?php
/* Name: Srijan Shandilya | Roll Number: 20231BCA0248 */

session_start();                                   // needed to read and write $_SESSION
require_once 'config/db.php';                      // gives us the $conn database connection
require_once 'includes/csrf.php';                  // NEW: loads the CSRF helper functions

// This file only accepts form submissions; anyone typing its URL is sent to the login page
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// NEW: reject the request if the secret token is missing or wrong
if (!csrf_verify()) {
    $_SESSION['flash_error'] = 'Security check failed. Please try again.';  // message for the login page
    header('Location: index.php');                 // go back to the login form
    exit;                                          // stop: nothing else runs
}

$username = trim($_POST['username'] ?? '');        // read username; ?? '' avoids an error if the field is missing; trim removes spaces
$password = $_POST['password'] ?? '';              // read password (not trimmed)

// Server-side validation: both fields are required (JavaScript could have been bypassed)
if ($username === '' || $password === '') {
    $_SESSION['flash_error'] = 'Please enter both username and password.';
    header('Location: index.php');
    exit;
}

try {
    // Prepared statement: ? is a placeholder for the username
    $stmt = $conn->prepare('SELECT id, username, password FROM users WHERE username = ?');
    $stmt->bind_param('s', $username);             // 's' means the value is a string
    $stmt->execute();                              // run the query
    $result = $stmt->get_result();                 // get the result set
    $user = $result->fetch_assoc();                // one row as an array, or null if no user found
    $stmt->close();                                // free the statement

    // Does the user exist AND does the typed password match the stored hash?
    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);               // new session id for safety
        unset($_SESSION['csrf_token']);            // NEW: delete the old token so a fresh one is made after login
        $_SESSION['user_id'] = $user['id'];        // this is what auth_check.php looks for
        $_SESSION['username'] = $user['username']; // used to show "Welcome, name"
        header('Location: dashboard.php');         // login success
        exit;
    }

    $_SESSION['flash_error'] = 'Invalid username or password.';   // same message for both failures
} catch (mysqli_sql_exception $e) {
    error_log('Login error: ' . $e->getMessage()); // technical detail goes to the log only
    $_SESSION['flash_error'] = 'Something went wrong. Please try again.';
}

header('Location: index.php');                     // any failure ends here: back to the login page
exit;