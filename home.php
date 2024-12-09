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
          <img src="<?= htmlspecialchars($post['profile_picture']) ?>" alt="User" class="post-avatar">
          <div class="post-info">
            <strong><?= htmlspecialchars($post['email']) ?></strong>
            <p class="timestamp"><?= htmlspecialchars($post['post_created_at']) ?></p>
          </div>
          <?php if ($post['user_id'] === $_SESSION['user_id']) { ?>
          <!-- Three-dot menu -->
          <div class="post-menu">
            <button class="menu-btn">⋯</button>
            <div class="menu-options">
              <form method="POST" action="home.php" style="display:inline;">
                <input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
                <button type="submit" name="delete_post">Delete</button>
              </form>
              <button onclick="editPost(<?= $post['post_id'] ?>)">Edit</button>
            </div>
          </div>
          <?php } ?>
        </div>
        <p class="post-content"><?= htmlspecialchars($post['post_content']) ?></p>
        <div class="post-actions">
          <button class="like-btn" onclick="likePost(this)">❤️</button>
          <button class="comment-btn" onclick="toggleCommentSection(event)">💬</button>
          <button class="share-btn">🔄</button>
        </div>

        <div class="comment-section">
          <input type="text" id="commentInput" placeholder="Write a comment...">
          <button onclick="postComment(event)">Post Comment</button>
          <div id="commentsDisplay"></div>
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
