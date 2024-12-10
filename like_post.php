<?php
session_start();
include('db.php'); // Database connection

// Ensure the request is made via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure the user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'User not logged in.']);
        exit();
    }

    // Decode the incoming JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Check if post_id is provided
    if (!isset($data['post_id'])) {
        echo json_encode(['error' => 'Post ID not provided.']);
        exit();
    }

    $post_id = $data['post_id'];
    $user_id = $_SESSION['user_id'];

    try {
        // Check if the user has already liked the post
        $check_like = "SELECT id FROM likes WHERE post_id = :post_id AND user_id = :user_id";
        $stmt = $pdo->prepare($check_like);
        $stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);
        $like_exists = $stmt->fetch();

        if ($like_exists) {
            // If already liked, unlike the post (delete like)
            $delete_like = "DELETE FROM likes WHERE post_id = :post_id AND user_id = :user_id";
            $stmt = $pdo->prepare($delete_like);
            $stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);

            // Return success response for unliking
            echo json_encode(['action' => 'unliked']);
        } else {
            // If not liked, add a like (insert into likes table)
            $insert_like = "INSERT INTO likes (post_id, user_id) VALUES (:post_id, :user_id)";
            $stmt = $pdo->prepare($insert_like);
            $stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);

            // Return success response for liking
            echo json_encode(['action' => 'liked']);
        }
    } catch (PDOException $e) {
        // Handle any database errors
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }

    exit();
}

// If method is not POST
echo json_encode(['error' => 'Invalid request method.']);
?>
