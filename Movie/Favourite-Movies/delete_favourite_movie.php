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

$movieId = $data['movie_id'];

// Delete from favourite-movie table
$stmt = $conn->prepare("DELETE FROM `favourite-movie` WHERE user_id=? AND movie_id=?");
$stmt->bind_param("ss", $userId, $movieId);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(["success" => true, "message" => "Movie removed from favourites"]);
} else {
    echo json_encode(["success" => false, "message" => "Movie not found in favourites"]);
}

$stmt->close();
$conn->close();
?>
