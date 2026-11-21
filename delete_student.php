<?php
/* Name: Srijan Shandilya | Roll Number: 20231BCA0248 */

require_once 'includes/auth_check.php';            // the guard: only logged-in users may delete records
require_once 'config/db.php';                      // gives us the $conn database connection
require_once 'includes/csrf.php';                  // loads the CSRF helper functions
require_once 'includes/xml_helper.php';            // NEW 7B: gives us sync_students_xml()

// This file only accepts form submissions (POST). Typing its address in the browser sends a GET request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: view_students.php');         // do nothing and go back to the list
    exit;                                          // stop this script
}

// Reject the request if the secret token is missing or wrong
if (!csrf_verify()) {
    $_SESSION['flash_error'] = 'Security check failed. Nothing was deleted.';  // shown on the list page
    header('Location: view_students.php');
    exit;                                          // stop before any DELETE runs
}

$rawId = $_POST['id'] ?? '';                       // read the id from the hidden field; ?? '' avoids an error if it is missing

// Validate the id: digits only and greater than 0 (hidden fields can be edited by anyone)
if (!ctype_digit((string)$rawId) || (int)$rawId < 1) {
    $_SESSION['flash_error'] = 'Invalid student selected.';   // message shown on the list page
    header('Location: view_students.php');
    exit;
}
$id = (int)$rawId;                                 // from here on, $id is a safe whole number

try {
    // Prepared statement: ? is a placeholder, so the id is sent separately from the SQL.
    // WHERE id = ? is essential: without it, EVERY student would be deleted
    $stmt = $conn->prepare('DELETE FROM students WHERE id = ?');
    $stmt->bind_param('i', $id);                   // 'i' = integer
    $stmt->execute();                              // run the DELETE
    $deleted = $stmt->affected_rows;               // how many rows were actually removed (1 or 0)
    $stmt->close();                                // free the statement

    if ($deleted > 0) {
        $_SESSION['flash_success'] = 'Student deleted successfully.';

        // NEW 7B: rebuild students.xml so the deleted student disappears from it too
        if (!sync_students_xml($conn)) {           // false means the XML file could not be written
            // The student IS deleted from MySQL; only the XML copy failed, so show a warning
            $_SESSION['flash_error'] = 'Student deleted, but students.xml could not be updated. Use "Regenerate XML" on the XML page.';
        }
    } else {
        // 0 rows means no student has this id (maybe it was already deleted in another tab)
        $_SESSION['flash_error'] = 'Student not found. It may have already been deleted.';
    }
} catch (mysqli_sql_exception $e) {
    error_log('Delete student error: ' . $e->getMessage());   // real reason goes to the log only
    $_SESSION['flash_error'] = 'Could not delete the student. Please try again.';
}

header('Location: view_students.php');             // always return to the list (Post/Redirect/Get)
exit;