// JavaScript to toggle the menu options
const menuBtns = document.querySelectorAll('.menu-btn');
menuBtns.forEach(menuBtn => {
  menuBtn.addEventListener('click', function(event) {
    // Close any open menus before opening the clicked one
    const menuOptions = this.nextElementSibling;
    const isVisible = menuOptions.style.display === 'block';
    document.querySelectorAll('.menu-options').forEach(menu => {
      if (menu !== menuOptions) {
        menu.style.display = 'none';
      }
    });
    // Toggle the clicked menu's visibility
    menuOptions.style.display = isVisible ? 'none' : 'block';
  });
});

// Close menus when clicking outside
document.addEventListener("click", function(event) {
  if (!event.target.closest('.menu')) {
    document.querySelectorAll('.menu-options').forEach(menu => {
      menu.style.display = 'none';
    });
  }
});

// Function to handle likes
function likePost(button) {
  const likeCount = document.getElementById('likeCount');
  let likes = parseInt(likeCount.innerText.split(' ')[0]);
  likes += 1;
  likeCount.innerText = `${likes} Likes`;
  button.disabled = true; // Disable the like button once liked
}

// Function to toggle the comment section visibility
function toggleCommentSection(event) {
  const postElement = event.target.closest('.post');
  const commentSection = postElement.querySelector('.comment-section');
  commentSection.style.display = (commentSection.style.display === 'none' || commentSection.style.display === '') ? 'block' : 'none';
}

// Function to handle posting a comment
function postComment(event) {
  const postElement = event.target.closest('.post');
  const commentInput = postElement.querySelector('#commentInput');
  const commentsDisplay = postElement.querySelector('#commentsDisplay');
  const commentSection = postElement.querySelector('.comment-section');

  if (commentInput.value.trim()) {
    // Create and append the new comment
    const newComment = document.createElement('div');
    newComment.textContent = commentInput.value;
    commentsDisplay.appendChild(newComment);

    // Update comment count
    const commentCount = postElement.querySelector('#commentCount');
    let comments = parseInt(commentCount.innerText.split(' ')[0]);
    comments += 1;
    commentCount.innerText = `${comments} Comments`;

    // Clear input field
    commentInput.value = '';

    // Ensure comment section is displayed
    commentSection.style.display = 'block';

    // Scroll to the new comment (ensure the container scrolls to the bottom)
    commentsDisplay.scrollTop = commentsDisplay.scrollHeight;
  }
}

// Function to toggle menu options
function toggleMenu(event) {
  const menu = event.target.nextElementSibling;
  
  // Hide other menus
  document.querySelectorAll('.menu-options').forEach((opt) => {
    if (opt !== menu) opt.style.display = 'none';
  });
  
  // Toggle the current menu
  menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
}

// Function to edit a post
function editPost(event) {
  const postContent = event.target.closest('.post').querySelector('.post-content');
  const newText = prompt("Edit your post:", postContent.textContent);
  if (newText) postContent.textContent = newText;
}

// Function to delete a post
function deletePost(event) {
  const post = event.target.closest('.post');
  if (confirm("Are you sure you want to delete this post?")) {
    post.remove();
  }
}

// Event listeners for menu options
document.addEventListener("DOMContentLoaded", () => {
  // Three-dot menu toggle
  document.querySelectorAll(".menu-btn").forEach((btn) => {
    btn.addEventListener("click", toggleMenu);
  });

  // Edit post
  document.querySelectorAll(".edit-btn").forEach((btn) => {
    btn.addEventListener("click", editPost);
  });

  // Delete post
  document.querySelectorAll(".delete-btn").forEach((btn) => {
    btn.addEventListener("click", deletePost);
  });

  // Close menus if clicking outside
  document.addEventListener("click", (e) => {
    if (!e.target.matches(".menu-btn, .menu-options, .menu-options *")) {
      document.querySelectorAll(".menu-options").forEach((menu) => {
        menu.style.display = "none";
      });
    }
  });
});
