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

// Delete Post Functionality
if (isset($_POST['delete_post'])) {
    $post_id = $_POST['post_id'];
    $user_id = $_SESSION['user_id'];

    // Check if the logged-in user is the one who posted the post
    $check_post_owner = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
    $check_post_owner->execute([$post_id, $user_id]);

    // If the user is the owner of the post or if an admin is deleting the post
    if ($check_post_owner->rowCount() > 0 || $_SESSION['is_admin']) {
        // Delete the post from the database
        $delete_post_stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $delete_post_stmt->execute([$post_id]);

        echo "<p>Post deleted successfully.</p>";
    } else {
        echo "<p>You cannot delete this post.</p>";
    }
}

// Delete Comment Functionality
if (isset($_POST['delete_comment'])) {
    $comment_id = $_POST['comment_id'];
    $user_id = $_SESSION['user_id'];

    // Check if the logged-in user is the one who posted the comment
    $check_comment_owner = $pdo->prepare("SELECT * FROM comments WHERE id = ? AND user_id = ?");
    $check_comment_owner->execute([$comment_id, $user_id]);

    // If the user is the owner of the comment or if you want to allow admins to delete comments
    if ($check_comment_owner->rowCount() > 0 || $_SESSION['is_admin']) {
        // Delete the comment
        $delete_comment_stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
        $delete_comment_stmt->execute([$comment_id]);

        echo "<p>Comment deleted successfully.</p>";
    } else {
        echo "<p>You cannot delete this comment.</p>";
    }
}

// Fetch all posts and associated comments
$sql = "SELECT p.id AS post_id, p.content AS post_content, p.created_at AS post_created_at, 
               u.id AS user_id, u.email, u.profile_picture 
        FROM posts p 
        INNER JOIN users u ON p.user_id = u.id
        ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$posts_result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch users for the follow functionality
$sql = "SELECT id, username, email FROM users";
$users = $pdo->query($sql)->fetchAll();

// Fetch the users the logged-in user is following
$sql = "SELECT u.id, u.username, u.email FROM users u 
        INNER JOIN follows f ON u.id = f.following_id 
        WHERE f.follower_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$following_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            <strong><?= $post['email'] ?></strong> <!-- Displaying email instead of username -->
            <p class="timestamp"><?= $post['post_created_at'] ?></p>
          </div>
        </div>
        <p class="post-content"><?= $post['post_content'] ?></p>
        <div class="post-actions">
          <button class="like-btn" onclick="likePost(<?= $post['post_id'] ?>)">❤️</button>
          <button class="comment-btn" onclick="toggleCommentSection(<?= $post['post_id'] ?>)">💬</button>
          <button class="share-btn">🔄</button>
          <!-- Only show delete button if the logged-in user is the post author or an admin -->
          <?php if ($post['user_id'] == $_SESSION['user_id'] || $_SESSION['is_admin']) { ?>
            <form method="POST" action="home.php" style="display:inline;">
              <input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
              <button type="submit" name="delete_post" onclick="return confirm('Are you sure you want to delete this post?')">Delete Post</button>
            </form>
          <?php } ?>
        </div>
        <div class="post-stats">
          <p id="likeCount<?= $post['post_id'] ?>">0 Likes</p>
          <p id="commentCount<?= $post['post_id'] ?>">0 Comments</p>
        </div>
        <div class="comment-section" id="commentSection<?= $post['post_id'] ?>" style="display:none;">
          <textarea id="commentInput<?= $post['post_id'] ?>" placeholder="Add a comment..."></textarea>
          <button onclick="postComment(<?= $post['post_id'] ?>)">Post Comment</button>
          <div id="commentsDisplay<?= $post['post_id'] ?>">
            <?php 
            // Fetch comments for this post
            $comment_sql = "SELECT c.id AS comment_id, c.content AS comment_content, c.created_at AS comment_created_at, 
                               u.username 
                            FROM comments c 
                            INNER JOIN users u ON c.user_id = u.id 
                            WHERE c.post_id = ?";
            $comment_stmt = $pdo->prepare($comment_sql);
            $comment_stmt->execute([$post['post_id']]);
            $comments = $comment_stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($comments as $comment) {
              ?>
              <div class="comment">
                <strong><?= $comment['username'] ?>:</strong> <?= $comment['comment_content'] ?>
                <!-- Show delete button for comment if user is the owner or admin -->
                <?php if ($comment['user_id'] == $_SESSION['user_id'] || $_SESSION['is_admin']) { ?>
                  <form method="POST" action="home.php" style="display:inline;">
                    <input type="hidden" name="comment_id" value="<?= $comment['comment_id'] ?>">
                    <button type="submit" name="delete_comment" onclick="return confirm('Are you sure you want to delete this comment?')">Delete</button>
                  </form>
                <?php } ?>
              </div>
              <?php
            }
            ?>
          </div>
        </div>
      </div>
    <?php } ?>
  </div>

  <footer>
    <button>🏠</button>
    <button>🔍</button>
    <button id="createPostBtn">✍️</button>
    <button>👤</button>
  </footer>

  <script src="home.js"></script>
  <script>
    function likePost(postId) {
      fetch('home.php', {
        method: 'POST',
        body: new URLSearchParams({
          'like_post_id': postId
        })
      }).then(response => response.json()).then(data => {
        // Update like count dynamically
        document.getElementById('likeCount' + postId).innerText = data.likes + ' Likes';
      });
    }

    function postComment(postId) {
      const commentContent = document.getElementById('commentInput' + postId).value;
      fetch('home.php', {
        method: 'POST',
        body: new URLSearchParams({
          'comment_post_id': postId,
          'comment_content': commentContent
        })
      }).then(response => response.json()).then(data => {
        // Display new comment
        const commentDiv = document.createElement('div');
        commentDiv.innerHTML = data.username + ": " + data.comment;
        document.getElementById('commentsDisplay' + postId).appendChild(commentDiv);
      });
    }

    function toggleCommentSection(postId) {
      const commentSection = document.getElementById('commentSection' + postId);
      commentSection.style.display = commentSection.style.display === 'none' ? 'block' : 'none';
    }
  </script>
</body>
</html>
