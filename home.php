<?php
session_start();
include('db.php'); 

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: sign_in.php');
    exit();
}

// Handle form submission for creating a new post (text only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_post'])) {
    $post_content = trim($_POST['post_content']);
    $user_id = $_SESSION['user_id'];

    if (!empty($post_content)) {
        $insert_post = "INSERT INTO posts (user_id, content, created_at) VALUES (:user_id, :content, NOW())";
        $stmt = $pdo->prepare($insert_post);
        $stmt->execute([
            'user_id' => $user_id,
            'content' => $post_content
        ]);
    }

    // Redirect to avoid form resubmission
    header('Location: home.php');
    exit();
}

// Fetch all posts along with the like count and whether the user has liked the post
$sql = "SELECT 
            p.id AS post_id, 
            p.content AS post_content, 
            p.created_at AS post_created_at, 
            u.id AS user_id, 
            u.username, 
            u.profile_picture,
            (SELECT COUNT(*) FROM likes WHERE likes.post_id = p.id) AS like_count,
            (SELECT COUNT(*) FROM likes WHERE likes.post_id = p.id AND likes.user_id = :user_id) AS user_liked
        FROM posts p 
        INNER JOIN users u ON p.user_id = u.id
        ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$posts_result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch comments for each post
$comments_query = "SELECT c.id AS comment_id, c.content AS comment_content, c.created_at AS comment_created_at, 
                          u.username AS commenter_username, u.profile_picture AS commenter_picture, c.post_id
                   FROM comments c
                   INNER JOIN users u ON c.user_id = u.id
                   ORDER BY c.created_at ASC";
$comments_stmt = $pdo->prepare($comments_query);
$comments_stmt->execute();
$comments = $comments_stmt->fetchAll(PDO::FETCH_ASSOC);

// Group comments by post ID for easy retrieval
$comments_by_post = [];
foreach ($comments as $comment) {
    $comments_by_post[$comment['post_id']][] = $comment;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chattrix</title>
  <link rel="stylesheet" href="ccs/home.css">
</head>
<body>
  <!-- Header Section -->
  <header>
    <div class="header-left">
      <h1 class="app-name">Chattrix</h1>
    </div>
    <div class="header-right">
      <a href="notifications.php"><button id="notifBtn">🔔</button></a>
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
    <?php if (empty($posts_result)): ?>
      <p>No posts available.</p>
    <?php else: ?>
      <?php foreach ($posts_result as $post): ?>
        <div class="post" data-post-id="<?= $post['post_id'] ?>">
          <div class="post-header">
            <a href="timeline.php?user_id=<?= $post['user_id'] ?>" class="unstyled-link">
              <img src="<?= htmlspecialchars($post['profile_picture']) ?>" alt="User" class="post-avatar">
            </a>
            <div class="post-info">
              <a href="timeline.php?user_id=<?= $post['user_id'] ?>" class="unstyled-link">
                <strong><?= htmlspecialchars($post['username']) ?></strong>
              </a>
              <p class="timestamp"><?= htmlspecialchars($post['post_created_at']) ?></p>
            </div>
          </div>
          <p class="post-content"><?= htmlspecialchars($post['post_content']) ?></p>
          <div class="post-actions">
            <button class="like-btn <?= $post['user_liked'] ? 'liked' : '' ?>" onclick="likePost(this)">
                ❤️
            </button>
            <span class="like-count"><?= htmlspecialchars($post['like_count']) ?></span>
            <button class="comment-btn" onclick="toggleCommentSection(event)">💬</button>
            <button class="share-btn">🔄</button>
          </div>

          <!-- Comment Section -->
          <div class="comment-section">
            <form onsubmit="postComment(event, <?= $post['post_id'] ?>)">
              <textarea class="comment-input" placeholder="Write a comment..." required></textarea>
              <button type="submit">Post Comment</button>
            </form>
            <div class="comments-display">
              <?php if (isset($comments_by_post[$post['post_id']])): ?>
                <?php foreach ($comments_by_post[$post['post_id']] as $comment): ?>
                  <div class="comment">
                    <img src="<?= htmlspecialchars($comment['commenter_picture']) ?>" alt="User" class="comment-avatar">
                    <strong><?= htmlspecialchars($comment['commenter_username']) ?></strong>
                    <p><?= htmlspecialchars($comment['comment_content']) ?></p>
                    <span class="timestamp"><?= htmlspecialchars($comment['comment_created_at']) ?></span>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Footer Section -->
  <footer>
    <a href="home.php"><button>🏠</button></a>
    <a href="search.php"><button>🔍</button></a>
    <a href="create_post.php"><button id="createPostBtn">✍️</button></a>
    <a href="timeline.php?user_id=<?= $_SESSION['user_id'] ?>"><button>👤</button></a>
  </footer>

  <script src="home.js"></script>
</body>
</html>
