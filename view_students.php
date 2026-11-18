<?php
/* Name: Srijan Shandilya | Roll Number: 20231BCA0248 */

require_once 'includes/auth_check.php';            // the guard: only logged-in users may see student records
require_once 'config/db.php';                      // gives us the $conn database connection

$students = [];                                    // will hold all student rows (empty list by default)
$success  = '';                                    // green message (set by add, edit or delete pages)
$error    = '';                                    // red message

// Read one-time messages saved by other pages before they redirected here
if (isset($_SESSION['flash_success'])) {
    $success = $_SESSION['flash_success'];         // copy into a normal variable
    unset($_SESSION['flash_success']);             // delete it so it shows only once
}
if (isset($_SESSION['flash_error'])) {
    $error = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}

try {
    // No user input is used in this query, so a normal query() is safe (no placeholders needed)
    $result = $conn->query(
        'SELECT id, register_number, student_name, course, semester, email, contact_number
         FROM students
         ORDER BY register_number ASC'             // sorted by register number
    );
    $students = $result->fetch_all(MYSQLI_ASSOC);  // get ALL rows at once, each row as an array with column names as keys
} catch (mysqli_sql_exception $e) {
    error_log('View students error: ' . $e->getMessage());       // real reason goes to the log only
    $error = 'Could not load student records. Please try again.'; // friendly message for the user
}

$currentPage = 'view';                             // highlights "View Students" in the navbar
require_once 'includes/header.php';                // prints the top of the page and navbar
?>

<div class="page-header">
    <h1 class="page-title">Student Records (<?php echo count($students); ?>)</h1> <!-- count() = number of rows found -->
    <a href="add_student.php" class="btn btn-success">+ Add Student</a>
</div>

<?php if ($success !== ''): ?>                     <!-- green box after add, edit or delete -->
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if ($error !== ''): ?>                       <!-- red box if something failed -->
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if (empty($students)): ?>                    <!-- no rows: show a friendly message instead of an empty table -->
    <div class="alert alert-error">No student records found. Click "+ Add Student" to create one.</div>
<?php else: ?>

<div class="table-wrapper">                        <!-- wrapper gives the shadow and sideways scroll on small screens -->
    <table>
        <tr>                                       <!-- header row -->
            <th>#</th>
            <th>Register No</th>
            <th>Name</th>
            <th>Course</th>
            <th>Semester</th>
            <th>Email</th>
            <th>Contact</th>
            <th>Actions</th>
        </tr>

        <?php foreach ($students as $index => $s): ?>   <!-- repeat this block once for every student; $s is one row -->
        <tr>
            <td><?php echo $index + 1; ?></td>                              <!-- serial number (1, 2, 3...); the database id is not shown to users -->
            <td><?php echo htmlspecialchars($s['register_number']); ?></td> <!-- htmlspecialchars prevents XSS -->
            <td><?php echo htmlspecialchars($s['student_name']); ?></td>
            <td><?php echo htmlspecialchars($s['course']); ?></td>
            <td><?php echo (int)$s['semester']; ?></td>                     <!-- (int) forces a plain number -->
            <td><?php echo htmlspecialchars($s['email']); ?></td>
            <td><?php echo htmlspecialchars($s['contact_number']); ?></td>
            <td class="actions">
                <!-- Edit: a link, because opening a page changes no data. The id travels in the URL -->
                <a href="edit_student.php?id=<?php echo (int)$s['id']; ?>" class="btn btn-primary btn-small">Edit</a>

                <!-- Delete: a POST form (safer than a link). confirm() asks "OK / Cancel" first -->
                <form action="delete_student.php" method="POST"
                      onsubmit="return confirm('Delete this student record? This cannot be undone.');">
                    <input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>"> <!-- hidden field carries the id to delete_student.php -->
                    <button type="submit" class="btn btn-danger btn-small">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>                       <!-- end of the repeated row -->
    </table>
</div>

<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>       <!-- prints the footer and closes the page -->