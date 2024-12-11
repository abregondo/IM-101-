<?php
session_start();
include('db.php'); // Include the database connection

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: sign_in.php');
    exit();
}

// Check if user_id is provided in the URL
if (!isset($_GET['user_id'])) {
    echo "User ID is not provided.";
    exit();
}

$user_id = $_GET['user_id'];

// Fetch user details
$user_query = "SELECT email, profile_picture FROM users WHERE id = :user_id";
$user_stmt = $pdo->prepare($user_query);
$user_stmt->execute(['user_id' => $user_id]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found.";
    exit();
}

// Fetch posts by the user
$posts_query = "SELECT 
                    id AS post_id, 
                    content AS post_content, 
                    created_at AS post_created_at,
                    (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count
                FROM posts 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC";
$posts_stmt = $pdo->prepare($posts_query);
$posts_stmt->execute(['user_id' => $user_id]);
$user_posts = $posts_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($user['email']) ?>'s Timeline</title>
    <link rel="stylesheet" href="timeline.css">
</head>
<body>
    <!-- Header Section -->
    <header>
        <div class="header-left">
            <a href="home.php" class="back-link">&larr; Back to Home</a>
        </div>
        <div class="header-right">
            <h1><?= htmlspecialchars($user['email']) ?>'s Timeline</h1>
        </div>
    </header>

    <!-- Profile Section -->
    <div class="profile-section">
        <img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile Picture" class="profile-avatar">
        <h2><?= htmlspecialchars($user['email']) ?></h2>
    </div>

    <!-- User Posts Section -->
    <div class="user-posts">
        <?php if (empty($user_posts)): ?>
            <p>This user has not made any posts yet.</p>
        <?php else: ?>
            <?php foreach ($user_posts as $post): ?>
                <div class="post">
                    <p class="post-content"><?= htmlspecialchars($post['post_content']) ?></p>
                    <span class="timestamp">Posted on <?= htmlspecialchars($post['post_created_at']) ?></span>
                    <div class="post-actions">
                        <span class="like-count">❤️ <?= htmlspecialchars($post['like_count']) ?> Likes</span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
