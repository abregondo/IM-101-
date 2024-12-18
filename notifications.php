<?php
session_start();
include('db.php'); // Include the database connection

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: sign_in.php');
    exit();
}

// Fetch unread notifications for the logged-in user
$notifications_query = "
    SELECT 
        notifications.id, 
        notifications.message, 
        notifications.created_at, 
        followers.follower_id, 
        users.email AS follower_email
    FROM notifications
    LEFT JOIN followers ON followers.following_id = notifications.user_id
    LEFT JOIN users ON users.id = followers.follower_id
    WHERE notifications.user_id = :user_id AND notifications.is_read = 0
    ORDER BY notifications.created_at DESC";
$notifications_stmt = $pdo->prepare($notifications_query);
$notifications_stmt->execute(['user_id' => $_SESSION['user_id']]);
$notifications = $notifications_stmt->fetchAll(PDO::FETCH_ASSOC);

// Mark notifications as read
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mark_read_query = "UPDATE notifications SET is_read = 1 WHERE user_id = :user_id";
    $mark_read_stmt = $pdo->prepare($mark_read_query);
    $mark_read_stmt->execute(['user_id' => $_SESSION['user_id']]);
    header('Location: notifications.php');
    exit();
}

// Handle follow/unfollow action
if (isset($_POST['follow'])) {
    $follow_user_id = $_POST['follow_user_id'];

    // Check if the logged-in user is following the user who followed them
    $follow_query = "SELECT * FROM followers WHERE follower_id = :logged_in_user_id AND following_id = :follow_user_id";
    $follow_stmt = $pdo->prepare($follow_query);
    $follow_stmt->execute(['logged_in_user_id' => $_SESSION['user_id'], 'follow_user_id' => $follow_user_id]);
    $is_following = $follow_stmt->fetch(PDO::FETCH_ASSOC);

    if ($is_following) {
        // Unfollow: Delete from followers table
        $unfollow_query = "DELETE FROM followers WHERE follower_id = :logged_in_user_id AND following_id = :follow_user_id";
        $unfollow_stmt = $pdo->prepare($unfollow_query);
        $unfollow_stmt->execute(['logged_in_user_id' => $_SESSION['user_id'], 'follow_user_id' => $follow_user_id]);
    } else {
        // Follow back: Insert into followers table
        $follow_query = "INSERT INTO followers (follower_id, following_id) VALUES (:logged_in_user_id, :follow_user_id)";
        $follow_stmt = $pdo->prepare($follow_query);
        $follow_stmt->execute(['logged_in_user_id' => $_SESSION['user_id'], 'follow_user_id' => $follow_user_id]);
    }

    // Reload the page to reflect the change
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="css/notifications.css">
</head>
<body>
    <header>
        <div class="header-left">
            <a href="home.php" class="back-link">&larr; Back to Home</a>
        </div>
    </header>

    <div class="notifications">
        <h1>Notifications</h1>
        <?php if (empty($notifications)): ?>
            <p>No new notifications.</p>
        <?php else: ?>
            <form method="POST" action="">
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification">
                        <p><?= htmlspecialchars($notification['message']) ?> 
                            <strong><?= htmlspecialchars($notification['follower_email']) ?></strong> followed you.</p>
                        <span class="timestamp"><?= htmlspecialchars($notification['created_at']) ?></span>
                        
                        <!-- Follow back option -->
                        <input type="hidden" name="follow_user_id" value="<?= htmlspecialchars($notification['follower_id']) ?>">
                        <button type="submit" name="follow" class="follow-button">
                            Follow Back
                        </button>
                    </div>
                <?php endforeach; ?>
                <button type="submit" class="mark-read-button">Mark All as Read</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
