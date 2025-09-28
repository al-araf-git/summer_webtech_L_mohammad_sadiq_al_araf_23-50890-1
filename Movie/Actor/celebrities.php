<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Authentication/login.html");
    exit;
}

// Fetch logged-in user's profile picture from database
$userProfilePic = "../assets/img/user.jpeg"; // default
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
    <title>Movie Point - Celebrities</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../global.css">
    <style>
        #loader { display:none; text-align:center; margin:20px; }
        .loader-spinner {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #7e3ff2;
            border-radius: 50%;
            width: 40px; height: 40px;
            animation: spin 1s linear infinite;
            margin:auto;
        }
        @keyframes spin { 100% { transform: rotate(360deg); } }

        /* Profile Dropdown */
        .profile-dropdown {
    display: none;
    position: absolute;
    top: 60px; /* adjust if needed */
    right: 0;
    background: #1a1c29; /* changed to dark theme */
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    border-radius: 10px;
    overflow: hidden;
    z-index: 1000;
}

.profile-dropdown.active {
    display: block;
}

.profile-dropdown a {
    display: block;
    padding: 10px 20px;
    color: #fff; /* text color white for dark background */
    text-decoration: none;
}

.profile-dropdown a:hover {
    background-color: #333; /* hover color stays darker */
}

    </style>
</head>
<body>
<div>
    <div class="background-image" style="margin-top:-100px;">
        <!-- Header -->
        <header class="site-header">
            <div class="logo">🎥 <span>Movie Point</span></div>
            <nav class="nav-links">
                <a href="../index.php">Home</a>
                <a href="../Movies/movies.php">Movies</a>
                <a href="Celebrities.php">CelebritiesList</a>
            </nav>
            <div class="search-login" style="position: relative;">
                <form id="actorSearchForm" onsubmit="return false;" style="display:flex; gap:5px;">
                    <input type="text" id="actorSearch" placeholder="Search actors..." style="padding:5px;">
                    <button type="submit" style="padding:5px 10px; cursor:pointer;">Search</button>
                </form>

                <!-- Profile Picture & Dropdown -->
                <div class="profile-pic-wrapper" id="profileWrapper" style="cursor:pointer;">
                    <img src="<?= htmlspecialchars($userProfilePic) ?>" alt="Profile" class="profile-pic">
                </div>
                <div class="profile-dropdown" id="profileDropdown">
                    <a href="../Profile/profile.php">Profile</a>
                    <a href="../Authentication/logout.php">Logout</a>
                    <a href="../Completed-Movies/completed-movies.php">Completed Movies</a>
                    <a href="../Favourite-Movies/favourite-movies.php">Favourite Movies</a>
                </div>
            </div>
        </header>

        <div class="headlines" style="margin-top:50px;">
            <h3 style="font-size:80px; display:flex; justify-content:center; margin-top:100px;">Actors</h3>
        </div>
    </div>

    <div class="headlines">
        <h3>Popular Actors</h3>
        <hr>
        <div id="actors-container" style="display:flex; flex-wrap:wrap; gap:30px; justify-content:center;"></div>
        <div id="loader"><div class="loader-spinner"></div></div>
    </div>
</div>

<script>
let currentPage = 1;
let loading = false;

function loadActors(page){
    if(loading) return;
    loading = true;
    document.getElementById('loader').style.display = 'block';

    fetch(`actors_loader.php?page=${page}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById('actors-container').insertAdjacentHTML('beforeend', html);
            document.getElementById('loader').style.display = 'none';
            loading = false;
        });
}

// initial load
loadActors(currentPage);

// infinite scroll
window.addEventListener('scroll', () => {
    if(window.innerHeight + window.scrollY >= document.body.offsetHeight - 100){
        currentPage++;
        loadActors(currentPage);
    }
});

// Actor search
document.getElementById('actorSearchForm').addEventListener('submit', function(e){
    e.preventDefault();
    const query = document.getElementById('actorSearch').value.trim();
    if(!query) return;

    document.getElementById('actors-container').innerHTML = "<p style='color:white;'>Searching...</p>";

    fetch(`search_actor.php?q=${encodeURIComponent(query)}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById('actors-container').innerHTML = html;
        });
});

// Profile dropdown toggle
const profileWrapper = document.getElementById('profileWrapper');
const profileDropdown = document.getElementById('profileDropdown');

profileWrapper.addEventListener('click', () => {
    profileDropdown.classList.toggle('active');
});

// Close dropdown if clicked outside
document.addEventListener('click', (e) => {
    if (!profileWrapper.contains(e.target) && !profileDropdown.contains(e.target)) {
        profileDropdown.classList.remove('active');
    }
});
</script>
</body>
</html>
