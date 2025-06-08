<?php
header('Content-Type: application/json');

require_once 'db.php';

$department = isset($_GET['department']) ? $_GET['department'] : '';

if ($department) {
    $sql = "SELECT p.program_name 
            FROM programs p 
            JOIN departments d ON p.department_id = d.department_id 
            WHERE d.department_name = ? 
            ORDER BY p.program_name";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $department);
    $stmt->execute();
    $result = $stmt->get_result();

    $programs = [];
    while ($row = $result->fetch_assoc()) {
        $programs[] = $row;
    }

    $stmt->close();
    echo json_encode($programs);
} else {
    echo json_encode([]);
}

$conn->close();
