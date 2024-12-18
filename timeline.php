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

// Handle follow/unfollow action
if (isset($_POST['follow'])) {
    // Check if the logged-in user is following the current user
    $follow_query = "SELECT * FROM followers WHERE follower_id = :logged_in_user_id AND following_id = :user_id";
    $follow_stmt = $pdo->prepare($follow_query);
    $follow_stmt->execute(['logged_in_user_id' => $_SESSION['user_id'], 'user_id' => $user_id]);
    $is_following = $follow_stmt->fetch(PDO::FETCH_ASSOC);

    if ($is_following) {
        // Unfollow: Delete from followers table
        $unfollow_query = "DELETE FROM followers WHERE follower_id = :logged_in_user_id AND following_id = :user_id";
        $unfollow_stmt = $pdo->prepare($unfollow_query);
        $unfollow_stmt->execute(['logged_in_user_id' => $_SESSION['user_id'], 'user_id' => $user_id]);
    } else {
        // Follow: Insert into followers table
        $follow_query = "INSERT INTO followers (follower_id, following_id) VALUES (:logged_in_user_id, :user_id)";
        $follow_stmt = $pdo->prepare($follow_query);
        $follow_stmt->execute(['logged_in_user_id' => $_SESSION['user_id'], 'user_id' => $user_id]);

        // Add notification
        $notification_message = $_SESSION['user_id'] . " followed you!";
        $notification_query = "INSERT INTO notifications (user_id, message, follower_id) 
                               VALUES (:user_id, :message, :follower_id)";
        $notification_stmt = $pdo->prepare($notification_query);
        $notification_stmt->execute(['user_id' => $user_id, 'message' => $notification_message, 'follower_id' => $_SESSION['user_id']]);
    }

    // Reload the page to reflect the change
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

// Check if the logged-in user is following the current user
$follow_query = "SELECT * FROM followers WHERE follower_id = :logged_in_user_id AND following_id = :user_id";
$follow_stmt = $pdo->prepare($follow_query);
$follow_stmt->execute(['logged_in_user_id' => $_SESSION['user_id'], 'user_id' => $user_id]);
$is_following = $follow_stmt->fetch(PDO::FETCH_ASSOC);

// Fetch notifications for the logged-in user
$notifications_query = "SELECT n.id, n.message, n.created_at, u.email AS follower_email 
                        FROM notifications n 
                        JOIN users u ON u.id = n.follower_id
                        WHERE n.user_id = :user_id ORDER BY n.created_at DESC";
$notifications_stmt = $pdo->prepare($notifications_query);
$notifications_stmt->execute(['user_id' => $_SESSION['user_id']]);
$notifications = $notifications_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($user['email']) ?>'s Timeline</title>
    <link rel="stylesheet" href="ccs/timeline.css">
</head>
<body>
    <!-- Header Section -->
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

    <!-- Profile Section -->
    <div class="profile-section">
        <img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile Picture" class="profile-avatar">
        <h2><?= htmlspecialchars($user['email']) ?></h2>

        <!-- Edit Profile (Only if the logged-in user is viewing their own timeline) -->
        <?php if ($_SESSION['user_id'] == $user_id): ?>
            <a href="edit_profile.php" class="edit-profile-link">Edit Profile</a>
        <?php else: ?>
            <!-- Follow/Unfollow Button -->
            <form method="POST" action="">
                <button type="submit" name="follow" class="follow-button">
                    <?= $is_following ? 'Unfollow' : 'Follow' ?>
                </button>
            </form>
        <?php endif; ?>
    </div>

    <!-- Notifications Section -->
    <div class="notifications">
        <h1>Notifications</h1>
        <?php if (empty($notifications)): ?>
            <p>No new notifications.</p>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="notification">
                    <p><?= htmlspecialchars($notification['message']) ?> (By <?= htmlspecialchars($notification['follower_email']) ?>)</p>
                    <span class="timestamp"><?= htmlspecialchars($notification['created_at']) ?></span>
                    <form method="POST" action="">
                        <button type="submit" name="follow" class="follow-button">Follow Back</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
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
