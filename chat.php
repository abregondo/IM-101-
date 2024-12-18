<?php
session_start();
include('db.php');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: sign_in.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$conversation_id = $_GET['conversation_id'] ?? null;

// Fetch messages in the conversation
if ($conversation_id) {
    $sql = "SELECT m.content, m.created_at, u.email, u.profile_picture 
            FROM messages m 
            JOIN users u ON m.sender_id = u.id 
            WHERE m.conversation_id = :conversation_id 
            ORDER BY m.created_at ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['conversation_id' => $conversation_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    header('Location: messages.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <link rel="stylesheet" href="ccs/chat.css">
</head>
<body>
    <div class="chat-window">
        <div class="messages">
            <?php foreach ($messages as $message): ?>
                <div class="message">
                    <img src="<?= htmlspecialchars($message['profile_picture']) ?>" alt="User" class="avatar">
                    <div class="message-content">
                        <strong><?= htmlspecialchars($message['email']) ?></strong>
                        <p><?= htmlspecialchars($message['content']) ?></p>
                        <span class="timestamp"><?= htmlspecialchars($message['created_at']) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <form action="send_message.php" method="POST">
            <input type="hidden" name="conversation_id" value="<?= $conversation_id ?>">
            <textarea name="message" placeholder="Type your message..." required></textarea>
            <button type="submit">Send</button>
        </form>
    </div>
</body>
</html>
