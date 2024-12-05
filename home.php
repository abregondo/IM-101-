<?php
session_start();
include('db.php'); // Make sure you have a file for DB connection

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: sign_in.php');
    exit();
}

// Handle form submission for creating a new post
if (isset($_POST['create_post'])) {
    $post_content = $_POST['post_content'];
    $user_id = $_SESSION['user_id'];

    // Insert the new post into the database
    $insert_post = "INSERT INTO posts (user_id, content, created_at) VALUES (:user_id, :content, NOW())";
    $stmt = $pdo->prepare($insert_post);
    $stmt->execute(['user_id' => $user_id, 'content' => $post_content]);

    // Redirect after POST to avoid form resubmission
    header("Location: home.php");
    exit(); // Make sure to exit after the redirect to prevent further code execution
}

// Fetch user posts
$sql = "SELECT p.id AS post_id, p.content AS post_content, p.created_at AS post_created_at, 
               u.id AS user_id, u.email, u.profile_picture 
        FROM posts p 
        INNER JOIN users u ON p.user_id = u.id
        ORDER BY p.created_at DESC"; // Get posts ordered by created_at
$stmt = $pdo->prepare($sql);
$stmt->execute();
$posts_result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Add like functionality (AJAX request)
if (isset($_POST['like_post_id'])) {
    $post_id = $_POST['like_post_id'];
    $user_id = $_SESSION['user_id'];

    // Check if already liked
    $check_like = "SELECT * FROM likes WHERE post_id = :post_id AND user_id = :user_id";
    $check_stmt = $pdo->prepare($check_like);
    $check_stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);
    if ($check_stmt->rowCount() == 0) {
        // Add like
        $insert_like = "INSERT INTO likes (post_id, user_id) VALUES (:post_id, :user_id)";
        $insert_stmt = $pdo->prepare($insert_like);
        $insert_stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);
    }
}

// Add comment functionality (AJAX request)
if (isset($_POST['comment_post_id']) && isset($_POST['comment_content'])) {
    $post_id = $_POST['comment_post_id'];
    $content = $_POST['comment_content'];
    $user_id = $_SESSION['user_id'];

    // Add comment to the database
    $insert_comment = "INSERT INTO comments (post_id, user_id, content) VALUES (:post_id, :user_id, :content)";
    $insert_stmt = $pdo->prepare($insert_comment);
    $insert_stmt->execute(['post_id' => $post_id, 'user_id' => $user_id, 'content' => $content]);
}

// Follow a user (AJAX request)
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

// Fetch all users to display them and allow following
$sql = "SELECT id, username, email FROM users";
$users = $pdo->query($sql)->fetchAll();
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
                        <strong><a href="profile.php?id=<?= $post['user_id'] ?>" class="post-username"><?= $post['email'] ?></a></strong>
                        <p class="timestamp"><?= $post['post_created_at'] ?></p>
                    </div>
                </div>
                <p class="post-content"><?= $post['post_content'] ?></p>
                <div class="post-actions">
                    <button class="like-btn" onclick="likePost(<?= $post['post_id'] ?>)">❤️</button>
                    <button class="comment-btn" onclick="toggleCommentSection(<?= $post['post_id'] ?>)">💬</button>
                    <button class="share-btn">🔄</button>
                </div>
                <div class="post-stats">
                    <p id="likeCount<?= $post['post_id'] ?>">0 Likes</p>
                    <p id="commentCount<?= $post['post_id'] ?>">0 Comments</p>
                </div>
                <div class="comment-section" id="commentSection<?= $post['post_id'] ?>" style="display:none;">
                    <textarea id="commentInput<?= $post['post_id'] ?>" placeholder="Add a comment..."></textarea>
                    <button onclick="postComment(<?= $post['post_id'] ?>)">Post Comment</button>
                    <div id="commentsDisplay<?= $post['post_id'] ?>"></div>
                </div>
            </div>
        <?php } ?>
    </div>

    <!-- Follow Users Section -->
    <h3>Follow Users</h3>
    <?php foreach ($users as $user) { ?>
        <?php if ($user['id'] != $_SESSION['user_id']) { // Don't show follow button for the logged-in user ?>
            <div>
                <p><?php echo $user['username']; ?> (<?php echo $user['email']; ?>)</p>
                <form method="POST" action="home.php">
                    <input type="hidden" name="follow_user_id" value="<?php echo $user['id']; ?>">
                    <button type="submit">Follow</button>
                </form>
            </div>
        <?php } ?>
    <?php } ?>

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
