<?php
session_start();
require 'config/config.php';

$loggedIn = isset($_SESSION['user_id']); 
$userProfilePic = "";

if ($loggedIn) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!empty($user['profile_pic'])) {
        $userProfilePic = "Profile-Pictures/" . $user['profile_pic'];
    }
}

$spotlightMovies = [];
$tmdbUrl = TMDB_BASE_URL . "/movie/popular?api_key=" . TMDB_API_KEY . "&language=en-US&page=1";

$response = @file_get_contents($tmdbUrl);
if ($response) {
    $data = json_decode($response, true);
    if (isset($data['results'])) {
        $spotlightMovies = array_slice($data['results'], 0, 4);
    }
}

// Fetch trailers for each movie
foreach ($spotlightMovies as $key => $movie) {
    $trailerUrl = "";
    $videosUrl = TMDB_BASE_URL . "/movie/{$movie['id']}/videos?api_key=" . TMDB_API_KEY . "&language=en-US";
    $videoResponse = @file_get_contents($videosUrl);
    if ($videoResponse) {
        $videoData = json_decode($videoResponse, true);
        if (isset($videoData['results'])) {
            foreach ($videoData['results'] as $video) {
                if ($video['site'] === "YouTube" && $video['type'] === "Trailer") {
                    $trailerUrl = "https://www.youtube.com/watch?v=" . $video['key'];
                    break;
                }
            }
        }
    }
    $spotlightMovies[$key]['trailer'] = $trailerUrl;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Movie Card Demo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="global.css">
</head>
<body class="home-page">
<div>
    <div class="background-image">

        <!-- Header -->
        <header class="site-header">
            <div class="logo">🎥 <span>Movie Point</span></div>

            <?php if ($loggedIn): ?>
                <nav class="nav-links">
                    <a href="index.php" class="active">Home</a>
                    <a href="Movies/movies.php">Movies</a>
                    <a href="Actor/celebrities.php">CelebritiesList</a>
                </nav>

                <div class="search-login nav-links" style="position: relative;">
                    <?php if ($loggedIn && !empty($userProfilePic)): ?>
                    <div class="profile-pic-wrapper">
                        <img src="<?= htmlspecialchars($userProfilePic) ?>" alt="Profile" class="profile-pic">
                    </div>
                    <?php endif; ?>
                    <div class="profile-dropdown">
                        <a href="Profile/profile.php">Profile</a>
                        <a href="Completed-Movies/completed-movies.php">Completed Movies</a>
                        <a href="Favourite-Movies/favourite-movies.php">Favourite Movies</a>
                        <a href="Authentication/logout.php">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="search-login nav-links">
                    <a href="Authentication/login.html" class="login">Login</a>
                </div>
            <?php endif; ?>
        </header>

        <!-- Movie Card -->
        <div class="movie-card" data-movies='<?= htmlspecialchars(json_encode($spotlightMovies), ENT_QUOTES, 'UTF-8') ?>'>
            <div class="movie-image"></div>
            <div class="movie-card-info">
                <h2>Loading...</h2>
                <div class="rating">⭐⭐⭐⭐⭐ <span>0 voters</span></div>
                <p class="description">Loading description...</p>
                <div class="actions">
                    <span class="trailer-text btn">🎬 Watch Trailer</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Spotlight Section -->
    <div class="headlines">
        <h3>Spotlight This Month</h3>
        <hr>
        <div style="align-items: center; display: flex; flex-wrap: wrap; gap: 58px;">
            <?php foreach ($spotlightMovies as $movie): ?>
            <div class="featured-trailer">
                <img src="<?= TMDB_IMAGE_BASE . $movie['poster_path'] ?>" alt="<?= htmlspecialchars($movie['title']) ?>">
                <div class="play-btn" data-video="<?= $movie['trailer'] ?>"></div>
                <p><?= htmlspecialchars($movie['title']) ?></p>
                <div class="rating-heaings">
                    <?= str_repeat('⭐', round($movie['vote_average']/2)) ?>
                    <span><?= $movie['vote_count'] ?> voters</span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <!-- Trailers & Videos Section (static) -->
        <div class="headlines">
            <h3>Trailers & Videos</h3>
            <hr>
            <div class="trailers-container">
                <div class="featured-trailer">
                    <img src="assets/img/video/video1.png" alt="Featured Trailer">
                    <div class="play-btn" data-video="https://www.youtube.com/watch?v=RZXnugbhw_4"></div>
                    <div class="overlay">
                        <div class="title">Angle of Death</div>
                        <div class="stars">⭐⭐⭐⭐</div>
                        <div class="voters">18k voters</div>
                    </div>
                </div>
                <div class="side-trailers">
                    <div class="side-trailer">
                        <img src="assets/img/video/video2.png" alt="Trailer 2">
                        <div class="play-btn" data-video="https://www.youtube.com/watch?v=RZXnugbhw_4"></div>
                    </div>
                    <div class="side-trailer">
                        <img src="assets/img/video/video3.png" alt="Trailer 3">
                        <div class="play-btn" data-video="https://www.youtube.com/watch?v=RZXnugbhw_4"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Video Modal -->
    <div class="video-modal" id="videoModal">
        <span class="close-btn" id="closeModal">&times;</span>
        <div class="video-wrapper">
            <video id="trailerVideo" controls style="display:none;">
                <source id="videoSource" src="" type="video/mp4">
            </video>
            <iframe id="trailerIframe" style="display:none;" width="800" height="450" frameborder="0" allowfullscreen></iframe>
        </div>
    </div>

</div>
<!-- Footer -->
<footer class="site-footer">
  <div class="footer-overlay">
    <div class="footer-container">

      <!-- Logo + Info -->
      <div class="footer-section about">
        <h2 class="footer-logo">
          <i class="icofont-film"></i> Movie <span>Point</span>
        </h2>
        <p>7th Harley Place, London W1G 8LZ<br>United Kingdom</p>
        <p><strong>Call us:</strong> (+880) 111 222 3456</p>
      </div>

      <!-- Legal -->
      <div class="footer-section">
        <h3>Legal</h3>
        <ul>
          <li><a href="#">Terms of Use</a></li>
          <li><a href="#">Privacy Policy</a></li>
          <li><a href="#">Security</a></li>
        </ul>
      </div>

      <!-- Account -->
      <div class="footer-section">
        <h3>Account</h3>
        <ul>
          <li><a href="Profile/profile.php">My Account</a></li>
          <li><a href="Completed-Movies/completed-movies.php">Watchlist</a></li>
          <li><a href="Favourite-Movies/favourite-movies.php">Collections</a></li>
          <li><a href="#">User Guide</a></li>
        </ul>
      </div>

      <!-- Newsletter -->
      <div class="footer-section newsletter">
        <h3>Newsletter</h3>
        <p>Subscribe to our newsletter system now to get latest news from us.</p>
        <form>
          <input type="email" placeholder="Enter your email.." required>
          <button type="submit">SUBSCRIBE NOW</button>
        </form>
      </div>
    </div>

    <!-- Bottom -->
    <div class="footer-bottom">
      <p>Templates Hub</p>
      <a href="#top" class="back-to-top">Back to top ↑</a>
    </div>
  </div>
</footer>



<script src="index.js"></script>
</body>
</html>
