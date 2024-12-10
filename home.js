// Function to handle likes
async function likePost(button) {
  const postElement = button.closest('.post');
  const postId = postElement.dataset.postId; // Get the post ID from the data attribute
  const likeCountElement = postElement.querySelector('.like-count');
  let likeCount = parseInt(likeCountElement.innerText.split(' ')[0]) || 0;

  try {
    // Send an AJAX request to like_post.php
    const response = await fetch('like_post.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ post_id: postId })
    });

    if (response.ok) {
      const result = await response.json();

      if (result.action === 'liked') {
        likeCount += 1; // Increase like count if post was liked
        button.style.color = 'red'; // Change the color of the like button to indicate it was liked
      } else if (result.action === 'unliked') {
        likeCount -= 1; // Decrease like count if post was unliked
        button.style.color = ''; // Reset the like button color if unliked
      }

      // Update the like count on the page
      likeCountElement.innerText = `${likeCount} Likes`;

      // Disable the like button after it is clicked (optional)
      button.disabled = false; // Ensure button remains usable for toggling
    } else {
      alert('Failed to like/unlike the post. Please try again.');
    }
  } catch (error) {
    console.error('Error liking/unliking post:', error);
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
  event.preventDefault(); // Prevent the form from submitting normally

  const postElement = event.target.closest('.post');
  const commentInput = postElement.querySelector('.comment-input');
  const commentsDisplay = postElement.querySelector('.comments-display');
  const postId = postElement.dataset.postId;

  if (commentInput.value.trim()) {
    const commentContent = commentInput.value;

    try {
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

        commentInput.value = ''; // Clear the input field
      } else {
        alert('Failed to post comment. Please try again.');
      }
    } catch (error) {
      console.error('Error posting comment:', error);
    }
  }
}
