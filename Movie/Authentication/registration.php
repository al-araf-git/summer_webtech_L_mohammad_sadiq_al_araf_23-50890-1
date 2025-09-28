<?php
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../api/firebase.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: registration.html");
    exit;
}

$fullname = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$confirm_password = trim($_POST['confirm_password'] ?? '');

// Step 1: Validate inputs
if (empty($fullname) || empty($email) || empty($password) || empty($confirm_password)) {
    echo "<script>alert('❌ All fields are required'); window.history.back();</script>";
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('❌ Invalid email format'); window.history.back();</script>";
    exit;
}

if (strlen($password) < 6) {
    echo "<script>alert('❌ Password must be at least 6 characters long'); window.history.back();</script>";
    exit;
}

if ($password !== $confirm_password) {
    echo "<script>alert('❌ Passwords do not match!'); window.history.back();</script>";
    exit;
}

// Step 2: Create Firebase account
$result = firebaseSignUp($email, $password, $FIREBASE_API_KEY);

if (isset($result['error'])) {
    $error = htmlspecialchars($result['error']['message'], ENT_QUOTES, 'UTF-8');
    echo "<script>alert('❌ Firebase Error: $error'); window.history.back();</script>";
    exit;
}

$firebaseUid = $result['localId'] ?? null;

if (!$firebaseUid) {
    echo "<script>alert('❌ Failed to create Firebase account.'); window.history.back();</script>";
    exit;
}

// Step 3: Save extra info in MySQL
$type = "user";
$profile_pic = "../assets/img/user.jpeg";

$stmt = $conn->prepare("INSERT INTO users (id, full_name, email, type, profile_pic) VALUES (?, ?, ?, ?, ?)");
if (!$stmt) {
    error_log("MySQL prepare failed: " . $conn->error);
    echo "<script>alert('❌ Database error. Try again later.'); window.history.back();</script>";
    exit;
}

$stmt->bind_param("sssss", $firebaseUid, $fullname, $email, $type, $profile_pic);

if ($stmt->execute()) {
    echo "<script>
            alert('✅ User registered successfully!');
            window.location.href = 'login.html';
          </script>";
} else {
    $dberror = htmlspecialchars($stmt->error, ENT_QUOTES, 'UTF-8');
    echo "<script>alert('❌ Database Error: $dberror'); window.history.back();</script>";
}

$stmt->close();
$conn->close();
