<?php
session_start();
include('db.php'); // Include the database connection

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: sign_in.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$default_profile_picture = 'uploads/default_profile.png'; // Default profile picture path

// Handle form submission for updating the profile picture
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove_profile_picture'])) {
        // Remove profile picture (set to default)
        $update_query = "UPDATE users SET profile_picture = :profile_picture WHERE id = :user_id";
        $stmt = $pdo->prepare($update_query);
        $stmt->execute(['profile_picture' => $default_profile_picture, 'user_id' => $user_id]);

        $_SESSION['success_message'] = "Profile picture removed successfully!";
        header('Location: edit_profile.php');
        exit();
    }

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['profile_picture']['tmp_name'];
        $file_name = basename($_FILES['profile_picture']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_exts)) {
            // Generate a unique file name
            $new_file_name = 'profile_' . time() . '_' . $user_id . '.' . $file_ext;
            $upload_dir = 'uploads/';
            $file_destination = $upload_dir . $new_file_name;

            // Move uploaded file to the destination directory
            if (move_uploaded_file($file_tmp, $file_destination)) {
                // Update the user's profile picture in the database
                $update_query = "UPDATE users SET profile_picture = :profile_picture WHERE id = :user_id";
                $stmt = $pdo->prepare($update_query);
                $stmt->execute(['profile_picture' => $file_destination, 'user_id' => $user_id]);

                $_SESSION['success_message'] = "Profile picture updated successfully!";
                header('Location: edit_profile.php');
                exit();
            } else {
                $error_message = "Failed to upload the file. Please try again.";
            }
        } else {
            $error_message = "Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.";
        }
    } else {
        $error_message = "Please select a valid image file.";
    }
}

// Fetch current user details
$user_query = "SELECT profile_picture FROM users WHERE id = :user_id";
$stmt = $pdo->prepare($user_query);
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="ccs/edit_profile.css">
</head>
<body>
    <h2>Edit Profile</h2>

    <!-- Display success or error message -->
    <?php if (isset($error_message)): ?>
        <p style="color: red;"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['success_message'])): ?>
        <p style="color: green;"><?= htmlspecialchars($_SESSION['success_message']) ?></p>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <!-- Profile Picture Preview -->
    <div class="profile-picture">
        <img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile Picture" width="150" height="150">
    </div>

    <!-- Form to Upload New Profile Picture -->
    <form method="POST" enctype="multipart/form-data">
        <label for="profile_picture">Choose a new profile picture:</label><br>
        <input type="file" name="profile_picture" id="profile_picture" accept="image/*" required><br><br>
        <button type="submit">Save Changes</button>
    </form>

    <!-- Remove Profile Picture -->
    <form method="POST" style="margin-top: 20px;">
        <button type="submit" name="remove_profile_picture" style="background-color: red; color: white; border: none; padding: 10px;">Remove Profile Picture</button>
    </form>

    <br>
    <a href="timeline.php?user_id=<?= $user_id ?>">Back to Timeline</a>
</body>
</html>
