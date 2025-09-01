<?php
session_start();

$message = "Not found";

if (!isset($_SESSION['email']) || !isset($_COOKIE['user'])) {
    echo "<h2>$message</h2>";
    exit();
}

$email = $_SESSION['email'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>
    <h2>Welcome, Test</h2>

    <p>Cookie set for: <?php echo htmlspecialchars($_COOKIE['user']); ?></p>

    <a href="logout.php">Logout</a>
</body>
</html>
