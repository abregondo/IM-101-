<?php
session_start();
include('db.php'); // Include the database connection

// Handle search form submission
$search_query = "";
if (isset($_POST['search_term'])) {
    $search_query = $_POST['search_term'];
}

// Query to search users by username or email (use the correct column name)
$sql = "SELECT id, email, profile_picture, username FROM users WHERE username LIKE :search_query OR email LIKE :search_query";
$stmt = $pdo->prepare($sql);
$stmt->execute(['search_query' => '%' . $search_query . '%']);
$search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search</title>
    <link rel="stylesheet" href="search.css">
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

    <!-- Search Section -->
    <div class="search-container">
        <form method="POST" action="search.php" class="search-form">
            <input type="text" name="search_term" placeholder="Search users by name or email..." value="<?= htmlspecialchars($search_query) ?>" required>
            <button type="submit">🔍</button>
        </form>
        
        <!-- Search Results -->
        <div class="search-results">
            <?php if ($search_query): ?>
                <?php if (count($search_results) > 0): ?>
                    <?php foreach ($search_results as $user): ?>
                        <div class="result-item">
                            <div class="user-profile">
                                <img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile Picture" class="profile-img">
                                <strong><?= htmlspecialchars($user['username']) ?></strong>
                            </div>
                            <p class="user-email"><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No users found with that name or email.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer (Navbar) -->
    <footer>
        <a href="home.php"><button>🏠</button></a>
        <a href="explore.php"><button>🔍</button></a>
        <a href="create_post.php"><button>✍️</button></a>
        <a href="profile.php"><button>👤</button></a>
    </footer>

</body>
</html>
