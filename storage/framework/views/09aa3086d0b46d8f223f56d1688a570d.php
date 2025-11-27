<div id="posts-container" data-group-slug="<?php echo e($group->slug ?? ''); ?>">
<?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<article class="post-card bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-md mb-6" x-data="{ showComments: false }">
    <div class="p-5">
        <!-- Post Header -->
        <header class="flex items-start justify-between mb-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-medium">
                    <?php echo e($post->user ? strtoupper(substr($post->user->username, 0, 1)) : '?'); ?>

                </div>
                <div>
                    <h3 class="font-medium"><?php echo e($post->user ? $post->user->username : 'Deleted User'); ?></h3>
                    <p class="text-xs text-gray-500"><?php echo e($post->created_at->diffForHumans()); ?></p>
                </div>
            </div>
            <button class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-ellipsis-h"></i>
            </button>
        </header>

        <!-- Post Content -->
        <div class="mb-4">
            <p class="text-gray-800 mb-3 whitespace-pre-line">
                <?php echo e($post->content); ?>

            </p>
            
            <?php if($post->media_path): ?>
            <div class="mt-3 rounded-lg overflow-hidden">
                <?php if(in_array($post->media_type, ['image/jpeg', 'image/png', 'image/gif'])): ?>
                    <img src="<?php echo e(asset('storage/' . $post->media_path)); ?>" 
                         alt="Post image" 
                         class="w-full h-auto rounded-lg">
                <?php elseif($post->media_type === 'video/mp4'): ?>
                    <video controls class="w-full rounded-lg">
                        <source src="<?php echo e(asset('storage/' . $post->media_path)); ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                <?php else: ?>
                    <a href="<?php echo e(asset('storage/' . $post->media_path)); ?>" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-file-download mr-2"></i>
                        Download <?php echo e($post->media_name); ?>

                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Post Actions -->
        <div class="flex items-center justify-between text-gray-500 pt-3 border-t">
            <div class="flex space-x-4">
                <button class="flex items-center space-x-1 hover:text-indigo-600">
                    <i class="far fa-thumbs-up"></i>
                    <span>0</span>
                </button>
                <button class="flex items-center space-x-1 hover:text-indigo-600" @click="showComments = !showComments">
                    <i class="far fa-comment"></i>
                    <span><?php echo e($post->replies_count ?? 0); ?></span>
                </button>
            </div>
            <div>
                <button class="flex items-center space-x-1 hover:text-indigo-600">
                    <i class="far fa-share-square"></i>
                    <span>Share</span>
                </button>
            </div>
        </div>

        <!-- Comments Section -->
        <div x-show="showComments" x-transition class="space-y-3 mt-4 pt-4 border-t border-gray-100 px-5 pb-5 -mx-5 -mb-5 bg-gray-50">
            <?php for($j = 1; $j <= 2; $j++): ?>
            <div class="flex items-start space-x-2">
                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 text-xs font-medium flex-shrink-0">
                    <?php echo e(strtoupper(substr("User $j", 0, 1))); ?>

                </div>
                <div class="flex-1 bg-gray-50 rounded-lg p-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium">User <?php echo e($j); ?></span>
                        <span class="text-xs text-gray-400"><?php echo e(now()->subMinutes(rand(5, 60))->diffForHumans()); ?></span>
                    </div>
                    <p class="text-sm text-gray-700 mt-1">This is a sample comment on the post.</p>
                </div>
            </div>
            <?php endfor; ?>

            <!-- Add Comment -->
            <div class="flex items-center space-x-2 pt-3 mt-3 border-t border-gray-100">
                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 text-xs font-medium flex-shrink-0">
                    <?php echo e(strtoupper(substr("You", 0, 1))); ?>

                </div>
                <div class="flex-1 relative">
                    <input type="text" 
                           placeholder="Write a comment..." 
                           class="w-full bg-gray-50 border-0 rounded-full px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-300">
                    <button class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-indigo-600">
                        <i class="far fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</article>

<?php if($posts->hasMorePages()): ?>
    <div class="mt-6 text-center">
        <button id="load-more-posts" 
                data-next-page="<?php echo e($posts->nextPageUrl()); ?>"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Load More Posts
        </button>
    </div>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Handle file upload filename display
    document.addEventListener('DOMContentLoaded', function() {
        const mediaUpload = document.getElementById('media-upload');
        if (mediaUpload) {
            mediaUpload.addEventListener('change', function(e) {
                const fileName = e.target.files[0]?.name || '';
                const fileNameDisplay = document.getElementById('file-name');
                if (fileNameDisplay) {
                    fileNameDisplay.textContent = fileName;
                }
            });
        }

        // Handle form submission with AJAX
        const createPostForm = document.getElementById('create-post-form');
        if (createPostForm) {
            createPostForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const submitButton = this.querySelector('button[type="submit"]');
                const originalButtonText = submitButton.innerHTML;
                
                // Disable button and show loading state
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Posting...';
                
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Clear form
                        this.reset();
                        const fileNameDisplay = document.getElementById('file-name');
                        if (fileNameDisplay) fileNameDisplay.textContent = '';
                        
                        // Reload the page to show the new post
                        window.location.reload();
                    } else {
                        alert('Failed to create post: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while creating the post');
                })
                .finally(() => {
                    // Re-enable button
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                });
            });
        }
        
        // Load more posts functionality
        let isLoading = false;
        const loadMoreBtn = document.getElementById('load-more-posts');
        
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', function() {
                const nextPageUrl = this.dataset.nextPage;
                if (!nextPageUrl || isLoading) return;
                
                isLoading = true;
                const originalButtonText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Loading...';
                
                fetch(nextPageUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    // Create temporary container
                    const temp = document.createElement('div');
                    temp.innerHTML = html;
                    
                    // Find posts container in the response
                    const newPostsContainer = temp.querySelector('#posts-container');
                    if (newPostsContainer) {
                        // Append new posts to the current container
                        document.getElementById('posts-container').insertAdjacentHTML(
                            'beforeend',
                            Array.from(newPostsContainer.children).map(el => el.outerHTML).join('')
                        );
                        
                        // Update load more button
                        const newLoadMoreBtn = temp.querySelector('#load-more-posts');
                        if (newLoadMoreBtn && newLoadMoreBtn.dataset.nextPage) {
                            loadMoreBtn.dataset.nextPage = newLoadMoreBtn.dataset.nextPage;
                        } else {
                            loadMoreBtn.remove();
                        }
                    } else {
                        loadMoreBtn.remove();
                    }
                })
                .catch(error => {
                    console.error('Error loading more posts:', error);
                    loadMoreBtn.innerHTML = 'Error loading posts. Try again.';
                    setTimeout(() => {
                        loadMoreBtn.innerHTML = originalButtonText;
                    }, 2000);
                })
                .finally(() => {
                    isLoading = false;
                });
            });
        }
        
        // Toggle replies functionality
        window.toggleReplies = function(postId) {
            const replies = document.getElementById(`replies-${postId}`);
            const replyForm = document.getElementById(`reply-form-${postId}`);
            
            if (replies && replyForm) {
                replies.classList.toggle('hidden');
                replyForm.classList.toggle('hidden');
            }
        };
        
        // Handle reply form submission
        window.handleReply = function(form, event) {
            event.preventDefault();
            
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            
            // Disable button and show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Posting...';
            
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload the page to show the new reply
                    window.location.reload();
                } else {
                    alert('Failed to post reply: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while posting the reply');
            })
            .finally(() => {
                // Re-enable button
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            });
        };
    });
</script>
<?php $__env->stopPush(); ?>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="bg-white rounded-xl p-8 text-center border border-gray-100">
        <i class="fas fa-comment-slash text-4xl text-gray-300 mb-3"></i>
        <h3 class="text-lg font-medium text-gray-900">No posts yet</h3>
        <p class="text-gray-500">Be the first to start a discussion in this group!</p>
    </div>
<?php endif; ?>
</div>

<?php if($posts->hasMorePages()): ?>
<div class="mt-8 text-center">
    <button class="bg-indigo-50 text-indigo-600 px-6 py-2 rounded-full font-medium hover:bg-indigo-100 transition" id="load-more-posts" data-next-page="<?php echo e($posts->nextPageUrl()); ?>">
        <span class="inline-flex items-center">
            <span>Load More Posts</span>
            <svg id="loading-spinner" class="hidden animate-spin -mr-1 ml-2 h-4 w-4 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </span>
    </button>
</div>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const loadMoreButton = document.getElementById('load-more-posts');
        
        if (loadMoreButton) {
            loadMoreButton.addEventListener('click', function() {
                const button = this;
                const url = button.getAttribute('data-next-page');
                const loadingSpinner = button.querySelector('#loading-spinner');
                
                if (!url) return;
                
                // Show loading state
                button.disabled = true;
                loadingSpinner.classList.remove('hidden');
                
                // Fetch the next page
                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Append new posts
                    const temp = document.createElement('div');
                    temp.innerHTML = data.html;
                    const postsContainer = document.getElementById('posts-container');
                    const newPosts = temp.querySelectorAll('.post-card');
                    
                    newPosts.forEach(post => {
                        postsContainer.appendChild(post);
                    });
                    
                    // Update the next page URL or remove the button if there are no more pages
                    if (data.next_page_url) {
                        button.setAttribute('data-next-page', data.next_page_url);
                    } else {
                        button.parentElement.remove();
                    }
                })
                .catch(error => {
                    console.error('Error loading more posts:', error);
                })
                .finally(() => {
                    // Reset button state
                    button.disabled = false;
                    loadingSpinner.classList.add('hidden');
                });
            });
        }
    });
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\xampp\htdocs\eduflow\resources\views/forums/partials/posts.blade.php ENDPATH**/ ?>