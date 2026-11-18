/* Name: Srijan Shandilya | Roll Number: 20231BCA0248 */

// Show an error message under an input box
function showError(input, message) {
    clearError(input);                               // remove any old error first so messages don't pile up
    input.classList.add('invalid');                  // adds the red border (defined in style.css)
    const span = document.createElement('span');     // create a new <span> element in memory
    span.className = 'error-text';                   // give it the red small-text style
    span.textContent = message;                      // put the message text inside it (textContent is safe from HTML injection)
    input.parentElement.appendChild(span);           // place it inside the same .form-group, under the input
}

// Remove the error message and red border from an input box
function clearError(input) {
    input.classList.remove('invalid');                                // remove the red border
    const old = input.parentElement.querySelector('.error-text');     // look for an existing error message
    if (old) {                                                        // if one exists...
        old.remove();                                                 // ...delete it from the page
    }
}

// Rule: the field must not be empty. Returns true if OK, false if not
function validateRequired(input, label) {
    if (input.value.trim() === '') {                 // trim() removes spaces at both ends, so "   " counts as empty
        showError(input, label + ' is required.');   // show a message like "Username is required."
        return false;                                // tell the caller this field failed
    }
    clearError(input);                               // field is fine, so clear any old error
    return true;                                     // tell the caller this field passed
}

// Rule: username is 3-20 characters, only letters, numbers and underscore
function validateUsername(input) {
    if (!validateRequired(input, 'Username')) {      // first make sure it's not empty
        return false;                                // stop here if empty (error already shown)
    }
    const pattern = /^[A-Za-z0-9_]{3,20}$/;          // regular expression describing a valid username
    if (!pattern.test(input.value.trim())) {         // test() returns true when the text matches the pattern
        showError(input, 'Username must be 3-20 characters: letters, numbers or underscore only.');
        return false;
    }
    clearError(input);
    return true;
}

// Rule: password has at least 6 characters, with at least one letter and one number
function validatePassword(input) {
    if (!validateRequired(input, 'Password')) {      // must not be empty
        return false;
    }
    const value = input.value;                       // passwords are NOT trimmed, spaces can be part of a password
    if (value.length < 6 || !/[A-Za-z]/.test(value) || !/[0-9]/.test(value)) {
        showError(input, 'Password must be at least 6 characters and contain a letter and a number.');
        return false;
    }
    clearError(input);
    return true;
}

// Rule: confirm password must be identical to the password
function validateConfirm(passwordInput, confirmInput) {
    if (!validateRequired(confirmInput, 'Confirm password')) {   // must not be empty
        return false;
    }
    if (passwordInput.value !== confirmInput.value) {            // both boxes must match exactly
        showError(confirmInput, 'Passwords do not match.');
        return false;
    }
    clearError(confirmInput);
    return true;
}

/* ===================== NEW: STUDENT FORM RULES ===================== */

// Rule: register number is 5-20 letters/digits only (no spaces or symbols)
function validateRegisterNumber(input) {
    if (!validateRequired(input, 'Register number')) {   // must not be empty
        return false;
    }
    if (!/^[A-Za-z0-9]{5,20}$/.test(input.value.trim())) {   // letters and digits only, length 5 to 20
        showError(input, 'Register number must be 5-20 letters or digits, no spaces or symbols.');
        return false;
    }
    clearError(input);
    return true;
}

// Rule: student name is 2-100 characters: letters, spaces and dots only
function validateStudentName(input) {
    if (!validateRequired(input, 'Student name')) {
        return false;
    }
    if (!/^[A-Za-z .]{2,100}$/.test(input.value.trim())) {   // allows names like "A. P. Kumar"
        showError(input, 'Name must be 2-100 characters: letters, spaces and dots only.');
        return false;
    }
    clearError(input);
    return true;
}

// Rule: a dropdown (select) must have a real option chosen (the empty first option is not valid)
function validateSelect(input, label) {
    if (input.value === '') {                            // the placeholder option has an empty value
        showError(input, 'Please select a ' + label + '.');
        return false;
    }
    clearError(input);
    return true;
}

// Rule: email must look like name@domain.xyz
function validateEmail(input) {
    if (!validateRequired(input, 'Email')) {
        return false;
    }
    const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;       // no spaces, one @, and a dot after the @
    if (!pattern.test(input.value.trim())) {
        showError(input, 'Please enter a valid email address.');
        return false;
    }
    clearError(input);
    return true;
}

// Rule: contact number is exactly 10 digits
function validateContact(input) {
    if (!validateRequired(input, 'Contact number')) {
        return false;
    }
    if (!/^[0-9]{10}$/.test(input.value.trim())) {       // exactly ten digits, nothing else
        showError(input, 'Contact number must be exactly 10 digits.');
        return false;
    }
    clearError(input);
    return true;
}

// Run this code only after the whole HTML page has loaded
document.addEventListener('DOMContentLoaded', function () {

    // ---- LOGIN FORM ----
    const loginForm = document.getElementById('loginForm');       // find the form (null on pages that don't have it)
    if (loginForm) {                                              // only attach the code if the form exists on this page
        loginForm.addEventListener('submit', function (event) {   // runs when the user clicks the submit button
            const userOk = validateRequired(loginForm.username, 'Username');  // check username (loginForm.username = input named "username")
            const passOk = validateRequired(loginForm.password, 'Password');  // check password
            if (!userOk || !passOk) {                             // if any check failed...
                event.preventDefault();                           // ...stop the form from being sent to the server
            }
        });
    }

    // ---- REGISTER FORM ----
    const registerForm = document.getElementById('registerForm'); // find the registration form
    if (registerForm) {
        registerForm.addEventListener('submit', function (event) {
            const userOk = validateUsername(registerForm.username);
            const passOk = validatePassword(registerForm.password);
            const confOk = validateConfirm(registerForm.password, registerForm.confirm_password);
            if (!userOk || !passOk || !confOk) {                  // all three must pass
                event.preventDefault();                           // block submission if anything failed
            }
        });
    }

    // ---- STUDENT FORM (used by add_student.php now and edit_student.php later) ----
    const studentForm = document.getElementById('studentForm');   // find the student form
    if (studentForm) {
        studentForm.addEventListener('submit', function (event) {
            // Run EVERY check (not stopping at the first failure) so all errors show together
            const regOk     = validateRegisterNumber(studentForm.register_number);
            const nameOk    = validateStudentName(studentForm.student_name);
            const courseOk  = validateSelect(studentForm.course, 'course');
            const semOk     = validateSelect(studentForm.semester, 'semester');
            const emailOk   = validateEmail(studentForm.email);
            const contactOk = validateContact(studentForm.contact_number);

            if (!regOk || !nameOk || !courseOk || !semOk || !emailOk || !contactOk) {
                event.preventDefault();                           // block submission if any rule failed
            }
        });
    }
});