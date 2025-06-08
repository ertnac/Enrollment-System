<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicationId = $_POST['application_id'] ?? '';
    $studentId = $_POST['student_id'] ?? '';
    $programName = $_POST['program_name'] ?? '';
    $status = $_POST['status'] ?? '';
    $courseAssignments = json_decode($_POST['course_assignments'] ?? '[]', true);

    if (empty($applicationId) || empty($studentId) || empty($programName) || empty($status)) {
        $response['message'] = "Missing required fields.";
        echo json_encode($response);
        exit();
    }

    $conn->begin_transaction();

    try {
        // Update pending_applications status 
        $sql = "UPDATE pending_applications SET status = ? WHERE application_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $status, $applicationId);
        if (!$stmt->execute()) {
            throw new Exception("Error updating application status: " . $conn->error);
        }
        $stmt->close();

        if ($status === 'Accepted' && !empty($courseAssignments)) {
            // Insert into enrollments table for each course
            $sql = "INSERT INTO enrollments (student_id, course_code, program_name, date_enrolled, status, instructor_id, schedule_id) 
                    VALUES (?, ?, ?, ?, 'Active', ?, ?)";
            $stmt = $conn->prepare($sql);
            $dateEnrolled = date('Y-m-d');

            foreach ($courseAssignments as $assignment) {
                $courseCode = $assignment['course_code'];
                $instructorId = $assignment['instructor_id'];
                $scheduleId = $assignment['schedule_id'];

                // Validate schedule_id
                $checkScheduleSql = "SELECT COUNT(*) as count FROM schedules WHERE schedule_id = ?";
                $checkStmt = $conn->prepare($checkScheduleSql);
                $checkStmt->bind_param('i', $scheduleId);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result()->fetch_assoc();
                $checkStmt->close();

                if ($checkResult['count'] == 0) {
                    throw new Exception("Invalid schedule ID: $scheduleId");
                }

                // Insert enrollment record
                $stmt->bind_param('sssssi', $studentId, $courseCode, $programName, $dateEnrolled, $instructorId, $scheduleId);
                if (!$stmt->execute()) {
                    throw new Exception("Error adding enrollment for course $courseCode: " . $conn->error);
                }

                // Get the generated enrollment_id
                $enrollmentId = $conn->insert_id;

                // Update student_courses with enrollment_id and set status to Active
                $updateSql = "UPDATE student_courses SET status = 'Active', instructor_id = ?, schedule_id = ?, enrollment_id = ? 
                                WHERE student_id = ? AND course_code = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param('siiss', $instructorId, $scheduleId, $enrollmentId, $studentId, $courseCode);
                if (!$updateStmt->execute()) {
                    throw new Exception("Error updating student_courses for course $courseCode: " . $conn->error);
                }
                $updateStmt->close();
            }
            $stmt->close();

            // Update students table with the first enrollment_id (if needed)
            $sql = "UPDATE students SET enrollment_id = (SELECT MIN(enrollment_id) FROM enrollments WHERE student_id = ?) WHERE student_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ss', $studentId, $studentId);
            if (!$stmt->execute()) {
                throw new Exception("Error updating student enrollment_id: " . $conn->error);
            }
            $stmt->close();
        }

        $conn->commit();
        $response['success'] = true;
        $response['message'] = "Application $status successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = "Error: " . $e->getMessage();
    }
}

echo json_encode($response);
$conn->close();
