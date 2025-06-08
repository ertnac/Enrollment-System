<?php
require_once 'db.php';

class StudentController
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function enrollStudent()
    {
        $errors = [];
        $success_message = '';
        $error_message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll_student'])) {
            // Sanitize inputs
            $student_id = sanitizeInput($this->conn, $_POST['student_id']);
            $first_name = sanitizeInput($this->conn, $_POST['first_name']);
            $last_name = sanitizeInput($this->conn, $_POST['last_name']);
            $middle_name = sanitizeInput($this->conn, $_POST['middle_name'] ?? '');
            $suffix = sanitizeInput($this->conn, $_POST['suffix'] ?? '');
            $email = sanitizeInput($this->conn, $_POST['email']);
            $program = sanitizeInput($this->conn, $_POST['program']);
            $year_level = sanitizeInput($this->conn, $_POST['year_level']);
            $enrollment_date = sanitizeInput($this->conn, $_POST['enrollment_date']);
            $date_of_birth = sanitizeInput($this->conn, $_POST['date_of_birth'] ?? '');
            $sex = sanitizeInput($this->conn, $_POST['sex'] ?? '');
            $nationality = sanitizeInput($this->conn, $_POST['nationality'] ?? '');
            $mobile_number = sanitizeInput($this->conn, $_POST['mobile_number'] ?? '');

            // Validation
            if (empty($student_id)) {
                $errors[] = "Student ID is required";
            }
            if (empty($first_name) || empty($last_name)) {
                $errors[] = "First and last names are required";
            }
            if (!validateEmail($email)) {
                $errors[] = "Valid email is required";
            }
            if (empty($program)) {
                $errors[] = "Program selection is required";
            }
            if (empty($year_level)) {
                $errors[] = "Year level is required";
            }

            // Check if student ID or email already exists
            $check_query = "SELECT student_id, email FROM Students WHERE student_id = '$student_id' OR email = '$email'";
            $check_result = $this->conn->query($check_query);
            if ($check_result->num_rows > 0) {
                $errors[] = "Student ID or email already exists";
            }

            if (empty($errors)) {
                // Insert student data
                $sql = "INSERT INTO Students (
                    student_id, 
                    last_name, 
                    first_name, 
                    middle_name, 
                    suffix, 
                    email, 
                    program_name, 
                    date_created, 
                    date_of_birth, 
                    sex, 
                    nationality, 
                    mobile_number,
                    year_level
                ) VALUES (
                    '$student_id', 
                    '$last_name', 
                    '$first_name', 
                    '$middle_name', 
                    '$suffix', 
                    '$email', 
                    '$program', 
                    '$enrollment_date', 
                    '$date_of_birth', 
                    '$sex', 
                    '$nationality', 
                    '$mobile_number',
                    '$year_level'
                )";

                if ($this->conn->query($sql)) {
                    // Insert into Enrollments table
                    $enrollment_sql = "INSERT INTO Enrollments (student_id, program_name, date_enrolled, year_level, status) 
                        VALUES ('$student_id', '$program', '$enrollment_date', '$year_level', 'Active')";

                    if ($this->conn->query($enrollment_sql)) {
                        $success_message = "Student enrolled successfully!";
                    } else {
                        $errors[] = "Failed to create enrollment record: " . $this->conn->error;
                    }
                } else {
                    $errors[] = "Error enrolling student: " . $this->conn->error;
                }
            }

            if (!empty($errors)) {
                $error_message = implode("<br>", $errors);
            }
        }

        return [
            'errors' => $errors,
            'success_message' => $success_message,
            'error_message' => $error_message
        ];
    }

    public function getStudents()
    {
        return fetchData($this->conn, "Students");
    }

    public function getRecentEnrollments()
    {
        return fetchData(
            $this->conn,
            "Enrollments e JOIN Students s ON e.student_id = s.student_id",
            "e.student_id, CONCAT(s.first_name, ' ', s.last_name) as name, e.program_name, e.date_enrolled, e.status",
            "1 ORDER BY e.date_enrolled DESC LIMIT 3"
        );
    }

    public function getPrograms()
    {
        return fetchData($this->conn, "Programs");
    }
}
