<?php
/* Name: Srijan Shandilya | Roll Number: 20231BCA0248 */

// Connection settings stored as constants so they are defined in one place only
define('DB_HOST', 'localhost');          // the MySQL server runs on the same computer
define('DB_USER', 'root');               // XAMPP's default MySQL username
define('DB_PASS', '');                   // XAMPP's default password is empty
define('DB_NAME', 'student_management'); // the database we created in Step 2

// Make mysqli throw exceptions on errors so we can catch them with try/catch
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Create the connection object; $conn is what every other file will use
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Use utf8mb4 so text is sent and received in the same encoding as the database
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    // Write the real technical error to the server log (for the developer only)
    error_log('Database connection error: ' . $e->getMessage());

    // Show only a safe, friendly message to the visitor and stop the page
    die('Database connection failed. Please try again later.');
}