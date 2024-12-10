<?php
session_start();
include('db.php'); // Include your database connection

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id']; // Get the post_id from the request

// Check if the user has already liked the post
$query = "SELECT * FROM likes WHERE post_id = :post_id AND user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);
$like = $stmt->fetch();

if ($like) {
    // User has already liked the post, so let's remove the like
    $delete_query = "DELETE FROM likes WHERE post_id = :post_id AND user_id = :user_id";
    $delete_stmt = $pdo->prepare($delete_query);
    $delete_stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);

    echo json_encode(['action' => 'unliked']); // Return JSON response for unliking
} else {
    // User hasn't liked the post, so let's add a like
    $insert_query = "INSERT INTO likes (post_id, user_id, created_at) VALUES (:post_id, :user_id, NOW())";
    $insert_stmt = $pdo->prepare($insert_query);
    $insert_stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);

    echo json_encode(['action' => 'liked']); // Return JSON response for liking
}
?>
