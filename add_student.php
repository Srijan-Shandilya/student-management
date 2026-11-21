<?php
/* Name: Srijan Shandilya | Roll Number: 20231BCA0248 */

require_once 'includes/auth_check.php';            // the guard: only logged-in users may use this page
require_once 'config/db.php';                      // gives us the $conn database connection
require_once 'includes/csrf.php';                  // loads the CSRF helper functions
require_once 'includes/xml_helper.php';            // NEW 7B: gives us sync_students_xml()

// The allowed course names; also used to build the dropdown below
$courses = ['BCA', 'BBA', 'B.Com', 'B.Sc', 'MCA'];

$errors  = [];                                     // list of error messages (empty = no errors)
$success = '';                                     // success message shown after saving

// Empty starting values so the form fields exist even before the first submit
$old = [
    'register_number' => '',
    'student_name'    => '',
    'course'          => '',
    'semester'        => '',
    'email'           => '',
    'contact_number'  => ''
];

// Read the one-time success message saved before the redirect
if (isset($_SESSION['flash_success'])) {
    $success = $_SESSION['flash_success'];         // copy to a normal variable
    unset($_SESSION['flash_success']);             // delete it so it shows only once
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {       // true only when the form has been submitted

    // Wrong or missing token adds an error, which stops the INSERT below
    if (!csrf_verify()) {
        $errors[] = 'Security check failed. Please reload the page and try again.';
    }

    // Read and clean every field; ?? '' avoids errors if a field is missing
    $old['register_number'] = strtoupper(trim($_POST['register_number'] ?? '')); // uppercase so abc123 and ABC123 are treated as the same
    $old['student_name']    = trim($_POST['student_name'] ?? '');
    $old['course']          = trim($_POST['course'] ?? '');
    $old['semester']        = trim($_POST['semester'] ?? '');
    $old['email']           = trim($_POST['email'] ?? '');
    $old['contact_number']  = trim($_POST['contact_number'] ?? '');

    // ---- Server-side validation (same rules as JavaScript, but these cannot be bypassed) ----
    if ($old['register_number'] === '') {
        $errors[] = 'Register number is required.';
    } elseif (!preg_match('/^[A-Z0-9]{5,20}$/', $old['register_number'])) {   // already uppercase, so only A-Z and 0-9
        $errors[] = 'Register number must be 5-20 letters or digits, no spaces or symbols.';
    }

    if ($old['student_name'] === '') {
        $errors[] = 'Student name is required.';
    } elseif (!preg_match('/^[A-Za-z .]{2,100}$/', $old['student_name'])) {
        $errors[] = 'Name must be 2-100 characters: letters, spaces and dots only.';
    }

    if (!in_array($old['course'], $courses, true)) {     // the value must be one of the allowed courses
        $errors[] = 'Please select a valid course.';
    }

    // ctype_digit = only digits; then the number must be between 1 and 8
    if (!ctype_digit($old['semester']) || (int)$old['semester'] < 1 || (int)$old['semester'] > 8) {
        $errors[] = 'Please select a valid semester (1 to 8).';
    }

    if ($old['email'] === '') {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || strlen($old['email']) > 100) {  // PHP's built-in email checker
        $errors[] = 'Please enter a valid email address.';
    }

    if (!preg_match('/^[0-9]{10}$/', $old['contact_number'])) {   // exactly 10 digits
        $errors[] = 'Contact number must be exactly 10 digits.';
    }

    // ---- Only touch the database if validation passed ----
    if (empty($errors)) {
        try {
            // Step 1: does this register number already exist?
            $check = $conn->prepare('SELECT id FROM students WHERE register_number = ?');
            $check->bind_param('s', $old['register_number']);   // 's' = string
            $check->execute();
            $check->store_result();                              // needed so num_rows works
            $exists = $check->num_rows > 0;                      // true if a row was found
            $check->close();

            if ($exists) {
                $errors[] = 'A student with this register number already exists.';
            } else {
                // Step 2: insert the new student
                $semester = (int)$old['semester'];               // the database column is a number
                $insert = $conn->prepare(
                    'INSERT INTO students (register_number, student_name, course, semester, email, contact_number)
                     VALUES (?, ?, ?, ?, ?, ?)'
                );
                // s=string, i=integer: register, name, course, semester(int), email, contact
                $insert->bind_param('sssiss',
                    $old['register_number'], $old['student_name'], $old['course'],
                    $semester, $old['email'], $old['contact_number']
                );
                $insert->execute();                              // run the INSERT
                $insert->close();

                $_SESSION['flash_success'] = 'Student added successfully.';

                // NEW 7B: rebuild students.xml so it includes the new student
                if (!sync_students_xml($conn)) {                 // false means the XML file could not be written
                    // The student IS saved in MySQL; only the XML copy failed, so show a warning, not a failure
                    $_SESSION['flash_error'] = 'Student saved, but students.xml could not be updated. Use "Regenerate XML" on the XML page.';
                }

                // Post/Redirect/Get: redirect so F5 cannot resubmit the form
                header('Location: view_students.php');           // go to the list so the user sees the new record
                exit;
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {                        // 1062 = duplicate entry (UNIQUE rule hit)
                $errors[] = 'A student with this register number already exists.';
            } else {
                error_log('Add student error: ' . $e->getMessage());   // real reason goes to the log only
                $errors[] = 'Something went wrong while saving. Please try again.';
            }
        }
    }
}

$currentPage = 'add';                              // highlights "Add Student" in the navbar
require_once 'includes/header.php';                // prints the top of the page and navbar
?>

<div class="page-header">
    <h1 class="page-title">Add Student</h1>
</div>

<?php if ($success !== ''): ?>                     <!-- green box when a student was saved -->
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php foreach ($errors as $err): ?>                <!-- one red box per error -->
    <div class="alert alert-error"><?php echo htmlspecialchars($err); ?></div>
<?php endforeach; ?>

<!-- action="" posts back to this same file; novalidate lets our JavaScript show the messages -->
<form id="studentForm" class="form-card" action="" method="POST" novalidate>

    <?php echo csrf_field(); ?>                    <!-- prints the hidden token input -->

    <div class="form-group">
        <label for="register_number">Register Number</label>
        <input type="text" id="register_number" name="register_number" maxlength="20"
               placeholder="e.g. 20231BCA0248"
               value="<?php echo htmlspecialchars($old['register_number']); ?>">  <!-- refill after an error -->
    </div>

    <div class="form-group">
        <label for="student_name">Student Name</label>
        <input type="text" id="student_name" name="student_name" maxlength="100"
               placeholder="Full name"
               value="<?php echo htmlspecialchars($old['student_name']); ?>">
    </div>

    <div class="form-group">
        <label for="course">Course</label>
        <select id="course" name="course">
            <option value="">-- Select course --</option>              <!-- empty value = nothing chosen -->
            <?php foreach ($courses as $c): ?>                          <!-- build one option per course -->
                <option value="<?php echo htmlspecialchars($c); ?>"
                    <?php echo $old['course'] === $c ? 'selected' : ''; ?>>   <!-- re-select the earlier choice -->
                    <?php echo htmlspecialchars($c); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="semester">Semester</label>
        <select id="semester" name="semester">
            <option value="">-- Select semester --</option>
            <?php for ($i = 1; $i <= 8; $i++): ?>                       <!-- semesters 1 to 8 -->
                <option value="<?php echo $i; ?>"
                    <?php echo $old['semester'] === (string)$i ? 'selected' : ''; ?>>
                    <?php echo $i; ?>
                </option>
            <?php endfor; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="email">Email ID</label>
        <input type="email" id="email" name="email" maxlength="100"
               placeholder="name@example.com"
               value="<?php echo htmlspecialchars($old['email']); ?>">
    </div>

    <div class="form-group">
        <label for="contact_number">Contact Number</label>
        <input type="text" id="contact_number" name="contact_number" maxlength="10"
               inputmode="numeric" placeholder="10-digit mobile number"
               value="<?php echo htmlspecialchars($old['contact_number']); ?>">
    </div>

    <button type="submit" class="btn btn-success">Save Student</button>
    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>        <!-- link styled like a button -->
</form>

<?php require_once 'includes/footer.php'; ?>       <!-- prints the footer and closes the page -->