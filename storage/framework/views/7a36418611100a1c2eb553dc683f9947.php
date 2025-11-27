<?php $__env->startSection('title', 'Home - EduFlow'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .room-card {
        @apply bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow duration-200 border border-gray-100;
    }
    
    .post-card {
        @apply bg-white rounded-lg p-5 shadow-sm hover:shadow-md transition-shadow duration-200 border border-gray-100;
    }
    
    .action-btn {
        @apply flex items-center justify-center p-2 rounded-lg hover:bg-gray-100 text-gray-600 hover:text-indigo-600 transition-colors duration-200;
    }
    
    .pomodoro-timer {
        @apply bg-gradient-to-br from-indigo-600 to-indigo-500 text-white rounded-xl p-5 shadow-lg;
    }
    
    .todo-item {
        @apply flex items-center justify-between py-2 border-b border-gray-100 last:border-0;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Horizontal Room Recommendations -->
    <div class="mb-6">
    <div class="flex items-center justify-between mb-3 px-1">
        <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider flex items-center">
            <i class="fas fa-users mr-2 text-indigo-600"></i>
            Recommended Groups
        </h3>
        <a href="<?php echo e(route('forums.index')); ?>" class="text-xs text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 px-2 py-1 rounded transition-colors">
            See all
        </a>
    </div>
    <div class="relative">
        <div class="flex space-x-3 overflow-x-auto pb-3 -mx-1 px-1" style="scrollbar-width: thin;">
            <?php if(auth()->check()): ?>
            <a href="<?php echo e(route('forum.groups.create')); ?>" class="flex-shrink-0 w-32 bg-white rounded-lg p-3 shadow-xs border border-gray-100 hover:shadow-sm transition-shadow">
                <div class="flex items-center space-x-2">
                    <div class="w-6 h-6 rounded bg-indigo-100 flex-shrink-0 flex items-center justify-center">
                        <i class="fas fa-plus text-indigo-600 text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <h4 class="text-xs font-medium text-gray-900 truncate">Create Group</h4>
                    </div>
                </div>
            </a>
            <?php endif; ?>
            
            <?php $__empty_1 = true; $__currentLoopData = $publicGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <a href="<?php echo e(route('forums.groups.show', $group->slug)); ?>" class="flex-shrink-0 w-32 bg-white rounded-lg p-3 shadow-xs border border-gray-100 hover:shadow-sm transition-shadow">
                <div class="flex items-center space-x-2">
                    <div class="w-6 h-6 rounded bg-gray-200 flex-shrink-0 overflow-hidden">
                        <?php if($group->avatar_path): ?>
                        <img 
                            src="<?php echo e(asset('storage/' . $group->avatar_path)); ?>" 
                            alt="<?php echo e($group->name); ?>" 
                            class="w-full h-full object-cover"
                            style="width: 24px; height: 24px; object-fit: cover;"
                        >
                        <?php else: ?>
                        <div class="w-full h-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-medium">
                            <?php echo e(strtoupper(substr($group->name, 0, 2))); ?>

                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="min-w-0">
                        <h4 class="text-xs font-medium text-gray-900 truncate"><?php echo e($group->name); ?></h4>
                        <p class="text-xs text-gray-500 truncate"><?php echo e($group->members_count); ?> members</p>
                    </div>
                </div>
            </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-sm text-gray-500 py-2">No public groups available</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Create Post -->
<div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 mb-6">
    <?php if(auth()->guard()->check()): ?>
        <form action="<?php echo e(route('forums.posts.store')); ?>" method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <div class="flex items-start space-x-3">
                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-medium flex-shrink-0">
                    <?php echo e(strtoupper(substr(auth()->user()->name, 0, 1))); ?>

                </div>
                <div class="flex-1">
                    <input type="text" 
                           name="title" 
                           placeholder="Title" 
                           class="w-full text-sm p-2 mb-2 bg-gray-50 rounded-lg border border-gray-200 focus:ring-1 focus:ring-indigo-500 focus:border-transparent"
                           required>
                    <div class="flex items-center justify-between border border-gray-200 rounded-lg bg-gray-50">
                        <input type="text" 
                               name="content" 
                               placeholder="What's on your mind?" 
                               class="flex-1 text-sm p-3 bg-transparent focus:outline-none"
                               required>
                        <div class="flex items-center pr-2">
                            <label class="text-gray-400 hover:text-indigo-600 p-2 cursor-pointer">
                                <i class="fas fa-paperclip"></i>
                                <input type="file" 
                                       name="media" 
                                       class="hidden" 
                                       id="media-upload"
                                       accept="image/*,.pdf,.doc,.docx,.xls,.xlsx">
                            </label>
                            <button type="submit" class="bg-indigo-600 text-white text-sm font-medium px-4 py-1.5 rounded-lg hover:bg-indigo-700 transition-colors">
                                Post
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php else: ?>
        <p class="text-sm text-gray-500">Please <a href="<?php echo e(route('login')); ?>" class="text-indigo-600 hover:text-indigo-800 font-medium">login</a> to create a post.</p>
    <?php endif; ?>
    <!-- Selected file preview -->
    <div id="file-preview" class="mt-2 hidden">
        <div class="flex items-center justify-between bg-blue-50 p-2 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-paperclip text-blue-500 mr-2"></i>
                <span id="file-name" class="text-sm text-gray-700"></span>
            </div>
            <button type="button" id="remove-file" class="text-gray-400 hover:text-red-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>

<div class="flex flex-col lg:flex-row gap-6 w-full">
    <!-- Main Content Area -->
    <div class="flex-1 min-w-0">
        <!-- Discussion Feed -->
        <div class="space-y-6">
        <?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="post-card bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-md" x-data="{ showComments: false }">
                <div class="p-5">
                    <!-- Post Header -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-medium">
                                <?php if($post->user): ?>
                                    <?php echo e(strtoupper(substr($post->user->name ?? $post->user->name ?? 'U', 0, 1))); ?>

                                <?php else: ?>
                                    U
                                <?php endif; ?>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900">
                                    <?php
                                        $user = $post->user;
                                        // Use getRawOriginal to bypass any accessors
                                        $userName = $user ? ($user->getRawOriginal('name') ?? 'No name') : 'No user';
                                        $userId = $user ? $user->id : 'N/A';
                                    ?>
                                    
                                    
                                    <?php echo e($userName); ?>

                                    
                                    <?php if(!$user): ?>
                                        <div class="text-red-500 text-xs">No user associated with this post</div>
                                    <?php endif; ?>
                                </h4>
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
                                    ?>

                                    <?php if($isPostOwner): ?>
                                        <!-- Edit option for post owner -->
                                        <a 
                                            href="#" 
                                            class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100"
                                            role="menuitem"
                                            tabindex="-1"
                                            onclick="event.preventDefault(); document.getElementById('edit-post-form-<?php echo e($post->postid); ?>').submit();"
                                        >
                                            <i class="fas fa-edit mr-2"></i> Edit Post
                                        </a>
                                        <form id="edit-post-form-<?php echo e($post->postid); ?>" action="<?php echo e(route('posts.edit', $post->postid)); ?>" method="GET" class="hidden">
                                            <?php echo csrf_field(); ?>
                                        </form>
                                        
                                        <!-- Delete option for post owner -->
                                        <form action="<?php echo e(route('posts.destroy', $post->postid)); ?>" method="POST" class="w-full">
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
                                    
                                    <!-- Copy link option -->
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
                    </div>
                    
                    <!-- Post Title -->
                    <h3 class="text-lg font-semibold text-gray-800 mb-2"><?php echo e($post->title); ?></h3>
                    
                    <!-- Post Content -->
                    <p class="text-gray-700 mb-4 text-sm leading-relaxed">
                        <?php echo e($post->content); ?>

                    </p>
                </div>
                
                <?php if($post->media_path): ?>
                <!-- Post Media/Attachment -->
                <div class="bg-gray-50 border-t border-b border-gray-100">
                    <?php
                        // Get the file extension from the media path
                        $extension = strtolower(pathinfo($post->media_path, PATHINFO_EXTENSION));
                        // Define common image extensions
                        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'tiff'];
                        // Check if extension is in the image extensions array or media_type starts with 'image/'
                        $isImage = in_array($extension, $imageExtensions) || 
                                 (isset($post->media_type) && str_starts_with($post->media_type, 'image'));
                    ?>

                    <?php if($isImage): ?>
                        <!-- Display Image -->
                        <div class="w-full flex justify-center p-2 bg-white">
                            <img src="<?php echo e(asset('storage/' . $post->media_path)); ?>" 
                                 alt="Post media" 
                                 class="max-h-96 max-w-full h-auto object-contain"
                                 loading="lazy">
                        </div>
                    <?php else: ?>
                        <!-- Display Document/Other File -->
                        <div class="p-4">
                            <a href="<?php echo e(asset('storage/' . $post->media_path)); ?>" 
                               target="_blank" 
                               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-file-download mr-2"></i>
                                <?php echo e($post->media_name ?? 'Download Attachment'); ?>

                                <?php if(isset($extension)): ?>
                                    <span class="ml-2 text-xs text-gray-500">(<?php echo e(strtoupper($extension)); ?>)</span>
                                <?php endif; ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                

                
                <div class="p-4">
                    <!-- Post Actions -->
                    <div class="flex items-center text-gray-500 py-2">
                        <!-- like and dislike button -->
                        <div class="flex items-center justify-center">
                            <div class="flex items-center space-x-2 border border-gray-200 rounded-full py-0.5 px-2">
                                <?php 
                                // Debug: Check if post ID exists
                                // echo '<!-- Post ID: ' . $post->id . ' -->';
                                ?>
                                <button 
                                    class="like-button flex items-center justify-center text-gray-500 hover:text-indigo-600 transition-colors duration-200 py-1.5 px-2"
                                    data-post-id="<?php echo e($post->postid); ?>">
                                    <i class="far fa-thumbs-up text-base"></i>
                                    <span class="like-count text-sm px-2"><?php echo e($post->like_count ?? 0); ?></span>
                                </button>
                                <div class="h-5 border-r border-gray-300 mx-1"></div>
                                <button class="group flex items-center justify-center py-1.5 px-2" onmouseover="this.querySelector('i').style.color='#DC2626'" onmouseout="this.querySelector('i').style.color='#6B7280'">
                                    <i class="far fa-thumbs-down fa-flip-horizontal text-base" style="color: #6B7280; transition: color 200ms;"></i>
                                </button>
                            </div>
                        </div>
                        <!-- comment button --> 
                        <button @click="showComments = !showComments" class="flex-0 flex items-center justify-center hover:text-indigo-600 transition-colors duration-200 px-2">
                            <i class="far fa-comment text-lg"></i>
                            <span class="ml-1 text-sm"><?php echo e($post->replies->count()); ?></span>
                        </button>
                        <!-- share button -->
                        <button class="flex-0 flex items-center justify-center hover:text-indigo-600 transition-colors duration-200 px-2">
                            <i class="fas fa-share text-lg"></i>
                        </button>
                    </div>
                    
                    <!-- Comments -->
                    <div x-show="showComments" x-transition class="space-y-3 mt-4 pt-4 border-t border-gray-100">
                        <?php $__empty_2 = true; $__currentLoopData = $post->replies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reply): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                        <div class="flex items-start space-x-2">
                            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 text-xs font-medium flex-shrink-0">
                                <?php echo e(strtoupper(substr($reply->user->name, 0, 1))); ?>

                            </div>
                            <div class="bg-gray-50 rounded-lg p-2 text-sm flex-1">
                                <div class="font-medium text-gray-900"><?php echo e($reply->user->name); ?></div>
                                <p class="text-gray-700"><?php echo e($reply->content); ?></p>
                                <div class="text-xs text-gray-400 mt-1"><?php echo e($reply->created_at->diffForHumans()); ?></div>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                        <p class="text-sm text-gray-500 text-center py-2">No comments yet</p>
                        <?php endif; ?>
                        
                        <!-- Add Comment -->
                        <div class="flex items-center space-x-2 mt-3">
                            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 text-xs font-medium flex-shrink-0">
                                <?php if(auth()->guard()->check()): ?>
                                    <?php echo e(strtoupper(substr(auth()->user()->name, 0, 1))); ?>

                                <?php else: ?>
                                    <i class="fas fa-user text-xs"></i>
                                <?php endif; ?>
                            </div>
                            <form action="<?php echo e(route('forum.reply', $post->postid)); ?>" method="POST" class="flex-1 relative">
                                <?php echo csrf_field(); ?>
                                <input 
                                    type="text" 
                                    name="content"
                                    placeholder="Write a comment..." 
                                    class="w-full text-sm p-2 pr-8 bg-gray-50 rounded-full border border-gray-200 focus:ring-1 focus:ring-indigo-500 focus:border-transparent"
                                    <?php if(auth()->guard()->guest()): ?> disabled <?php endif; ?>
                                    <?php if(auth()->guard()->guest()): ?> onclick="window.location.href='<?php echo e(route('login')); ?>'" <?php endif; ?>
                                    required
                                >
                                <div class="absolute right-2 top-1/2 transform -translate-y-1/2 flex space-x-1">
                                    <button type="button" class="text-gray-400 hover:text-indigo-600">
                                        <i class="far fa-smile"></i>
                                    </button>
                                    <button type="button" class="text-gray-400 hover:text-indigo-600">
                                        <i class="fas fa-paperclip"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-center py-10">
                <p class="text-gray-500">No forum posts found. Be the first to create one!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Right Sidebar -->
    <div class="lg:w-80 flex-shrink-0">
        <div class="sticky top-4 space-y-6">
            <!-- To-Do List -->
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-gray-800">To-Do List</h3>
                    <a href="<?php echo e(route('tasks.index')); ?>" class="text-indigo-600 hover:text-indigo-800">
                        <i class="fas fa-plus"></i>
                    </a>
                </div>
                <div class="space-y-2">
                    <?php $__empty_1 = true; $__currentLoopData = $recentTasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="todo-item flex items-center justify-between group">
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   class="rounded text-indigo-600 focus:ring-indigo-500 mr-2 task-checkbox" 
                                   data-task-id="<?php echo e($task->id); ?>"
                                   <?php echo e($task->status === 'done' ? 'checked' : ''); ?>>
                            <span class="text-sm <?php echo e($task->status === 'done' ? 'line-through text-gray-400' : 'text-gray-700'); ?>">
                                <?php echo e($task->title); ?>

                                <?php if($task->due_date): ?>
                                    <span class="text-xs text-gray-500 ml-1">
                                        (<?php echo e(\Carbon\Carbon::parse($task->due_date)->diffForHumans()); ?>)
                                    </span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="<?php echo e(route('tasks.index')); ?>" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-ellipsis-h text-xs"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="text-center py-2">
                        <p class="text-sm text-gray-500">No tasks found</p>
                        <a href="<?php echo e(route('tasks.index')); ?>" class="text-indigo-600 text-sm hover:underline">
                            Create your first task
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if(count($recentTasks) > 0): ?>
                <a href="<?php echo e(route('tasks.index')); ?>" class="block w-full mt-3 text-sm text-center text-indigo-600 hover:bg-indigo-50 py-1.5 rounded-md transition-colors duration-200">
                    View all tasks
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('post', () => ({
            showComments: false
        }))
    })
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Media upload handling
        const mediaInput = document.getElementById('media-upload');
        const filePreview = document.getElementById('file-preview');
        const fileName = document.getElementById('file-name');
        const removeFileBtn = document.getElementById('remove-file');

        if (mediaInput) {
            mediaInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    fileName.textContent = this.files[0].name;
                    filePreview.classList.remove('hidden');
                }
            });
        }

        if (removeFileBtn) {
            removeFileBtn.addEventListener('click', function() {
                mediaInput.value = '';
                filePreview.classList.add('hidden');
            });
        }

        // Like button functionality
        document.addEventListener('click', async function(e) {
            // Check if the clicked element or its parent is a like button
            const button = e.target.closest('.like-button');
            if (!button) return;
            
            e.preventDefault();
            e.stopPropagation();
            
            let postId = button.getAttribute('data-post-id');
            console.log('Post ID from attribute:', postId); // Debug log
            
            // Try to get post ID from the closest post element if data-post-id is empty
            if (!postId) {
                const postElement = button.closest('[data-post-id]');
                if (postElement) {
                    postId = postElement.getAttribute('data-post-id');
                    console.log('Found post ID from parent element:', postId);
                }
            }
            
            if (!postId) {
                console.error('No post ID found on the like button or its parents');
                console.log('Button attributes:', button.attributes);
                console.log('Button HTML:', button.outerHTML);
                return;
            }
            
            const likeCount = button.querySelector('.like-count');
            const icon = button.querySelector('i');
            
            // Disable button to prevent multiple clicks
            button.disabled = true;
            
            // Show loading state
            const originalHTML = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            try {
                // Send AJAX request
                const response = await fetch(`/forums/posts/${postId}/like`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Update like count
                    likeCount.textContent = data.like_count;
                    
                    // Toggle active state
                    button.classList.toggle('text-indigo-600');
                    icon.classList.toggle('far');
                    icon.classList.toggle('fas');
                    
                    // Show success message
                    const toast = document.createElement('div');
                    toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center z-50';
                    toast.innerHTML = `
                        <i class="fas fa-thumbs-up mr-2"></i>
                        Post liked!
                    `;
                    document.body.appendChild(toast);
                    
                    // Remove toast after 3 seconds
                    setTimeout(() => {
                        toast.remove();
                    }, 3000);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to like the post. Please try again.');
            } finally {
                // Re-enable button and restore original content
                button.disabled = false;
                button.innerHTML = originalHTML;
                if (icon) {
                    icon.className = button.classList.contains('text-indigo-600') ? 'fas fa-thumbs-up text-base' : 'far fa-thumbs-up text-base';
                }
            }
        });
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /media/prayoga/home/projects/Eduflow_Website/resources/views/home.blade.php ENDPATH**/ ?>