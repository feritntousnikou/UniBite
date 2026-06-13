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

if (!isset($_POST['title'])           || $_POST['title']           == '' ||
    !isset($_POST['portions'])        || $_POST['portions']        == '' ||
    !isset($_POST['pickup_location']) || $_POST['pickup_location'] == '' ||
    !isset($_POST['pickup_time'])     || $_POST['pickup_time']     == '') {
    echo json_encode(["success" => false, "message" => "Συμπληρώστε όλα τα υποχρεωτικά πεδία."]);
    $conn->close();
    exit();
}

$title           = $_POST['title'];
$description     = isset($_POST['description']) ? $_POST['description'] : '';
$portions        = $_POST['portions'];
$pickup_location = $_POST['pickup_location'];
$pickup_time     = $_POST['pickup_time'];
$allergens       = isset($_POST['allergens']) ? $_POST['allergens'] : '';

$photo = '';

if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
    $filename = time() . '_' . $_FILES['photo']['name'];
    $dest = 'C:/wamp64/www/unibite/upload/' . $filename;
    
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
        $photo = $filename;
    }
}

$sql = "INSERT INTO meals
            (cook_id, title, description, photo, portions_total,
             portions_available, pickup_location, pickup_time, allergens)
        VALUES
            ($cook_id, '$title', '$description', '$photo', $portions,
             $portions, '$pickup_location', '$pickup_time', '$allergens')";

if ($conn->query($sql)) {
    echo json_encode([
        "success" => true,
        "message" => "Η αγγελία δημοσιεύτηκε επιτυχώς!",
        "id"      => $conn->insert_id
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Σφάλμα: " . $conn->error]);
}

$conn->close();
?>