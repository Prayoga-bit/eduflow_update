

<?php $__env->startSection('title', 'Forums - EduFlow'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .post-card {
        @apply bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-md;
    }
    
    .action-btn {
        @apply flex items-center justify-center p-2 rounded-lg hover:bg-gray-100 text-gray-600 hover:text-indigo-600 transition-colors duration-200;
    }
    
    .forum-banner {
        @apply relative w-full min-h-[200px] md:min-h-[250px] rounded-xl overflow-hidden mb-6;
    }
    
    .forum-banner-bg {
        @apply absolute inset-0 w-full h-full object-cover brightness-75;
    }
    
    .forum-banner-overlay {
        @apply absolute inset-0 bg-gradient-to-b from-black/30 to-black/80;
    }
    
    .forum-banner-content {
        @apply relative z-10 p-6 md:p-8 h-full flex flex-col;
    }
    
    .tab-active {
        @apply text-indigo-600 border-b-2 border-indigo-600;
    }
    
    .tab-inactive {
        @apply text-gray-500 hover:text-gray-700;
    }
    
    .forum-avatar {
        @apply rounded-lg border-4 border-white shadow-lg overflow-hidden;
        width: 80px;
        height: 80px;
    }
    
    .forum-stats {
        @apply flex flex-wrap gap-4 md:gap-6 mt-6;
    }
    
    .stat-item {
        @apply text-center;
    }
    
    .stat-value {
        @apply text-xl md:text-2xl font-bold text-white;
    }
    
    .stat-label {
        @apply text-sm text-white/80;
    }
    
    /* Fix layout issues */
    .main-content {
        min-height: calc(100vh - 200px);
    }
    
    .forum-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1rem;
    }
    
    @media (min-width: 1280px) {
        .forum-container {
            padding: 0;
        }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="main-content">
    <div class="forum-container">
        <!-- Forum Banner -->
        <?php echo $__env->make('forums.components.forum-banner', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Main Content -->
            <div class="flex-1 min-w-0 py-6">
                <!-- Feed Tabs -->
                <div class="bg-white rounded-xl p-1 mb-6 flex border-b border-gray-200">
                    <a href="<?php echo e(route('forums.index')); ?>" 
                       class="flex-1 py-3 px-4 text-center font-medium <?php echo e($activeTab === 'all' ? 'tab-active' : 'tab-inactive'); ?>">
                        <i class="fas fa-stream mr-2"></i>All Posts
                    </a>
                    <div class="h-9 border-r border-gray-200 mx-auto w-px"></div>
                    <a href="<?php echo e(route('forums.media')); ?>" 
                       class="flex-1 py-3 px-4 text-center font-medium <?php echo e($activeTab === 'media' ? 'tab-active' : 'tab-inactive'); ?>">
                        <i class="fas fa-image mr-2"></i>Media
                    </a>
                </div>

                <!-- Feed Content -->
                <div class="space-y-6">
                    <?php echo $__env->yieldContent('forum-content'); ?>
                </div>
            </div>
            
            <!-- Right Sidebar -->
            <div class="lg:w-80 flex-shrink-0">
                <?php echo $__env->make('forums.components.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>
    </div>
</div>

<!-- Create Post Modal -->
<div id="createPostModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" x-data="{ show: false }" x-show="show" x-transition>
    <div class="bg-white rounded-xl w-full max-w-2xl mx-4 overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold">Create Post</h3>
                <button @click="show = false" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="p-5">
            <form id="createPostForm">
                <div class="mb-4">
                    <label for="postTitle" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" id="postTitle" name="title" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="mb-4">
                    <label for="postContent" class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                    <textarea id="postContent" name="content" rows="4" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>
                <div class="mb-4">
                    <label for="postGroup" class="block text-sm font-medium text-gray-700 mb-1">Group (Optional)</label>
                    <select id="postGroup" name="group_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select a group (optional)</option>
                        <?php if(auth()->guard()->check()): ?>
                            <?php $__currentLoopData = auth()->user()->forumGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($group->id); ?>"><?php echo e($group->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <button type="button" class="p-2 text-gray-500 hover:text-indigo-600 rounded-full hover:bg-gray-100">
                            <i class="fas fa-image"></i>
                        </button>
                        <button type="button" class="p-2 text-gray-500 hover:text-indigo-600 rounded-full hover:bg-gray-100">
                            <i class="fas fa-link"></i>
                        </button>
                        <button type="button" class="p-2 text-gray-500 hover:text-indigo-600 rounded-full hover:bg-gray-100">
                            <i class="fas fa-code"></i>
                        </button>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Post
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize any forum-specific JavaScript here
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\eduflow\resources\views/forums/components/feed-layout.blade.php ENDPATH**/ ?>