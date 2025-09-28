<?php
session_start();
require '../config/config.php';
header('Content-Type: application/json');

// Hide warnings to avoid breaking JSON
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

if(!isset($_SESSION['user_id'])){
    echo json_encode(["success"=>false,"message"=>"Not logged in"]);
    exit;
}

$userId = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);

if(!isset($data['movie_id'])){
    echo json_encode(["success"=>false,"message"=>"Movie ID missing"]);
    exit;
}

$movieId = (int)$data['movie_id'];

try {
    // Check completed-movie
    $stmt = $conn->prepare("SELECT id FROM `completed-movie` WHERE user_id=? AND movie_id=? LIMIT 1");
    $stmt->bind_param("ii", $userId, $movieId);
    $stmt->execute();
    $result = $stmt->get_result();
    $completedRow = $result->fetch_assoc();
    $stmt->close();

    if(!$completedRow){
        echo json_encode(["success"=>false,"message"=>"Completed movie not found"]);
        exit;
    }

    $completedId = $completedRow['id'];

    // Check if already favorited
    $stmt = $conn->prepare("SELECT id FROM `favourite-movie` WHERE user_id=? AND movie_id=? LIMIT 1");
    $stmt->bind_param("ii", $userId, $movieId);
    $stmt->execute();
    $result = $stmt->get_result();
    $favRow = $result->fetch_assoc();
    $stmt->close();

    $alreadyFavorited = $favRow ? true : false;

    echo json_encode([
        "success"=>true,
        "completed_id"=>$completedId,
        "user_id"=>$userId,
        "movie_id"=>$movieId,
        "already_favorited"=>$alreadyFavorited
    ]);

} catch(Exception $e){
    echo json_encode(["success"=>false,"message"=>"Server error: ".$e->getMessage()]);
}

$conn->close();
?>
