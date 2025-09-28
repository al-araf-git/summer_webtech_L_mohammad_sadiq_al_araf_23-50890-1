<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    exit;
}

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($query === '') {
    exit("<p style='color:white;'>Please enter a search term.</p>");
}

$apiUrl = TMDB_BASE_URL . "/search/person?api_key=" . TMDB_API_KEY . "&language=en-US&query=" . urlencode($query) . "&page=1";
$response = @file_get_contents($apiUrl);
$data = json_decode($response, true);

if (!$data || empty($data['results'])) {
    exit("<p style='color:white;'>No actors found for '<b>" . htmlspecialchars($query) . "</b>'.</p>");
}

foreach ($data['results'] as $actor) {
    $name = htmlspecialchars($actor['name']);
    $profile = $actor['profile_path'] ? TMDB_IMAGE_BASE . $actor['profile_path'] : "../assets/img/user.jpeg";

    // Pick a small story from known_for
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
