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
            <!-- More button with dropdown -->
            <div x-data="{ open: false }" class="relative">
                <button 
                    @click="open = !open" 
                    @click.away="open = false"
                    class="text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100 focus:outline-none"
                    aria-label="More options"
                >
                    <i class="fas fa-ellipsis-h text-sm"></i>
                </button>
                
                <!-- Dropdown menu -->
                <div 
                    x-show="open" 
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute right-0 z-10 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
                    role="menu"
                    aria-orientation="vertical"
                    aria-labelledby="menu-button"
                    tabindex="-1"
                    x-cloak
                >
                    <div class="py-1" role="none">
                        <?php
                            $isPostOwner = auth()->check() && auth()->id() === $post->user_id;
                            $isGroupAdmin = auth()->check() && $group->isAdmin(auth()->user());
                            $canEdit = $isPostOwner || $isGroupAdmin;
                        ?>

                        <?php if($canEdit): ?>
                            <!-- Edit option for post owner or group admin -->
                            <a 
                                href="<?php echo e(route('forums.groups.posts.edit', ['group' => $group->slug, 'post' => $post->id])); ?>" 
                                class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100"
                                role="menuitem"
                                tabindex="-1"
                                onclick="event.preventDefault(); document.getElementById('edit-post-<?php echo e($post->id); ?>').submit();"
                            >
                                <i class="fas fa-edit mr-2"></i> Edit Post
                            </a>
                            <form id="edit-post-<?php echo e($post->id); ?>" action="<?php echo e(route('forums.groups.posts.edit', ['group' => $group->slug, 'post' => $post->id])); ?>" method="GET" class="hidden">
                                <?php echo csrf_field(); ?>
                            </form>
                            
                            <!-- Delete option for post owner or group admin -->
                            <form action="<?php echo e(route('forums.groups.posts.destroy', ['group' => $group->slug, 'post' => $post->id])); ?>" method="POST" class="w-full">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button 
                                    type="button" 
                                    class="text-red-600 w-full text-left block px-4 py-2 text-sm hover:bg-gray-100"
                                    role="menuitem"
                                    tabindex="-1"
                                    onclick="if(confirm('Are you sure you want to delete this post?')) { this.form.submit(); }"
                                >
                                    <i class="fas fa-trash mr-2"></i> Delete Post
                                </button>
                            </form>
                        <?php elseif(auth()->check()): ?>
                            <!-- Report option for non-owners -->
                            <button 
                                type="button"
                                class="text-gray-700 w-full text-left block px-4 py-2 text-sm hover:bg-gray-100"
                                onclick="alert('Report functionality will be implemented here')"
                            >
                                <i class="fas fa-flag mr-2"></i> Report Post
                            </button>
                        <?php endif; ?>
                        
                        <!-- Copy Link option (always visible) -->
                        <button 
                            type="button" 
                            class="text-gray-700 w-full text-left block px-4 py-2 text-sm hover:bg-gray-100"
                            role="menuitem"
                            tabindex="-1"
                            @click="navigator.clipboard.writeText('<?php echo e(url()->current()); ?>'); alert('Link copied to clipboard!'); open = false;"
                        >
                            <i class="fas fa-link mr-2"></i> Copy Link
                        </button>
                    </div>
                </div>
            </div>
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
            <?php $__empty_2 = true; $__currentLoopData = $post->replies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reply): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
            <div class="flex items-start space-x-2">
                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-xs font-medium flex-shrink-0">
                    <?php echo e(strtoupper(substr($reply->user->username, 0, 1))); ?>

                </div>
                <div class="flex-1 bg-white rounded-lg p-3 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium"><?php echo e($reply->user->username); ?></span>
                        <span class="text-xs text-gray-400"><?php echo e($reply->created_at->diffForHumans()); ?></span>
                    </div>
                    <p class="text-sm text-gray-700 mt-1 whitespace-pre-line"><?php echo e($reply->content); ?></p>
                    <?php if($reply->media_path): ?>
                        <div class="mt-2">
                            <?php if(str_starts_with($reply->media_type, 'image/')): ?>
                                <img src="<?php echo e(asset('storage/' . $reply->media_path)); ?>" 
                                     alt="Reply image" 
                                     class="max-w-full h-auto rounded-lg border border-gray-200">
                            <?php elseif(str_starts_with($reply->media_type, 'video/')): ?>
                                <video controls class="w-full rounded-lg border border-gray-200">
                                    <source src="<?php echo e(asset('storage/' . $reply->media_path)); ?>" type="<?php echo e($reply->media_type); ?>">
                                    Your browser does not support the video tag.
                                </video>
                            <?php else: ?>
                                <a href="<?php echo e(asset('storage/' . $reply->media_path)); ?>" 
                                   class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    <i class="fas fa-file-download mr-2"></i>
                                    <?php echo e($reply->media_name ?? 'Download File'); ?>

                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                <p class="text-sm text-gray-500 text-center py-2">No replies yet. Be the first to comment!</p>
            <?php endif; ?>

            <!-- Add Comment -->
            <div class="pt-3 mt-3 border-t border-gray-100">
                <form class="reply-form" method="POST" enctype="multipart/form-data" 
                      data-post-id="<?php echo e($post->id); ?>"
                      data-group-slug="<?php echo e($group->slug ?? ''); ?>"
                      action="<?php echo e(route('forums.groups.posts.replies.store', ['group' => $group->slug ?? '', 'post' => $post->id])); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="flex items-start space-x-2">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-xs font-medium flex-shrink-0">
                            <?php echo e(auth()->check() ? strtoupper(substr(auth()->user()->username, 0, 1)) : '?'); ?>

                        </div>
                        <div class="flex-1 relative">
                            <textarea name="content" 
                                    placeholder="Write a comment..." 
                                    rows="1"
                                    class="reply-content w-full bg-gray-50 border-0 rounded-2xl px-4 py-2 pr-10 text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-300 resize-none"
                                    <?php echo e(!auth()->check() ? 'disabled placeholder="Please login to comment"' : ''); ?>></textarea>
                            <div class="absolute right-2 bottom-2 flex items-center space-x-1">
                                <label class="text-gray-400 hover:text-indigo-600 cursor-pointer">
                                    <i class="far fa-image"></i>
                                    <input type="file" name="media" class="hidden" accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx">
                                </label>
                                <button type="submit" class="text-gray-400 hover:text-indigo-600 disabled:opacity-50" <?php echo e(!auth()->check() ? 'disabled' : ''); ?>>
                                    <i class="far fa-paper-plane"></i>
                                </button>
                            </div>
                            <div class="file-info text-xs text-gray-500 mt-1 ml-2"></div>
                        </div>
                    </div>
                </form>
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
// Handle file upload filename display and form submission
document.addEventListener('DOMContentLoaded', function() {
    // Handle file selection for all reply forms
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || '';
            const fileInfo = this.closest('.relative')?.querySelector('.file-info');
            if (fileInfo) {
                fileInfo.textContent = fileName || '';
            }
        });
    });

    // Auto-resize textarea
    document.querySelectorAll('.reply-content').forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    });

    // Track if we're currently submitting
    let isSubmitting = false;

    // Handle reply form submission
    document.body.addEventListener('submit', async function(e) {
        // Only handle .reply-form submissions
        if (!e.target.matches('.reply-form')) return;
        
        e.preventDefault();
        e.stopImmediatePropagation();
        
        const form = e.target;
        
        // Prevent multiple submissions
        if (isSubmitting) {
            console.log('Form submission already in progress');
            return false;
        }
        
        const formData = new FormData(form);
        const submitButton = form.querySelector('button[type="submit"]');
        const contentField = form.querySelector('.reply-content');
        
        // Validate content
        const content = formData.get('content')?.trim() || '';
        if (!content) {
            alert('Please enter a comment');
            return false;
        }
        
        // Set submitting state
        isSubmitting = true;
        const originalButtonHTML = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData,
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || 'Failed to post reply');
            }
            
            // Clear form
            form.reset();
            if (contentField) contentField.style.height = 'auto';
            const fileInput = form.querySelector('input[type="file"]');
            if (fileInput) fileInput.value = '';
            const fileInfo = form.querySelector('.file-info');
            if (fileInfo) fileInfo.textContent = '';
            
            // Reload the page to show the new reply
            window.location.reload();
            
        } catch (error) {
            console.error('Error:', error);
            alert('Error: ' + (error.message || 'Failed to post reply. Please try again.'));
        } finally {
            // Re-enable the form
            isSubmitting = false;
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonHTML;
            }
        }
    }, true); // Use capture phase to ensure we catch the event early

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
});

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
<?php /**PATH /media/prayoga/home/projects/Eduflow_Website/resources/views/forums/groups/partials/posts.blade.php ENDPATH**/ ?>