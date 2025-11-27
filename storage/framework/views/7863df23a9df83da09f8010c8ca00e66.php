<?php $__env->startSection('group-content'); ?>
    <?php if(auth()->check() && $group->isMember(auth()->user())): ?>
        <!-- Create Post Form -->
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 mb-6">
            <form action="<?php echo e(route('forums.groups.posts.store', ['group' => $group->slug])); ?>" method="POST" enctype="multipart/form-data" id="create-post-form">
                <?php echo csrf_field(); ?>
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-medium">
                            <?php echo e(strtoupper(substr(auth()->user()->username, 0, 1))); ?>

                        </div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div>
                            <textarea name="content" 
                                    id="post-content"
                                    rows="3"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-lg"
                                    placeholder="What would you like to discuss?"
                                    required><?php echo e(old('content')); ?></textarea>
                            <?php $__errorArgs = ['content'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="mt-3 flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <label for="media-upload" class="p-2 text-gray-400 hover:text-gray-500 cursor-pointer">
                                    <i class="far fa-image"></i>
                                    <input id="media-upload" name="media" type="file" class="hidden" accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx">
                                </label>
                                <div id="file-name" class="text-sm text-gray-500 truncate max-w-xs"></div>
                            </div>
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Post
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- File Upload Script -->
        <script>
            document.getElementById('media-upload').addEventListener('change', function(e) {
                const fileName = e.target.files[0]?.name || '';
                document.getElementById('file-name').textContent = fileName;
            });
            
            // Handle form submission with AJAX
            document.getElementById('create-post-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const form = e.target;
                const formData = new FormData(form);
                
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload the page to show the new post
                        window.location.reload();
                    } else {
                        console.error('Error:', data.message);
                        alert('Failed to create post: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while creating the post');
                });
            });
        </script>
    <?php endif; ?>

    <!-- Debug Info -->
    <?php if(app()->environment('local')): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        Debug Info: Loaded <?php echo e($posts->count()); ?> posts
                        <?php if($posts->count() > 0): ?>
                            <br>First post ID: <?php echo e($posts->first()->id); ?>

                            <br>First post content: <?php echo e(Str::limit($posts->first()->content, 50)); ?>

                            <br>First post user: <?php echo e($posts->first()->user->name ?? 'N/A'); ?>

                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Posts -->
    <div class="space-y-4" id="posts-container">
        <?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Post Header -->
                <div class="px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-medium">
                                    <?php echo e($post->user ? strtoupper(substr($post->user->username, 0, 1)) : '?'); ?>

                                </div>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">
                                    <?php echo e($post->user ? $post->user->username : 'Deleted User'); ?>

                                </h4>
                                <p class="text-xs text-gray-500"><?php echo e($post->created_at->diffForHumans()); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Post Content -->
                <div class="p-6">
                    <div class="prose max-w-none text-gray-700">
                        <?php echo nl2br(e($post->content)); ?>

                    </div>
                    
                    <?php if($post->media_path): ?>
                        <div class="mt-4">
                            <?php if(str_starts_with($post->media_type, 'image/')): ?>
                                <img src="<?php echo e(asset('storage/' . $post->media_path)); ?>" 
                                     alt="<?php echo e($post->media_name); ?>" 
                                     class="max-w-full h-auto rounded-lg">
                            <?php elseif(str_starts_with($post->media_type, 'video/')): ?>
                                <video controls class="w-full rounded-lg">
                                    <source src="<?php echo e(asset('storage/' . $post->media_path)); ?>" type="<?php echo e($post->media_type); ?>">
                                    Your browser does not support the video tag.
                                </video>
                            <?php else: ?>
                                <a href="<?php echo e(asset('storage/' . $post->media_path)); ?>" 
                                   target="_blank"
                                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    <i class="fas fa-download mr-2"></i>
                                    <?php echo e($post->media_name); ?>

                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Post Actions -->
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-100">
                    <div class="flex items-center justify-between">
                        <button type="button" 
                                class="flex items-center space-x-1 text-gray-500 hover:text-gray-700"
                                onclick="toggleReplies(<?php echo e($post->id); ?>)">
                            <i class="far fa-comment"></i>
                            <span class="text-sm"><?php echo e($post->replies_count ?? 0); ?> <?php echo e(Str::plural('reply', $post->replies_count ?? 0)); ?></span>
                        </button>
                    </div>
                    
                    <!-- Reply Form (Hidden by default) -->
                    <div id="reply-form-<?php echo e($post->id); ?>" class="mt-3 hidden">
                        <form action="<?php echo e(route('forums.groups.posts.replies.store', ['group' => $group->slug, 'post' => $post->id])); ?>" 
                              method="POST" 
                              enctype="multipart/form-data"
                              class="reply-form"
                              data-post-id="<?php echo e($post->id); ?>">
                            <?php echo csrf_field(); ?>
                            <div class="flex space-x-2">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-xs font-medium">
                                        <?php echo e(strtoupper(substr(auth()->user()->name, 0, 1))); ?>

                                    </div>
                                </div>
                                <div class="flex-1">
                                    <textarea name="content" 
                                            rows="2"
                                            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-lg"
                                            placeholder="Write a reply..."
                                            required></textarea>
                                    <div class="mt-2 flex items-center justify-between">
                                        <div>
                                            <input type="file" 
                                                   name="media" 
                                                   id="reply-media-<?php echo e($post->id); ?>" 
                                                   class="hidden reply-media" 
                                                   data-post-id="<?php echo e($post->id); ?>"
                                                   accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx">
                                            <button type="button" 
                                                    class="p-1 text-gray-400 hover:text-gray-500 reply-media-btn"
                                                    data-post-id="<?php echo e($post->id); ?>">
                                                <i class="far fa-image"></i>
                                            </button>
                                            <span id="reply-file-name-<?php echo e($post->id); ?>" class="text-sm text-gray-500"></span>
                                        </div>
                                        <button type="submit" 
                                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Post Reply
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Replies -->
                    <div id="replies-<?php echo e($post->id); ?>" class="mt-3 space-y-3 hidden">
                        <?php $__currentLoopData = $post->replies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reply): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex space-x-3 pt-3">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-xs font-medium">
                                        <?php echo e($reply->user ? strtoupper(substr($reply->user->username, 0, 1)) : '?'); ?>

                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-900">
                                                <?php echo e($reply->user ? $reply->user->username : 'Deleted User'); ?>

                                            </span>
                                            <span class="text-xs text-gray-500"><?php echo e($reply->created_at->diffForHumans()); ?></span>
                                        </div>
                                        <p class="mt-1 text-sm text-gray-700"><?php echo e($reply->content); ?></p>
                                        <?php if($reply->media_path): ?>
                                            <div class="mt-2">
                                                <?php if(str_starts_with($reply->media_type, 'image/')): ?>
                                                    <img src="<?php echo e(asset('storage/' . $reply->media_path)); ?>" 
                                                         alt="<?php echo e($reply->media_name); ?>" 
                                                         class="max-w-xs h-auto rounded">
                                                <?php elseif(str_starts_with($reply->media_type, 'video/')): ?>
                                                    <video controls class="w-full max-w-xs rounded">
                                                        <source src="<?php echo e(asset('storage/' . $reply->media_path)); ?>" type="<?php echo e($reply->media_type); ?>">
                                                        Your browser does not support the video tag.
                                                    </video>
                                                <?php else: ?>
                                                    <a href="<?php echo e(asset('storage/' . $reply->media_path)); ?>" 
                                                       target="_blank"
                                                       class="inline-flex items-center px-3 py-1 border border-gray-300 rounded-md shadow-sm text-xs font-medium text-gray-700 bg-white hover:bg-gray-50">
                                                        <i class="fas fa-download mr-1"></i>
                                                        <?php echo e($reply->media_name); ?>

                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="bg-white rounded-xl p-8 text-center border border-gray-100">
                <i class="fas fa-comment-slash text-4xl text-gray-300 mb-3"></i>
                <h3 class="text-lg font-medium text-gray-900">No posts yet</h3>
                <p class="mt-1 text-sm text-gray-500">
                    <?php if(auth()->check() && $group->isMember(auth()->user())): ?>
                        Be the first to start a discussion in this group!
                    <?php else: ?>
                        Join the group to participate in discussions
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>
        
        <?php if($posts->hasPages()): ?>
            <div class="mt-6">
                <?php echo e($posts->links()); ?>

            </div>
        <?php endif; ?>
    </div>
    
    <!-- Toggle Replies Script -->
    <script>
        function toggleReplies(postId) {
            const replies = document.getElementById(`replies-${postId}`);
            const replyForm = document.getElementById(`reply-form-${postId}`);
            
            if (replies.classList.contains('hidden')) {
                replies.classList.remove('hidden');
                if (replyForm) replyForm.classList.remove('hidden');
            } else {
                replies.classList.add('hidden');
                if (replyForm) replyForm.classList.add('hidden');
            }
        }
        
        // Handle reply media uploads
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('reply-media-btn')) {
                const postId = e.target.getAttribute('data-post-id');
                document.querySelector(`#reply-media-${postId}`).click();
            }
        });
        
        // Update file name when a file is selected
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('reply-media')) {
                const postId = e.target.getAttribute('data-post-id');
                const fileName = e.target.files[0]?.name || '';
                document.getElementById(`reply-file-name-${postId}`).textContent = fileName;
            }
        });
        
        // Handle reply form submission with AJAX
        document.addEventListener('submit', function(e) {
            if (e.target && e.target.classList.contains('reply-form')) {
                e.preventDefault();
                
                const form = e.target;
                const postId = form.getAttribute('data-post-id');
                const formData = new FormData(form);
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;
                
                // Disable submit button and show loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Posting...';
                
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload the page to show the new reply
                        window.location.reload();
                    } else {
                        console.error('Error:', data.message);
                        alert('Failed to post reply: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while posting the reply');
                })
                .finally(() => {
                    // Re-enable submit button and restore original text
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                });
            }
        });
        
        // Load more posts when scrolling to bottom
        let isLoading = false;
        
        function loadMorePosts(url) {
            if (!url || isLoading) return;
            
            isLoading = true;
            const loadMoreBtn = document.querySelector('#load-more-posts');
            if (loadMoreBtn) loadMoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Loading...';
            
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const postsContainer = document.getElementById('posts-container');
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data.html;
                    
                    // Append new posts
                    const newPosts = tempDiv.querySelectorAll('.bg-white.rounded-xl');
                    newPosts.forEach(post => {
                        postsContainer.appendChild(post);
                    });
                    
                    // Update or remove load more button
                    const loadMoreBtnContainer = tempDiv.querySelector('#load-more-container');
                    if (loadMoreBtnContainer) {
                        const currentLoadMoreBtn = document.querySelector('#load-more-container');
                        if (currentLoadMoreBtn) {
                            currentLoadMoreBtn.outerHTML = loadMoreBtnContainer.outerHTML;
                        } else {
                            postsContainer.insertAdjacentHTML('beforeend', loadMoreBtnContainer.outerHTML);
                        }
                    } else if (document.querySelector('#load-more-container')) {
                        document.querySelector('#load-more-container').remove();
                    }
                }
            })
            .catch(error => {
                console.error('Error loading more posts:', error);
                if (loadMoreBtn) loadMoreBtn.innerHTML = 'Error loading posts. Try again.';
            })
            .finally(() => {
                isLoading = false;
            });
        }
        
        // Infinite scroll
        window.addEventListener('scroll', () => {
            if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 500) {
                const loadMoreBtn = document.querySelector('#load-more-posts');
                if (loadMoreBtn && !isLoading) {
                    loadMorePosts(loadMoreBtn.getAttribute('data-url'));
                }
            }
        });
    </script>
<?php $__env->stopSection(); ?>
<?php /**PATH C:\xampp\htdocs\eduflow\resources\views/forums/groups/partials/discussion.blade.php ENDPATH**/ ?>