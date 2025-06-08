<?php
header('Content-Type: application/json');

require_once 'db.php';

if (isset($_GET['student_id'])) {
    $studentId = $_GET['student_id'];
    
    try {
        $sql = "SELECT COUNT(*) as count FROM students WHERE student_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $studentId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        echo json_encode(['exists' => $result['count'] > 0]);
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['exists' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['exists' => false, 'error' => 'No student ID provided']);
}

$conn->close();
?>  