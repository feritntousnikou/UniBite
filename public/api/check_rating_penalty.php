<?php

session_start();
header("Content-Type: application/json");
include("db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Μη εξουσιοδοτημένη πρόσβαση."]);
    $conn->close();
    exit();
}

$sql = "SELECT r.id, r.consumer_id, r.collected_at
        FROM requests r
        WHERE r.status = 'collected'
        AND r.penalty_applied = 0
        AND r.collected_at IS NOT NULL";

$result = $conn->query($sql);
$penalized = 0;

if ($result) {
    while ($row = $result->fetch_assoc()) {

        if (!isset($row['collected_at']) || $row['collected_at'] === null) {
            continue;
        }

        $collected = strtotime($row['collected_at']);
        $now       = time();
        $hours     = ($now - $collected) / 3600;

        if ($hours >= 48) {
            $req_id     = $row['id'];
            $sql_rating = "SELECT id FROM ratings WHERE request_id = $req_id";
            $res_rating = $conn->query($sql_rating);

            if ($res_rating->num_rows == 0) {
                $consumer_id = $row['consumer_id'];
                $sql_penalty = "UPDATE users SET points = points - 1
                                WHERE id = $consumer_id AND points > 0";
                $conn->query($sql_penalty);
                $penalized++;
            }

            $sql_flag = "UPDATE requests SET penalty_applied = 1 WHERE id = $req_id";
            $conn->query($sql_flag);
        }
    }
}

$conn->close();

echo json_encode(["success" => true, "penalized" => $penalized]);
?>