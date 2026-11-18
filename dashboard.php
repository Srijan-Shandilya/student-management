<?php
/* Name: Srijan Shandilya | Roll Number: 20231BCA0248 */

require_once 'includes/auth_check.php';            // the guard: visitors who aren't logged in never get past this line
?>
<!-- Name: Srijan Shandilya | Roll Number: 20231BCA0248 -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Srijan Shandilya - 20231BCA0248</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="brand">University Student Management System</div>
        <div class="nav-links">
            <a href="logout.php">Logout</a>
        </div>
    </nav>
    <div class="container">
        <h1 class="page-title">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <div class="alert alert-success">Login works. The real dashboard comes in Phase 4.</div>
    </div>
    <div class="footer">Srijan Shandilya - 20231BCA0248</div>
</body>
</html>