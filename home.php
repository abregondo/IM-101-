<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home</title>
  <link rel="stylesheet" href="home.css">
</head>
<body>
  <header>
    <div class="header-left">
      <h1>My Social App</h1>
    </div>
    <div class="header-right">
      <button>👤</button>
    </div>
  </header>

  <section class="create-post-section">
    <textarea placeholder="What's on your mind?"></textarea>
    <button>Post</button>
  </section>

  <section class="posts-section">
    <div class="post">
      <div class="post-header">
        <img class="post-avatar" src="avatar.jpg" alt="Avatar">
        <div class="post-info">
          <p>John Doe</p>
          <span class="timestamp">Just now</span>
        </div>
        <div class="menu">
          <button class="menu-btn">⋮</button>
          <div class="menu-options">
            <button class="edit-btn">Edit</button>
            <button class="delete-btn">Delete</button>
          </div>
        </div>
      </div>
      <div class="post-content">
        This is a sample post content.
      </div>
      <div class="post-actions">
        <button onclick="likePost(this)">Like</button>
        <span id="likeCount">0 Likes</span>
        <button onclick="toggleCommentSection(event)">Comment</button>
        <span id="commentCount">0 Comments</span>
      </div>
      <div class="comment-section" style="display: none;">
        <div id="commentsDisplay" class="comments-display"></div>
        <input id="commentInput" type="text" placeholder="Write a comment...">
        <button onclick="postComment(event)">Post Comment</button>
      </div>
    </div>
  </section>

  <footer>
    <button>🏠</button>
    <button>🔍</button>
    <button>➕</button>
    <button>💬</button>
    <button>⚙️</button>
  </footer>

  <script src="home.js"></script>
</body>
</html>
