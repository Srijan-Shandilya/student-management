<!-- Name: Srijan Shandilya | Roll Number: 20231BCA0248 -->

# University Student Information and Academic Management System

A web-based student management system built for an experience towards Web Development. Users register and log in, then manage student records (add, view, edit, delete). Student data is stored in MySQL and also mirrored into an XML file that can be viewed in the application.

| | |
|---|---|
| **Student Name** | Srijan Shandilya |
| **Roll Number** | 20231BCA0248 |
| **Project Type** | Web Development |

## Features

- **Login and registration** with username and password validation
- **Secure sessions:** protected pages redirect to the login page when the user is not logged in
- **Dashboard** with cards: Total Students, Add Student, View Students, XML Records
- **Student management (CRUD):** add, view, edit and delete students
- **Duplicate prevention:** register numbers must be unique
- **Validation on both sides:** JavaScript in the browser and PHP on the server
- **XML records:** `students.xml` is generated from MySQL after every add, edit or delete, and displayed as a table by parsing the XML with PHP
- **Responsive design** in a blue and white university theme

## Technologies Used

| Technology | Purpose |
|---|---|
| HTML5 | Page structure |
| CSS3 | Styling, layout and responsive design |
| JavaScript | Client-side validation and interactivity |
| PHP 8 | Server-side logic, sessions, CRUD, XML processing |
| MySQL | Primary database |
| XML | Storing and displaying student records |
| XAMPP | Local server (Apache, MySQL, phpMyAdmin) |
| Git and GitHub | Version control |

## Security Measures

- Passwords are hashed with `password_hash()` and checked with `password_verify()`
- All database queries use **prepared statements** (protection against SQL injection)
- Output is escaped with `htmlspecialchars()` (protection against XSS)
- **CSRF tokens** on every form that changes data
- Server-side validation of every input, even when JavaScript has already checked it
- `students.xml` cannot be opened directly in the browser (blocked by `.htaccess`); it is only visible through the login-protected XML page

## How the Parts Work Together

```
Browser (HTML, CSS, JavaScript)  --HTTP-->  Apache + PHP  --SQL-->  MySQL (student_management)
                                                  |
                                                  +--> students.xml (generated from MySQL)
```

1. The browser shows the pages, and JavaScript checks the form fields.
2. PHP checks the login session, validates the data again and talks to MySQL.
3. After a student is added, edited or deleted, PHP rebuilds `students.xml` from MySQL. MySQL is the primary database; the XML file is always generated from it.

## Project Structure

```
student-management/
├── index.php              Login page (first page)
├── login.php              Processes the login form
├── register.php           Registration page
├── logout.php             Ends the session
├── dashboard.php          Dashboard with cards
├── add_student.php        Add a student
├── view_students.php      List of students
├── edit_student.php       Edit a student
├── delete_student.php     Delete a student
├── xml_display.php        Shows the XML records
├── students.xml           XML copy of the student records
├── .htaccess              Blocks direct access to students.xml
├── config/
│   └── db.php             MySQL connection settings
├── css/
│   └── style.css          Shared stylesheet
├── js/
│   └── validation.js      Client-side validation
├── includes/
│   ├── auth_check.php     Login guard for protected pages
│   ├── csrf.php           CSRF token helper functions
│   ├── header.php         Shared page top and navigation
│   ├── footer.php         Shared page bottom
│   └── xml_helper.php     Rebuilds students.xml from MySQL
├── database/
│   └── student_management.sql   Database creation script
└── README.md
```

## Database

Database name: `student_management`

**Table `users`**

| Column | Type | Notes |
|---|---|---|
| id | INT | Primary key, auto increment |
| username | VARCHAR(50) | Unique |
| password | VARCHAR(255) | Hashed password |

**Table `students`**

| Column | Type | Notes |
|---|---|---|
| id | INT | Primary key, auto increment |
| register_number | VARCHAR(20) | Unique |
| student_name | VARCHAR(100) | |
| course | VARCHAR(100) | |
| semester | TINYINT UNSIGNED | 1 to 8 |
| email | VARCHAR(100) | |
| contact_number | VARCHAR(10) | 10 digits |

## Installation and Setup (Windows, XAMPP)

### 1. Install XAMPP

1. Download XAMPP from https://www.apachefriends.org (choose a PHP 8.x version).
2. Run the installer, keep the default folder `C:\xampp`, and make sure **Apache, MySQL, PHP and phpMyAdmin** are selected.

### 2. Get the project

Clone the repository into XAMPP's web folder:

```
cd C:\xampp\htdocs
git clone https://github.com/Srijan-Shandilya/student-management.git
```

Or download the ZIP from GitHub and extract it so the path becomes `C:\xampp\htdocs\student-management`. The folder name must be exactly `student-management`.

### 3. Start the servers

Open the **XAMPP Control Panel** and click **Start** next to **Apache** and **MySQL**. Both should turn green.

### 4. Create the database

1. Open http://localhost/phpmyadmin
2. Click the **Import** tab.
3. Choose the file `database/student_management.sql` from the project folder.
4. Click **Import**. The database `student_management` and its two tables are created.

### 5. Check the connection settings

`config/db.php` uses the XAMPP defaults. Change them only if your MySQL setup is different.

| Setting | Default value |
|---|---|
| Host | localhost |
| User | root |
| Password | (empty) |
| Database | student_management |

### 6. Run the project

Open http://localhost/student-management/ in your browser. The login page appears.

## How to Use

1. Click **Register here** and create an account (username 3 to 20 characters of letters, digits or underscore; password at least 6 characters with a letter and a number).
2. Log in with that account. There is no default user; every account is created through the registration page.
3. Use **Add Student** and **View Students** to manage records. Edit and Delete buttons are in the student table.
4. Open **View XML Records** to see the XML data. The page shows when `students.xml` was last updated, and a button rebuilds the file from MySQL if needed.

## Troubleshooting

| Problem | Solution |
|---|---|
| Apache will not start | Port 80 is probably in use by another program (for example Skype or IIS). Close it or change Apache's port in the XAMPP Control Panel. |
| "Database connection failed" | Start MySQL in XAMPP and check that the `student_management` database was imported. |
| The page shows a folder listing instead of the login page | The project folder is named incorrectly, or `index.php` is missing from it. |
| `students.xml` shows 403 Forbidden in the browser | This is intentional. View the data through **View XML Records** after logging in. |
| Styles do not load | Press Ctrl + F5 to clear the browser cache. |

## Screenshots

Screenshots of the login page, registration page, dashboard, student list, add and edit forms, and XML page will be added to the project report.

## Version Control

The project is managed with Git and hosted on GitHub. Each major module was committed separately: database and connection, design and authentication, dashboard and CRUD, security and XML.

## Author

**Srijan Shandilya** (Roll Number: 20231BCA0248)