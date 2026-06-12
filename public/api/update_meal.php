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

if (!isset($_POST['meal_id'])        || $_POST['meal_id']        == '' ||
    !isset($_POST['title'])          || $_POST['title']          == '' ||
    !isset($_POST['portions_total']) || $_POST['portions_total'] == '' ||
    !isset($_POST['pickup_location'])|| $_POST['pickup_location']== '' ||
    !isset($_POST['pickup_time'])    || $_POST['pickup_time']    == '') {
    echo json_encode(["success" => false, "message" => "Συμπληρώστε όλα τα υποχρεωτικά πεδία."]);
    $conn->close();
    exit();
}

$meal_id         = $_POST['meal_id'];
$title           = $_POST['title'];
$description     = isset($_POST['description']) ? $_POST['description'] : '';
$portions_total  = $_POST['portions_total'];
$pickup_location = $_POST['pickup_location'];
$pickup_time     = $_POST['pickup_time'];
$allergens       = isset($_POST['allergens']) ? $_POST['allergens'] : '';

$sql_check = "SELECT id FROM meals WHERE id = $meal_id AND cook_id = $cook_id";
$res_check = $conn->query($sql_check);

if ($res_check->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "Η αγγελία δεν βρέθηκε."]);
    $conn->close();
    exit();
}

$sql = "UPDATE meals
        SET title              = '$title',
            description        = '$description',
            portions_available = portions_available + ($portions_total - portions_total),
            portions_total     = $portions_total,
            pickup_location    = '$pickup_location',
            pickup_time        = '$pickup_time',
            allergens          = '$allergens'
        WHERE id = $meal_id AND cook_id = $cook_id";

if ($conn->query($sql)) {
    echo json_encode(["success" => true, "message" => "Η αγγελία ενημερώθηκε!"]);
} else {
    echo json_encode(["success" => false, "message" => "Σφάλμα: " . $conn->error]);
}

$conn->close();
?>