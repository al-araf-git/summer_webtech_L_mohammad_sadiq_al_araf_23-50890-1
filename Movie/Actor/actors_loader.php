<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    exit;
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// Call TMDb popular people API
$tmdbUrl = TMDB_BASE_URL . "/person/popular?api_key=" . TMDB_API_KEY . "&language=en-US&page=" . $page;
$response = @file_get_contents($tmdbUrl);
$data = json_decode($response, true);

if (!$data || empty($data['results'])) {
    exit;
}

foreach ($data['results'] as $actor) {
    $name = htmlspecialchars($actor['name']);
    $profile = $actor['profile_path'] ? TMDB_IMAGE_BASE . $actor['profile_path'] : "../assets/img/user.jpeg";

    // Use their "known_for" overview as a mini story
    $story = "";
    if (!empty($actor['known_for'])) {
        foreach ($actor['known_for'] as $work) {
            if (!empty($work['overview'])) {
                $story = $work['overview'];
                break;
            }
        }
    }
    if (!$story) $story = "No biography available.";

    echo '
    <div class="actor-card" style="background:#1a1c29; border-radius:15px; padding:15px; width:250px; color:white; box-shadow:0 5px 15px rgba(0,0,0,0.5); transition:transform 0.2s ease-in-out;">
        <img src="'.$profile.'" alt="'.$name.'" style="width:100%; height:300px; border-radius:10px; object-fit:cover; margin-bottom:10px;">
        <h4 style="font-size:20px; margin:5px 0;">'.$name.'</h4>
        <p style="font-size:14px; line-height:1.4; color:#bbb;">'.htmlspecialchars(substr($story,0,120)).'...</p>
    </div>';
}
