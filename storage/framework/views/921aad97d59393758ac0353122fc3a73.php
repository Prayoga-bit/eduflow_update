

<?php $__env->startSection('title', 'Forums - EduFlow'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .group-card {
        @apply bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-300;
    }
    
    .group-banner {
        @apply w-full h-32 rounded-t-lg object-cover;
    }
    
    .group-avatar {
        @apply w-16 h-16 rounded-full border-4 border-white -mt-8 ml-6 bg-white shadow-md;
    }
    
    .badge {
        @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
    }
    
    .badge-primary {
        @apply bg-indigo-100 text-indigo-800;
    }
    
    .badge-success {
        @apply bg-green-100 text-green-800;
    }
    
    .stat-value {
        @apply text-lg font-semibold text-gray-900;
    }
    
    .stat-label {
        @apply text-sm text-gray-500;
    }
    
    .tab-active {
        @apply text-indigo-600 border-b-2 border-indigo-600;
    }
    
    .tab-inactive {
        @apply text-gray-500 hover:text-gray-700;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Forum Groups</h2>
            <?php if(auth()->guard()->check()): ?>
                <a href="<?php echo e(route('forum.groups.create')); ?>" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-plus mr-2"></i> Create Group
                </a>
            <?php endif; ?>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-xl p-1 mb-6 flex border-b border-gray-200">
            <a href="<?php echo e(route('forums.index')); ?>" 
               class="flex-1 py-3 px-4 text-center font-medium <?php echo e(request()->routeIs('forums.index') ? 'tab-active' : 'tab-inactive'); ?>">
                <i class="fas fa-users mr-2"></i>All Groups
            </a>
            <?php if(auth()->guard()->check()): ?>
            <div class="h-9 border-r border-gray-200 mx-auto w-px"></div>
            <a href="<?php echo e(route('forums.my-groups')); ?>" 
               class="flex-1 py-3 px-4 text-center font-medium <?php echo e(request()->routeIs('forums.my-groups') ? 'tab-active' : 'tab-inactive'); ?>">
                <i class="fas fa-user-friends mr-2"></i>My Groups
            </a>
            <?php endif; ?>
        </div>

        <!-- Search and Filter -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" 
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Search groups...">
                </div>
                <div class="flex items-center space-x-2">
                    <select class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-lg">
                        <option>All Categories</option>
                        <option>Study Groups</option>
                        <option>Course Discussions</option>
                        <option>General</option>
                    </select>
                    <button class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                </div>
            </div>
        </div>

        <!-- Groups Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 px-4 sm:px-6 lg:px-8 py-8">
            <?php $__empty_1 = true; $__currentLoopData = $forumGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300 border border-gray-100 relative">
                    <!-- Banner -->
                    <div class="h-32 bg-gradient-to-r from-indigo-600 to-purple-700 overflow-hidden">
                        <?php if($group->cover_image): ?>
                            <img src="<?php echo e(asset('storage/' . $group->cover_image)); ?>" 
                                 alt="<?php echo e($group->name); ?>" 
                                 class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-white/20">
                                <i class="fas fa-image text-4xl"></i>
                            </div>
                        <?php endif; ?>
                    </div>
            
                    <!-- Content -->
                    <div class="relative px-6 pb-5 -mt-8 bg-white rounded-t-xl z-10 py-4">
                        <!-- Group Header -->
                        <div class="flex items-start justify-between -mt-4 mb-4">
                            <!-- Group Icon -->
                            <div class="bg-white rounded-full shadow-md">
                                <?php if($group->icon): ?>
                                    <img src="<?php echo e(asset('storage/' . $group->icon)); ?>" 
                                         alt="<?php echo e($group->name); ?>" 
                                         class="w-16 h-16 rounded-full border-4 border-white bg-white object-cover">
                                <?php else: ?>
                                    <div class="w-16 h-16 rounded-full border-4 border-white bg-gradient-to-br from-indigo-100 to-blue-100 flex items-center justify-center text-2xl font-bold text-indigo-600">
                                        <?php echo e(strtoupper(substr($group->name, 0, 1))); ?>

                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Privacy Badge -->
                            <div class="mt-2">
                                <?php if($group->is_private): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 shadow-sm">
                                        <i class="fas fa-lock text-[10px] mr-1 px-0.5"></i> Private
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 shadow-sm">
                                        <i class="fas fa-globe-americas text-[10px] mr-1 px-0.5"></i> Public
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Group Description -->
                        <div class="mt-2">
                            <h3 class="text-base font-semibold text-gray-900 mb-2 line-clamp-1"><?php echo e($group->name); ?></h3>
                            <p class="text-gray-600 text-sm line-clamp-2 min-h-[2.5rem]">
                                <?php echo e($group->description ?: 'No description provided.'); ?>

                            </p>
                        </div>
            
                        <!-- Members Preview -->
                        <div class="flex items-center justify-between mb-5">
                            <div class="flex -space-x-2">
                                <?php $__currentLoopData = $group->members->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="w-7 h-7 rounded-full bg-indigo-100 border-2 border-white overflow-hidden shadow-sm hover:z-10 hover:shadow-md transition-transform hover:-translate-y-0.5 flex-shrink-0">
                                        <?php
                                            $hasPhoto = !empty($member->profile_photo_path);
                                            $initials = strtoupper(substr($member->name, 0, 1));
                                        ?>
                                        <?php if($hasPhoto): ?>
                                            <img src="<?php echo e(asset('storage/' . $member->profile_photo_path)); ?>" 
                                                 alt="<?php echo e($member->name); ?>"
                                                 class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <div class="w-full h-full flex items-center justify-center text-xs font-medium text-indigo-700">
                                                <?php echo e($initials); ?>

                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php if($group->members_count > 3): ?>
                                    <div class="w-7 h-7 rounded-full bg-indigo-50 border-2 border-white flex items-center justify-center text-xs font-medium text-indigo-600 shadow-sm">
                                        +<?php echo e($group->members_count - 3); ?>

                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- count -->
                            <div class="text-right">
                                <div class="text-sm font-medium text-gray-700"><?php echo e($group->posts_count); ?> <?php echo e(Str::plural('post', $group->posts_count)); ?></div>
                                <div class="text-xs text-gray-500"><?php echo e($group->members_count); ?> <?php echo e(Str::plural('member', $group->members_count)); ?></div>
                            </div>
                        </div>
            
                        <!-- Action Button -->
                        <div class="pt-4">
                            <?php if(auth()->check() && ($group->isMember(auth()->user()) || $group->isAdmin(auth()->user()))): ?>
                                <a href="<?php echo e(route('forums.groups.show', $group->slug)); ?>" 
                                   class="w-full flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm hover:shadow-md transition-all duration-200">
                                    <i class="fas fa-door-open mr-2 text-xs"></i> Enter Group
                                </a>
                            <?php else: ?>
                                <a href="<?php echo e(route('forums.groups.show', $group->slug)); ?>" 
                                   class="w-full flex items-center justify-center px-4 py-2 bg-white border border-indigo-600 text-indigo-600 hover:bg-indigo-50 text-sm font-medium rounded-lg shadow-sm hover:shadow-md transition-all duration-200">
                                    <i class="fas fa-eye mr-2 text-xs"></i> View
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-span-full py-16 text-center bg-white rounded-xl shadow-sm border border-gray-100">
                    <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-500">
                        <i class="fas fa-users text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">No groups found</h3>
                    <p class="text-gray-600 mb-6 max-w-md mx-auto">Be the first to create a group and start the conversation!</p>
                    <?php if(auth()->guard()->check()): ?>
                        <a href="<?php echo e(route('forum.groups.create')); ?>" 
                           class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm hover:shadow-md transition-all duration-200">
                            <i class="fas fa-plus mr-2 text-xs"></i> Create New Group
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if($forumGroups->hasPages()): ?>
            <div class="mt-8">
                <?php echo e($forumGroups->links()); ?>

            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\eduflow\resources\views/forums/index.blade.php ENDPATH**/ ?>