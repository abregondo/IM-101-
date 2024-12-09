// JavaScript to handle likes and comments

// Function to handle likes
function likePost(button) {
  const likeCount = button.closest('.post').querySelector('.like-count');
  let likes = parseInt(likeCount.innerText.split(' ')[0]);
  likes += 1;
  likeCount.innerText = `${likes} Likes`; // Update the like count
  button.disabled = true; // Disable the like button once liked
  button.style.color = 'red'; // Optional: Change the color of the like button after it's clicked
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
  const commentInput = postElement.querySelector('.comment-input');
  const commentsDisplay = postElement.querySelector('.comments-display');
  const commentSection = postElement.querySelector('.comment-section');

  if (commentInput.value.trim()) {
    // Create and append the new comment
    const newComment = document.createElement('div');
    newComment.textContent = commentInput.value;
    commentsDisplay.appendChild(newComment);

    // Update comment count
    const commentCount = postElement.querySelector('.comment-count');
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
