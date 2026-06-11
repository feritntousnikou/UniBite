<?php
session_start();
header("Content-Type: application/json");
include("db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Μη εξουσιοδοτημένη πρόσβαση."]);
    $conn->close();
    exit();
}

$consumer_id = $_SESSION['user_id'];

if (!isset($_POST['meal_id']) || $_POST['meal_id'] == '') {
    echo json_encode(["success" => false, "message" => "Λείπει το meal_id."]);
    $conn->close();
    exit();
}

$meal_id = $_POST['meal_id'];

$sql_points = "SELECT points FROM users WHERE id = $consumer_id";
$res_points = $conn->query($sql_points);
$user       = $res_points->fetch_assoc();

if ($user['points'] < 1) {
    echo json_encode(["success" => false, "message" => "Δεν έχεις αρκετούς πόντους για κράτηση."]);
    $conn->close();
    exit();
}

$sql_meal = "SELECT id, cook_id, portions_available, created_at
             FROM meals
             WHERE id = $meal_id";
$res_meal = $conn->query($sql_meal);

if ($res_meal->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "Η αγγελία δεν βρέθηκε."]);
    $conn->close();
    exit();
}

$meal = $res_meal->fetch_assoc();

if ($meal['cook_id'] == $consumer_id) {
    echo json_encode(["success" => false, "message" => "Δεν μπορείς να αιτηθείς τη δική σου αγγελία."]);
    $conn->close();
    exit();
}

$created   = strtotime($meal['created_at']);
$now       = time();
$hours_old = ($now - $created) / 3600;

if ($hours_old >= 48) {
    echo json_encode(["success" => false, "message" => "Η αγγελία έχει λήξει."]);
    $conn->close();
    exit();
}

if ($meal['portions_available'] <= 0) {
    echo json_encode(["success" => false, "message" => "Δεν υπάρχουν διαθέσιμες μερίδες."]);
    $conn->close();
    exit();
}

$sql_dup = "SELECT id FROM requests
            WHERE meal_id = $meal_id
            AND consumer_id = $consumer_id
            AND (status = 'pending' OR status = 'approved')";
$res_dup = $conn->query($sql_dup);

if ($res_dup->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Έχεις ήδη ενεργό αίτημα για αυτή την αγγελία."]);
    $conn->close();
    exit();
}

$sql_insert = "INSERT INTO requests (meal_id, consumer_id, status)
               VALUES ($meal_id, $consumer_id, 'pending')";

if ($conn->query($sql_insert)) {
    echo json_encode([
        "success"    => true,
        "message"    => "Το αίτημά σου στάλθηκε επιτυχώς!",
        "request_id" => $conn->insert_id
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Σφάλμα κατά την αποθήκευση: " . $conn->error]);
}

$conn->close();
?>