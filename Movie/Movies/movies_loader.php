<?php
session_start();
require '../config/config.php';

if(!isset($_SESSION['user_id'])){
    echo json_encode(['movies'=>[]]);
    exit;
}

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$userId = $_SESSION['user_id'];

// Fetch completed movies for user
$completedMovies = [];
$res = $conn->query("SELECT movie_id FROM `completed-movie` WHERE user_id='$userId'");
while($row = $res->fetch_assoc()){
    $completedMovies[] = $row['movie_id'];
}

// Fetch popular movies from TMDb
$tmdbUrl = TMDB_BASE_URL . "/movie/popular?api_key=" . TMDB_API_KEY . "&language=en-US&page={$page}";
$response = @file_get_contents($tmdbUrl);
$movies = [];
if($response){
    $data = json_decode($response, true);
    $movies = $data['results'] ?? [];
}

// Add trailer and completed info
foreach($movies as &$movie){
    $videoUrl = TMDB_BASE_URL . "/movie/{$movie['id']}/videos?api_key=" . TMDB_API_KEY . "&language=en-US";
    $videoResponse = @file_get_contents($videoUrl);
    $trailerLink = '';
    if($videoResponse){
        $videoData = json_decode($videoResponse, true);
        if(!empty($videoData['results'])){
            foreach($videoData['results'] as $video){
                if($video['site']=='YouTube' && $video['type']=='Trailer'){
                    $trailerLink = "https://www.youtube.com/embed/".$video['key'];
                    break;
                }
            }
        }
    }
    $movie['trailer'] = $trailerLink;
    $movie['poster'] = TMDB_IMAGE_BASE . $movie['poster_path'];
    $movie['completed'] = in_array($movie['id'], $completedMovies);
}
unset($movie);

echo json_encode(['movies'=>$movies]);
