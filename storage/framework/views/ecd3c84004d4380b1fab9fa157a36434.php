<?php $__env->startSection('title', $group->name . ' - EduFlow'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .group-avatar {
        @apply w-24 h-24 rounded-full border-4 border-white -mt-12 ml-8 bg-white shadow-lg;
    }
    
    .tab-active {
        @apply border-indigo-500 text-indigo-600 border-b-2 font-medium;
    }
    
    .tab-inactive {
        @apply border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300;
    }
    
    .member-avatar {
        @apply w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-medium text-sm;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Forum Banner -->
        <?php echo $__env->make('forums.components.forum-banner', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        
        <!-- Group Navigation Tabs -->
        <?php echo $__env->make('forums.groups.partials.nav-tabs', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        
        <div class="mt-6 flex flex-col lg:flex-row gap-6">
            <!-- Main Content -->
            <main class="flex-1 min-w-0 py-2">
                <?php if(request()->routeIs('forums.groups.show') || request()->routeIs('forums.groups.discussion')): ?>
                    <!-- Posts List -->
                    <?php echo $__env->make('forums.groups.partials.posts', ['posts' => $posts], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php else: ?>
                    <?php if (! empty(trim($__env->yieldContent('group-content')))): ?>
                        <?php echo $__env->yieldContent('group-content'); ?>
                    <?php endif; ?>
                <?php endif; ?>
            </main>
            
            <!-- Sidebar -->
            <aside class="lg:w-80 flex-shrink-0 space-y-6">
                <?php echo $__env->make('forums.components.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </aside>
        </div>
    </div>
</div>

<?php echo $__env->yieldPushContent('modals'); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Group specific JavaScript can go here
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize any group page specific JavaScript
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /media/prayoga/home/projects/Eduflow_Website/resources/views/forums/groups/show.blade.php ENDPATH**/ ?>