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

if (!isset($_POST['meal_id']) || $_POST['meal_id'] == '') {
    echo json_encode(["success" => false, "message" => "Λείπει το meal_id."]);
    $conn->close();
    exit();
}

$meal_id = $_POST['meal_id'];

$sql_check = "SELECT id FROM meals WHERE id = $meal_id AND cook_id = $cook_id";
$res_check = $conn->query($sql_check);

if ($res_check->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "Η αγγελία δεν βρέθηκε."]);
    $conn->close();
    exit();
}

$sql = "DELETE FROM meals WHERE id = $meal_id AND cook_id = $cook_id";

if ($conn->query($sql)) {
    echo json_encode(["success" => true, "message" => "Η αγγελία διαγράφηκε."]);
} else {
    echo json_encode(["success" => false, "message" => "Σφάλμα: " . $conn->error]);
}

$conn->close();
?>