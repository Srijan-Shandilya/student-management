<?php
/* Name: Srijan Shandilya | Roll Number: 20231BCA0248 */

require_once 'includes/auth_check.php';            // the guard: only logged-in users may see this page
require_once 'config/db.php';                      // gives us the $conn database connection
require_once 'includes/csrf.php';                  // CSRF helper functions
require_once 'includes/xml_helper.php';            // gives us sync_students_xml()

$xmlFile = __DIR__ . '/students.xml';              // full path to the XML file in the project root
$success = '';                                     // green message
$error   = '';                                     // red message

// The Regenerate button posts to this same page
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {                          // wrong or missing token: do nothing
        $_SESSION['flash_error'] = 'Security check failed. Please try again.';
    } elseif (sync_students_xml($conn)) {          // rebuild students.xml from MySQL; true means it worked
        $_SESSION['flash_success'] = 'students.xml was regenerated from the database.';
    } else {
        $_SESSION['flash_error'] = 'Could not write students.xml. Please check the file permissions.';
    }
    header('Location: xml_display.php');           // Post/Redirect/Get: pressing F5 will not resubmit the button
    exit;
}

// Read one-time messages saved before the redirect
if (isset($_SESSION['flash_success'])) {
    $success = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);             // delete so it shows only once
}
if (isset($_SESSION['flash_error'])) {
    $error = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}

$records = [];                                     // will hold the students read from the XML file
$lastUpdated = '';                                 // when the XML file was last written

if (!file_exists($xmlFile)) {                      // the file was deleted or never created
    $error = 'students.xml was not found. Click "Regenerate XML" to create it.';
} else {
    libxml_use_internal_errors(true);              // collect XML errors quietly instead of printing PHP warnings
    $xml = simplexml_load_file($xmlFile);          // PARSE the XML file into an object, or false if it is not valid XML

    if ($xml === false) {                          // the file is broken (for example a missing closing tag)
        $error = 'students.xml is not valid XML. Click "Regenerate XML" to rebuild it.';
        libxml_clear_errors();                     // clear the collected errors
    } else {
        foreach ($xml->student as $s) {            // loop over every <student> element
            $records[] = [
                'id'       => (string)$s['id'],              // attribute: $s['id']
                'register' => (string)$s->register_number,   // child element: $s->register_number
                'name'     => (string)$s->name,
                'course'   => (string)$s->course,
                'semester' => (string)$s->semester,
                'email'    => (string)$s->email
            ];
        }
        $lastUpdated = date('d M Y, h:i A', filemtime($xmlFile));   // file modification time, nicely formatted
    }
}

$currentPage = 'xml';                              // highlights "View XML Records" in the navbar
require_once 'includes/header.php';                // prints the top of the page and navbar
?>

<div class="page-header">
    <h1 class="page-title">XML Records (<?php echo count($records); ?>)</h1>

    <!-- Regenerate button: a POST form with the CSRF token, posting back to this same page -->
    <form action="" method="POST">
        <?php echo csrf_field(); ?>
        <button type="submit" class="btn btn-primary">Regenerate XML from Database</button>
    </form>
</div>

<?php if ($success !== ''): ?>                     <!-- green box after regenerating -->
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if ($error !== ''): ?>                       <!-- red box if something failed -->
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($lastUpdated !== ''): ?>                 <!-- show the file time only when the file was read successfully -->
    <p style="margin-bottom:14px; color:#6b7280;">Source: students.xml &mdash; last updated <?php echo htmlspecialchars($lastUpdated); ?></p>
<?php endif; ?>

<?php if (empty($records) && $error === ''): ?>    <!-- valid file, but no students inside -->
    <div class="alert alert-error">No records in students.xml yet. Click "Regenerate XML from Database".</div>
<?php elseif (!empty($records)): ?>

<div class="table-wrapper">
    <table>
        <tr>
            <th>#</th>
            <th>MySQL ID</th>
            <th>Register No</th>
            <th>Name</th>
            <th>Course</th>
            <th>Semester</th>
            <th>Email</th>
        </tr>
        <?php foreach ($records as $i => $r): ?>   <!-- one table row per XML record -->
        <tr>
            <td><?php echo $i + 1; ?></td>
            <td><?php echo htmlspecialchars($r['id']); ?></td>        <!-- the id attribute: links to the MySQL row -->
            <td><?php echo htmlspecialchars($r['register']); ?></td>  <!-- XML text can contain anything, so escape it -->
            <td><?php echo htmlspecialchars($r['name']); ?></td>
            <td><?php echo htmlspecialchars($r['course']); ?></td>
            <td><?php echo htmlspecialchars($r['semester']); ?></td>
            <td><?php echo htmlspecialchars($r['email']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>       <!-- prints the footer and closes the page -->