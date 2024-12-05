<?php
session_start();
include('db.php'); // DB connection file

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo 'You must be logged in to view this page.';
    exit();
}

// Fetch current user ID
$user_id = $_SESSION['user_id'];

// Fetch two specific friends (change the IDs based on your test users)
$friend_ids = [2, 3];  // Example: Friend IDs to display. Change these to real ones.

$sql_friends = "SELECT id, email, profile_picture FROM users WHERE id IN (" . implode(',', $friend_ids) . ")";
$stmt_friends = $pdo->prepare($sql_friends);
$stmt_friends->execute();
$friends_result = $stmt_friends->fetchAll(PDO::FETCH_ASSOC);

// Fetch current user's friends and follow status
$sql_user_friends = "SELECT friend_id, status FROM friends WHERE user_id = :user_id OR friend_id = :user_id";
$stmt_user_friends = $pdo->prepare($sql_user_friends);
$stmt_user_friends->execute(['user_id' => $user_id]);
$current_user_friends = $stmt_user_friends->fetchAll(PDO::FETCH_ASSOC);

// Add friend request functionality
if (isset($_POST['send_friend_request'])) {
    $friend_id = $_POST['friend_id'];

    // Check if a pending request already exists
    $check_request = "SELECT * FROM friends WHERE (user_id = :user_id AND friend_id = :friend_id) OR (user_id = :friend_id AND friend_id = :user_id)";
    $stmt_check_request = $pdo->prepare($check_request);
    $stmt_check_request->execute(['user_id' => $user_id, 'friend_id' => $friend_id]);

    if ($stmt_check_request->rowCount() == 0) {
        // Insert new friend request
        $insert_request = "INSERT INTO friends (user_id, friend_id, status) VALUES (:user_id, :friend_id, 'pending')";
        $stmt_insert_request = $pdo->prepare($insert_request);
        $stmt_insert_request->execute(['user_id' => $user_id, 'friend_id' => $friend_id]);
    }
}

// Follow functionality
if (isset($_POST['follow_user'])) {
    $followed_id = $_POST['followed_id'];

    // Check if already following
    $check_follow = "SELECT * FROM followers WHERE follower_id = :follower_id AND followed_id = :followed_id";
    $stmt_check_follow = $pdo->prepare($check_follow);
    $stmt_check_follow->execute(['follower_id' => $user_id, 'followed_id' => $followed_id]);

    if ($stmt_check_follow->rowCount() == 0) {
        // Follow the user
        $insert_follow = "INSERT INTO followers (follower_id, followed_id) VALUES (:follower_id, :followed_id)";
        $stmt_insert_follow = $pdo->prepare($insert_follow);
        $stmt_insert_follow->execute(['follower_id' => $user_id, 'followed_id' => $followed_id]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chattrix</title>
  <link rel="stylesheet" href="home.css">
</head>
<body>
  <!-- Header Section -->
  <header>
    <div class="header-left">
      <h1 class="app-name">Chattrix</h1>
    </div>
    <div class="header-right">
      <a href="notification.php"><button id="notifBtn">🔔</button></a>
      <a href="messages.php"><button id="msgBtn">💬</button></a>
    </div>
  </header>

  <!-- Friends Section -->
  <div class="feed">
    <h2>Friends (Add or Follow)</h2>
    <?php foreach ($friends_result as $friend) { ?>
      <div class="user-profile">
        <img src="<?= $friend['profile_picture'] ?>" alt="User" class="user-avatar">
        <strong><?= $friend['email'] ?></strong>

        <form action="home.php" method="POST">
          <?php
          // Check if the current user and friend are already connected
          $is_friend = false;
          foreach ($current_user_friends as $user_friend) {
              if ($user_friend['friend_id'] == $friend['id'] || $user_friend['friend_id'] == $friend['id']) {
                  $is_friend = true;
                  break;
              }
          }

          if ($is_friend) {
              echo "<p>You are friends!</p>";
          } else {
              // Check if the user has already sent a friend request
              $request_pending = false;
              foreach ($current_user_friends as $user_friend) {
                  if (($user_friend['friend_id'] == $friend['id'] || $user_friend['friend_id'] == $friend['id']) && $user_friend['status'] == 'pending') {
                      $request_pending = true;
                      break;
                  }
              }

              if ($request_pending) {
                  echo "<p>Friend request pending...</p>";
              } else {
                  echo '<button type="submit" name="send_friend_request" value="1" style="background-color: #4CAF50;">Send Friend Request</button>';
                  echo '<input type="hidden" name="friend_id" value="' . $friend['id'] . '">';
              }
          }
          ?>
        </form>

        <!-- Follow Button -->
        <form action="home.php" method="POST">
          <button type="submit" name="follow_user" value="1">Follow</button>
          <input type="hidden" name="followed_id" value="<?= $friend['id'] ?>">
        </form>
      </div>
    <?php } ?>
  </div>

  <footer>
    <button>🏠</button>
    <button>🔍</button>
    <button id="createPostBtn">✍️</button>
    <button>👤</button>
  </footer>

</body>
</html>
