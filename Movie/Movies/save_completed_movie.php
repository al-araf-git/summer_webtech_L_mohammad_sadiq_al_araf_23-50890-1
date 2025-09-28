<?php
session_start();
require '../config/config.php';

if(!isset($_SESSION['user_id'])){
    echo json_encode(['success'=>false,'message'=>'Not logged in']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if(!isset($input['movie_id'])){
    echo json_encode(['success'=>false,'message'=>'Movie ID missing']);
    exit;
}

$userId = $_SESSION['user_id'];
$movieId = intval($input['movie_id']);

// Check if the movie is already marked completed by this user
$stmt = $conn->prepare("SELECT * FROM `completed-movie` WHERE user_id=? AND movie_id=?");
$stmt->bind_param("si", $userId, $movieId);
$stmt->execute();
$stmt->store_result();

if($stmt->num_rows > 0){
    // echo json_encode(['success'=>false,'message'=>'Movie already marked complete']);
    echo json_encode(['success'=>false,'message'=>'']);

    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Insert new completed movie
$guid = bin2hex(random_bytes(16)); // 32 character unique ID
$stmt = $conn->prepare("INSERT INTO `completed-movie` (id, user_id, movie_id) VALUES (?, ?, ?)");
$stmt->bind_param("ssi", $guid, $userId, $movieId);

if($stmt->execute()){
    echo json_encode(['success'=>true,'message'=>'Movie marked complete']);
} else {
    echo json_encode(['success'=>false, 'message'=>$stmt->error]);
}

$stmt->close();
$conn->close();
