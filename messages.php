<?php
session_start();
include('db.php');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: sign_in.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch conversations for the logged-in user
$sql = "SELECT c.id AS conversation_id, 
               u.id AS user_id, 
               u.email, 
               u.profile_picture 
        FROM conversations c
        JOIN users u ON (c.user1_id = u.id OR c.user2_id = u.id) 
        WHERE (c.user1_id = :user_id OR c.user2_id = :user_id) AND u.id != :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chattrix</title>
    <link rel="stylesheet" href="ccs/messages.css">
</head> 
<body>
    <h1>Your Conversations</h1>
    <div class="conversations">
        <?php foreach ($conversations as $conversation): ?>
            <div class="conversation">
                <img src="<?= htmlspecialchars($conversation['profile_picture']) ?>" alt="User" class="avatar">
                <a href="chat.php?conversation_id=<?= $conversation['conversation_id'] ?>">
                    <?= htmlspecialchars($conversation['email']) ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
