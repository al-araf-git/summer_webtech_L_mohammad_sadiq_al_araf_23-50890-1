<?php
session_start();
require '../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Authentication/login.html");
    exit;
}

$loggedIn = true; // Optional, for later use
$userProfilePic = "../assets/img/user.jpeg"; // default profile pic

// Fetch profile pic from database if user is logged in
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
if (!empty($user['profile_pic'])) {
    $userProfilePic = "../Profile-Pictures/" . $user['profile_pic'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Movie Point</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../global.css">
    <style>
        /* Loader */
        #loader {
            display:none;
            text-align:center;
            margin:20px;
        }
        .loader-spinner {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #7e3ff2;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin:auto;
        }
        @keyframes spin { 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
<div>
    <div class="background-image" style="margin-top: -100px;">
    <!-- Header -->
        <header class="site-header">
            <div class="logo">🎥 <span>Movie Point</span></div>
            <nav class="nav-links">
                <a href="../index.php">Home</a>
                <a href="../Movies/movies.php">Movies</a>
                <a href="../Actor/celebrities.php">CelebritiesList</a>

            </nav>
            <div class="search-login" style="position:relative;">
                <?php if ($loggedIn): ?>
                    <div class="profile-pic-wrapper">
                        <img src="<?= htmlspecialchars($userProfilePic) ?>" alt="Profile" class="profile-pic">
                    </div>
                    <div class="profile-dropdown">
                        <a href="../Profile/profile.php">Profile</a>
                        <a href="../Completed-Movies/completed-movies.php">Completed Movies</a>
                        <a href="../Favourite-Movies/favourite-movies.php">Favourite Movies</a>
                        <a href="../Authentication/logout.php">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="../Authentication/login.html" class="login">Login</a>
                <?php endif; ?>
            </div>
        </header>

        <div class="headlines" style="margin-top:50px;">
            <h3 style="font-size: 80px; display:flex; justify-content:center; margin-top:100px;">Completed Movies</h3>
        </div>
    </div>

    <div class="headlines">
        <h3>Completed Movies</h3>
        <hr>
        <div id="movies-container" style="display:flex; flex-wrap:wrap; gap:30px;"></div>
        <div id="loader"><div class="loader-spinner"></div></div>
    </div>
</div>

<!-- Video Modal -->
<div class="video-modal" id="videoModal">
    <span class="close-btn" id="closeModal">&times;</span>
    <div class="video-wrapper">
        <iframe id="trailerIframe" style="display:none;" width="800" height="450" frameborder="0" allowfullscreen></iframe>
    </div>
</div>

<!-- Favourite Info Popup -->
<div id="favPopup" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%);
     background:#1a1c29; color:white; padding:20px; border-radius:10px; z-index:9999; box-shadow:0 5px 15px rgba(0,0,0,0.5);">
    <h3>Movie Favourite Info</h3>
    <p id="favInfo"></p>
    <button id="confirmFav" style="margin-top:10px; padding:5px 10px; cursor:pointer;">Add to Favourites</button>
    <button id="closeFav" style="margin-top:10px; margin-left:10px; padding:5px 10px; cursor:pointer;">Cancel</button>
</div>

<script src="../index.js"></script>

<script>
let currentPage = 1;
let loading = false;

function loadMovies(page){
    if(loading) return;
    loading = true;
    document.getElementById('loader').style.display = 'block';

    fetch(`completed_movies_loader.php?page=${page}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById('movies-container').insertAdjacentHTML('beforeend', html);
            document.getElementById('loader').style.display = 'none';
            loading = false;

            const playButtons = document.querySelectorAll('.icon-btn.play');
            const videoModal = document.getElementById('videoModal');
            const trailerIframe = document.getElementById('trailerIframe');
            const closeModal = document.getElementById('closeModal');

            playButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const videoUrl = btn.getAttribute('data-video');
                    if(videoUrl){
                        trailerIframe.src = videoUrl;
                        trailerIframe.style.display = 'block';
                        videoModal.classList.add('active');
                    }
                });
            });

            closeModal.addEventListener('click', () => {
                trailerIframe.src = '';
                trailerIframe.style.display = 'none';
                videoModal.classList.remove('active');
            });

            videoModal.addEventListener('click', (e) => {
                if(e.target === videoModal){
                    trailerIframe.src = '';
                    trailerIframe.style.display = 'none';
                    videoModal.classList.remove('active');
                }
            });
        });
}

// Initial load
loadMovies(currentPage);

// Delete movie
document.addEventListener("click", function(e){
    if(e.target.closest(".icon-btn.delete")){
        const btn = e.target.closest(".icon-btn.delete");
        const movieId = btn.dataset.movieId;
        const card = btn.closest(".featured-trailer");

        if(confirm("Remove this movie?")){
            fetch('delete_completed_movie.php', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({movie_id: movieId})
            })
            .then(res => res.json())
            .then(data => {
                if(data.success){
                    card.remove();
                    alert("✅ " + data.message);
                } else {
                    alert("❌ " + data.message);
                }
            })
            .catch(err => console.error(err));
        }
    }
});
document.addEventListener("DOMContentLoaded", () => {
    const profilePic = document.querySelector(".profile-pic");
    const dropdown = document.querySelector(".profile-dropdown");

    if (profilePic && dropdown) {
        profilePic.addEventListener("click", () => {
            dropdown.classList.toggle("active");
        });

        // Optional: close dropdown when clicking outside
        document.addEventListener("click", (e) => {
            if (!profilePic.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.remove("active");
            }
        });
    }
});

// Heart button click for favourites
document.addEventListener("click", function(e){
    if(e.target.closest(".icon-btn.heart")){
        const btn = e.target.closest(".icon-btn.heart");
        const movieId = btn.dataset.movieId;

        fetch('get_completed_id.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ movie_id: movieId })
        })
        .then(res => res.json())
        .then(data => {
            if(!data.success){
                alert(data.message);
                return;
            }

            const { completed_id, user_id, movie_id, already_favorited } = data;
            const popup = document.getElementById('favPopup');
            let message = `Completed ID: ${completed_id}\nUser ID: ${user_id}\nMovie ID: ${movie_id}`;

            if(already_favorited){
                message += "\n\n💖 Already in favourites!";
                document.getElementById('confirmFav').style.display = 'none';
            } else {
                message += "\n\nYou can add this movie to favourites.";
                document.getElementById('confirmFav').style.display = 'inline-block';
            }

            document.getElementById('favInfo').innerText = message;
            popup.style.display = 'block';

            document.getElementById('confirmFav').onclick = () => {
                fetch('favourite-movie.php', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({ completed_id, movie_id })
                })
                .then(res => res.json())
                .then(favData => {
                    alert(favData.message);
                    if(favData.success) btn.classList.add("favorited");
                    popup.style.display = 'none';
                });
            };

            document.getElementById('closeFav').onclick = () => {
                popup.style.display = 'none';
            };
        })
        .catch(err => console.error(err));
    }
});
</script>
</body>
</html>
