<?php
// get_student_courses.php
header('Content-Type: application/json');

require_once 'db.php';

if (!isset($_GET['student_id'])) {
    echo json_encode(['error' => 'Student ID is required']);
    exit();
}

$studentId = $_GET['student_id'];

// Prepare SQL query to fetch enrolled courses
$sql = "SELECT sc.course_code, c.course_name, c.units, c.prerequisites, sc.status
        FROM student_courses sc
        LEFT JOIN courses c ON sc.course_code = c.course_code
        WHERE sc.student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $studentId);
$stmt->execute();
$result = $stmt->get_result();

$courses = [];
while ($row = $result->fetch_assoc()) {
    $courses[] = [
        'course_code' => $row['course_code'],
        'course_name' => $row['course_name'],
        'units' => $row['units'],
        'prerequisites' => $row['prerequisites'] ? $row['prerequisites'] : 'None',
        'status' => $row['status']
    ];
}

$stmt->close();
$conn->close();

echo json_encode($courses);
?>