<?php
session_start();
require '../config/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$userId = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['movie_id'])) {
    echo json_encode(["success" => false, "message" => "Movie ID missing"]);
    exit;
}

$movieId = (int)$data['movie_id'];

// Delete row
$stmt = $conn->prepare("DELETE FROM `completed-movie` WHERE user_id=? AND movie_id=?");
$stmt->bind_param("ii", $userId, $movieId);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(["success" => true, "message" => "Movie removed from completed list"]);
} else {
    echo json_encode(["success" => false, "message" => "Movie not found or already deleted"]);
}

$stmt->close();
$conn->close();
