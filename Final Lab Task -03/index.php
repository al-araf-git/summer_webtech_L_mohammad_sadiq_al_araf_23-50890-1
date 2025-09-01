<?php
session_start();

if (isset($_SESSION['email'])) {
    header("Location: dashboard.php");
    exit();
}

$hardcodedEmail = "test@gmail.com";
$hardcodedPassword = "123";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    }
    elseif (empty($password)) {
        $error = "Password is required!";
    }
    elseif ($email === $hardcodedEmail && $password === $hardcodedPassword) {
        $_SESSION['email'] = $email;
        setcookie("user", $email, time() + 3600, "/");
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST" action="">
        <label>Email:</label>
        <input type="email" name="email" required><br><br>

        <label>Password:</label>
        <input type="password" name="password" required><br><br>

        <input type="submit" value="Login">
    </form>
</body>
</html>
