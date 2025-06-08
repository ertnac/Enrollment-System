<?php
// Start session to store success message
session_start();

// Include database connection and controller
require_once 'db.php';
require_once 'StudentController.php';

// Create controller instance
$studentController = new StudentController($conn);

// Function to calculate age from date of birth
function calculateAge($dateOfBirth)
{
    $dob = new DateTime($dateOfBirth);
    $today = new DateTime();
    $age = $today->diff($dob)->y;
    return $age;
}

// Function to generate unique application ID
function generateApplicationID($conn)
{
    $currentYear = date('Y');
    $sql = "SELECT MAX(CAST(SUBSTRING(application_id, 7) AS UNSIGNED)) as max_id FROM pending_applications WHERE application_id LIKE 'APP$currentYear%'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $maxID = $row['max_id'] ?? 0;
    $newID = $maxID + 1;
    return "APP$currentYear-" . str_pad($newID, 3, '0', STR_PAD_LEFT);
}

// Initialize result array
$result = ['success_message' => '', 'error_message' => ''];

// Check for success message in session
if (isset($_SESSION['success_message'])) {
    $result['success_message'] = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll_student'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];

    $studentId = $_POST['student_id'];
    if (!preg_match('/^S[0-9]{4}-[0-9]{3}$/', $studentId)) {
        $response['message'] = "Invalid student ID format. Use SYYYY-NNN (e.g., S2025-001).";
        echo json_encode($response);
        exit();
    }

    $checkSql = "SELECT COUNT(*) as count FROM students WHERE student_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param('s', $studentId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();

    if ($checkResult['count'] > 0) {
        $response['message'] = "Student ID $studentId already exists.";
        echo json_encode($response);
        exit();
    }

    $age = calculateAge($_POST['date_of_birth']);

    $studentData = [
        'student_id' => $studentId,
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'middle_name' => $_POST['middle_name'] ?? '',
        'suffix' => $_POST['suffix'] ?? '',
        'age' => $age,
        'date_of_birth' => $_POST['date_of_birth'],
        'sex' => $_POST['sex'],
        'nationality' => $_POST['nationality'],
        'mobile_number' => $_POST['mobile_number'],
        'email' => $_POST['email'],
        'date_created' => date('Y-m-d'),
        'program_name' => $_POST['program'],
        'year_level' => $_POST['year_level'],
        'department_name' => trim($_POST['department'])
    ];

    // Validate department_name
    $checkDeptSql = "SELECT COUNT(*) as count FROM departments WHERE department_name = ?";
    $checkDeptStmt = $conn->prepare($checkDeptSql);
    $checkDeptStmt->bind_param('s', $studentData['department_name']);
    $checkDeptStmt->execute();
    $checkDeptResult = $checkDeptStmt->get_result()->fetch_assoc();
    $checkDeptStmt->close();

    if ($checkDeptResult['count'] == 0) {
        $response['message'] = "Invalid department name: {$studentData['department_name']} does not exist.";
        echo json_encode($response);
        exit();
    }

    $conn->begin_transaction();

    try {
        $sql = "INSERT INTO students (student_id, last_name, first_name, middle_name, suffix, age, date_of_birth, sex, nationality, mobile_number, email, date_created, program_name, year_level, department_name) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'sssssisssssssss',
            $studentData['student_id'],
            $studentData['last_name'],
            $studentData['first_name'],
            $studentData['middle_name'],
            $studentData['suffix'],
            $studentData['age'],
            $studentData['date_of_birth'],
            $studentData['sex'],
            $studentData['nationality'],
            $studentData['mobile_number'],
            $studentData['email'],
            $studentData['date_created'],
            $studentData['program_name'],
            $studentData['year_level'],
            $studentData['department_name']
        );

        if (!$stmt->execute()) {
            throw new Exception("Error enrolling student: " . $conn->error);
        }
        $stmt->close();

        $applicationId = generateApplicationID($conn);
        $studentName = $studentData['first_name'] . ' ' . ($studentData['middle_name'] ? $studentData['middle_name'] . ' ' : '') . $studentData['last_name'] . ($studentData['suffix'] ? ' ' . $studentData['suffix'] : '');

        $sql = "INSERT INTO pending_applications (application_id, student_id, student_name, program_name, year_level, date_submitted, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'Pending')";
        $stmt = $conn->prepare($sql);
        $dateSubmitted = date('Y-m-d');
        $stmt->bind_param(
            'ssssss',
            $applicationId,
            $studentData['student_id'],
            $studentName,
            $studentData['program_name'],
            $studentData['year_level'],
            $dateSubmitted
        );

        if (!$stmt->execute()) {
            throw new Exception("Error adding to pending applications: " . $conn->error);
        }
        $stmt->close();

        $conn->commit();

        $response['success'] = true;
        $response['message'] = "Student enrolled successfully and added to pending applications!";
    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = "Error: " . $e->getMessage();
    }

    echo json_encode($response);
    exit();
}

// Handle edit student form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_student'])) {
    $studentId = $_POST['student_id'];
    $age = calculateAge($_POST['date_of_birth']);

    $studentData = [
        'last_name' => $_POST['last_name'],
        'first_name' => $_POST['first_name'],
        'middle_name' => $_POST['middle_name'] ?? '',
        'suffix' => $_POST['suffix'] ?? '',
        'age' => $age,
        'date_of_birth' => $_POST['date_of_birth'],
        'sex' => $_POST['sex'],
        'nationality' => $_POST['nationality'],
        'mobile_number' => $_POST['mobile_number'],
        'email' => $_POST['email'],
        'department_name' => $_POST['department'],
        'program_name' => $_POST['program'],
        'year_level' => $_POST['year_level']
    ];

    try {
        $sql = "UPDATE students SET last_name = ?, first_name = ?, middle_name = ?, suffix = ?, age = ?, date_of_birth = ?, sex = ?, nationality = ?, mobile_number = ?, email = ?, department_name = ?, program_name = ?, year_level = ? WHERE student_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'ssssisssssssss',
            $studentData['last_name'],
            $studentData['first_name'],
            $studentData['middle_name'],
            $studentData['suffix'],
            $studentData['age'],
            $studentData['date_of_birth'],
            $studentData['sex'],
            $studentData['nationality'],
            $studentData['mobile_number'],
            $studentData['email'],
            $studentData['department_name'],
            $studentData['program_name'],
            $studentData['year_level'],
            $studentId
        );

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Student updated successfully!";
            header("Location: index.php");
            exit();
        } else {
            $result['error_message'] = "Error updating student: " . $conn->error;
        }
        $stmt->close();
    } catch (Exception $e) {
        $result['error_message'] = "Error: " . $e->getMessage();
    }
}

// Generate dynamic student ID
function generateStudentID($conn)
{
    $currentYear = date('Y');
    $sql = "SELECT MAX(CAST(SUBSTRING(student_id, 7) AS UNSIGNED)) as max_id FROM students WHERE student_id LIKE 'S$currentYear%'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $maxID = $row['max_id'] ?? 0;
    $newID = $maxID + 1;
    return "S$currentYear-" . str_pad($newID, 3, '0', STR_PAD_LEFT);
}

$dynamicStudentID = generateStudentID($conn);

// Fetch departments and programs data from database
function fetchDepartmentsAndPrograms($conn)
{
    $sql = "SELECT d.department_name, p.program_name, c.course_code, c.course_name, c.units, c.prerequisites, c.year_level 
            FROM departments d
            LEFT JOIN programs p ON d.department_id = p.department_id
            LEFT JOIN courses c ON p.program_name = c.program_name
            ORDER BY d.department_name, p.program_name, c.year_level, c.course_code";
    $result = $conn->query($sql);
    $departments = [];
    while ($row = $result->fetch_assoc()) {
        $dept = $row['department_name'];
        $prog = $row['program_name'];
        if (!isset($departments[$dept])) {
            $departments[$dept] = [];
        }
        if ($prog && !isset($departments[$dept][$prog])) {
            $departments[$dept][$prog] = [];
        }
        if ($row['course_code']) {
            $departments[$dept][$prog][] = [
                'course_code' => $row['course_code'],
                'course_name' => $row['course_name'],
                'units' => $row['units'],
                'prerequisites' => $row['prerequisites'] ? explode(',', $row['prerequisites']) : [],
                'year_level' => $row['year_level']
            ];
        }
    }
    return $departments;
}

$departmentsDataJSON = json_encode(fetchDepartmentsAndPrograms($conn));
?>