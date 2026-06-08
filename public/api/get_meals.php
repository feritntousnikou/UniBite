<?php
// ---------------
//  get_meals.php — Επιστρέφει αγγελίες φαγητού
// ---------------
session_start();
header("Content-Type: application/json");
include("db.php");

// Έλεγχος σύνδεσης χρήστη
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Μη εξουσιοδοτημένη πρόσβαση."]);
    $conn->close();
    exit();
}

$user_id = $_SESSION['user_id'];

// Μία συγκεκριμένη αγγελία
if (isset($_GET['id']) && $_GET['id'] != '') {
    $meal_id = $_GET['id'];

    // Στοιχεία αγγελίας
    $sql_meal = "SELECT
                     m.id,
                     m.cook_id,
                     m.title,
                     m.description,
                     m.photo,
                     m.portions_total,
                     m.portions_available,
                     m.pickup_location,
                     m.pickup_time,
                     m.allergens,
                     m.created_at,
                     m.lat,
                     m.lng,
                     u.firstName AS cook_firstName,
                     u.lastName  AS cook_lastName,
                     CASE
                         WHEN TIMESTAMPDIFF(HOUR, m.created_at, NOW()) >= 48 THEN 'expired'
                         WHEN m.portions_available > 0                       THEN 'active'
                         ELSE 'inactive'
                     END AS status
                 FROM meals m
                 JOIN users u ON m.cook_id = u.id
                 WHERE m.id = $meal_id";

    $res_meal = $conn->query($sql_meal);

    if ($res_meal->num_rows == 0) {
        echo json_encode(["success" => false, "message" => "Η αγγελία δεν βρέθηκε."]);
        $conn->close();
        exit();
    }

    $meal = $res_meal->fetch_assoc();

    $sql_req = "SELECT id, status FROM requests
                WHERE meal_id = $meal_id
                AND consumer_id = $user_id
                ORDER BY created_at DESC
                LIMIT 1";

    $res_req      = $conn->query($sql_req);
    $user_request = null;

    if ($res_req->num_rows > 0) {
        $user_request = $res_req->fetch_assoc();

        // Αν υπάρχει αίτημα, ψάχνουμε αν έχει αξιολόγηση
        $req_id      = $user_request['id'];
        $sql_rating  = "SELECT rating FROM ratings WHERE request_id = $req_id";
        $res_rating  = $conn->query($sql_rating);

        if ($res_rating->num_rows > 0) {
            $rating_row             = $res_rating->fetch_assoc();
            $user_request['rating'] = $rating_row['rating'];
        } else {
            $user_request['rating'] = null;
        }
    }

    $conn->close();

    echo json_encode([
        "success"      => true,
        "meal"         => $meal,
        "user_request" => $user_request
    ]);
    exit();
}

// Όλες οι αγγελίες
$meals = [];

$sql = "SELECT
            m.id,
            m.title,
            m.description,
            m.photo,
            m.portions_total,
            m.portions_available,
            m.pickup_location,
            m.pickup_time,
            m.allergens,
            m.created_at,
            m.lat,
            m.lng,
            u.firstName AS cook_firstName,
            u.lastName  AS cook_lastName,
            CASE
                WHEN m.portions_available > 0 THEN 'active'
                ELSE 'inactive'
            END AS status
        FROM meals m
        JOIN users u ON m.cook_id = u.idσ
        WHERE TIMESTAMPDIFF(HOUR, m.created_at, NOW()) < 48
        ORDER BY m.created_at DESC";

$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        array_push($meals, $row);
    }
}

$conn->close();

echo json_encode([
    "success" => true,
    "meals"   => $meals
]);
?>
