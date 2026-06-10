<?php

session_start();
header("Content-Type: application/json");
include("db.php");
 
if (!isset($_POST['firstName']) || !isset($_POST['lastName']) ||
    !isset($_POST['email'])     || !isset($_POST['password']) ||
    !isset($_POST['role'])      ||
    $_POST['firstName'] === ''  || $_POST['lastName'] === ''  ||
    $_POST['email']     === ''  || $_POST['password'] === ''  ||
    $_POST['role']      === '') {
    echo json_encode(["success" => false, "message" => "Συμπληρώστε όλα τα πεδία."]);
    $conn->close();
    exit();
}
 
$firstName = $_POST['firstName'];
$lastName  = $_POST['lastName'];
$email     = $_POST['email'];
$password  = md5($_POST['password']);
$role      = $_POST['role'];
 

$sql_check = "SELECT id FROM users WHERE email = '$email'";
$res_check = $conn->query($sql_check);
 
if ($res_check->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Το email χρησιμοποιείται ήδη."]);
    $conn->close();
    exit();
}
 

$sql = "INSERT INTO users (firstName, lastName, email, password, role)
        VALUES ('$firstName', '$lastName', '$email', '$password', '$role')";
 
if ($conn->query($sql)) {
    echo json_encode([
        "success" => true,
        "message" => "Ο λογαριασμός δημιουργήθηκε επιτυχώς!",
        "id"      => $conn->insert_id
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Σφάλμα: " . $conn->error]);
}
 
$conn->close();
?>