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

if (!isset($_POST['request_id']) || $_POST['request_id'] == '' ||
    !isset($_POST['rating'])     || $_POST['rating']     == '') {
    echo json_encode(["success" => false, "message" => "Λείπουν απαραίτητα πεδία."]);
    $conn->close();
    exit();
}

$request_id = $_POST['request_id'];
$rating     = $_POST['rating'];

// Έλεγχος εύρους βαθμολογίας
if ($rating < 1 || $rating > 5) {
    echo json_encode(["success" => false, "message" => "Η βαθμολογία πρέπει να είναι μεταξύ 1 και 5."]);
    $conn->close();
    exit();
}

// Έλεγχος αιτήματος
$sql_req = "SELECT id, status, meal_id
            FROM requests
            WHERE id = $request_id
            AND consumer_id = $consumer_id";
$res_req = $conn->query($sql_req);

if ($res_req->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "Το αίτημα δεν βρέθηκε."]);
    $conn->close();
    exit();
}

$request = $res_req->fetch_assoc();

if ($request['status'] != 'collected') {
    echo json_encode(["success" => false, "message" => "Μπορείς να αξιολογήσεις μόνο μετά την παραλαβή."]);
    $conn->close();
    exit();
}

// Ανάκτηση cook_id από το αντίστοιχο meal
$meal_id  = $request['meal_id'];
$sql_meal = "SELECT cook_id FROM meals WHERE id = $meal_id";
$res_meal = $conn->query($sql_meal);
$meal     = $res_meal->fetch_assoc();
$cook_id  = $meal['cook_id'];

// Έλεγχος για διπλή αξιολόγηση
$sql_dup = "SELECT id FROM ratings WHERE request_id = $request_id";
$res_dup = $conn->query($sql_dup);

if ($res_dup->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Έχεις ήδη αξιολογήσει αυτή τη μερίδα."]);
    $conn->close();
    exit();
}

// Εισαγωγή αξιολόγησης
$sql_insert = "INSERT INTO ratings (request_id, rating) VALUES ($request_id, $rating)";

if (!$conn->query($sql_insert)) {
    echo json_encode(["success" => false, "message" => "Σφάλμα αποθήκευσης: " . $conn->error]);
    $conn->close();
    exit();
}

// Ενημέρωση πόντων μάγειρα
// rating <= 3 → +1 πόντος
// rating > 3  → +2 πόντοι
if ($rating > 3) {
    $points_to_add = 2;
} else {
    $points_to_add = 1;
}

$sql_points = "UPDATE users SET points = points + $points_to_add WHERE id = $cook_id";
$conn->query($sql_points);

$conn->close();

echo json_encode([
    "success"      => true,
    "message"      => "Η αξιολόγησή σου καταχωρήθηκε!",
    "points_given" => $points_to_add
]);
?>
