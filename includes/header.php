<!-- Name: Srijan Shandilya | Roll Number: 20231BCA0248 -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">                                                 <!-- character encoding -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  <!-- responsive on phones -->
    <title>Srijan Shandilya - 20231BCA0248</title>                         <!-- required title for every page -->
    <link rel="stylesheet" href="css/style.css">                           <!-- shared stylesheet -->
</head>
<body>

    <nav class="navbar">
        <div class="brand">University Student Management System</div>
        <div class="nav-links">
            <!-- ?? '' means: if $currentPage was not set by the page, use an empty text instead of an error -->
            <!-- If the current page matches, the word "active" is added, so CSS highlights the link -->
            <a href="dashboard.php" class="<?php echo ($currentPage ?? '') === 'dashboard' ? 'active' : ''; ?>">Dashboard</a>
            <a href="add_student.php" class="<?php echo ($currentPage ?? '') === 'add' ? 'active' : ''; ?>">Add Student</a>
            <a href="view_students.php" class="<?php echo ($currentPage ?? '') === 'view' ? 'active' : ''; ?>">View Students</a>
            <a href="xml_display.php" class="<?php echo ($currentPage ?? '') === 'xml' ? 'active' : ''; ?>">View XML Records</a>
            <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>)</a> <!-- shows who is logged in; htmlspecialchars prevents XSS -->
        </div>
    </nav>

    <div class="container">  <!-- the main content area opens here; footer.php closes it -->