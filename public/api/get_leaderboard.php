<?php
session_start();
header("Content-Type: application/json");
include("db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Μη εξουσιοδοτημένη πρόσβαση."]);
    $conn->close();
    exit();
}

if ($_SESSION['role'] != 'admin') {
    echo json_encode(["success" => false, "message" => "Δεν έχεις δικαίωμα πρόσβασης."]);
    $conn->close();
    exit();
}

$sql_donor = "SELECT u.firstName, u.lastName, COUNT(r.id) AS total_collected
              FROM requests r
              JOIN users u ON r.consumer_id = u.id
              WHERE r.status = 'collected'
              GROUP BY r.consumer_id
              ORDER BY total_collected DESC
              LIMIT 1";

$res_donor = $conn->query($sql_donor);
$top_donor = null;

if ($res_donor && $res_donor->num_rows > 0) {
    $top_donor = $res_donor->fetch_assoc();
}

$sql_meals = "SELECT m.title, AVG(rt.rating) AS avg_rating
              FROM ratings rt
              JOIN requests r  ON rt.request_id = r.id
              JOIN meals m     ON r.meal_id = m.id
              GROUP BY m.id
              ORDER BY avg_rating DESC
              LIMIT 5";

$res_meals = $conn->query($sql_meals);
$top_meals = [];

if ($res_meals) {
    while ($row = $res_meals->fetch_assoc()) {
        array_push($top_meals, $row);
    }
}

$conn->close();

echo json_encode([
    "success"   => true,
    "top_donor" => $top_donor,
    "top_meals" => $top_meals
]);
?>