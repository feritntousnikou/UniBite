<?php
$conn = new mysqli('localhost', 'root', '', 'unibite');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => $conn->connect_error]);
    exit();
}

$conn->set_charset("utf8");
?>