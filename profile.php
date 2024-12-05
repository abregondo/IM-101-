<?php
session_start();
include('db.php');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: sign_in.php');
    exit();
}

// Get the profile user ID from the URL parameter
$profile_user_id = $_GET['user_id'];

// Fetch user information (profile details)
$sql = "SELECT id, username, email, profile_picture FROM users WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$profile_user_id]);
$profile_user = $stmt->fetch();

// Fetch posts by the profile user
$sql = "SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$profile_user_id]);
$posts_result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Follow user functionality
if (isset($_POST['follow_user_id'])) {
    $follower_id = $_SESSION['user_id'];
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $profile_user['username'] ?>'s Profile</title>
  <link rel="stylesheet" href="profile.css">
</head>
<body>
  <header>
    <div class="header-left">
      <h1 class="app-name">Chattrix</h1>
    </div>
    <div class="header-right">
      <a href="notification.php"><button id="notifBtn">🔔</button></a>
      <a href="messages.php"><button id="msgBtn">💬</button></a>
    </div>
  </header>

  <!-- Profile Section -->
  <div class="profile">
    <img src="<?= $profile_user['profile_picture'] ?>" alt="User Profile Picture" class="profile-picture">
    <h2><?= $profile_user['username'] ?></h2>
    <p><?= $profile_user['email'] ?></p>

    <!-- Follow Button -->
    <?php if ($_SESSION['user_id'] != $profile_user['id']) { // Don't show follow button for logged-in user ?>
      <form method="POST" action="profile.php?user_id=<?= $profile_user_id ?>">
        <input type="hidden" name="follow_user_id" value="<?= $profile_user_id ?>">
        <button type="submit">Follow</button>
      </form>
    <?php } ?>
  </div>

  <!-- Feed Section -->
  <div class="feed">
    <?php foreach ($posts_result as $post) { ?>
      <div class="post">
        <div class="post-header">
          <img src="<?= $profile_user['profile_picture'] ?>" alt="User" class="post-avatar">
          <div class="post-info">
            <strong><?= $profile_user['username'] ?></strong> 
            <p class="timestamp"><?= $post['created_at'] ?></p>
          </div>
        </div>
        <p class="post-content"><?= $post['content'] ?></p>
      </div>
    <?php } ?>
  </div>

  <footer>
    <button>🏠</button>
    <button>🔍</button>
    <button id="createPostBtn">✍️</button>
    <button>👤</button>
  </footer>

  <script src="profile.js"></script>
</body>
</html>
