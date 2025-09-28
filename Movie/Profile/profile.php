<?php
session_start();
require '../config/config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
    <link rel="stylesheet" href="../global.css">
    <style>
        .profile-container {
            width: 400px;
            margin: 120px auto;
            padding: 25px;
            border-radius: 12px;
            background: #1e202b;
            box-shadow: 0 10px 25px rgba(0,0,0,0.6);
            color: white;
            text-align: center;
        }
        .profile-container input {
            width: 85%;              /* reduce width so left/right margins are visible */
            padding: 10px;
            margin: 12px auto;       /* top-bottom 12px, auto left-right */
            display: block;          /* ensure margin:auto works */
            border-radius: 8px;
            border: 1px solid #444;
            background: #13151f;
            color: white;
            box-sizing: border-box;  /* keeps padding inside the width */
        }

        .profile-container button {
            padding: 8px 16px;
            margin: 8px 5px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .edit-btn { background: #7e3ff2; color: #fff; }
        .save-btn { background: #7e3ff2; color: #fff; }
        .cancel-btn { background: #7e3ff2; color: #fff; }
        #uploadBtn { display: none; margin-top: 10px; }
        .profile-pic {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #7e3ff2;
            margin-bottom: 15px;
        }
        
        html,
        body {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            background-color: #13151f;
            font-family: 'MyFont', sans-serif;
        }

        @font-face {
            font-family: 'MyFont';
            src: url('assets/fonts/icofont7858.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        .background {
            content: "";
            position: absolute;
            width: 100%;
            height: 100%;
            background: url("../assets/img/breadcrumb.png"), #000;
            background-position: center;
            background-size: cover;
        }

        /* Header profile picture */
/* Header profile picture */
.profile-pic-wrapper img {
    width: 40px;          /* desired size */
    height: 40px;
    border-radius: 50%;   /* makes it circular */
    object-fit: cover;    /* fills the circle, cropping if necessary */
    object-position: center; /* centers the image */
    border: 2px solid #7e3ff2; /* optional border */
}


    </style>
</head>
<body>
    
    <div class="background">
<header class="site-header">
    <div class="logo">🎥 <span>Movie Point</span></div>
    <nav class="nav-links">
                <a href="../index.php">Home</a>
                <a href="../Movies/movies.php">Movies</a>
                <a href="../Actor/celebrities.php">CelebritiesList</a>
    </nav>
    <div class="search-login">
<div class="profile-pic-wrapper">
    <img src="<?php echo $user['profile_pic'] ? $user['profile_pic'] : '../assets/img/default.png'; ?>" 
         alt="Profile">
</div>

        <div class="profile-dropdown">
                        <a href="../Profile/profile.php">Profile</a>
                        <a href="../Completed-Movies/completed-movies.php">Completed Movies</a>
                        <a href="../Favourite-Movies/favourite-movies.php">Favourite Movies</a>
                        <a href="../Authentication/logout.php">Logout</a>
                    
        </div>
    </div>
</header>

    <div class="profile-container">
        <form id="profileForm" action="update_profile.php" method="POST" enctype="multipart/form-data">
            <h1>Profile</h3>
            <img src="<?php echo $user['profile_pic'] ? $user['profile_pic'] : 'assets/img/default.png'; ?>" 
                 class="profile-pic" id="profilePreview"><br>

            <input type="file" name="profile_pic" id="uploadBtn" accept="image/*" onchange="previewImage(event)"><br>

            <!-- Only Full Name is editable -->
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" disabled>

            <!-- Email stays uneditable -->
            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>

            <div id="buttonSection">
                <button type="button" class="edit-btn" onclick="enableEdit()">Edit</button>
            </div>
        </form>
    </div>
    </div>


    <script>
        function enableEdit(){
            document.querySelector("input[name='full_name']").disabled = false;
            document.getElementById("uploadBtn").style.display = "block";
            document.getElementById("buttonSection").innerHTML = `
                <button type="submit" class="save-btn">Save</button>
                <button type="button" class="cancel-btn" onclick="cancelEdit()">Cancel</button>
            `;
        }

        function cancelEdit(){
            window.location.reload();
        }

        function previewImage(event){
            const reader = new FileReader();
            reader.onload = function(){
                document.getElementById("profilePreview").src = reader.result;
            }
            reader.readAsDataURL(event.target.files[0]);
        }

        const profilePic = document.querySelector('.profile-pic-wrapper img');
const profileDropdown = document.querySelector('.profile-dropdown');

profilePic.addEventListener('click', () => {
    profileDropdown.classList.toggle('active');
});

// Optional: hide dropdown if clicked outside
document.addEventListener('click', (e) => {
    if (!profileDropdown.contains(e.target) && !profilePic.contains(e.target)) {
        profileDropdown.classList.remove('active');
    }
});

    </script>
</body>
</html>
