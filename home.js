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

      // Handle the like/unlike action based on the response
      if (result.user_liked) {
        likeCount += 1; // Increase like count if post was liked
        button.classList.add('liked'); // Add 'liked' class for styling
      } else {
        likeCount -= 1; // Decrease like count if post was unliked
        button.classList.remove('liked'); // Remove 'liked' class
      }

      // Update the like count on the page
      likeCountElement.innerText = `${likeCount} Likes`;
    } else {
      throw new Error('Failed to like/unlike the post.');
    }
  } catch (error) {
    alert(error.message);
  }
}

// Function to toggle the comment section visibility
function toggleCommentSection(event) {
  const postElement = event.target.closest('.post');
  const commentSection = postElement.querySelector('.comment-section');
  // Toggle display of the comment section
  commentSection.style.display = (commentSection.style.display === 'none' || commentSection.style.display === '') ? 'block' : 'none';
}

// Function to handle posting a comment (AJAX update)
async function postComment(event, postId) {
  event.preventDefault(); // Prevent form submission from reloading the page
  const postElement = event.target.closest('.post');
  const commentInput = postElement.querySelector('.comment-input');
  const commentsDisplay = postElement.querySelector('.comments-display');

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

        // Clear the comment input
        commentInput.value = '';
      } else {
        throw new Error('Failed to post comment.');
      }
    } catch (error) {
      alert(error.message);
    }
  }
}
