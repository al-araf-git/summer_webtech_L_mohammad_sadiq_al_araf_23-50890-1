<?php
session_start();
require '../config/config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$full_name = $_POST['full_name'];

// Handle profile picture upload
$profilePicPath = null;
if(isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0){
    $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png'];

    if(in_array($ext, $allowed)){
        $folder = "../Profile-Pictures/";
        if(!is_dir($folder)) mkdir($folder);

        // fetch email to generate filename
        $stmt = $conn->prepare("SELECT email FROM users WHERE id=?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $email = $res['email'];

        $fileName = $email . "." . $ext;
        $filePath = $folder . $fileName;

        move_uploaded_file($_FILES['profile_pic']['tmp_name'], $filePath);
        $profilePicPath = $filePath;
    }
}

// Update only full_name and profile_pic
if($profilePicPath){
    $stmt = $conn->prepare("UPDATE users SET full_name=?, profile_pic=? WHERE id=?");
    $stmt->bind_param("sss", $full_name, $profilePicPath, $user_id);
} else {
    $stmt = $conn->prepare("UPDATE users SET full_name=? WHERE id=?");
    $stmt->bind_param("ss", $full_name, $user_id);
}
$stmt->execute();

header("Location: profile.php");
exit;
?>
