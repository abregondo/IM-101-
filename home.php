<?php
session_start();
include('db.php'); // Include your PDO connection

// Fetch user posts
$sql = "SELECT p.*, u.username, u.profile_picture FROM posts p 
        INNER JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$posts_result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user's friends
$user_id = $_SESSION['user_id'];
$sql_friends = "SELECT * FROM friends WHERE user_id = :user_id AND status = 'accepted'";
$stmt_friends = $pdo->prepare($sql_friends);
$stmt_friends->execute(['user_id' => $user_id]);
$friends_result = $stmt_friends->fetchAll(PDO::FETCH_ASSOC);

// Add like functionality (AJAX request)
if (isset($_POST['like_post_id'])) {
    $post_id = $_POST['like_post_id'];
    $user_id = $_SESSION['user_id'];

    // Check if already liked
    $check_like = "SELECT * FROM likes WHERE post_id = :post_id AND user_id = :user_id";
    $stmt_check = $pdo->prepare($check_like);
    $stmt_check->execute(['post_id' => $post_id, 'user_id' => $user_id]);

    if ($stmt_check->rowCount() == 0) {
        // Add like
        $insert_like = "INSERT INTO likes (post_id, user_id) VALUES (:post_id, :user_id)";
        $stmt_insert = $pdo->prepare($insert_like);
        $stmt_insert->execute(['post_id' => $post_id, 'user_id' => $user_id]);
    }
}

// Add comment functionality (AJAX request)
if (isset($_POST['comment_post_id']) && isset($_POST['comment_content'])) {
    $post_id = $_POST['comment_post_id'];
    $content = $_POST['comment_content'];
    $user_id = $_SESSION['user_id'];

    // Add comment to the database
    $insert_comment = "INSERT INTO comments (post_id, user_id, content) VALUES (:post_id, :user_id, :content)";
    $stmt_comment = $pdo->prepare($insert_comment);
    $stmt_comment->execute(['post_id' => $post_id, 'user_id' => $user_id, 'content' => $content]);
}

// Add friend functionality (AJAX request)
if (isset($_POST['friend_id'])) {
    $friend_id = $_POST['friend_id'];
    $user_id = $_SESSION['user_id'];

    // Add friend request
    $add_friend = "INSERT INTO friends (user_id, friend_id, status) VALUES (:user_id, :friend_id, 'pending')";
    $stmt_add_friend = $pdo->prepare($add_friend);
    $stmt_add_friend->execute(['user_id' => $user_id, 'friend_id' => $friend_id]);
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

  <!-- Feed Section -->
  <div class="feed">
    <?php foreach ($posts_result as $post) { ?>
      <div class="post">
        <div class="post-header">
          <img src="<?= htmlspecialchars($post['profile_picture']) ?>" alt="User" class="post-avatar">
          <div class="post-info">
            <strong><?= htmlspecialchars($post['username']) ?></strong>
            <p class="timestamp"><?= htmlspecialchars($post['created_at']) ?></p>
          </div>
        </div>
        <p class="post-content"><?= htmlspecialchars($post['content']) ?></p>
        <div class="post-actions">
          <button class="like-btn" onclick="likePost(<?= $post['id'] ?>)">❤️</button>
          <button class="comment-btn" onclick="toggleCommentSection(<?= $post['id'] ?>)">💬</button>
          <button class="share-btn">🔄</button>
        </div>
        <div class="post-stats">
          <p id="likeCount<?= $post['id'] ?>">0 Likes</p>
          <p id="commentCount<?= $post['id'] ?>">0 Comments</p>
        </div>
        <div class="comment-section" id="commentSection<?= $post['id'] ?>" style="display:none;">
          <textarea id="commentInput<?= $post['id'] ?>" placeholder="Add a comment..."></textarea>
          <button onclick="postComment(<?= $post['id'] ?>)">Post Comment</button>
          <div id="commentsDisplay<?= $post['id'] ?>"></div>
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
      fetch('sign_in.php', {
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
      fetch('sign_in.php', {
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
