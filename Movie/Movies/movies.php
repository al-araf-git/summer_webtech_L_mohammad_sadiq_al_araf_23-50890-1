<?php
session_start();
require '../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Authentication/login.html");
    exit;
}

$userId = $_SESSION['user_id'];

$loggedIn = isset($_SESSION['user_id']); // Check if user is logged in
$userProfilePic = ""; // Leave empty by default

if ($loggedIn) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!empty($user['profile_pic'])) {
        // Make sure the path is correct relative to this file
        $userProfilePic = "../Profile/" . $user['profile_pic'];
    }
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
        #loader { display:none; text-align:center; margin:20px; }
        .loader-spinner { border:5px solid #f3f3f3; border-top:5px solid #7e3ff2; border-radius:50%; width:40px; height:40px; animation:spin 1s linear infinite; margin:auto; }
        @keyframes spin { 100% { transform: rotate(360deg); } }

        /* Profile dropdown (click-to-open) */
        .profile-menu {
            position: relative;
            display: inline-block;
        }
        .profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
        }
        .profile-dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 50px;
            background-color: #1a1c29;
            min-width: 160px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.5);
            border-radius: 8px;
            z-index: 100;
        }
        .profile-dropdown.show {
            display: block;
        }
        .profile-dropdown a {
            color: white;
            padding: 10px;
            text-decoration: none;
            display: block;
        }
        .profile-dropdown a:hover {
            background-color: #333;
        }
    </style>
</head>
<body>
<div>
    <div class="background-image" style="margin-top: -100px;">
        <header class="site-header">
            <div class="logo">🎥 <span>Movie Point</span></div>
            <nav class="nav-links">
                <a href="../index.php">Home</a>
                <a href="movies.php" class="active">Movies</a>
                <a href="../Actor/celebrities.php">CelebritiesList</a>
            </nav>
            <div class="search-login">
                <form id="movieSearchForm" onsubmit="return false;" style="display:flex; gap:5px;">
                    <input type="text" id="movieSearch" placeholder="Search movies..." style="padding:5px;">
                    <button type="submit" style="padding:5px 10px; cursor:pointer;">Search</button>
                </form>

                <?php if ($loggedIn && !empty($userProfilePic)): ?>
                <div class="profile-menu">
                    <div class="profile-pic-wrapper">
                        <img src="<?= htmlspecialchars($userProfilePic) ?>" alt="Profile" class="profile-pic">
                    </div>
                    <div class="profile-dropdown">
                        <a href="../Profile/profile.php">Profile</a>
                        <a href="../Completed-Movies/completed-movies.php">Completed Movies</a>
                        <a href="../Favourite-Movies/favourite-movies.php">Favourite Movies</a>
                        <a href="../Authentication/logout.php">Logout</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </header>

        <div class="headlines" style="margin-top:50px;">
            <h3 style="font-size: 80px; display:flex; justify-content:center; margin-top:100px;">MOVIES</h3>
        </div>
    </div>

    <div class="headlines">
        <h3>All Movies</h3>
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

<script src="../index.js"></script>
<script>
let currentPage = 1;
let loading = false;

function loadMovies(page){
    if(loading) return;
    loading = true;
    document.getElementById('loader').style.display = 'block';

    fetch(`movies_loader.php?page=${page}`)
    .then(res => res.json())
    .then(data => {
        const container = document.getElementById('movies-container');
        data.movies.forEach(movie => {
            const div = document.createElement('div');
            div.className = 'featured-trailer';
            div.style = "width:220px; margin-bottom:40px; background-color:#1a1c29; border-radius:10px; overflow:hidden; box-shadow:0 5px 15px rgba(0,0,0,0.5);";

            div.innerHTML = `
                <img src="${movie.poster}" alt="${movie.title}" style="width:100%; height:330px; object-fit:cover;">
                <div style="padding:10px; color:white;">
                    <h4 style="margin:5px 0; font-size:16px;">${movie.title}</h4>
                    <p style="font-size:12px; color:#ccc; height:50px; overflow:hidden; text-overflow:ellipsis; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical;">${movie.overview}</p>
                    <div class="rating-heaings" style="font-size:12px; margin-top:5px; white-space:nowrap;">
                        ${'⭐'.repeat(Math.round(movie.vote_average/2))}
                        <span style="margin-left:5px;">${movie.vote_count} voters</span>
                    </div>
                </div>
                <div class="action-buttons" style="display:flex; gap:5px; margin:10px;">
                    ${movie.trailer ? `<button class="icon-btn play" data-video="${movie.trailer}"><span>▶</span></button>` : ''}
                    <button class="icon-btn tick" data-movie-id="${movie.id}" ${movie.completed ? 'disabled' : ''}><span>✔</span></button>
                </div>
            `;
            container.appendChild(div);
        });

        document.getElementById('loader').style.display = 'none';
        loading = false;

        // Play button
        const playButtons = document.querySelectorAll('.icon-btn.play');
        const videoModal = document.getElementById('videoModal');
        const trailerIframe = document.getElementById('trailerIframe');
        const closeModal = document.getElementById('closeModal');

        playButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                trailerIframe.src = btn.dataset.video;
                trailerIframe.style.display = 'block';
                videoModal.classList.add('active');
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

        // Tick button with loader
        document.querySelectorAll('.icon-btn.tick').forEach(btn => {
            btn.addEventListener('click', () => {
                const movieId = btn.dataset.movieId;

                const spinner = document.createElement('span');
                spinner.className = 'loader-spinner';
                spinner.style.width = '20px';
                spinner.style.height = '20px';
                spinner.style.borderWidth = '3px';
                btn.appendChild(spinner);
                btn.disabled = true;

                fetch('save_completed_movie.php', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({movie_id: movieId})
                })
                .then(res => res.json())
                .then(data => {
                    btn.removeChild(spinner);
                    if(data.success){
                        alert('✅ ' + data.message);
                        btn.disabled = true;
                    } else {
                        alert('❌ ' + data.message);
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    btn.removeChild(spinner);
                    alert('❌ Error: ' + err.message);
                    btn.disabled = false;
                });
            });
        });
    });
}

// Initial load
loadMovies(currentPage);

// Infinite scroll
window.addEventListener('scroll', () => {
    if(window.innerHeight + window.scrollY >= document.body.offsetHeight - 500){
        currentPage++;
        loadMovies(currentPage);
    }
});

// Movie search
document.getElementById('movieSearchForm').addEventListener('submit', function(e){
    e.preventDefault();
    const query = document.getElementById('movieSearch').value.trim();
    if(!query) return;

    const container = document.getElementById('movies-container');
    container.innerHTML = "<p style='color:white;'>Searching...</p><br>";

    fetch(`search_movies.php?q=${encodeURIComponent(query)}`)
        .then(res => res.text())
        .then(html => {
            container.innerHTML = html;

            const playButtons = document.querySelectorAll('.icon-btn.play');
            const videoModal = document.getElementById('videoModal');
            const trailerIframe = document.getElementById('trailerIframe');
            const closeModal = document.getElementById('closeModal');

            playButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    trailerIframe.src = btn.dataset.video;
                    trailerIframe.style.display = 'block';
                    videoModal.classList.add('active');
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
});

// Profile dropdown toggle (click)
document.addEventListener("DOMContentLoaded", () => {
    const profilePic = document.querySelector(".profile-pic");
    const dropdown = document.querySelector(".profile-dropdown");

    if (profilePic && dropdown) {
        profilePic.addEventListener("click", (e) => {
            e.stopPropagation();
            dropdown.classList.toggle("show");
        });

        // Close dropdown if clicked outside
        document.addEventListener("click", () => {
            dropdown.classList.remove("show");
        });
    }
});
</script>
</body>
</html>
