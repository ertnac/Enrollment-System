<?php
// Start session to store success message
session_start();

// Include database connection
require_once 'db.php';

// Set header for JSON response
header('Content-Type: application/json');

// Initialize response array
$response = ['success' => false, 'message' => ''];

// Check if request is POST and has required data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_student'])) {
    // Sanitize inputs
    $studentId = sanitizeInput($conn, $_POST['student_id']);
    $firstName = sanitizeInput($conn, $_POST['first_name']);
    $lastName = sanitizeInput($conn, $_POST['last_name']);
    $middleName = sanitizeInput($conn, $_POST['middle_name'] ?? '');
    $suffix = sanitizeInput($conn, $_POST['suffix'] ?? '');
    $dateOfBirth = sanitizeInput($conn, $_POST['date_of_birth']);
    $sex = sanitizeInput($conn, $_POST['sex']);
    $nationality = sanitizeInput($conn, $_POST['nationality']);
    $mobileNumber = sanitizeInput($conn, $_POST['mobile_number']);
    $email = sanitizeInput($conn, $_POST['email']);

    // Validate email
    if (!validateEmail($email)) {
        $response['message'] = 'Invalid email format.';
        echo json_encode($response);
        exit;
    }

    // Calculate age from date of birth
    $dob = new DateTime($dateOfBirth);
    $today = new DateTime();
    $age = $today->diff($dob)->y;

    // Check if student ID exists
    $checkSql = "SELECT COUNT(*) as count FROM students WHERE student_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param('s', $studentId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();

    if ($checkResult['count'] == 0) {
        $response['message'] = "Student ID $studentId does not exist.";
        echo json_encode($response);
        exit;
    }

    // Check if email is already used by another student
    $emailCheckSql = "SELECT COUNT(*) as count FROM students WHERE email = ? AND student_id != ?";
    $emailCheckStmt = $conn->prepare($emailCheckSql);
    $emailCheckStmt->bind_param('ss', $email, $studentId);
    $emailCheckStmt->execute();
    $emailCheckResult = $emailCheckStmt->get_result()->fetch_assoc();
    $emailCheckStmt->close();

    if ($emailCheckResult['count'] > 0) {
        $response['message'] = "Email $email is already in use by another student.";
        echo json_encode($response);
        exit;
    }

    // Prepare update query
    try {
        $sql = "UPDATE students SET last_name = ?, first_name = ?, middle_name = ?, suffix = ?, age = ?, date_of_birth = ?, sex = ?, nationality = ?, mobile_number = ?, email = ? WHERE student_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'ssssissssss',
            $lastName,
            $firstName,
            $middleName,
            $suffix,
            $age,
            $dateOfBirth,
            $sex,
            $nationality,
            $mobileNumber,
            $email,
            $studentId
        );

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Student updated successfully!";
            $_SESSION['success_message'] = $response['message'];
        } else {
            $response['message'] = "Error updating student: " . $conn->error;
        }
        $stmt->close();
    } catch (Exception $e) {
        $response['message'] = "Error: " . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method.';
}

// Close database connection
$conn->close();

// Output JSON response
echo json_encode($response);
