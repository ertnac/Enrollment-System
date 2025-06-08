<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "edutrack";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to fetch data from database
function fetchData($conn, $table, $columns = "*", $where = "") {
    $sql = "SELECT $columns FROM $table";
    if ($where) {
        $sql .= " WHERE $where";
    }
    $result = $conn->query($sql);
    return $result;
}

// Function to validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to sanitize input
function sanitizeInput($conn, $input) {
    return mysqli_real_escape_string($conn, trim($input));
}
?>