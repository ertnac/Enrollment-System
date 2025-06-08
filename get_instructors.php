<?php
require_once 'db.php';
header('Content-Type: application/json');
$sql = "SELECT instructor_id, name FROM instructors WHERE status = 'Active'";
$result = $conn->query($sql);
$instructors = [];
while ($row = $result->fetch_assoc()) {
    $instructors[] = $row;
}
echo json_encode($instructors);
$conn->close();
?>