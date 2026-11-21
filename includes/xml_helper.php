<?php
/* Name: Srijan Shandilya | Roll Number: 20231BCA0248 */

// Rebuilds students.xml from the students table. $conn is the mysqli connection from config/db.php
function sync_students_xml(mysqli $conn): bool {

    $xmlPath = __DIR__ . '/../students.xml';       // __DIR__ = this includes folder; /../ goes up one level to the project root

    try {
        // No user input here, so a normal query() is safe. Same fields as the XML requirement
        $result = $conn->query(
            'SELECT id, register_number, student_name, course, semester, email
             FROM students ORDER BY register_number ASC'
        );
        $rows = $result->fetch_all(MYSQLI_ASSOC);  // all students as an array of arrays

        $dom = new DOMDocument('1.0', 'UTF-8');    // create an empty XML document (version 1.0, UTF-8 encoding)
        $dom->formatOutput = true;                 // indent the file so a human can read it

        // The required identification comment at the top of the XML file
        $dom->appendChild($dom->createComment(' Name: Srijan Shandilya | Roll Number: 20231BCA0248 '));

        $root = $dom->createElement('students');   // the root element: every XML file has exactly one
        $dom->appendChild($root);                  // attach it to the document

        foreach ($rows as $row) {                  // one <student> element per database row
            $student = $dom->createElement('student');
            $student->setAttribute('id', (string)$row['id']);   // attribute = the MySQL primary key (links XML to MySQL)

            // tag name => value, in the order they should appear in the file
            $fields = [
                'register_number' => $row['register_number'],
                'name'            => $row['student_name'],
                'course'          => $row['course'],
                'semester'        => $row['semester'],
                'email'           => $row['email']
            ];

            foreach ($fields as $tag => $value) {
                $element = $dom->createElement($tag);                          // e.g. <name></name>
                $element->appendChild($dom->createTextNode((string)$value));   // createTextNode escapes & < > so the XML stays valid
                $student->appendChild($element);                               // put the field inside <student>
            }

            $root->appendChild($student);          // put <student> inside <students>
        }

        // save() returns the number of bytes written, or false on failure. @ hides PHP's warning so the page layout is not broken
        return @$dom->save($xmlPath) !== false;

    } catch (Throwable $e) {
        error_log('XML sync error: ' . $e->getMessage());   // real reason goes to the log only
        return false;                                       // the caller decides what message to show
    }
}