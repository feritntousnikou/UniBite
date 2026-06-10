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

$sql_portions = "SELECT COUNT(*) AS portions_this_month
                 FROM requests
                 WHERE status = 'collected'
                 AND MONTH(created_at)  = MONTH(NOW())
                 AND YEAR(created_at)   = YEAR(NOW())";

$res_portions      = $conn->query($sql_portions);
$row_portions      = $res_portions->fetch_assoc();
$portions_month    = $row_portions['portions_this_month'];

$sql_meals    = "SELECT COUNT(*) AS total FROM meals";
$res_meals    = $conn->query($sql_meals);
$row_meals    = $res_meals->fetch_assoc();
$total_meals  = $row_meals['total'];

$sql_users    = "SELECT COUNT(*) AS total FROM users WHERE role != 'admin'";
$res_users    = $conn->query($sql_users);
$row_users    = $res_users->fetch_assoc();
$total_users  = $row_users['total'];

$conn->close();

echo json_encode([
    "success"            => true,
    "portions_this_month"=> $portions_month,
    "total_meals"        => $total_meals,
    "total_users"        => $total_users
]);
?>