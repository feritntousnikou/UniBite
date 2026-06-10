<?php
session_start();
header("Content-Type: application/json");
include("db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Μη εξουσιοδοτημένη πρόσβαση."]);
    $conn->close();
    exit();
}

$cook_id = $_SESSION['user_id'];

$sql = "SELECT id, title, description, portions_total,
               portions_available, pickup_location,
               pickup_time, allergens, created_at
        FROM meals
        WHERE cook_id = $cook_id
        ORDER BY created_at DESC";

$result = $conn->query($sql);
$meals  = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        array_push($meals, $row);
    }
}

$conn->close();

echo json_encode([
    "success" => true,
    "meals"   => $meals
]);
?>