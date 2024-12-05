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
        <div class="search-bar">
            <input type="text" id="searchTerm" name="search_term" placeholder="Search for users..." autocomplete="off">
            <button id="searchIcon">
                🔍
            </button>
        </div>

        <!-- Search Results -->
        <div id="searchResults" class="search-results">
            <!-- Search results will be dynamically populated here -->
        </div>
    </div>

    <!-- Footer (Navbar) -->
    <footer class="navbar">
        <a href="home.php"><button>🏠</button></a>
        <a href="explore.php"><button>🔍</button></a>
        <a href="create_post.php"><button>✍️</button></a>
        <a href="profile.php"><button>👤</button></a>
    </footer>

    <script>
        document.getElementById('searchTerm').addEventListener('input', function () {
            let searchTerm = this.value;

            // Make the AJAX request only if the search term is not empty
            if (searchTerm.length > 0) {
                fetchSearchResults(searchTerm);
            } else {
                // If search term is empty, clear the results and hide them
                document.getElementById('searchResults').style.opacity = '0';
                setTimeout(() => {
                    document.getElementById('searchResults').innerHTML = ''; // Clear results after fade-out
                }, 500);
            }
        });

        function fetchSearchResults(query) {
            // Create a new FormData object to send the search term
            let formData = new FormData();
            formData.append('search_term', query);

            // Perform AJAX request using fetch
            fetch('search.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const searchResultsDiv = document.getElementById('searchResults');

                // Clear previous results and update the results container
                searchResultsDiv.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(user => {
                        searchResultsDiv.innerHTML += `
                            <div class="result-item">
                                <div class="user-profile">
                                    <img src="${user.profile_picture}" alt="Profile Picture" class="profile-img">
                                    <strong>${user.username}</strong>
                                </div>
                                <p class="user-email">${user.email}</p>
                            </div>
                        `;
                    });
                } else {
                    searchResultsDiv.innerHTML = '<p>No users found.</p>';
                }

                // Fade in the results
                searchResultsDiv.style.opacity = '1';
            });
        }
    </script>
</body>
</html>
