<?php
session_start();
include('db.php'); // Include the database connection

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: sign_in.php');
    exit();
}

// Handle form submission for creating a new post
if (isset($_POST['create_post'])) {
    $post_content = $_POST['post_content'];
    $user_id = $_SESSION['user_id'];

    // Check if the post already exists (to prevent duplicates)
    $check_post = $pdo->prepare("SELECT * FROM posts WHERE content = :content AND user_id = :user_id");
    $check_post->execute(['content' => $post_content, 'user_id' => $user_id]);

    if ($check_post->rowCount() == 0) {
        // Insert the new post into the database if not a duplicate
        $insert_post = "INSERT INTO posts (user_id, content, created_at) VALUES (:user_id, :content, NOW())";
        $stmt = $pdo->prepare($insert_post);
        $stmt->execute(['user_id' => $user_id, 'content' => $post_content]);
    } else {
        echo "";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chattrix</title>
  <link rel="stylesheet" href="home.css">
</head>
<body>
  <!-- Header Section -->
  <header>
    <div class="header-left">
      <h1 class="app-name">Chattrix</h1>
    </div>
    <div class="header-right">
      <a href="notification.php"><button id="notifBtn">🔔</button></a>
      <a href="messages.php"><button id="msgBtn">💬</button></a>
    </div>
  </header>

  <!-- Create Post Section -->
  <div class="create-post-section">
    <form method="POST" action="home.php">
      <textarea name="post_content" placeholder="What's on your mind?" required></textarea>
      <button type="submit" name="create_post">Post</button>
    </form>
  </div>

  <!-- Footer Navigation -->
  <footer>
    <button>🏠</button>
    <button>🔍</button>
    <button id="createPostBtn">✍️</button>
    <button>👤</button>
  </footer>

  <script src="home.js"></script>
</body>
</html>
