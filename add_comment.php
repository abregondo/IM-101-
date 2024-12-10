<?php
session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $post_id = $data['post_id'];
    $comment_content = trim($data['comment_content']);
    $user_id = $_SESSION['user_id'];

    if (!empty($comment_content)) {
        $insert_comment = "INSERT INTO comments (post_id, user_id, content, created_at) VALUES (:post_id, :user_id, :content, NOW())";
        $stmt = $pdo->prepare($insert_comment);
        $stmt->execute([
            'post_id' => $post_id,
            'user_id' => $user_id,
            'content' => $comment_content
        ]);

        // Fetch the newly inserted comment
        $comment_id = $pdo->lastInsertId();
        $select_comment = "SELECT c.content AS comment_content, c.created_at AS comment_created_at,
                                  u.email AS commenter_email, u.profile_picture AS commenter_picture
                           FROM comments c
                           INNER JOIN users u ON c.user_id = u.id
                           WHERE c.id = :comment_id";
        $stmt = $pdo->prepare($select_comment);
        $stmt->execute(['comment_id' => $comment_id]);
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);

        // Return the comment details as JSON
        echo json_encode($comment);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Comment content cannot be empty.']);
    }
}
