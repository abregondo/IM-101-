<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $profile_picture = $_FILES['profile_picture']['name']; // Profile picture (if uploaded)

    // Check if passwords match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Hash the password for secure storage
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if the email is already registered
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $error = "Email is already registered.";
        } else {
            // Check if profile picture is uploaded and move it to a folder
            if ($profile_picture) {
                $target_dir = "uploads/";
                $target_file = $target_dir . basename($profile_picture);
                move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file);
            } else {
                $target_file = null; // If no profile picture, set to null
            }

            // Insert the new user into the database
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, profile_picture) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password, $target_file]);

            // Redirect to the sign-in page after successful registration
            header('Location: sign_in.php');
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Account</title>
    <style>
        /* Your existing CSS goes here */
    </style>
</head>
<body>

<form action="create_account.php" method="POST" enctype="multipart/form-data">
    <h2>Create Account</h2>
    <?php if (isset($error)) echo "<p>$error</p>"; ?>

    <div class="form-group">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" required>
    </div>

    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" required>
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
    </div>

    <div class="form-group">
        <label for="confirm_password">Confirm Password</label>
        <input type="password" name="confirm_password" id="confirm_password" required>
    </div>

    <div class="form-group">
        <label for="profile_picture">Profile Picture</label>
        <input type="file" name="profile_picture" id="profile_picture" accept="image/*">
    </div>

    <button type="submit">Create Account</button>

    <div class="create-account">
        <p>Already have an account? <a href="sign_in.php">Sign In</a></p>
    </div>
</form>

</body>
</html>
