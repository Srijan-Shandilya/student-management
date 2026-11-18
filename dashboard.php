<?php
/* Name: Srijan Shandilya | Roll Number: 20231BCA0248 */

require_once 'includes/auth_check.php';            // the guard: stops visitors who are not logged in
require_once 'config/db.php';                      // gives us the $conn database connection

$totalStudents = 0;                                // default value in case the query fails

try {
    $result = $conn->query('SELECT COUNT(*) AS total FROM students'); // ask MySQL how many student rows exist
    $row = $result->fetch_assoc();                 // fetch the single result row as an array
    $totalStudents = (int) $row['total'];          // convert to a whole number
} catch (mysqli_sql_exception $e) {
    error_log('Dashboard count error: ' . $e->getMessage()); // log the technical error for the developer
}

$currentPage = 'dashboard';                        // tells header.php which menu link to highlight

require_once 'includes/header.php';                // prints the top of the page and navbar
?>

<h1 class="page-title">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>

<div class="cards">                                <!-- grid container from style.css -->

    <a href="view_students.php" class="card">      <!-- whole card is a link -->
        <h3>Total Students</h3>
        <div class="number"><?php echo $totalStudents; ?></div> <!-- the count from MySQL -->
        <p>Records in the database</p>
    </a>

    <a href="add_student.php" class="card">
        <h3>Add Student</h3>
        <p>Create a new student record</p>
    </a>

    <a href="view_students.php" class="card">
        <h3>View Students</h3>
        <p>See, edit or delete student records</p>
    </a>

    <a href="xml_display.php" class="card">
        <h3>XML Records</h3>
        <p>View student data stored in XML</p>
    </a>

</div>

<?php require_once 'includes/footer.php'; ?>      <!-- prints the footer and closes the page -->