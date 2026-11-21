<?php
/* Name: Srijan Shandilya | Roll Number: 20231BCA0248 */

session_start();                                   // start or resume the session
require_once 'includes/csrf.php';                  // NEW: loads csrf_token(), csrf_field(), csrf_verify()

// If the user is already logged in, skip the login page
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');             // go straight to the dashboard
    exit;                                          // stop this script
}

$error = '';                                       // message variables start empty
$success = '';

// Read a one-time error message saved by login.php or auth_check.php
if (isset($_SESSION['flash_error'])) {
    $error = $_SESSION['flash_error'];             // copy it into a normal variable
    unset($_SESSION['flash_error']);               // delete it so it shows only once
}

// Read a one-time success message saved by register.php
if (isset($_SESSION['flash_success'])) {
    $success = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}

// logout.php redirects here with ?logout=1
if (isset($_GET['logout'])) {
    $success = 'You have been logged out successfully.';
}
?>
<!-- Name: Srijan Shandilya | Roll Number: 20231BCA0248 -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">                                                 <!-- character encoding -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  <!-- makes the page responsive on phones -->
    <title>Srijan Shandilya - 20231BCA0248</title>                         <!-- required page title -->
    <link rel="stylesheet" href="css/style.css">                           <!-- our shared stylesheet -->
</head>
<body>

    <nav class="navbar">
        <div class="brand">University Student Management System</div>      <!-- navbar with the brand only (no links before login) -->
    </nav>

    <div class="auth-wrapper">                                             <!-- centres the login box on the page -->
        <div class="auth-card">
            <h2>Student Login</h2>

            <?php if ($error !== ''): ?>                                   <!-- show the red box only if there is an error -->
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success !== ''): ?>                                 <!-- show the green box only if there is a success message -->
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <!-- Form sends data to login.php using POST (POST keeps the password out of the URL) -->
            <form id="loginForm" action="login.php" method="POST" novalidate>
                <?php echo csrf_field(); ?>                                <!-- NEW: prints the hidden token input -->
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter username">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter password"> <!-- type=password hides characters -->
                </div>
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>

            <p class="switch-link">New user? <a href="register.php">Register here</a></p>
        </div>
    </div>

    <div class="footer">Srijan Shandilya - 20231BCA0248</div>

    <script src="js/validation.js"></script>                               <!-- load JS at the end so the form already exists -->
</body>
</html>