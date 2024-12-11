<?php
session_start();
include('db.php');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: sign_in.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conversation_id = $_POST['conversation_id'];
    $message_content = trim($_POST['message']);
    $user_id = $_SESSION['user_id'];

    if (!empty($message_content)) {
        $sql = "INSERT INTO messages (conversation_id, sender_id, content, created_at) 
                VALUES (:conversation_id, :sender_id, :content, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'conversation_id' => $conversation_id,
            'sender_id' => $user_id,
            'content' => $message_content
        ]);
    }

    header("Location: chat.php?conversation_id=$conversation_id");
    exit();
}
?>
