<?php
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../api/firebase.php';

session_start();

// Validate POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../Authentication/login.html");
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

// Step 1: Basic validation
if (empty($email) || empty($password)) {
    echo "<script>alert('❌ Please enter both email and password'); window.history.back();</script>";
    exit;
}

// Step 2: Login using Firebase
$result = firebaseLogin($email, $password, $FIREBASE_API_KEY);

if (isset($result['error'])) {
    $error = htmlspecialchars($result['error']['message'], ENT_QUOTES, 'UTF-8');
    echo "<script>alert('❌ Firebase Error: $error'); window.history.back();</script>";
    exit;
}

$firebaseUid = $result['localId'] ?? null;

if (!$firebaseUid) {
    echo "<script>alert('❌ Invalid Firebase response.'); window.history.back();</script>";
    exit;
}

// Step 3: Check user in MySQL
$stmt = $conn->prepare("SELECT type FROM users WHERE id = ?");
if (!$stmt) {
    error_log("MySQL prepare failed: " . $conn->error);
    echo "<script>alert('❌ Database error. Please try again later.'); window.history.back();</script>";
    exit;
}

$stmt->bind_param("s", $firebaseUid);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo "<script>alert('❌ User not found in database'); window.history.back();</script>";
    $stmt->close();
    $conn->close();
    exit;
}

$row = $res->fetch_assoc();
$userType = $row['type'] ?? 'user'; // default fallback

// Step 4: Set session securely
session_regenerate_id(true); // prevent session fixation
$_SESSION['user_id'] = $firebaseUid;
$_SESSION['email'] = $email;
$_SESSION['user_type'] = $userType;

$stmt->close();
$conn->close();

// Step 5: Redirect based on user type
if ($userType === "admin") {
    header("Location: ../admin/admin.php");
} else {
    header("Location: ../index.php");
}
exit;
