<?php
session_start();
include('db.php'); // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure the user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'User not logged in.']);
        exit();
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $post_id = $data['post_id'];
    $user_id = $_SESSION['user_id'];

    // Check if the user has already liked the post
    $check_like = "SELECT id FROM likes WHERE post_id = :post_id AND user_id = :user_id";
    $stmt = $pdo->prepare($check_like);
    $stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);
    $like_exists = $stmt->fetch();

    if ($like_exists) {
        // If liked, unlike the post
        $delete_like = "DELETE FROM likes WHERE post_id = :post_id AND user_id = :user_id";
        $stmt = $pdo->prepare($delete_like);
        $stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);

        echo json_encode(['action' => 'unliked']);
    } else {
        // If not liked, add a like
        $insert_like = "INSERT INTO likes (post_id, user_id) VALUES (:post_id, :user_id)";
        $stmt = $pdo->prepare($insert_like);
        $stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);

        echo json_encode(['action' => 'liked']);
    }
    exit();
}
?>
