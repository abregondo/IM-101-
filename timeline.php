<?php
session_start();
include('db.php'); // Include the database connection

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

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
try {
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

    // Fetch followers and following count
    $followers_query = "SELECT COUNT(*) AS followers_count FROM followers WHERE following_id = :user_id";
    $followers_stmt = $pdo->prepare($followers_query);
    $followers_stmt->execute(['user_id' => $user_id]);
    $followers_count = $followers_stmt->fetch(PDO::FETCH_ASSOC)['followers_count'];

    $following_query = "SELECT COUNT(*) AS following_count FROM followers WHERE follower_id = :user_id";
    $following_stmt = $pdo->prepare($following_query);
    $following_stmt->execute(['user_id' => $user_id]);
    $following_count = $following_stmt->fetch(PDO::FETCH_ASSOC)['following_count'];

    // Check if the logged-in user is following the current user
    $follow_query = "SELECT * FROM followers WHERE follower_id = :logged_in_user_id AND following_id = :user_id";
    $follow_stmt = $pdo->prepare($follow_query);
    $follow_stmt->execute(['logged_in_user_id' => $_SESSION['user_id'], 'user_id' => $user_id]);
    $is_following = $follow_stmt->fetch(PDO::FETCH_ASSOC);

    // Handle follow/unfollow action
    if (isset($_POST['follow'])) {
        if ($is_following) {
            // Unfollow
            $unfollow_query = "DELETE FROM followers WHERE follower_id = :logged_in_user_id AND following_id = :user_id";
            $unfollow_stmt = $pdo->prepare($unfollow_query);
            $unfollow_stmt->execute(['logged_in_user_id' => $_SESSION['user_id'], 'user_id' => $user_id]);
        } else {
            // Follow
            $follow_query = "INSERT INTO followers (follower_id, following_id) VALUES (:logged_in_user_id, :user_id)";
            $follow_stmt = $pdo->prepare($follow_query);
            $follow_stmt->execute(['logged_in_user_id' => $_SESSION['user_id'], 'user_id' => $user_id]);
        }
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($user['email']) ?>'s Timeline</title>
    
    <!-- Update the CSS link path -->
    <link rel="stylesheet" href="/IM-101-/css/timeline.css">
    
    <!-- Inline Style for Debugging -->
    <style>
        body {
            background-color: lightgray; /* Temporary testing */
        }
    </style>
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

        <div class="follow-stats">
            <p><strong>Followers:</strong> <?= $followers_count ?></p>
            <p><strong>Following:</strong> <?= $following_count ?></p>
        </div>

        <form method="POST" action="">
            <button type="submit" name="follow" class="follow-button">
                <?= $is_following ? 'Unfollow' : 'Follow' ?>
            </button>
        </form>
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

    <footer>
        <p>&copy; 2024 Your Website | <a href="#">Privacy</a> | <a href="#">Terms</a></p>
    </footer>
</body>
</html>
