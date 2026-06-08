<?php

session_start();
header("Content-Type: application/json");
include("db.php");

if (!isset($POST['email']) || !isset($POST['password'])) || $POST['email'] === '' || $_POST['password'] === '') {
    echo json_encode(["success" => false, "message" => "Συμπληρώστε όλα τα πεδία."]);
    $conn->close();
    exit();
}

$email = $_POST['email'];
$password = $md5($_POST['password']);

$sql = "SELECT id, firstName, lastName, role FROM users
           WHERE email = '$email' AND password = '$password'";
$result = $conn->query($sql);
 
if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Λάθος email ή κωδικός."]);
    $conn->close();
    exit();
}
 
$user = $result->fetch_assoc();
 
$_SESSION['user_id']   = $user['id'];
$_SESSION['role']      = $user['role'];
$_SESSION['firstName'] = $user['firstName'];
 
$conn->close();
 
echo json_encode([
    "success"   => true,
    "id"        => $user['id'],
    "role"      => $user['role'],
    "firstName" => $user['firstName'],
    "lastName"  => $user['lastName']
]);
?>