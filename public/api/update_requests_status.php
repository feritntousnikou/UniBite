<?php

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

if (!isset($_POST['request_id']) || $_POST['request_id'] == '' ||
    !isset($_POST['status'])     || $_POST['status']     == '') {
    echo json_encode(["success" => false, "message" => "Λείπουν απαραίτητα πεδία."]);
    $conn->close();
    exit();
}

$request_id = $_POST['request_id'];
$new_status = $_POST['status'];

if ($new_status != 'approved'      && $new_status != 'rejected' &&
    $new_status != 'collected'     && $new_status != 'not_collected') {
    echo json_encode(["success" => false, "message" => "Μη έγκυρη κατάσταση."]);
    $conn->close();
    exit();
}

$sql_req = "SELECT r.id, r.status, r.consumer_id, r.meal_id
            FROM requests r
            JOIN meals m ON r.meal_id = m.id
            WHERE r.id = $request_id
            AND m.cook_id = $cook_id";

$res_req = $conn->query($sql_req);

if ($res_req->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "Το αίτημα δεν βρέθηκε."]);
    $conn->close();
    exit();
}

$req         = $res_req->fetch_assoc();
$old_status  = $req['status'];
$meal_id     = $req['meal_id'];
$consumer_id = $req['consumer_id'];

$valid = false;
if ($old_status === 'pending'  && ($new_status === 'approved' || $new_status === 'rejected')) {
    $valid = true;
}
if ($old_status === 'approved' && ($new_status === 'collected' || $new_status === 'not_collected')) {
    $valid = true;
}

if (!$valid) {
    echo json_encode(["success" => false, "message" => "Μη επιτρεπτή μετάβαση κατάστασης."]);
    $conn->close();
    exit();
}

$sql_update = "UPDATE requests SET status = '$new_status' WHERE id = $request_id";
if (!$conn->query($sql_update)) {
    echo json_encode(["success" => false, "message" => "Σφάλμα ενημέρωσης: " . $conn->error]);
    $conn->close();
    exit();
}

if ($new_status === 'approved') {
    $sql_portions = "UPDATE meals SET portions_available = portions_available - 1
                     WHERE id = $meal_id AND portions_available > 0";
    $conn->query($sql_portions);
}

if ($new_status === 'not_collected') {
    $sql_points = "UPDATE users SET points = points - 1
                   WHERE id = $consumer_id AND points > 0";
    $conn->query($sql_points);
}

$conn->close();

if ($new_status === 'approved') {
    $msg = 'Το αίτημα εγκρίθηκε!';
} else if ($new_status === 'rejected') {
    $msg = 'Το αίτημα απορρίφθηκε.';
} else if ($new_status === 'collected') {
    $msg = 'Η μερίδα παρελήφθη επιτυχώς!';
} else {
    $msg = 'Καταγράφηκε ότι η μερίδα δεν παρελήφθη.';
}

echo json_encode(["success" => true, "message" => $msg]);
?>