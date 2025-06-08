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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'])) {
    // Sanitize input
    $studentId = sanitizeInput($conn, $_POST['student_id']);

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

    // Check if student has active enrollments
    $enrollmentCheckSql = "SELECT COUNT(*) as count FROM enrollments WHERE student_id = ? AND status IN ('Active', 'Pending')";
    $enrollmentCheckStmt = $conn->prepare($enrollmentCheckSql);
    $enrollmentCheckStmt->bind_param('s', $studentId);
    $enrollmentCheckStmt->execute();
    $enrollmentCheckResult = $enrollmentCheckStmt->get_result()->fetch_assoc();
    $enrollmentCheckStmt->close();

    if ($enrollmentCheckResult['count'] > 0) {
        $response['message'] = "Cannot delete student with active or pending enrollments.";
        echo json_encode($response);
        exit;
    }

    // Prepare delete query
    try {
        // Begin transaction to ensure data integrity
        $conn->begin_transaction();

        // Delete from enrollments (if any completed or dropped)
        $deleteEnrollmentsSql = "DELETE FROM enrollments WHERE student_id = ?";
        $deleteEnrollmentsStmt = $conn->prepare($deleteEnrollmentsSql);
        $deleteEnrollmentsStmt->bind_param('s', $studentId);
        $deleteEnrollmentsStmt->execute();
        $deleteEnrollmentsStmt->close();

        // Delete from students
        $deleteStudentSql = "DELETE FROM students WHERE student_id = ?";
        $deleteStudentStmt = $conn->prepare($deleteStudentSql);
        $deleteStudentStmt->bind_param('s', $studentId);

        if ($deleteStudentStmt->execute()) {
            $conn->commit();
            $response['success'] = true;
            $response['message'] = "Student deleted successfully!";
            $_SESSION['success_message'] = $response['message'];
        } else {
            $conn->rollback();
            $response['message'] = "Error deleting student: " . $conn->error;
        }
        $deleteStudentStmt->close();
    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = "Error: " . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method or missing student ID.';
}

// Close database connection
$conn->close();

// Output JSON response
echo json_encode($response);
