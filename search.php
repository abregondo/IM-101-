<?php
session_start();
include('db.php'); // Include the database connection

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: sign_in.php');
    exit();
}

// Initialize variables
$search_query = '';
$search_results = [];

// Handle form submission for search
if (isset($_POST['search'])) {
    $search_query = trim($_POST['search_query']);

    if (!empty($search_query)) {
        // Search for users or posts based on the query
        $sql = "SELECT u.id, u.email, u.profile_picture, p.content AS post_content, p.created_at
                FROM users u
                LEFT JOIN posts p ON p.user_id = u.id
                WHERE u.email LIKE :search_query OR p.content LIKE :search_query
                ORDER BY u.email";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['search_query' => "%$search_query%"]);
        $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Search | Chattrix</title>
  <link rel="stylesheet" href="search.css">
</head>
<body>

  <!-- Header Section -->
  <header>
    <div class="header-left">
      <h1 class="app-name">Chattrix</h1>
    </div>
    <div class="header-right">
      <a href="notification.php"><button>🔔</button></a>
      <a href="messages.php"><button>💬</button></a>
    </div>
  </header>

  <!-- Search Section -->
  <div class="search-container">
    <form method="POST" action="search.php" class="search-form">
      <input type="text" name="search_query" placeholder="Search for users or posts..." value="<?= htmlspecialchars($search_query) ?>" required>
      <button type="submit" name="search">🔍</button>
    </form>

    <!-- Display search results -->
    <?php if ($search_query && count($search_results) > 0): ?>
      <div class="search-results">
        <?php foreach ($search_results as $result): ?>
          <div class="result-item">
            <!-- User's Profile -->
            <div class="user-profile">
              <img src="<?= $result['profile_picture'] ?>" alt="Profile Picture" class="profile-img">
              <strong><?= $result['email'] ?></strong>
            </div>

            <!-- Post content (if available) -->
            <?php if ($result['post_content']): ?>
              <div class="post-content">
                <p><?= htmlspecialchars($result['post_content']) ?></p>
                <span class="timestamp"><?= $result['created_at'] ?></span>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php elseif ($search_query): ?>
      <p>No results found for "<?= htmlspecialchars($search_query) ?>".</p>
    <?php endif; ?>
  </div>

  <footer>
    <a href="home.php"><button>🏠</button></a>
    <a href="search.php"><button>🔍</button></a>
    <a href="create_post.php"><button>✍️</button></a>
    <a href="profile.php"><button>👤</button></a>
  </footer>

  <script src="home.js"></script>
</body>
</html>
