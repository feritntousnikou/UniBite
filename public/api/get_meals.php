<?php
session_start();
header("Content-Type: application/json");
include("db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Μη εξουσιοδοτημένη πρόσβαση."]);
    $conn->close();
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['id']) && $_GET['id'] != '') {
    $meal_id = $_GET['id'];

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
                     u.lastName  AS cook_lastName
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

    $created   = strtotime($meal['created_at']);
    $now       = time();
    $hours_old = ($now - $created) / 3600;

    if ($hours_old >= 48) {
        $meal['status'] = 'expired';
    } else if ($meal['portions_available'] > 0) {
        $meal['status'] = 'active';
    } else {
        $meal['status'] = 'inactive';
    }

    $sql_req = "SELECT id, status FROM requests
                WHERE meal_id = $meal_id
                AND consumer_id = $user_id
                ORDER BY created_at DESC
                LIMIT 1";

    $res_req      = $conn->query($sql_req);
    $user_request = null;

    if ($res_req->num_rows > 0) {
        $user_request = $res_req->fetch_assoc();

        $req_id     = $user_request['id'];
        $sql_rating = "SELECT rating FROM ratings WHERE request_id = $req_id";
        $res_rating = $conn->query($sql_rating);

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
            u.lastName  AS cook_lastName
        FROM meals m
        JOIN users u ON m.cook_id = u.id
        ORDER BY m.created_at DESC";

$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {

        $created   = strtotime($row['created_at']);
        $now       = time();
        $hours_old = ($now - $created) / 3600;

        if ($hours_old >= 48) {
            continue;
        } else if ($row['portions_available'] > 0) {
            $row['status'] = 'active';
        } else {
            $row['status'] = 'inactive';
        }

        array_push($meals, $row);
    }
}

$conn->close();

echo json_encode([
    "success" => true,
    "meals"   => $meals
]);
?>