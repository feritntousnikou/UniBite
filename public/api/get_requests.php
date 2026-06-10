<?php

session_start();
header("Content-Type: application/json");
include("db.php");

if (!isset($_SESSION[`user_id`])) {
    echo json_encode(["success" => false, "message" => "Μη εξουσιοδοτημένη πρόσβαση."]);
    $conn->close();
    exit();
}

$cook_id = $_SESSION[`user_id`];

$sql = "SELECT
            r.id,
            r.status,
            r.created_at,
            m.title AS meal_title,
            m.id    AS meal_id,
            u.firstName AS consumer_firstName,
            u.lastName  AS consumer_lastName,
            u.id        AS consumer_id
        FROM requests r
        JOIN meals m ON r.meal_id = m.id
        JOIN users u ON r.consumer_id = u.id
        WHERE m.cook_id = $cook_id
        ORDER BY r.created_at DESC";

$result   = $conn->query($sql);
$requests = [];
 
if ($result) {
    while ($row = $result->fetch_assoc()) {
        array_push($requests, $row);
    }
}
 
$conn->close();
 
echo json_encode([
    "success"  => true,
    "requests" => $requests
]);
?>