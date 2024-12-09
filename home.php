<?php
session_start();
include('db.php'); // Include the database connection

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: sign_in.php');
    exit();
}

// Handle form submission for creating a new post (text only)
if (isset($_POST['create_post'])) {
    $post_content = $_POST['post_content'];
    $user_id = $_SESSION['user_id'];

    // Insert the new post into the database
    $insert_post = "INSERT INTO posts (user_id, content, created_at) VALUES (:user_id, :content, NOW())";
    $stmt = $pdo->prepare($insert_post);
    $stmt->execute([
        'user_id' => $user_id, 
        'content' => $post_content
    ]);
}

// Fetch all posts
$sql = "SELECT p.id AS post_id, p.content AS post_content, p.created_at AS post_created_at, 
               u.id AS user_id, u.email, u.profile_picture 
        FROM posts p 
        INNER JOIN users u ON p.user_id = u.id
        ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$posts_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

  <!-- Feed Section -->
  <div class="feed">
    <?php foreach ($posts_result as $post) { ?>
      <div class="post">
        <div class="post-header">
          <img src="<?= $post['profile_picture'] ?>" alt="User" class="post-avatar">
          <div class="post-info">
            <strong><?= $post['email'] ?></strong>
            <p class="timestamp"><?= $post['post_created_at'] ?></p>
          </div>
        </div>
        <p class="post-content"><?= $post['post_content'] ?></p>
        <div class="post-actions">
          <button class="like-btn">❤️</button>
          <button class="comment-btn">💬</button>
          <button class="share-btn">🔄</button>
        </div>
      </div>
    <?php } ?>
  </div>

  <!-- Footer Section -->
  <footer>
    <a href="home.php"><button>🏠</button></a>
    <a href="search.php"><button>🔍</button></a>
    <a href="create_post.php"><button id="createPostBtn">✍️</button></a>
    <a href="profile.php"><button>👤</button></a>
  </footer>

  <script src="home.js"></script>
</body>
</html>
