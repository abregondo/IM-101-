<?php
session_start();
include('db.php');

// Get the user ID from the query string
$user_id = $_GET['user_id'];

// Fetch user information
$sql_user = "SELECT email FROM users WHERE id = :user_id";
$stmt_user = $pdo->prepare($sql_user);
$stmt_user->execute(['user_id' => $user_id]);
$user = $stmt_user->fetch();

if (!$user) {
    die("User not found.");
}

// Fetch posts created by the user
$sql_posts = "SELECT * FROM posts WHERE user_id = :user_id ORDER BY created_at DESC";
$stmt_posts = $pdo->prepare($sql_posts);
$stmt_posts->execute(['user_id' => $user_id]);
$posts = $stmt_posts->fetchAll(PDO::FETCH_ASSOC);
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
    <div class="timeline-container">
        <h1><?= htmlspecialchars($user['email']) ?>'s Timeline</h1>
        <div class="posts">
            <?php foreach ($posts as $post): ?>
                <div class="post">
                    <p><?= htmlspecialchars($post['content']) ?></p>
                    <span class="timestamp"><?= htmlspecialchars($post['created_at']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
