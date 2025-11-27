<!-- Forum Banner Component -->
<?php
    $group = $group ?? null;
    $bannerImage = $group && $group->banner_image 
        ? asset('storage/' . $group->banner_image) 
        : asset('images/forum/banner.jpg');
    $avatarImage = $group && $group->avatar 
        ? asset('storage/' . $group->avatar) 
        : asset('images/forum/avatar.jpg');
    $memberCount = $group ? $group->members_count ?? $group->members()->count() : 1200;
    $postCount = $group ? $group->posts_count ?? $group->posts()->count() : 5700;
    $isMember = $group && auth()->check() ? $group->isMember(auth()->user()) : false;
?>

<div class="forum-banner relative w-full rounded-t-xl" style="height: 16rem;">
    <!-- Background Image Container -->
    <div class="absolute inset-0 w-full h-full overflow-hidden rounded-t-xl">
        <!-- Background Image -->
        <div class="absolute inset-0 w-full h-full rounded-t-xl">
            <img src="<?php echo e($bannerImage); ?>" 
                 alt="<?php echo e($group ? $group->name . ' Banner' : 'Forum Banner'); ?>" 
                 class="w-full h-full object-cover object-center"
                 style="filter: brightness(0.8) blur(1px); min-width: 100%; min-height: 100%; position: absolute; top: 0; left: 0; right: 0; bottom: 0;">
        </div>
    
        <!-- Dark overlay with gradient -->
        <div class="absolute inset-0 bg-gradient-to-b from-black/40 to-black/70"></div>
        
        <!-- Content -->
        <div class="relative z-10 h-full flex items-center">
            <div class="container mx-auto px-4 md:px-6 lg:px-8 py-8">
                <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
                    <!-- Left Side: Avatar and Title -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full lg:w-auto">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl overflow-hidden border-2 border-black/30 shadow-lg bg-white/50 flex-shrink-0">
                            <img src="<?php echo e($avatarImage); ?>" 
                                 alt="<?php echo e($group ? $group->name . ' Avatar' : 'Forum Avatar'); ?>" 
                                 class="w-full h-full object-cover">
                        </div>
                        <div class="text-white">
                            <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white drop-shadow-lg">
                                <?php echo e($group ? $group->name : 'Forum'); ?>

                            </h1>
                            <?php if($group && $group->description): ?>
                                <p class="text-white mt-2 text-sm sm:text-base max-w-3xl leading-relaxed">
                                    <?php echo e(Str::limit($group->description, 120)); ?>

                                </p>
                            <?php endif; ?>
                        </div>
                    </div>  
                    
                    <!-- Right Side: Stats and Button -->
                    <div class="w-8 lg:w-auto mt-2 lg:mt-0 right-2 absolute">
                        <?php if($group): ?>
                        <!-- Stats -->
                        <div class="flex items-center justify-between sm:justify-start gap-3 sm:gap-4 bg-white/50 backdrop-blur-sm px-4 py-2 rounded-xl shadow-xl">
                            <div class="text-center min-w-[70px]">
                                <div class="text-xl sm:text-2xl font-bold text-white"><?php echo e(number_format($memberCount)); ?></div>
                                <div class="text-xs sm:text-sm text-white font-medium">Members</div>
                            </div>
                            <div class="h-8 w-px bg-white/40"></div>
                            <div class="text-center min-w-[70px]">
                                <div class="text-xl sm:text-2xl font-bold text-white"><?php echo e(number_format($postCount)); ?></div>
                                <div class="text-xs sm:text-sm text-white font-medium">Posts</div>
                            </div>
                            <!-- Online
                            <div class="h-8 w-px bg-white/40"></div>
                            
                            <div class="text-center min-w-[70px]">
                                <div class="text-xl sm:text-2xl font-bold text-white">
                                    <?php echo e(rand(10, $memberCount > 100 ? 100 : $memberCount)); ?>

                                </div>
                                <div class="text-xs sm:text-sm text-white font-medium">Online</div>
                            </div> -->
                        </div>
                        
                        <!-- Join/Leave Button -->
                        <div class="mt-3 w-32 sm:w-1/2 bg-white/50 backdrop-blur-sm rounded-xl right-0 absolute transform">
                            <?php if(auth()->guard()->check()): ?>
                                <?php if(!$isMember): ?>
                                    <form action="<?php echo e(route('forum.groups.join', $group)); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-1.5 rounded-lg font-medium transition-all duration-200 shadow hover:shadow-md hover:scale-[1.02] flex items-center justify-center gap-1.5 text-sm bg-white/50 backdrop-blur-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                                            </svg>
                                            Join Group
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="<?php echo e(route('login')); ?>" class="inline-flex items-center justify-center w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-1.5 rounded-lg font-medium transition-all duration-200 shadow hover:shadow-md hover:scale-[1.02] gap-1.5 text-sm bg-white/50 backdrop-blur-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                    </svg>
                                    Sign in to join
                                </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /media/prayoga/home/projects/Eduflow_Website/resources/views/forums/components/forum-banner.blade.php ENDPATH**/ ?>