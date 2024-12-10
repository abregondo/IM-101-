// Function to handle likes
async function likePost(button) {
  const postElement = button.closest('.post');
  const postId = postElement.dataset.postId;
  const likeCount = postElement.querySelector('.like-count');
  let likes = parseInt(likeCount.innerText.split(' ')[0]) || 0;

  try {
    // Send like action to the server
    const response = await fetch('like_post.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ post_id: postId })
    });

    if (response.ok) {
      const result = await response.json();
      if (result.success) {
        likes += 1; // Increment likes
        likeCount.innerText = `${likes} Likes`; // Update the UI
        button.disabled = true; // Disable button to prevent multiple likes
        button.style.color = 'red'; // Indicate liked state
      } else {
        alert('Failed to like post. Please try again.');
      }
    } else {
      alert('Server error. Please try again later.');
    }
  } catch (error) {
    console.error('Error liking post:', error);
    alert('Failed to connect to the server. Please try again.');
  }
}

// Function to toggle the comment section visibility
function toggleCommentSection(event) {
  const postElement = event.target.closest('.post');
  const commentSection = postElement.querySelector('.comment-section');
  commentSection.style.display = (commentSection.style.display === 'none' || commentSection.style.display === '') ? 'block' : 'none';
}

// Function to handle posting a comment (AJAX update)
async function postComment(event) {
  const postElement = event.target.closest('.post');
  const commentInput = postElement.querySelector('.comment-input');
  const commentsDisplay = postElement.querySelector('.comments-display');
  const postId = postElement.dataset.postId;

  if (commentInput.value.trim()) {
    const commentContent = commentInput.value;

    // Send comment to the server
    const response = await fetch('add_comment.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ post_id: postId, comment_content: commentContent })
    });

    if (response.ok) {
      const result = await response.json();

      // Update comments display
      const newComment = document.createElement('div');
      newComment.classList.add('comment');
      newComment.innerHTML = `
        <img src="${result.commenter_picture}" alt="User" class="comment-avatar">
        <strong>${result.commenter_email}</strong>
        <p>${result.comment_content}</p>
        <span class="timestamp">${result.comment_created_at}</span>
      `;
      commentsDisplay.appendChild(newComment);

      commentInput.value = '';
    } else {
      alert('Failed to post comment. Please try again.');
    }
  }
}
