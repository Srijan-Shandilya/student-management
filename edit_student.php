<?php
/* Name: Srijan Shandilya | Roll Number: 20231BCA0248 */

require_once 'includes/auth_check.php';            // the guard: only logged-in users may edit records
require_once 'config/db.php';                      // gives us the $conn database connection

// The allowed course names; also used to build the dropdown below (same list as add_student.php)
$courses = ['BCA', 'BBA', 'B.Com', 'B.Sc', 'MCA'];

$errors = [];                                      // list of error messages (empty = no errors)

// The id comes from the URL (?id=5) when the page opens, and from the hidden field when the form is submitted
$rawId = ($_SERVER['REQUEST_METHOD'] === 'POST') ? ($_POST['id'] ?? '') : ($_GET['id'] ?? '');

// Validate the id: it must be digits only and greater than 0 (anyone can type anything in a URL)
if (!ctype_digit((string)$rawId) || (int)$rawId < 1) {
    $_SESSION['flash_error'] = 'Invalid student selected.';   // message shown on the list page
    header('Location: view_students.php');                    // send the user back to the list
    exit;                                                     // stop this script
}
$id = (int)$rawId;                                 // from here on, $id is a safe whole number

// Load the student from the database to make sure this record really exists
try {
    $stmt = $conn->prepare(
        'SELECT register_number, student_name, course, semester, email, contact_number
         FROM students WHERE id = ?'               // ? is a placeholder for the id
    );
    $stmt->bind_param('i', $id);                   // 'i' = integer
    $stmt->execute();                              // run the query
    $student = $stmt->get_result()->fetch_assoc(); // one row as an array, or null if nothing was found
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    error_log('Edit load error: ' . $e->getMessage());        // real reason goes to the log only
    $_SESSION['flash_error'] = 'Could not load the student record.';
    header('Location: view_students.php');
    exit;
}

if (!$student) {                                   // no student has this id (maybe it was already deleted)
    $_SESSION['flash_error'] = 'Student not found.';
    header('Location: view_students.php');
    exit;
}

// Starting values for the form: the saved data from the database
$old = [
    'register_number' => $student['register_number'],
    'student_name'    => $student['student_name'],
    'course'          => $student['course'],
    'semester'        => (string)$student['semester'],   // as text, so it can be compared with the dropdown values
    'email'           => $student['email'],
    'contact_number'  => $student['contact_number']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {       // true only when the form has been submitted

    // Replace the saved values with what the user typed (read and clean every field)
    $old['register_number'] = strtoupper(trim($_POST['register_number'] ?? '')); // uppercase so abc123 and ABC123 are the same
    $old['student_name']    = trim($_POST['student_name'] ?? '');
    $old['course']          = trim($_POST['course'] ?? '');
    $old['semester']        = trim($_POST['semester'] ?? '');
    $old['email']           = trim($_POST['email'] ?? '');
    $old['contact_number']  = trim($_POST['contact_number'] ?? '');

    // ---- Server-side validation (same rules as add_student.php) ----
    if ($old['register_number'] === '') {
        $errors[] = 'Register number is required.';
    } elseif (!preg_match('/^[A-Z0-9]{5,20}$/', $old['register_number'])) {
        $errors[] = 'Register number must be 5-20 letters or digits, no spaces or symbols.';
    }

    if ($old['student_name'] === '') {
        $errors[] = 'Student name is required.';
    } elseif (!preg_match('/^[A-Za-z .]{2,100}$/', $old['student_name'])) {
        $errors[] = 'Name must be 2-100 characters: letters, spaces and dots only.';
    }

    if (!in_array($old['course'], $courses, true)) {            // value must be one of the allowed courses
        $errors[] = 'Please select a valid course.';
    }

    if (!ctype_digit($old['semester']) || (int)$old['semester'] < 1 || (int)$old['semester'] > 8) {
        $errors[] = 'Please select a valid semester (1 to 8).';
    }

    if ($old['email'] === '') {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || strlen($old['email']) > 100) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (!preg_match('/^[0-9]{10}$/', $old['contact_number'])) {
        $errors[] = 'Contact number must be exactly 10 digits.';
    }

    // ---- Only touch the database if validation passed ----
    if (empty($errors)) {
        try {
            // Step 1: does ANOTHER student already use this register number? (id <> ? excludes the student we are editing)
            $check = $conn->prepare('SELECT id FROM students WHERE register_number = ? AND id <> ?');
            $check->bind_param('si', $old['register_number'], $id);   // s = string, i = integer
            $check->execute();
            $check->store_result();                                   // needed so num_rows works
            $exists = $check->num_rows > 0;                           // true if another student has it
            $check->close();

            if ($exists) {
                $errors[] = 'Another student already has this register number.';
            } else {
                // Step 2: update the row. WHERE id = ? is essential: without it, EVERY student would be changed
                $semester = (int)$old['semester'];                    // the database column is a number
                $update = $conn->prepare(
                    'UPDATE students
                     SET register_number = ?, student_name = ?, course = ?, semester = ?, email = ?, contact_number = ?
                     WHERE id = ?'
                );
                // s,s,s = register, name, course; i = semester; s,s = email, contact; i = id
                $update->bind_param('sssissi',
                    $old['register_number'], $old['student_name'], $old['course'],
                    $semester, $old['email'], $old['contact_number'], $id
                );
                $update->execute();                                   // run the UPDATE
                $update->close();

                // Post/Redirect/Get: save the message, then go to the list
                $_SESSION['flash_success'] = 'Student updated successfully.';
                header('Location: view_students.php');
                exit;
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {                             // 1062 = duplicate entry (UNIQUE rule hit)
                $errors[] = 'Another student already has this register number.';
            } else {
                error_log('Edit student error: ' . $e->getMessage()); // real reason goes to the log only
                $errors[] = 'Something went wrong while saving. Please try again.';
            }
        }
    }
}

$currentPage = 'view';                             // Edit belongs to the student list, so highlight "View Students"
require_once 'includes/header.php';                // prints the top of the page and navbar
?>

<div class="page-header">
    <h1 class="page-title">Edit Student</h1>
</div>

<?php foreach ($errors as $err): ?>                <!-- one red box per error -->
    <div class="alert alert-error"><?php echo htmlspecialchars($err); ?></div>
<?php endforeach; ?>

<!-- id="studentForm" makes js/validation.js check this form too; action="" posts back to this same file -->
<form id="studentForm" class="form-card" action="" method="POST" novalidate>

    <!-- Hidden field: tells the POST request WHICH student to update -->
    <input type="hidden" name="id" value="<?php echo $id; ?>">

    <div class="form-group">
        <label for="register_number">Register Number</label>
        <input type="text" id="register_number" name="register_number" maxlength="20"
               value="<?php echo htmlspecialchars($old['register_number']); ?>">  <!-- pre-filled value -->
    </div>

    <div class="form-group">
        <label for="student_name">Student Name</label>
        <input type="text" id="student_name" name="student_name" maxlength="100"
               value="<?php echo htmlspecialchars($old['student_name']); ?>">
    </div>

    <div class="form-group">
        <label for="course">Course</label>
        <select id="course" name="course">
            <option value="">-- Select course --</option>
            <?php foreach ($courses as $c): ?>                           <!-- build one option per course -->
                <option value="<?php echo htmlspecialchars($c); ?>"
                    <?php echo $old['course'] === $c ? 'selected' : ''; ?>>  <!-- pre-select the saved course -->
                    <?php echo htmlspecialchars($c); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="semester">Semester</label>
        <select id="semester" name="semester">
            <option value="">-- Select semester --</option>
            <?php for ($i = 1; $i <= 8; $i++): ?>                        <!-- semesters 1 to 8 -->
                <option value="<?php echo $i; ?>"
                    <?php echo $old['semester'] === (string)$i ? 'selected' : ''; ?>>  <!-- pre-select the saved semester -->
                    <?php echo $i; ?>
                </option>
            <?php endfor; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="email">Email ID</label>
        <input type="email" id="email" name="email" maxlength="100"
               value="<?php echo htmlspecialchars($old['email']); ?>">
    </div>

    <div class="form-group">
        <label for="contact_number">Contact Number</label>
        <input type="text" id="contact_number" name="contact_number" maxlength="10" inputmode="numeric"
               value="<?php echo htmlspecialchars($old['contact_number']); ?>">
    </div>

    <button type="submit" class="btn btn-primary">Update Student</button>
    <a href="view_students.php" class="btn btn-secondary">Cancel</a>     <!-- back to the list without saving -->
</form>

<?php require_once 'includes/footer.php'; ?>       <!-- prints the footer and closes the page -->