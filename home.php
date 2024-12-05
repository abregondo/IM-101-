<?php
// Start session for user-specific functionality if needed
session_start();
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

  <!-- Header Section (Title & Notifications) -->
  <header>
    <div class="header-left">
      <h1 class="app-name">Chattrix</h1>
    </div>
    <div class="header-right">
      <!-- Notification Button with Link -->
      <a href="notification.php">
        <button id="notifBtn">🔔</button>
      </a>
      <!-- Message Button with Link -->
      <a href="messages.php">
        <button id="msgBtn">💬</button>
      </a>
    </div>
  </header>

  <!-- Stories Section -->
  <section class="stories">
    <div class="story">
      <div class="story-circle">
        <img src="jazee.jpg" alt="Your Story" class="story-img">
      </div>
      <p>Your Story</p>
    </div>
    <div class="story">
      <div class="story-circle">
        <img src="dirk.jpg" alt="Friend Story" class="story-img">
      </div>
      <p>Dirk Lato</p>
    </div>
    <!-- Add more friend stories dynamically if needed -->
  </section>

  <!-- Main Feed Section (Posts) -->
  <div class="feed">
    <div class="post">
      <div class="post-header">
        <img src="dirk.jpg" alt="User" class="post-avatar">
        <div class="post-info">
          <strong>Dirk Lato</strong>
          <p class="timestamp">5 minutes ago</p>
        </div>
      </div>
      <p class="post-content">This is a sample post with text content!</p>
      <div class="post-actions">
        <button class="like-btn" onclick="likePost(this)">❤️</button>
        <button class="comment-btn" onclick="toggleCommentSection()">💬</button>
        <button class="share-btn">🔄</button>
      </div>
      <div class="post-stats">
        <p id="likeCount">0 Likes</p>
        <p id="commentCount">0 Comments</p>
      </div>
      <div class="comment-section" style="display:none;">
        <textarea id="commentInput" placeholder="Add a comment..."></textarea>
        <button onclick="postComment()">Post Comment</button>
        <div id="commentsDisplay"></div>
      </div>
    </div>
  </div>

  <!-- Another Example Post -->
  <div class="feed">
    <div class="post">
      <div class="post-header">
        <img src="profile.jpg" alt="User" class="post-avatar">
        <div class="post-info">
          <strong>Dirk Lato</strong>
          <p class="timestamp">5 minutes ago</p>
        </div>
      </div>
      <p class="post-content">This is another sample post with text content!</p>
      <div class="post-actions">
        <button class="like-btn" onclick="likePost(this)">❤️</button>
        <button class="comment-btn" onclick="toggleCommentSection()">💬</button>
        <button class="share-btn">🔄</button>
      </div>
      <div class="post-stats">
        <p id="likeCount">0 Likes</p>
        <p id="commentCount">0 Comments</p>
      </div>
      <div class="comment-section" style="display:none;">
        <textarea id="commentInput" placeholder="Add a comment..."></textarea>
        <button onclick="postComment()">Post Comment</button>
        <div id="commentsDisplay"></div>
      </div>
    </div>
  </div>

  <!-- Footer Navigation (Navbar with Icons) -->
  <footer>
    <button>🏠</button>
    <button>🔍</button>
    <button id="createPostBtn">✍️</button>
    <button>👤</button>
  </footer>

  <script src="home.js"></script>
</body>
</html>
