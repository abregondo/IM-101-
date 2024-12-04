<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id']; // Set user session
        header('Location: home.php'); // Redirect to home page
        exit();
    } else {
        $error = "Invalid email or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign In</title>
    <link rel="stylesheet" href="sign_in.css">
</head>
<body>

<form action="sign_in.php" method="POST">
    <h2>Sign In</h2>
    <?php if (isset($error)) echo "<p>$error</p>"; ?>
    
    <label for="email">Email</label>
    <input type="email" name="email" id="email" required>

    <label for="password">Password</label>
    <input type="password" name="password" id="password" required>

    <button type="submit">Sign In</button>
</form>

<p>Don't have an account? <a href="create_account.php">Create Account</a></p> <!-- Create Account link -->

</body>
</html>
