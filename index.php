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

    // Check if student ID already exists
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
    $selectedCourses = json_decode($_POST['courses'] ?? '[]', true);

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

    // Validate selected courses
    if (!empty($selectedCourses)) {
        $placeholders = implode(',', array_fill(0, count($selectedCourses), '?'));
        $checkCoursesSql = "SELECT course_code FROM courses WHERE course_code IN ($placeholders) AND program_name = ? AND year_level = ?";
        $checkCoursesStmt = $conn->prepare($checkCoursesSql);
        $checkCoursesStmt->bind_param(str_repeat('s', count($selectedCourses)) . 'ss', ...array_merge($selectedCourses, [$studentData['program_name'], $studentData['year_level']]));
        $checkCoursesStmt->execute();
        $validCourses = $checkCoursesStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $checkCoursesStmt->close();

        $validCourseCodes = array_column($validCourses, 'course_code');
        $invalidCourses = array_diff($selectedCourses, $validCourseCodes);
        if (!empty($invalidCourses)) {
            $response['message'] = "Invalid courses selected: " . implode(', ', $invalidCourses);
            echo json_encode($response);
            exit();
        }
    }

    $conn->begin_transaction();

    try {
        // Insert into students table
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

        // Insert into pending_applications table
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

        // Insert into student_courses table
        if (!empty($selectedCourses)) {
            $sql = "INSERT INTO student_courses (student_id, course_code, date_enrolled, status) VALUES (?, ?, ?, 'Pending')";
            $stmt = $conn->prepare($sql);
            $dateEnrolled = date('Y-m-d');
            foreach ($selectedCourses as $courseCode) {
                $stmt->bind_param('sss', $studentData['student_id'], $courseCode, $dateEnrolled);
                if (!$stmt->execute()) {
                    throw new Exception("Error adding course $courseCode to student_courses: " . $conn->error);
                }
            }
            $stmt->close();
        }

        $conn->commit();

        $response['success'] = true;
        $response['message'] = "Student enrolled successfully, added to pending applications, and courses assigned!";
    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = "Error: " . $e->getMessage();
    }

    echo json_encode($response);
    exit();
}

// Handle edit student form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_student'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];

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
        'email' => $_POST['email']
    ];

    try {
        $sql = "UPDATE students SET last_name = ?, first_name = ?, middle_name = ?, suffix = ?, age = ?, date_of_birth = ?, sex = ?, nationality = ?, mobile_number = ?, email = ? WHERE student_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'ssssissssss',
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
            $studentId
        );

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Student updated successfully!";
        } else {
            $response['message'] = "Error updating student: " . $conn->error;
        }
        $stmt->close();
    } catch (Exception $e) {
        $response['message'] = "Error: " . $e->getMessage();
    }

    echo json_encode($response);
    exit();
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

// Fetch schedules from database
function fetchSchedules($conn)
{
    $sql = "SELECT schedule_id, schedule_description, days, time_start, time_end, room FROM schedules ORDER BY days, time_start";
    $result = $conn->query($sql);
    $schedules = [];
    while ($row = $result->fetch_assoc()) {
        $schedules[] = $row;
    }
    return $schedules;
}

$schedulesDataJSON = json_encode(fetchSchedules($conn));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>EduTrack - Student Enrollment</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link href="https://unpkg.com/lucide@latest/dist/lucide.min.css" rel="stylesheet" />
    <style>
        :root {
            --primary: rgb(252, 165, 165);
            --primary-light: rgb(252, 165, 165);
            --bg: rgb(248, 252, 251);
            --card: rgb(255, 255, 255);
            --text: rgb(51, 72, 85);
            --text-light: rgb(100, 139, 126);
            --border: rgb(226, 233, 240);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --radius: 12px;
            --space: 1.5rem;
            --error: #dc2626;
            --success: #166534;
        }

        [data-theme="dark"] {
            --primary: rgb(247, 115, 115) --primary-light: rgb(253, 124, 124);
            --bg: #0f172a;
            --card: #1e293b;
            --text: #f8fafc;
            --text-light: #94a3b8;
            --border: #334155;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Poppins", sans-serif;
            background-color: var(--bg);
            color: var(--text);
            transition: all 0.3s ease;
        }

        .container {
            display: grid;
            grid-template-columns: 240px 1fr;
            min-height: 100vh;
        }

        /* Sidebar styles */
        .sidebar {
            background-color: var(--card);
            border-right: 1px solid var(--border);
            padding: var(--space);
            position: sticky;
            top: 0;
            height: 100vh;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary);
        }

        .nav-menu {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            border-radius: var(--radius);
            text-decoration: none;
            color: var(--text-light);
            transition: all 0.2s ease;
        }

        .nav-item:hover,
        .nav-item.active {
            background-color: var(--primary-light);
            color: white;
        }

        .nav-item i {
            font-size: 1.1rem;
        }

        /* Main content */
        .main-content {
            padding: var(--space);
            overflow-y: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--space);
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 600;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .theme-toggle {
            background: none;
            border: none;
            color: var(--text-light);
            cursor: pointer;
            font-size: 1.25rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        /* Card styles */
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: var(--space);
            margin-bottom: var(--space);
        }

        .card {
            background-color: var(--card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: var(--space);
            transition: transform 0.2s ease;
        }

        .card:hover {
            transform: translateY(-4px);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 500;
        }

        .card-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background-color: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .card-value {
            font-size: 2rem;
            font-weight: 600;
            margin: 0.5rem 0;
        }

        .card-desc {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        /* Table styles */
        .table-container {
            background-color: var(--card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: var(--space);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        th {
            color: var(--text-light);
            font-weight: 500;
            font-size: 0.9rem;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status.active {
            background-color: #dcfce7;
            color: #166534;
        }

        .status.inactive {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .status.pending {
            background-color: #fef9c3;
            color: #854d0e;
        }

        .status.accepted {
            background-color: #c3e6cb;
            color: #155724;
        }

        .status.rejected {
            background-color: #f5c6cb;
            color: #721c24;
        }

        .action-btn {
            background: none;
            border: none;
            color: var(--primary);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 4px;
        }

        .action-btn:hover {
            background-color: var(--primary-light);
            color: white;
        }

        /* Form styles */
        .form-container {
            background-color: var(--card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: var(--space);
            height: fit-content;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background-color: var(--bg);
            color: var(--text);
            font-family: inherit;
            transition: all 0.2s ease;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px var(--primary-light);
        }

        .btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius);
            font-family: inherit;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn:hover {
            background-color: var(--primary-light);
        }

        .btn:disabled {
            background-color: var(--text-light);
            cursor: not-allowed;
        }

        .btn-block {
            display: block;
            width: 100%;
        }

        .btn-secondary {
            background-color: var(--bg);
            color: var(--text);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background-color: var(--border);
        }

        .btn-group {
            display: flex;
            gap: 0.75rem;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: var(--card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: var(--space);
            width: 90%;
            max-width: 1000px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-light);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1rem;
        }

        .alert {
            padding: 1rem;
            border-radius: var(--radius);
            margin-bottom: 1rem;
            opacity: 1;
            transition: opacity 0.5s ease-out;
        }

        .alert-success {
            background-color: #dcfce7;
            color: #166534;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .alert.hidden {
            opacity: 0;
            display: none;
        }

        /* Validation feedback styles */
        .form-group.invalid input {
            border-color: var(--error);
        }

        .form-group.valid input {
            border-color: var(--success);
        }

        .validation-message {
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }

        .validation-message.error {
            color: var(--error);
        }

        .validation-message.success {
            color: var(--success);
        }

        /* Existing styles remain unchanged */
        .program-card {
            background-color: var(--card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: var(--space);
            margin-bottom: var(--space);
        }

        .program-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .program-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary);
        }

        .course-list {
            margin-left: 1rem;
        }

        .course-item {
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
        }

        .course-item:last-child {
            border-bottom: none;
        }

        .search-filter {
            display: flex;
            gap: 1rem;
            margin-bottom: var(--space);
        }

        .search-box {
            flex: 1;
        }

        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
            }

            .sidebar {
                display: none;
            }

            .search-filter {
                flex-direction: column;
            }
        }

        .student-table-container {
            background-color: var(--card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: var(--space);
            overflow-x: auto;
        }

        .student-table {
            width: var(--card);
            border-collapse: collapse;
        }

        .student-table th,
        .student-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        .student-table th {
            color: var(--text-light);
            font-weight: 500;
            font-size: 0.9rem;
            position: sticky;
            top: 0;
            background-color: var(--card);
        }

        .student-table tr:hover td {
            background-color: rgba(99, 102, 241, 0.05);
        }

        .student-table tr:last-child td {
            border-bottom: none;
        }

        .student-actions {
            display: flex;
            gap: 0.5rem;
        }

        #course-selection-container {
            margin-top: -20;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.5rem;
            background-color: var(--card);
        }

        .course-checkbox {
            transform: scale(1.2);
            cursor: pointer;
        }

        .course-checkbox:disabled {
            cursor: not-allowed;
            opacity: 0.6;
        }

        #units-counter {
            font-weight: 500;
            color: var(--text);
        }

        #units-counter.warning {
            color: #d97706;
        }

        #units-counter.error {
            color: #dc2626;
        }

        .enrollment-container {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 1.5rem;
            align-items: start;
        }

        #course-selection-container {
            margin-top: 0;
        }

        #courses-table {
            width: 100%;
        }

        #courses-table thead {
            position: sticky;
            top: 0;
            background-color: var(--card);
            z-index: 2;
        }

        .table-container {
            max-height: 750px;
            overflow-y: auto;
            border: 1px solid var(--border);
            border-radius: var(--radius);
        }

        @media (max-width: 1200px) {
            .enrollment-container {
                grid-template-columns: 1fr;
            }

            .form-container {
                width: 100%;
            }
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1rem;
        }

        .btn-danger {
            background-color: #dc2626;
            color: white;
        }

        .btn-danger:hover {
            background-color: #b91c1c;
        }

        /* New styles for dropdown */
        .program-toggle {
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem;
            color: black;
            font-weight: bold;
            border-radius: var(--radius);
            margin-bottom: 0.5rem;
        }

        .program-toggle:hover {
            background-color: var(--primary);
            color: white;
        }

        .program-content {
            display: none;
            padding: 1rem;
        }

        .program-content.active {
            display: block;
        }

        .department-card {
            margin-bottom: 2rem;
        }

        .department-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="logo">
                <i data-lucide="school"></i>
                <span style="color: rgb(0, 0, 0);">Edu</span><span style="color: rgb(255, 70, 70);">Track</span>
            </div>
            <nav class="nav-menu">
                <a href="#" class="nav-item active" data-view="dashboard">
                    <i data-lucide="layout-dashboard"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="nav-item" data-view="students">
                    <i data-lucide="users"></i>
                    <span>Students</span>
                </a>
                <a href="#" class="nav-item" data-view="instructors">
                    <i data-lucide="user-cog"></i>
                    <span>Instructors</span>
                </a>
                <a href="#" class="nav-item" data-view="enroll">
                    <i data-lucide="book-open"></i>
                    <span>Enroll Students</span>
                </a>
                <a href="#" class="nav-item" data-view="pending-enrollees">
                    <i data-lucide="clock"></i>
                    <span>Pending Enrollees</span>
                </a>
                <a href="#" class="nav-item" data-view="programs">
                    <i data-lucide="layers"></i>
                    <span>Programs & Courses</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Display success or error message -->
            <?php if ($result['success_message']): ?>
                <div class="alert alert-success"><?php echo $result['success_message']; ?></div>
            <?php endif; ?>
            <?php if ($result['error_message']): ?>
                <div class="alert alert-error"><?php echo $result['error_message']; ?></div>
            <?php endif; ?>

            <!-- Dashboard View -->
            <div id="dashboard-view" class="view">
                <!-- Stats Cards -->
                <div class="card-grid">
                    <?php
                    $result = fetchData($conn, "Students", "COUNT(*) as total");
                    $row = $result->fetch_assoc();
                    $total_students = $row['total'];
                    ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Total Students</h3>
                            <div class="card-icon">
                                <i data-lucide="users"></i>
                            </div>
                        </div>
                        <div class="card-value"><?php echo $total_students; ?></div>
                        <p class="card-desc">Across all programs</p>
                    </div>

                    <?php
                    $result = fetchData($conn, "Courses", "COUNT(*) as total");
                    $row = $result->fetch_assoc();
                    $total_courses = $row['total'];
                    ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Active Courses</h3>
                            <div class="card-icon">
                                <i data-lucide="book-open"></i>
                            </div>
                        </div>
                        <div class="card-value"><?php echo $total_courses; ?></div>
                        <p class="card-desc">Across all departments</p>
                    </div>

                    <?php
                    $result = fetchData($conn, "Instructors", "COUNT(*) as total");
                    $row = $result->fetch_assoc();
                    $total_instructors = $row['total'];
                    ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Instructors</h3>
                            <div class="card-icon">
                                <i data-lucide="user-cog"></i>
                            </div>
                        </div>
                        <div class="card-value"><?php echo $total_instructors; ?></div>
                        <p class="card-desc">Teaching staff</p>
                    </div>
                </div>

                <!-- Recent Enrollments Table -->
                <div class="table-container">
                    <h2 class="card-title" style="margin-bottom: 1rem">Recent Enrollments</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Program</th>
                                <th>Date Enrolled</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = fetchData(
                                $conn,
                                "Enrollments e JOIN Students s ON e.student_id = s.student_id",
                                "e.student_id, CONCAT(s.first_name, ' ', s.last_name) as name, e.program_name, e.date_enrolled, e.status",
                                "1 ORDER BY e.date_enrolled DESC LIMIT 3"
                            );

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>
                                        <td>{$row['student_id']}</td>
                                        <td>{$row['name']}</td>
                                        <td>{$row['program_name']}</td>
                                        <td>{$row['date_enrolled']}</td>
                                        <td><span class='status " . strtolower($row['status']) . "'>{$row['status']}</span></td>
                                        <td>
                                            <button class='action-btn'>
                                                <i data-lucide='eye'></i>
                                            </button>
                                            <button class='action-btn'>
                                                <i data-lucide='edit'></i>
                                            </button>
                                        </td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6'>No recent enrollments found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="students-view" class="view" style="display: none">
                <div class="search-filter">
                    <div class="search-box">
                        <input type="text" placeholder="Search students..." />
                    </div>
                    <button class="btn btn-secondary">
                        <i data-lucide="filter"></i> Filter
                    </button>
                </div>

                <div class="table-container" style="overflow-x: auto">
                    <table style="width: 100%">
                        <colgroup>
                            <col style="width: 100px" />
                            <col style="width: 150px" />
                            <col style="width: 150px" />
                            <col style="width: 150px" />
                            <col style="width: 80px" />
                            <col style="width: 70px" />
                            <col style="width: 120px" />
                            <col style="width: 80px" />
                            <col style="width: 120px" />
                            <col style="width: 150px" />
                            <col style="width: 200px" />
                            <col style="width: 120px" />
                            <col style="width: 180px" />
                            <col style="width: 150px" />
                            <col style="width: 100px" />
                            <col style="width: 150px" />
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Last Name</th>
                                <th>First Name</th>
                                <th>Middle Name</th>
                                <th>Suffix</th>
                                <th>Age</th>
                                <th>Date of Birth</th>
                                <th>Sex</th>
                                <th>Nationality</th>
                                <th>Mobile Number</th>
                                <th>Email</th>
                                <th>Date Created</th>
                                <th>Department</th>
                                <th>Program Name</th>
                                <th>Enrollment ID</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = fetchData($conn, "Students");

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>
                                        <td>{$row['student_id']}</td>
                                        <td>{$row['last_name']}</td>
                                        <td>{$row['first_name']}</td>
                                        <td>{$row['middle_name']}</td>
                                        <td>{$row['suffix']}</td>
                                        <td>{$row['age']}</td>
                                        <td>{$row['date_of_birth']}</td>
                                        <td>{$row['sex']}</td>
                                        <td>{$row['nationality']}</td>
                                        <td>{$row['mobile_number']}</td>
                                        <td>{$row['email']}</td>
                                        <td>{$row['date_created']}</td>
                                        <td>{$row['department_name']}</td>
                                        <td>{$row['program_name']}</td>
                                        <td>{$row['enrollment_id']}</td>
                                        <td>
                                            <button class='action-btn view-student-btn' data-student-id='{$row['student_id']}'>
                                                <i data-lucide='eye'></i>
                                            </button>
                                            <button class='action-btn edit-student-btn' data-student-id='{$row['student_id']}'>
                                                <i data-lucide='edit'></i>
                                            </button>
                                            <button class='action-btn'>
                                                <i data-lucide='trash-2'></i>
                                            </button>
                                        </td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='16'>No students found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem">
                    <div style="color: var(--text-light); font-size: 0.9rem">
                        Showing all students
                    </div>
                </div>

                <!-- View Student Modal -->
                <div id="view-student-modal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="modal-title">Student Information</h2>
                            <button class="modal-close">×</button>
                        </div>
                        <div id="student-info">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Student ID</label>
                                    <p id="view-student-id"></p>
                                </div>
                                <div class="form-group">
                                    <label>First Name</label>
                                    <p id="view-first-name"></p>
                                </div>
                                <div class="form-group">
                                    <label>Last Name</label>
                                    <p id="view-last-name"></p>
                                </div>
                                <div class="form-group">
                                    <label>Middle Name</label>
                                    <p id="view-middle-name"></p>
                                </div>
                                <div class="form-group">
                                    <label>Suffix</label>
                                    <p id="view-suffix"></p>
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <p id="view-email"></p>
                                </div>
                                <div class="form-group">
                                    <label>Date of Birth</label>
                                    <p id="view-date-of-birth"></p>
                                </div>
                                <div class="form-group">
                                    <label>Age</label>
                                    <p id="view-age"></p>
                                </div>
                                <div class="form-group">
                                    <label>Sex</label>
                                    <p id="view-sex"></p>
                                </div>
                                <div class="form-group">
                                    <label>Nationality</label>
                                    <p id="view-nationality"></p>
                                </div>
                                <div class="form-group">
                                    <label>Mobile Number</label>
                                    <p id="view-mobile-number"></p>
                                </div>
                                <div class="form-group">
                                    <label>Department</label>
                                    <p id="view-department"></p>
                                </div>
                                <div class="form-group">
                                    <label>Program</label>
                                    <p id="view-program"></p>
                                </div>
                                <div class="form-group">
                                    <label>Year Level</label>
                                    <p id="view-year-level"></p>
                                </div>
                                <div class="form-group">
                                    <label>Enrollment Date</label>
                                    <p id="view-enrollment-date"></p>
                                </div>
                                <div class="form-group">
                                    <label>Enrollment ID</label>
                                    <p id="view-enrollment-id"></p>
                                </div>
                                <div class="form-group">
                                    <label>Status</label>
                                    <p id="view-status"></p>
                                </div>
                            </div>
                            <h3 style="margin-top: 1.5rem; margin-bottom: 1rem">Enrolled Courses</h3>
                            <div class="table-container">
                                <table id="student-courses-table">
                                    <thead>
                                        <tr>
                                            <th>Course Code</th>
                                            <th>Course Name</th>
                                            <th>Units</th>
                                            <th>Prerequisites</th>
                                            <th>Instructor</th>
                                            <th>Schedule</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="view-courses-table-body"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="btn-group" style="justify-content: flex-end; margin-top: 1.5rem">
                            <button type="button" class="btn btn-secondary modal-close">Close</button>
                        </div>
                    </div>
                </div>

                <!-- Edit Student Modal -->
                <div id="edit-student-modal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="modal-title">Edit Student Information</h2>
                            <button class="modal-close">×</button>
                        </div>
                        <form id="edit-student-form">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="edit-student-id">Student ID</label>
                                    <input type="text" id="edit-student-id" name="student_id" readonly />
                                </div>
                                <div class="form-group">
                                    <label for="edit-first-name">First Name</label>
                                    <input type="text" id="edit-first-name" name="first_name" required />
                                </div>
                                <div class="form-group">
                                    <label for="edit-last-name">Last Name</label>
                                    <input type="text" id="edit-last-name" name="last_name" required />
                                </div>
                                <div class="form-group">
                                    <label for="edit-middle-name">Middle Name</label>
                                    <input type="text" id="edit-middle-name" name="middle_name" />
                                </div>
                                <div class="form-group">
                                    <label for="edit-suffix">Suffix</label>
                                    <input type="text" id="edit-suffix" name="suffix" />
                                </div>
                                <div class="form-group">
                                    <label for="edit-email">Email</label>
                                    <input type="email" id="edit-email" name="email" required />
                                </div>
                                <div class="form-group">
                                    <label for="edit-date-of-birth">Date of Birth</label>
                                    <input type="date" id="edit-date-of-birth" name="date_of_birth" required />
                                </div>
                                <div class="form-group">
                                    <label for="edit-sex">Sex</label>
                                    <select id="edit-sex" name="sex" required>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="edit-nationality">Nationality</label>
                                    <input type="text" id="edit-nationality" name="nationality" required />
                                </div>
                                <div class="form-group">
                                    <label for="edit-mobile-number">Mobile Number</label>
                                    <input type="tel" id="edit-mobile-number" name="mobile_number" />
                                </div>
                            </div>
                            <input type="hidden" name="edit_student" value="1">
                            <div class="btn-group" style="justify-content: flex-end; margin-top: 1.5rem;">
                                <button type="button" class="btn btn-secondary modal-close">Cancel</button>
                                <button type="submit" class="btn" id="save-changes-btn">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Instructor Management View -->
            <div id="instructors-view" class="view" style="display: none">
                <div class="search-filter">
                    <div class="search-box">
                        <input type="text" placeholder="Search instructors..." />
                    </div>
                    <button class="btn btn-secondary">
                        <i data-lucide="filter"></i> Filter
                    </button>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Instructor ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Courses</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = fetchData($conn, "Instructors");

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>
                                        <td>{$row['instructor_id']}</td>
                                        <td>{$row['name']}</td>
                                        <td>{$row['email']}</td>
                                        <td>{$row['department']}</td>
                                        <td>{$row['courses_count']}</td>
                                        <td><span class='status " . strtolower(str_replace(' ', '-', $row['status'])) . "'>{$row['status']}</span></td>
                                        <td>
                                            <button class='action-btn'>
                                                <i data-lucide='eye'></i>
                                            </button>
                                            <button class='action-btn'>
                                                <i data-lucide='edit'></i>
                                            </button>
                                        </td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7'>No instructors found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Enrollment Form View -->
            <div id="enroll-view" class="view" style="display: none">
                <div class="enrollment-container">
                    <div class="form-container">
                        <form id="student-info-form" method="POST">
                            <h2 style="margin-bottom: 1.5rem">Student Information</h2>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="student-id">Student ID</label>
                                    <input
                                        type="text"
                                        id="student-id"
                                        name="student_id"
                                        placeholder="e.g., S2025-001"
                                        value="<?php echo $dynamicStudentID; ?>"
                                        required />
                                    <p id="student-id-validation" class="validation-message"></p>
                                </div>
                                <div class="form-group">
                                    <label for="first-name">First Name</label>
                                    <input
                                        type="text"
                                        id="first-name"
                                        name="first_name"
                                        placeholder="Enter first name"
                                        required />
                                </div>
                                <div class="form-group">
                                    <label for="last-name">Last Name</label>
                                    <input
                                        type="text"
                                        id="last-name"
                                        name="last_name"
                                        placeholder="Enter last name"
                                        required />
                                </div>
                                <div class="form-group">
                                    <label for="middle-name">Middle Name</label>
                                    <input
                                        type="text"
                                        id="middle-name"
                                        name="middle_name"
                                        placeholder="Enter middle name" />
                                </div>
                                <div class="form-group">
                                    <label for="suffix">Suffix</label>
                                    <input
                                        type="text"
                                        id="suffix"
                                        name="suffix"
                                        placeholder="e.g., Jr., Sr." />
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        placeholder="Enter email address"
                                        required />
                                </div>
                                <div class="form-group">
                                    <label for="date-of-birth">Date of Birth</label>
                                    <input
                                        type="date"
                                        id="date-of-birth"
                                        name="date_of_birth"
                                        required />
                                </div>
                                <div class="form-group">
                                    <label for="sex">Sex</label>
                                    <select id="sex" name="sex" required>
                                        <option value="">Select sex</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="nationality">Nationality</label>
                                    <input
                                        type="text"
                                        id="nationality"
                                        name="nationality"
                                        placeholder="Enter nationality"
                                        required />
                                </div>
                                <div class="form-group">
                                    <label for="mobile-number">Mobile Number</label>
                                    <input
                                        type="tel"
                                        id="mobile-number"
                                        name="mobile_number"
                                        placeholder="Enter mobile number"
                                        required />
                                </div>
                                <div class="form-group">
                                    <label for="department">Department</label>
                                    <select id="department" name="department" required>
                                        <option value="">Select a department</option>
                                        <?php
                                        $result = fetchData($conn, "Departments");
                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                echo "<option value='{$row['department_name']}'>{$row['department_name']}</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="program">Program</label>
                                    <select id="program" name="program" required disabled>
                                        <option value="">Select a department first</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="year-level">Year Level</label>
                                    <select id="year-level" name="year_level" required>
                                        <option value="">Select year level</option>
                                        <option value="1st Year">1st Year</option>
                                        <option value="2nd Year">2nd Year</option>
                                        <option value="3rd Year">3rd Year</option>
                                        <option value="4th Year">4th Year</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="enrollment-date">Enrollment Date</label>
                                    <input
                                        type="date"
                                        id="enrollment-date"
                                        name="enrollment_date"
                                        required
                                        value="<?php echo date('Y-m-d'); ?>" />
                                </div>
                            </div>
                            <input type="hidden" name="enroll_student" value="1">
                        </form>
                    </div>

                    <div class="form-container">
                        <h2 style="margin-bottom: 1.5rem">Course Enrollment</h2>
                        <div id="course-selection-container">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem">
                                <div id="units-counter" style="color: var(--text-light)">
                                    Selected: 0 units (Max: 28 units)
                                </div>
                            </div>

                            <div class="table-container" style="margin-bottom: 1.5rem; max-height: 500px; overflow-y: auto">
                                <table id="courses-table">
                                    <thead>
                                        <tr>
                                            <th>Select</th>
                                            <th>Course Code</th>
                                            <th>Course Name</th>
                                            <th>Units</th>
                                            <th>Prerequisites</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="6" style="text-align: center; padding: 2rem">
                                                Please select a department, program, and year level to view available courses
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="btn-group" style="justify-content: flex-end">
                            <button type="button" class="btn btn-secondary">Cancel</button>
                            <button type="submit" form="student-info-form" id="enroll-submit-btn" class="btn" disabled>
                                Enroll Student
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Programs & Courses View -->
            <div id="programs-view" class="view" style="display: none">
                <div class="search-filter">
                    <div class="search-box">
                        <input type="text" placeholder="Search departments, programs or courses..." />
                    </div>
                </div>

                <div class="department-container">
                    <h2 style="margin-bottom: 1rem">Departments & Programs</h2>
                    <?php
                    $departments = fetchDepartmentsAndPrograms($conn);
                    if (empty($departments)) {
                        echo '<div class="card">No departments found</div>';
                    } else {
                        foreach ($departments as $deptName => $programs) {
                            echo '<div class="department-card">';
                            echo '<h3 class="department-title">' . htmlspecialchars($deptName) . '</h3>';
                            foreach ($programs as $progName => $courses) {
                                if ($progName) {
                                    echo '<div class="program-card">';
                                    echo '<div class="program-toggle" data-program="' . htmlspecialchars($progName) . '">';
                                    echo '<span>' . htmlspecialchars($progName) . '</span>';
                                    echo '<i data-lucide="chevron-down"></i>';
                                    echo '</div>';
                                    echo '<div class="program-content">';
                                    if (empty($courses)) {
                                        echo '<div class="card">No courses found for this program</div>';
                                    } else {
                                        echo '<div class="table-container">';
                                        echo '<table>';
                                        echo '<thead>';
                                        echo '<tr>';
                                        echo '<th>Course Code</th>';
                                        echo '<th>Course Name</th>';
                                        echo '<th>Units</th>';
                                        echo '<th>Prerequisites</th>';
                                        echo '<th>Program</th>';
                                        echo '<th>Year Level</th>';
                                        echo '<th>Actions</th>';
                                        echo '</tr>';
                                        echo '</thead>';
                                        echo '<tbody>';
                                        foreach ($courses as $course) {
                                            echo '<tr>';
                                            echo '<td>' . htmlspecialchars($course['course_code']) . '</td>';
                                            echo '<td>' . htmlspecialchars($course['course_name']) . '</td>';
                                            echo '<td>' . htmlspecialchars($course['units']) . '</td>';
                                            echo '<td>' . htmlspecialchars(implode(', ', $course['prerequisites']) ?: 'None') . '</td>';
                                            echo '<td>' . htmlspecialchars($progName) . '</td>';
                                            echo '<td>' . htmlspecialchars($course['year_level']) . '</td>';
                                            echo '<td>';
                                            echo '<button class="action-btn"><i data-lucide="edit"></i></button>';
                                            echo '<button class="action-btn"><i data-lucide="trash-2"></i></button>';
                                            echo '</td>';
                                            echo '</tr>';
                                        }
                                        echo '</tbody>';
                                        echo '</table>';
                                        echo '</div>';
                                    }
                                    echo '</div>';
                                    echo '</div>';
                                }
                            }
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            </div>

            <!-- Pending Enrollees View -->
            <div id="pending-enrollees-view" class="view" style="display: none">
                <div class="header">
                    <h1 class="page-title">Pending Enrollment Applications</h1>
                    <div class="user-menu">
                        <button class="theme-toggle">
                            <i data-lucide="moon"></i>
                        </button>
                        <div class="user-avatar">AD</div>
                    </div>
                </div>

                <div class="search-filter">
                    <div class="search-box">
                        <input type="text" placeholder="Search pending applications..." />
                    </div>
                    <button class="btn btn-secondary">
                        <i data-lucide="filter"></i> Filter
                    </button>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Application ID</th>
                                <th>Student ID</th>
                                <th>Student Name</th>
                                <th>Program</th>
                                <th>Year Level</th>
                                <th>Date Submitted</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = fetchData($conn, "Pending_Applications");

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>
                            <td>{$row['application_id']}</td>
                            <td>{$row['student_id']}</td>
                            <td>{$row['student_name']}</td>
                            <td>{$row['program_name']}</td>
                            <td>{$row['year_level']}</td>
                            <td>{$row['date_submitted']}</td>
                            <td><span class='status " . strtolower(str_replace(' ', '-', $row['status'])) . "'>{$row['status']}</span></td>
                            <td>
                                <button class='action-btn approve-btn' data-application-id='{$row['application_id']}' data-student-id='{$row['student_id']}' title='Approve'>
                                    <i data-lucide='check'></i>
                                </button>
                                <button class='action-btn reject-btn' data-application-id='{$row['application_id']}' title='Reject'>
                                    <i data-lucide='x'></i>
                                </button>
                                <button class='action-btn view-btn' data-application-id='{$row['application_id']}' title='View Details'>
                                    <i data-lucide='eye'></i>
                                </button>
                            </td>
                        </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8'>No pending applications found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Approve Application Modal -->
            <div id="approve-application-modal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title">Approve Enrollment Application</h2>
                        <button class="modal-close">&times;</button>
                    </div>
                    <form id="approve-application-form">
                        <div class="form-group">
                            <label>Application ID</label>
                            <p id="approve-application-id" class="form-control-static"></p>
                        </div>
                        <div class="form-group">
                            <label>Student ID</label>
                            <p id="approve-student-id" class="form-control-static"></p>
                        </div>
                        <div class="form-group">
                            <label>Student Name</label>
                            <p id="approve-student-name" class="form-control-static"></p>
                        </div>
                        <div class="form-group">
                            <label>Program</label>
                            <p id="approve-program" class="form-control-static"></p>
                        </div>
                        <div class="form-group">
                            <label>Year Level</label>
                            <p id="approve-year-level" class="form-control-static"></p>
                        </div>
                        <div class="form-group">
                            <label>Applied Courses</label>
                            <div class="table-container">
                                <table id="approve-courses-table">
                                    <thead>
                                        <tr>
                                            <th>Course Code</th>
                                            <th>Course Name</th>
                                            <th>Units</th>
                                            <th>Instructor</th>
                                            <th>Schedule</th>
                                        </tr>
                                    </thead>
                                    <tbody id="approve-courses-table-body"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="btn-group">
                            <button type="submit" class="btn btn-block">Confirm Approval</button>
                            <button type="button" class="btn btn-secondary btn-block modal-close">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Logout View -->
            <div id="logout-view" class="view" style="display: none">
                <div class="header">
                    <h1 class="page-title">Logout</h1>
                    <div class="user-menu">
                        <button class="theme-toggle">
                            <i data-lucide="moon"></i>
                        </button>
                        <div class="user-avatar">AD</div>
                    </div>
                </div>

                <div class="form-container" style="text-align: center">
                    <div style="font-size: 5rem; margin-bottom: 1rem; color: var(--primary)">
                        <i data-lucide="log-out"></i>
                    </div>
                    <h2 style="margin-bottom: 1rem">
                        Are you sure you want to logout?
                    </h2>
                    <p style="margin-bottom: 2rem; color: var(--text-light)">
                        You will be redirected to the login page.
                    </p>
                    <div class="btn-group" style="justify-content: center">
                        <button class="btn btn-secondary">Cancel</button>
                        <button class="btn">Logout</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Theme toggle functionality
        const themeToggle = document.querySelector(".theme-toggle");
        const html = document.documentElement;

        themeToggle.addEventListener("click", () => {
            if (html.getAttribute("data-theme") === "dark") {
                html.removeAttribute("data-theme");
                themeToggle.innerHTML = '<i data-lucide="moon"></i>';
            } else {
                html.setAttribute("data-theme", "dark");
                themeToggle.innerHTML = '<i data-lucide="sun"></i>';
            }
            lucide.createIcons();
        });

        // Hide success/error messages after 3 seconds
        document.addEventListener("DOMContentLoaded", function() {
            const alerts = document.querySelectorAll(".alert");
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.classList.add("hidden");
                }, 3000);
            });

            // Student ID validation
            const studentIdInput = document.getElementById("student-id");
            const validationMessage = document.getElementById("student-id-validation");
            const submitBtn = document.getElementById("enroll-submit-btn");
            const formGroup = studentIdInput?.parentElement;

            function validateStudentIdFormat(id) {
                const regex = /^S[0-9]{4}-[0-9]{3}$/;
                return regex.test(id);
            }

            async function checkStudentIdExists(id) {
                try {
                    const response = await fetch(`check_student_id.php?student_id=${encodeURIComponent(id)}`);
                    const data = await response.json();
                    return data.exists;
                } catch (error) {
                    console.error('Error checking student ID:', error);
                    return false;
                }
            }

            async function validateStudentId() {
                if (!studentIdInput || !formGroup || !validationMessage) return;

                const id = studentIdInput.value.trim();
                formGroup.classList.remove("invalid", "valid");
                validationMessage.classList.remove("error", "success");
                validationMessage.textContent = "";
                submitBtn.disabled = true;

                if (!id) {
                    formGroup.classList.add("invalid");
                    validationMessage.classList.add("error");
                    validationMessage.textContent = "Student ID is required.";
                    return;
                }

                if (!validateStudentIdFormat(id)) {
                    formGroup.classList.add("invalid");
                    validationMessage.classList.add("error");
                    validationMessage.textContent = "Invalid format. Use SYYYY-NNN (e.g., S2025-001).";
                    return;
                }

                const exists = await checkStudentIdExists(id);
                if (exists) {
                    formGroup.classList.add("invalid");
                    validationMessage.classList.add("error");
                    validationMessage.textContent = "This student ID already exists.";
                } else {
                    formGroup.classList.add("valid");
                    validationMessage.classList.add("success");
                    validationMessage.textContent = "Student ID is valid.";
                    submitBtn.disabled = totalUnits > maxUnits;
                }
            }

            studentIdInput?.addEventListener("input", validateStudentId);

            // Initial validation
            validateStudentId();

            // Department and Program selection
            const departmentSelect = document.getElementById("department");
            const programSelect = document.getElementById("program");
            const editDepartmentSelect = document.getElementById("edit-department");
            const editProgramSelect = document.getElementById("edit-program");

            async function updatePrograms(department, programSelectElement) {
                if (!department) {
                    programSelectElement.innerHTML = '<option value="">Select a department first</option>';
                    programSelectElement.disabled = true;
                    return;
                }

                try {
                    const response = await fetch(`get_programs.php?department=${encodeURIComponent(department)}`);
                    const programs = await response.json();

                    programSelectElement.innerHTML = '<option value="">Select a program</option>';
                    programs.forEach(program => {
                        const option = document.createElement("option");
                        option.value = program.program_name;
                        option.textContent = program.program_name;
                        programSelectElement.appendChild(option);
                    });
                    programSelectElement.disabled = false;
                } catch (error) {
                    console.error('Error fetching programs:', error);
                    programSelectElement.innerHTML = '<option value="">Error loading programs</option>';
                    programSelectElement.disabled = true;
                }
            }

            departmentSelect?.addEventListener("change", function() {
                updatePrograms(this.value, programSelect);
                updateCoursesTable("", "");
            });

            editDepartmentSelect?.addEventListener("change", function() {
                updatePrograms(this.value, editProgramSelect);
            });
        });

        // Modal functionality
        const viewStudentModal = document.getElementById("view-student-modal");
        const editStudentModal = document.getElementById("edit-student-modal");
        const approveApplicationModal = document.getElementById("approve-application-modal");
        const modalCloses = document.querySelectorAll(".modal-close");

        // View Student Modal
        document.querySelectorAll(".view-student-btn").forEach((btn) => {
            btn.addEventListener("click", async function() {
                const studentId = this.dataset.studentId;
                const row = this.closest("tr");

                // Populate student information
                document.getElementById("view-student-id").textContent = row.cells[0].textContent;
                document.getElementById("view-first-name").textContent = row.cells[2].textContent;
                document.getElementById("view-last-name").textContent = row.cells[1].textContent;
                document.getElementById("view-middle-name").textContent = row.cells[3].textContent;
                document.getElementById("view-suffix").textContent = row.cells[4].textContent;
                document.getElementById("view-age").textContent = row.cells[5].textContent;
                document.getElementById("view-date-of-birth").textContent = row.cells[6].textContent;
                document.getElementById("view-sex").textContent = row.cells[7].textContent;
                document.getElementById("view-nationality").textContent = row.cells[8].textContent;
                document.getElementById("view-mobile-number").textContent = row.cells[9].textContent;
                document.getElementById("view-email").textContent = row.cells[10].textContent;
                document.getElementById("view-department").textContent = row.cells[12].textContent;
                document.getElementById("view-program").textContent = row.cells[13].textContent;
                document.getElementById("view-year-level").textContent = row.cells[14]?.textContent || row.cells[13].textContent;
                document.getElementById("view-enrollment-date").textContent = row.cells[11].textContent;
                document.getElementById("view-status").textContent = "Active";

                const coursesTableBody = document.getElementById("view-courses-table-body");
                coursesTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 2rem;">Loading courses...</td></tr>';

                try {
                    // Fetch enrolled courses for the student via AJAX
                    const response = await fetch(`get_student_courses.php?student_id=${encodeURIComponent(studentId)}`);
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    const courses = await response.json();

                    coursesTableBody.innerHTML = "";
                    if (courses.error || courses.length === 0) {
                        coursesTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 2rem;">No courses enrolled</td></tr>';
                    } else {
                        courses.forEach(course => {
                            const row = document.createElement("tr");
                            row.innerHTML = `
                        <td>${course.course_code}</td>
                        <td>${course.course_name}</td>
                        <td>${course.units}</td>
                        <td>${course.prerequisites || "None"}</td>
                        <td><span class="status ${course.status.toLowerCase()}">${course.status}</span></td>
                    `;
                            coursesTableBody.appendChild(row);
                        });
                    }
                } catch (error) {
                    coursesTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 2rem;">Error loading courses</td></tr>';
                    console.error('Error fetching courses:', error);
                }

                viewStudentModal.style.display = "flex";
            });
        });

        // Edit Student Modal
        document.querySelectorAll(".edit-student-btn").forEach((btn) => {
            btn.addEventListener("click", function() {
                const studentId = this.dataset.studentId;
                const row = this.closest("tr");

                // Populate form fields
                document.getElementById("edit-student-id").value = row.cells[0].textContent;
                document.getElementById("edit-first-name").value = row.cells[2].textContent;
                document.getElementById("edit-last-name").value = row.cells[1].textContent;
                document.getElementById("edit-middle-name").value = row.cells[3].textContent;
                document.getElementById("edit-suffix").value = row.cells[4].textContent;
                document.getElementById("edit-date-of-birth").value = row.cells[6].textContent;
                document.getElementById("edit-sex").value = row.cells[7].textContent;
                document.getElementById("edit-nationality").value = row.cells[8].textContent;
                document.getElementById("edit-mobile-number").value = row.cells[9].textContent;
                document.getElementById("edit-email").value = row.cells[10].textContent;

                editStudentModal.style.display = "flex";
            });
        });

        // Handle Edit Student Form Submission
        document.getElementById("edit-student-form").addEventListener("submit", async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const saveBtn = document.getElementById("save-changes-btn");
            saveBtn.disabled = true;

            try {
                const response = await fetch('index.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }

                const result = await response.json();

                const alert = document.createElement("div");
                alert.className = `alert alert-${result.success ? 'success' : 'error'}`;
                alert.textContent = result.message;
                document.querySelector(".main-content").prepend(alert);

                setTimeout(() => {
                    alert.classList.add("hidden");
                    setTimeout(() => alert.remove(), 500);
                }, 3000);

                if (result.success) {
                    editStudentModal.style.display = "none";
                    location.reload();
                }
            } catch (error) {
                console.error('Error:', error);
                const alert = document.createElement("div");
                alert.className = "alert alert-error";
                alert.textContent = "Error updating student: " + error.message;
                document.querySelector(".main-content").prepend(alert);

                setTimeout(() => {
                    alert.classList.add("hidden");
                    setTimeout(() => alert.remove(), 500);
                }, 3000);
            } finally {
                saveBtn.disabled = false;
            }
        });

        // Approve Application Modal
        const schedulesData = <?php echo $schedulesDataJSON; ?>;

        document.querySelectorAll(".approve-btn").forEach((btn) => {
            btn.addEventListener("click", async function() {
                const applicationId = this.dataset.applicationId;
                const studentId = this.dataset.studentId;
                const row = this.closest("tr");

                // Populate modal fields
                document.getElementById("approve-application-id").textContent = applicationId;
                document.getElementById("approve-student-id").textContent = studentId;
                document.getElementById("approve-student-name").textContent = row.cells[2].textContent;
                document.getElementById("approve-program").textContent = row.cells[3].textContent;
                document.getElementById("approve-year-level").textContent = row.cells[4].textContent;
                document.getElementById("approve-application-modal").dataset.applicationId = applicationId;

                const coursesTableBody = document.getElementById("approve-courses-table-body");
                coursesTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 2rem;">Loading courses...</td></tr>';

                try {
                    // Fetch enrolled courses for the student
                    const coursesResponse = await fetch(`get_student_courses.php?student_id=${encodeURIComponent(studentId)}`);
                    if (!coursesResponse.ok) {
                        throw new Error(`HTTP error! Status: ${coursesResponse.status}`);
                    }
                    const courses = await coursesResponse.json();

                    // Fetch all active instructors
                    const instructorsResponse = await fetch(`get_instructors.php`);
                    if (!instructorsResponse.ok) {
                        throw new Error(`HTTP error! Status: ${instructorsResponse.status}`);
                    }
                    const instructors = await instructorsResponse.json();

                    coursesTableBody.innerHTML = "";
                    if (courses.error || courses.length === 0) {
                        coursesTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 2rem;">No courses enrolled</td></tr>';
                    } else {
                        courses.forEach(course => {
                            const row = document.createElement("tr");
                            const instructorSelect = `<select name="instructor_${course.course_code}" required>
                                <option value="">Select Instructor</option>
                                ${instructors.map(instructor => `<option value="${instructor.instructor_id}">${instructor.name}</option>`).join('')}
                            </select>`;
                            const scheduleSelect = `<select name="schedule_${course.course_code}" required>
                                <option value="">Select Schedule</option>
                                ${schedulesData.map(schedule => `<option value="${schedule.schedule_id}">${schedule.schedule_description} (${schedule.days} ${schedule.time_start}-${schedule.time_end}, ${schedule.room})</option>`).join('')}
                            </select>`;
                            row.innerHTML = `
                                <td>${course.course_code}</td>
                                <td>${course.course_name}</td>
                                <td>${course.units}</td>
                                <td>${instructorSelect}</td>
                                <td>${scheduleSelect}</td>
                            `;
                            coursesTableBody.appendChild(row);
                        });
                    }
                } catch (error) {
                    coursesTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 2rem;">Error loading courses or instructors</td></tr>';
                    console.error('Error:', error);
                }

                approveApplicationModal.style.display = "flex";
            });
        });

        // Handle Approve Application Form Submission
        document.getElementById("approve-application-form").addEventListener("submit", async function(e) {
            e.preventDefault();

            const applicationId = this.dataset.applicationId;
            const studentId = document.getElementById("approve-student-id").textContent;
            const programName = document.getElementById("approve-program").textContent;
            const formData = new FormData(this);
            formData.append("application_id", applicationId);
            formData.append("student_id", studentId);
            formData.append("program_name", programName);
            formData.append("status", "Accepted");

            // Collect course assignments
            const courseAssignments = [];
            const courseRows = document.querySelectorAll("#approve-courses-table-body tr");
            courseRows.forEach(row => {
                const courseCode = row.cells[0].textContent;
                const instructorId = row.querySelector(`select[name="instructor_${courseCode}"]`).value;
                const scheduleId = row.querySelector(`select[name="schedule_${courseCode}"]`).value;
                courseAssignments.push({
                    course_code: courseCode,
                    instructor_id: instructorId,
                    schedule_id: scheduleId
                });
            });
            formData.append("course_assignments", JSON.stringify(courseAssignments));

            const submitBtn = this.querySelector("button[type='submit']");
            submitBtn.disabled = true;

            try {
                // Send request to update application status and create enrollments
                const response = await fetch("update_application_status.php", {
                    method: "POST",
                    body: formData
                });
                const result = await response.json();

                const alert = document.createElement("div");
                alert.className = `alert alert-${result.success ? "success" : "error"}`;
                alert.textContent = result.message;
                document.querySelector(".main-content").prepend(alert);

                setTimeout(() => {
                    alert.classList.add("hidden");
                    setTimeout(() => alert.remove(), 500);
                }, 3000);

                if (result.success) {
                    approveApplicationModal.style.display = "none";
                    location.reload();
                }
            } catch (error) {
                const alert = document.createElement("div");
                alert.className = "alert alert-error";
                alert.textContent = "Error approving application: " + error.message;
                document.querySelector(".main-content").prepend(alert);

                setTimeout(() => {
                    alert.classList.add("hidden");
                    setTimeout(() => alert.remove(), 500);
                }, 3000);
            } finally {
                submitBtn.disabled = false;
            }
        });

        // Close modals
        modalCloses.forEach((closeBtn) => {
            closeBtn.addEventListener("click", () => {
                viewStudentModal.style.display = "none";
                editStudentModal.style.display = "none";
                approveApplicationModal.style.display = "none";
            });
        });

        viewStudentModal?.addEventListener("click", (e) => {
            if (e.target === viewStudentModal) {
                viewStudentModal.style.display = "none";
            }
        });

        editStudentModal?.addEventListener("click", (e) => {
            if (e.target === editStudentModal) {
                editStudentModal.style.display = "none";
            }
        });

        approveApplicationModal?.addEventListener("click", (e) => {
            if (e.target === approveApplicationModal) {
                approveApplicationModal.style.display = "none";
            }
        });

        // Delete Student Functionality
        document.querySelectorAll(".action-btn [data-lucide='trash-2']").forEach((trashIcon) => {
            const deleteBtn = trashIcon.closest('.action-btn');
            deleteBtn.addEventListener("click", async function() {
                const studentId = this.closest('tr').querySelector('.view-student-btn').dataset.studentId;
                const studentName = this.closest('tr').cells[1].textContent + ' ' + this.closest('tr').cells[2].textContent;

                if (!confirm(`Are you sure you want to delete ${studentName} (${studentId})? This action cannot be undone.`)) {
                    return;
                }

                deleteBtn.disabled = true;

                try {
                    const formData = new FormData();
                    formData.append('student_id', studentId);

                    const response = await fetch('delete_student.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    const alert = document.createElement("div");
                    alert.className = `alert alert-${result.success ? 'success' : 'error'}`;
                    alert.textContent = result.message;
                    document.querySelector(".main-content").prepend(alert);

                    setTimeout(() => {
                        alert.classList.add("hidden");
                        setTimeout(() => alert.remove(), 500);
                    }, 3000);

                    if (result.success) {
                        this.closest('tr').remove();
                        const totalStudentsCard = document.querySelector('.card-value');
                        if (totalStudentsCard) {
                            const currentCount = parseInt(totalStudentsCard.textContent);
                            totalStudentsCard.textContent = currentCount - 1;
                        }
                    }
                } catch (error) {
                    const alert = document.createElement("div");
                    alert.className = "alert alert-error";
                    alert.textContent = "Error deleting student: " + error.message;
                    document.querySelector(".main-content").prepend(alert);

                    setTimeout(() => {
                        alert.classList.add("hidden");
                        setTimeout(function() {
                            alert.remove();
                        }, 500);
                    }, 3000);
                } finally {
                    deleteBtn.disabled = false;
                }
            });
        });

        // Navigation functionality
        document.querySelectorAll(".nav-item").forEach((item) => {
            item.addEventListener("click", function(e) {
                e.preventDefault();
                document.querySelectorAll(".nav-item").forEach((navItem) => {
                    navItem.classList.remove("active");
                });
                this.classList.add("active");
                document.querySelectorAll(".view").forEach((view) => {
                    view.style.display = "none";
                });
                const viewId = this.getAttribute("data-view") + "-view";
                document.getElementById(viewId).style.display = "block";
                updatePageTitle(this.getAttribute("data-view"));
            });
        });

        function updatePageTitle(view) {
            const titles = {
                dashboard: "Dashboard",
                students: "Student Management",
                instructors: "Instructor Management",
                enroll: "Enroll Students",
                "pending-enrollees": "Pending Enrollees",
                programs: "Programs & Courses",
                logout: "Logout"
            };

            const pageTitle = document.querySelector(".page-title");
            if (pageTitle) {
                pageTitle.textContent = titles[view] || "EduTrack";
            }
        }

        // Course selection logic
        const coursesData = <?php echo $departmentsDataJSON; ?>;
        let selectedCourses = [];
        let totalUnits = 0;
        const maxUnits = 28;

        document.getElementById("program")?.addEventListener("change", function() {
            const program = this.value;
            const yearLevel = document.getElementById("year-level").value;
            updateCoursesTable(program, yearLevel);
        });

        document.getElementById("year-level")?.addEventListener("change", function() {
            const program = document.getElementById("program").value;
            const yearLevel = this.value;
            if (program) {
                updateCoursesTable(program, yearLevel);
            }
        });

        function updateCoursesTable(program, yearLevel) {
            const tbody = document.querySelector("#courses-table tbody");
            if (!tbody) return;

            tbody.innerHTML = "";
            selectedCourses = [];
            totalUnits = 0;
            updateSelectedCourses();

            if (!program) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 2rem;">Please select a program to view available courses</td></tr>';
                return;
            }

            if (!yearLevel) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 2rem;">Please select a year level</td></tr>';
                return;
            }

            let filteredCourses = [];
            for (const dept in coursesData) {
                if (coursesData[dept][program]) {
                    filteredCourses = coursesData[dept][program].filter(
                        (course) => course.year_level === yearLevel
                    );
                    break;
                }
            }

            if (filteredCourses.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 2rem;">No courses available for this program and year level</td></tr>';
                return;
            }

            filteredCourses.forEach((course) => {
                const row = document.createElement("tr");
                const prerequisitesMet = checkPrerequisites(course.prerequisites);
                const status = prerequisitesMet ? "Eligible" : "Missing prerequisites";

                row.innerHTML = `
            <td><input type="checkbox" class="course-checkbox" data-code="${course.course_code}" data-units="${course.units}" ${prerequisitesMet ? "" : "disabled"}></td>
            <td>${course.course_code}</td>
            <td>${course.course_name}</td>
            <td>${course.units}</td>
            <td>${course.prerequisites.join(", ") || "None"}</td>
            <td><span class="status ${prerequisitesMet ? "active" : "inactive"}">${status}</span></td>
        `;
                tbody.appendChild(row);
            });

            document.querySelectorAll(".course-checkbox").forEach((checkbox) => {
                checkbox.addEventListener("change", updateSelectedCourses);
            });
        }

        function checkPrerequisites(prerequisites) {
            if (!prerequisites || prerequisites.length === 0) return true;
            return selectedCourses.some(code => prerequisites.includes(code));
        }

        function updateSelectedCourses() {
            selectedCourses = [];
            totalUnits = 0;

            document.querySelectorAll(".course-checkbox:checked").forEach((checkbox) => {
                selectedCourses.push(checkbox.dataset.code);
                totalUnits += parseInt(checkbox.dataset.units);
            });

            const unitsCounter = document.getElementById("units-counter");
            if (unitsCounter) {
                unitsCounter.textContent = `Selected: ${totalUnits} units (Max: ${maxUnits} units)`;

                if (totalUnits > maxUnits) {
                    unitsCounter.classList.add("error");
                    unitsCounter.classList.remove("warning");
                } else if (totalUnits > maxUnits - 5) {
                    unitsCounter.classList.add("warning");
                    unitsCounter.classList.remove("error");
                } else {
                    unitsCounter.classList.remove("warning", "error");
                }
            }

            const submitBtn = document.getElementById("enroll-submit-btn");
            if (submitBtn) {
                submitBtn.disabled = totalUnits > maxUnits || document.getElementById("student-id").parentElement.classList.contains("invalid");
            }
        }

        // Program toggle functionality
        document.querySelectorAll(".program-toggle").forEach((toggle) => {
            toggle.addEventListener("click", function() {
                const programContent = this.nextElementSibling;
                const isActive = programContent.classList.contains("active");

                document.querySelectorAll(".program-content").forEach((content) => {
                    content.classList.remove("active");
                    const toggleElement = content.previousElementSibling;
                    if (toggleElement) {
                        const icon = toggleElement.querySelector("i");
                        if (icon) {
                            icon.setAttribute("data-lucide", "chevron-down");
                        }
                    }
                });

                if (!isActive) {
                    programContent.classList.add("active");
                    const icon = this.querySelector("i");
                    if (icon) {
                        icon.setAttribute("data-lucide", "chevron-up");
                    }
                }

                lucide.createIcons();
            });
        });

        // Handle form submission for student enrollment
        document.getElementById("student-info-form")?.addEventListener("submit", async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append("courses", JSON.stringify(selectedCourses));

            const submitBtn = document.getElementById("enroll-submit-btn");
            submitBtn.disabled = true;

            try {
                const response = await fetch('index.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                const alert = document.createElement("div");
                alert.className = `alert alert-${result.success ? 'success' : 'error'}`;
                alert.textContent = result.message;
                document.querySelector(".main-content").prepend(alert);

                setTimeout(() => {
                    alert.classList.add("hidden");
                    setTimeout(() => alert.remove(), 500);
                }, 3000);

                if (result.success) {
                    this.reset();
                    selectedCourses = [];
                    totalUnits = 0;
                    updateCoursesTable("", "");
                    updateSelectedCourses();
                    location.reload();
                }
            } catch (error) {
                const alert = document.createElement("div");
                alert.className = "alert alert-error";
                alert.textContent = "Error enrolling student: " + error.message;
                document.querySelector(".main-content").prepend(alert);

                setTimeout(() => {
                    alert.classList.add("hidden");
                    setTimeout(() => alert.remove(), 500);
                }, 3000);
            } finally {
                submitBtn.disabled = false;
            }
        });

        // Initialize dashboard view
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("dashboard-view").style.display = "block";
            document.querySelector('.nav-item[data-view="dashboard"]').classList.add("active");
            updatePageTitle("dashboard");
        });

        document.addEventListener("DOMContentLoaded", function() {
            // Handle Reject buttons
            document.querySelectorAll(".reject-btn").forEach((btn) => {
                btn.addEventListener("click", async function() {
                    const applicationId = this.dataset.applicationId;
                    const status = "Rejected";
                    const action = status.toLowerCase();

                    if (!confirm(`Are you sure you want to ${action} application ${applicationId}?`)) {
                        return;
                    }

                    btn.disabled = true;

                    try {
                        const formData = new FormData();
                        formData.append("application_id", applicationId);
                        formData.append("status", status);

                        const response = await fetch("update_application_status.php", {
                            method: "POST",
                            body: formData
                        });
                        const result = await response.json();

                        const alert = document.createElement("div");
                        alert.className = `alert alert-${result.success ? "success" : "error"}`;
                        alert.textContent = result.message;
                        document.querySelector(".main-content").prepend(alert);

                        setTimeout(() => {
                            alert.classList.add("hidden");
                            setTimeout(() => alert.remove(), 500);
                        }, 3000);

                        if (result.success) {
                            const row = this.closest("tr");
                            const statusCell = row.querySelector(".status");
                            statusCell.textContent = status;
                            statusCell.className = `status ${status.toLowerCase()}`;
                        }
                    } catch (error) {
                        const alert = document.createElement("div");
                        alert.className = "alert alert-error";
                        alert.textContent = "Error updating application status: " + error.message;
                        document.querySelector(".main-content").prepend(alert);

                        setTimeout(() => {
                            alert.classList.add("hidden");
                            setTimeout(() => alert.remove(), 500);
                        }, 3000);
                    } finally {
                        btn.disabled = false;
                    }
                });
            });

            // Handle View Details button
            document.querySelectorAll(".view-btn").forEach((btn) => {
                btn.addEventListener("click", async function() {
                    const applicationId = this.dataset.applicationId;
                    const row = this.closest("tr");
                    const studentId = row.cells[1].textContent;

                    // Reuse the existing view-student-modal
                    const viewStudentModal = document.getElementById("view-student-modal");

                    try {
                        // Fetch student details
                        const response = await fetch(`get_student_details.php?student_id=${encodeURIComponent(studentId)}`);
                        const student = await response.json();

                        if (student.error) {
                            const alert = document.createElement("div");
                            alert.className = "alert alert-error";
                            alert.textContent = student.error;
                            document.querySelector(".main-content").prepend(alert);
                            setTimeout(() => {
                                alert.classList.add("hidden");
                                setTimeout(() => alert.remove(), 500);
                            }, 3000);
                            return;
                        }

                        // Populate modal fields
                        document.getElementById("view-student-id").textContent = student.student_id;
                        document.getElementById("view-first-name").textContent = student.first_name;
                        document.getElementById("view-last-name").textContent = student.last_name;
                        document.getElementById("view-middle-name").textContent = student.middle_name || '';
                        document.getElementById("view-suffix").textContent = student.suffix || '';
                        document.getElementById("view-age").textContent = student.age;
                        document.getElementById("view-date-of-birth").textContent = student.date_of_birth;
                        document.getElementById("view-sex").textContent = student.sex;
                        document.getElementById("view-nationality").textContent = student.nationality;
                        document.getElementById("view-mobile-number").textContent = student.mobile_number;
                        document.getElementById("view-email").textContent = student.email;
                        document.getElementById("view-department").textContent = student.department_name || '';
                        document.getElementById("view-program").textContent = student.program_name;
                        document.getElementById("view-year-level").textContent = student.year_level;
                        document.getElementById("view-enrollment-date").textContent = student.date_created;
                        document.getElementById("view-status").textContent = row.cells[6].textContent;

                        // Fetch enrolled courses
                        const coursesResponse = await fetch(`get_student_courses.php?student_id=${encodeURIComponent(studentId)}`);
                        const courses = await coursesResponse.json();
                        const coursesTableBody = document.getElementById("view-courses-table-body");
                        coursesTableBody.innerHTML = "";

                        if (courses.length === 0) {
                            coursesTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 2rem;">No courses enrolled</td></tr>';
                        } else {
                            courses.forEach(course => {
                                const row = document.createElement("tr");
                                row.innerHTML = `
                            <td>${course.course_code}</td>
                            <td>${course.course_name}</td>
                            <td>${course.units}</td>
                            <td>${course.prerequisites || "None"}</td>
                            <td><span class="status active">${course.status}</span></td>
                        `;
                                coursesTableBody.appendChild(row);
                            });
                        }

                        viewStudentModal.style.display = "flex";
                    } catch (error) {
                        const alert = document.createElement("div");
                        alert.className = "alert alert-error";
                        alert.textContent = "Error fetching student details: " + error.message;
                        document.querySelector(".main-content").prepend(alert);

                        setTimeout(() => {
                            alert.classList.add("hidden");
                            setTimeout(() => alert.remove(), 500);
                        }, 3000);
                    }
                });
            });
        });
    </script>
</body>

</html>
<?php
$conn->close();
?>