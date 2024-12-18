<?php
session_start();
include('db.php'); // Include the database connection

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: sign_in.php');
    exit();
}

// Handle sign out
if (isset($_POST['sign_out'])) {
    session_destroy();
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

// Handle follow/unfollow actions
if (isset($_POST['follow_action'])) {
    if ($_POST['follow_action'] == 'follow') {
        // Add follow relationship
        $follow_query = "INSERT INTO follows (follower_id, followed_id) VALUES (:follower_id, :followed_id)";
        $follow_stmt = $pdo->prepare($follow_query);
        $follow_stmt->execute([
            'follower_id' => $_SESSION['user_id'],
            'followed_id' => $user_id
        ]);

        // Add a notification for the followed user
        $notification_message = "User " . htmlspecialchars($_SESSION['user_id']) . " has started following you.";
        $notification_query = "INSERT INTO notifications (user_id, message) VALUES (:user_id, :message)";
        $notification_stmt = $pdo->prepare($notification_query);
        $notification_stmt->execute([
            'user_id' => $user_id,
            'message' => $notification_message
        ]);
    } elseif ($_POST['follow_action'] == 'unfollow') {
        // Remove follow relationship
        $unfollow_query = "DELETE FROM follows WHERE follower_id = :follower_id AND followed_id = :followed_id";
        $unfollow_stmt = $pdo->prepare($unfollow_query);
        $unfollow_stmt->execute([
            'follower_id' => $_SESSION['user_id'],
            'followed_id' => $user_id
        ]);
    }
}

// Check if the logged-in user is following the displayed user
$is_following_query = "SELECT 1 FROM follows WHERE follower_id = :follower_id AND followed_id = :followed_id";
$is_following_stmt = $pdo->prepare($is_following_query);
$is_following_stmt->execute([
    'follower_id' => $_SESSION['user_id'],
    'followed_id' => $user_id
]);
$is_following = $is_following_stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($user['email']) ?>'s Timeline</title>
    <link rel="stylesheet" href="css/timeline.css">
</head>
<body>
    <header>
        <div class="header-left">
            <a href="home.php" class="back-link">&larr; Back to Home</a>
        </div>
        <div class="header-right">
            <form method="POST" action="">
                <button type="submit" name="sign_out" class="sign-out-button">Sign Out</button>
            </form>
        </div>
    </header>

    <div class="profile-section">
        <img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile Picture" class="profile-avatar">
        <h2><?= htmlspecialchars($user['email']) ?></h2>

        <?php if ($_SESSION['user_id'] != $user_id): ?>
            <form method="POST" action="">
                <button 
                    type="submit" 
                    name="follow_action" 
                    value="<?= $is_following ? 'unfollow' : 'follow' ?>" 
                    class="follow-button">
                    <?= $is_following ? 'Unfollow' : 'Follow' ?>
                </button>
            </form>
        <?php else: ?>
            <a href="edit_profile.php" class="edit-profile-link">Edit Profile</a>
        <?php endif; ?>
    </div>

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
