// Function to handle likes
function likePost(button) {
  const likeCount = button.closest('.post').querySelector('.like-count');
  let likes = parseInt(likeCount.innerText.split(' ')[0]) || 0;
  likes += 1;
  likeCount.innerText = `${likes} Likes`;
  button.disabled = true;
  button.style.color = 'red';
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
