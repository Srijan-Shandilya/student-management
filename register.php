<?php
/* Name: Srijan Shandilya | Roll Number: 20231BCA0248 */

session_start();                                   // start or resume the session
require_once 'config/db.php';                      // database connection
require_once 'includes/csrf.php';                  // NEW: loads the CSRF helper functions

// A logged-in user has no reason to register again
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];                                      // list of error messages (empty means no errors)
$username = '';                                    // remembers the typed username so the form can refill it

if ($_SERVER['REQUEST_METHOD'] === 'POST') {       // true only when the form has been submitted

    // NEW: if the token is wrong, add an error. The database step below only runs when $errors is empty
    if (!csrf_verify()) {
        $errors[] = 'Security check failed. Please reload the page and try again.';
    }

    $username = trim($_POST['username'] ?? '');            // read and clean the inputs
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // ---- Server-side validation (same rules as JavaScript) ----
    if ($username === '') {
        $errors[] = 'Username is required.';
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,20}$/', $username)) {   // preg_match = PHP's regular expression test
        $errors[] = 'Username must be 3-20 characters: letters, numbers or underscore only.';
    }

    if ($password === '') {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must be at least 6 characters and contain a letter and a number.';
    }

    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    // ---- Only touch the database if validation passed ----
    if (empty($errors)) {
        try {
            // Step 1: is the username already taken?
            $check = $conn->prepare('SELECT id FROM users WHERE username = ?');
            $check->bind_param('s', $username);
            $check->execute();
            $check->store_result();                // needed so num_rows is available
            $exists = $check->num_rows > 0;        // true if at least one row was found
            $check->close();

            if ($exists) {
                $errors[] = 'This username is already taken. Please choose another.';
            } else {
                // Step 2: hash the password and insert the new user
                $hash = password_hash($password, PASSWORD_DEFAULT);   // PASSWORD_DEFAULT = PHP's recommended algorithm
                $insert = $conn->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
                $insert->bind_param('ss', $username, $hash);          // two strings
                $insert->execute();
                $insert->close();

                $_SESSION['flash_success'] = 'Registration successful! Please log in.';
                header('Location: index.php');                        // go to the login page
                exit;
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {                             // 1062 = duplicate entry (UNIQUE rule hit)
                $errors[] = 'This username is already taken. Please choose another.';
            } else {
                error_log('Registration error: ' . $e->getMessage()); // log the real reason
                $errors[] = 'Something went wrong. Please try again.';
            }
        }
    }
}
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
    </nav>

    <div class="auth-wrapper">
        <div class="auth-card">
            <h2>Create Account</h2>

            <?php foreach ($errors as $err): ?>                         <!-- one red box for each error message -->
                <div class="alert alert-error"><?php echo htmlspecialchars($err); ?></div>
            <?php endforeach; ?>

            <!-- action="" means: post back to this same file -->
            <form id="registerForm" action="" method="POST" novalidate>
                <?php echo csrf_field(); ?>                             <!-- NEW: prints the hidden token input -->
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Choose a username"
                           value="<?php echo htmlspecialchars($username); ?>"> <!-- refill what the user typed -->
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="At least 6 characters"> <!-- never refill passwords -->
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password">
                </div>
                <button type="submit" class="btn btn-success btn-block">Register</button>
            </form>

            <p class="switch-link">Already registered? <a href="index.php">Login here</a></p>
        </div>
    </div>

    <div class="footer">Srijan Shandilya - 20231BCA0248</div>

    <script src="js/validation.js"></script>
</body>
</html>