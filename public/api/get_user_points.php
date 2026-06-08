<?php
session_start();
header("Content-Type: application/json");
include("db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Μη εξουσιοδοτημένη πρόσβαση."]);
    $conn->close();
    exit();
}

$user_id = $_SESSION['user_id'];

$sql    = "SELECT firstName, lastName, points FROM users WHERE id = $user_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "Ο χρήστης δεν βρέθηκε."]);
    $conn->close();
    exit();
}

$user = $result->fetch_assoc();

$conn->close();

echo json_encode([
    "success"   => true,
    "firstName" => $user['firstName'],
    "lastName"  => $user['lastName'],
    "points"    => $user['points']
]);
?>
