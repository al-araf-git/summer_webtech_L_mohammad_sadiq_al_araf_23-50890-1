<?php
session_start();
require '../config/config.php';
header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])){
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if(!isset($data['movie_id'])){
    echo json_encode(["success" => false, "message" => "Missing movie_id"]);
    exit;
}

$userId = $_SESSION['user_id'];
$movieId = $data['movie_id'];

// Function to generate GUID/UUID
function generateUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

try {
    // 1. Check if the movie is completed
    $stmt = $conn->prepare("SELECT id FROM `completed-movie` WHERE user_id=? AND movie_id=? LIMIT 1");
    $stmt->bind_param("ss", $userId, $movieId);
    $stmt->execute();
    $result = $stmt->get_result();
    $completedRow = $result->fetch_assoc();
    $stmt->close();

    if(!$completedRow){
        echo json_encode([
            "success" => false,
            "message" => "You must mark the movie as completed before adding to favourites"
        ]);
        exit;
    }

    $completedId = $completedRow['id'];

    // 2. Check if already in favourites
    $stmt = $conn->prepare("SELECT id FROM `favourite-movie` WHERE completed_id=? LIMIT 1");
    $stmt->bind_param("s", $completedId);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->fetch_assoc()){
        echo json_encode(["success" => false, "message" => "Movie already in favourites"]);
        exit;
    }
    $stmt->close();

    // 3. Insert into favourite-movie with GUID
    $favouriteId = generateUUID();
    $stmt = $conn->prepare("INSERT INTO `favourite-movie` (id, completed_id, user_id, movie_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $favouriteId, $completedId, $userId, $movieId);

    if($stmt->execute()){
        echo json_encode([
            "success" => true,
            "message" => "Movie added to favourites",
            "favourite_id" => $favouriteId,
            "completed_id" => $completedId,
            "movie_id" => $movieId,
            "user_id" => $userId
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to add favourite: " . $stmt->error]);
    }

    $stmt->close();

} catch(Exception $e){
    echo json_encode(["success" => false, "message" => "Server error: " . $e->getMessage()]);
}

$conn->close();
?>
