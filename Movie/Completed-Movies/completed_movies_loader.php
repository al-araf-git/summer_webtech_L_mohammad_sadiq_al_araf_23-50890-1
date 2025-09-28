<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    exit;
}

$userId = $_SESSION['user_id'];
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Fetch completed movies
$stmt = $conn->prepare("SELECT movie_id FROM `completed-movie` WHERE user_id=? ORDER BY id DESC LIMIT ?, ?");
$stmt->bind_param("iii", $userId, $offset, $limit);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $movieId = $row['movie_id'];
    $tmdbUrl = "https://api.themoviedb.org/3/movie/{$movieId}?api_key=" . TMDB_API_KEY;
    $movieDetails = @file_get_contents($tmdbUrl);
    $movie = json_decode($movieDetails, true);

    if (!$movie || isset($movie['status_code'])) continue;

    $title = htmlspecialchars($movie['title']);
    $poster = TMDB_IMAGE_BASE . $movie['poster_path'];
    $overview = htmlspecialchars($movie['overview']);
    $rating = $movie['vote_average'];
    $voteCount = $movie['vote_count'] ?? 0;

    // 🎬 Fetch trailer from TMDB videos endpoint
    $trailerUrl = "";
    $videosUrl = "https://api.themoviedb.org/3/movie/{$movieId}/videos?api_key=" . TMDB_API_KEY;
    $videoDetails = @file_get_contents($videosUrl);
    $videoData = json_decode($videoDetails, true);

    if (!empty($videoData['results'])) {
        foreach ($videoData['results'] as $video) {
            if ($video['site'] === "YouTube" && $video['type'] === "Trailer") {
                $trailerUrl = "https://www.youtube.com/embed/" . $video['key'];
                break;
            }
        }
    }

    echo '
    <div class="featured-trailer" style="width:220px; margin-bottom:40px; background-color:#1a1c29; border-radius:10px; overflow:hidden; box-shadow:0 5px 15px rgba(0,0,0,0.5);">
        <img src="'.$poster.'" alt="'.$title.'" style="width:100%; height:330px; object-fit:cover;">
        <div style="padding:10px; color:white;">
            <h4 style="margin:5px 0; font-size:16px;">'.$title.'</h4>
            <p style="font-size:12px; color:#ccc; height:50px; overflow:hidden; text-overflow:ellipsis; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical;">'.$overview.'</p>
            <div class="rating-heaings" style="font-size:12px; margin-top:5px; white-space:nowrap;">
                '.str_repeat("⭐", round($rating/2)).'
                <span style="margin-left:5px;">'.$voteCount.' voters</span>
            </div>
        </div>
<div class="action-buttons" style="display:flex; gap:5px; margin:10px;">
    '.($trailerUrl ? '<button class="icon-btn play" data-video="'.$trailerUrl.'"><span>▶</span></button>' : '').'
    <button class="icon-btn delete" data-movie-id="'.$movieId.'"><span>❌</span></button>
    <button class="icon-btn heart" data-movie-id="'.$movieId.'"><span>❤</span></button>
</div>

    </div>';
}

$stmt->close();
$conn->close();



