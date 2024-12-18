<?php
session_start();
include('db.php'); // Include the database connection

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: sign_in.php');
    exit();
}

// Fetch unread notifications for the logged-in user
$notifications_query = "SELECT id, message, created_at FROM notifications WHERE user_id = :user_id AND is_read = 0 ORDER BY created_at DESC";
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
                        <p><?= htmlspecialchars($notification['message']) ?></p>
                        <span class="timestamp"><?= htmlspecialchars($notification['created_at']) ?></span>
                    </div>
                <?php endforeach; ?>
                <button type="submit" class="mark-read-button">Mark All as Read</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
