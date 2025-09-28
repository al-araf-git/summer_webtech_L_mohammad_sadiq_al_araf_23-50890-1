<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['user_id'])) exit;

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($query === '') {
    exit("<div style='width:100%; text-align:center; margin:20px 0;'><p style='color:white;'>⚠️ Please enter a search term.</p></div>");
}

// TMDb Search Movies API
$apiUrl = TMDB_BASE_URL . "/search/movie?api_key=" . TMDB_API_KEY . "&language=en-US&query=" . urlencode($query) . "&page=1";
$response = @file_get_contents($apiUrl);
$data = json_decode($response, true);

if (!$data || empty($data['results'])) {
    exit("<div style='width:100%; text-align:center; margin:20px 0;'><p style='color:white;'>❌ No movies found for '<b>" . htmlspecialchars($query) . "</b>'.</p></div>");
}

foreach ($data['results'] as $movie) {
    $title = htmlspecialchars($movie['title']);
    $poster = $movie['poster_path'] ? TMDB_IMAGE_BASE . $movie['poster_path'] : "../assets/img/user.jpeg";
    $overview = htmlspecialchars($movie['overview'] ?? '');
    $rating = $movie['vote_average'] ?? 0;
    $voteCount = $movie['vote_count'] ?? 0;

    // Fetch trailer
    $trailerUrl = "";
    $videosUrl = TMDB_BASE_URL . "/movie/{$movie['id']}/videos?api_key=" . TMDB_API_KEY;
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
            <button class="icon-btn tick" data-movie-id="'.$movie['id'].'"><span>✔</span></button>
            <button class="icon-btn list"><span>☰</span></button>
            <button class="icon-btn heart"><span>❤</span></button>
        </div>
    </div>';
}
