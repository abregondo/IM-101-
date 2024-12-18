<?php
session_start();
include('db.php');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: sign_in.php');
    exit();
}

$user_id = $_GET['user_id'];  // Get the user_id from the URL

// Follow a user
if (isset($_POST['follow_user_id'])) {
    $follower_id = $_SESSION['user_id'];  // Logged-in user
    $following_id = $_POST['follow_user_id'];

    // Check if already following
    $stmt = $pdo->prepare("SELECT * FROM follows WHERE follower_id = ? AND following_id = ?");
    $stmt->execute([$follower_id, $following_id]);
    $existing_follow = $stmt->fetch();

    if (!$existing_follow) {
        // Insert the follow record
        $stmt = $pdo->prepare("INSERT INTO follows (follower_id, following_id) VALUES (?, ?)");
        $stmt->execute([$follower_id, $following_id]);
        echo "<p>Followed successfully!</p>";
    } else {
        echo "<p>You are already following this user.</p>";
    }
}

// Fetch the user's profile information
$stmt = $pdo->prepare("SELECT username, email, profile_picture FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_profile = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $user_profile['username'] ?>'s Profile</title>
  <link rel="stylesheet" href="ccs/home.css">
</head>
<body>
  <header>
    <h1><?= $user_profile['username'] ?>'s Profile</h1>
  </header>

  <!-- User Info -->
  <div class="profile-info">
    <img src="<?= $user_profile['profile_picture'] ?>" alt="User" class="profile-avatar">
    <h2><?= $user_profile['username'] ?> (<?= $user_profile['email'] ?>)</h2>
  </div>

  <!-- Follow/Unfollow Button -->
  <form method="POST" action="profile.php?user_id=<?= $user_id ?>">
    <input type="hidden" name="follow_user_id" value="<?= $user_id ?>">
    <button type="submit">Follow</button>
  </form>

  <!-- Posts of the User -->
  <div class="user-posts">
    <!-- Display the posts of the user here -->
  </div>
</body>
</html>
