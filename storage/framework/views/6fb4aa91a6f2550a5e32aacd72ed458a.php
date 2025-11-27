<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6">
            <h2 class="text-2xl font-semibold text-gray-900 mb-6">Edit Post</h2>
            
            <form action="<?php echo e(isset($isGroupPost) && $isGroupPost ? route('forums.groups.posts.update', ['group' => $group->slug, 'post' => $post->id]) : route('forums.posts.update', $post)); ?>" 
                  method="POST" 
                  enctype="multipart/form-data" 
                  onsubmit="return handleFormSubmit(this);">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <input type="hidden" name="redirect_to" value="<?php echo e(url()->previous()); ?>">
                <!-- title
                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" 
                           name="title" 
                           id="title" 
                           value="<?php echo e(old('title', $post->title)); ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           required>
                    <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div> -->
                
                <div class="mb-4">
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                    <textarea name="content" 
                              id="content" 
                              rows="5" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                              required><?php echo e(old('content', $post->content)); ?></textarea>
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
                
                <?php if($post->media_path): ?>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Current Media</label>
                        <div class="flex items-center space-x-2">
                            <?php if(str_starts_with($post->media_type, 'image/')): ?>
                                <img src="<?php echo e(asset('storage/' . $post->media_path)); ?>" 
                                     alt="Post media" 
                                     class="h-20 w-20 object-cover rounded">
                            <?php else: ?>
                                <div class="p-3 bg-gray-100 rounded">
                                    <i class="fas fa-file"></i>
                                </div>
                            <?php endif; ?>
                            <span class="text-sm text-gray-600"><?php echo e($post->media_name); ?></span>
                        </div>
                        <div class="mt-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="remove_media" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-600">Remove media</span>
                            </label>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="mb-6">
                    <label for="media" class="block text-sm font-medium text-gray-700 mb-1">
                        <?php echo e($post->media_path ? 'Replace Media' : 'Add Media'); ?>

                    </label>
                    <input type="file" 
                           name="media" 
                           id="media" 
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="mt-1 text-xs text-gray-500">Images, PDF, DOC, DOCX, XLS, XLSX (Max: 10MB)</p>
                    <?php $__errorArgs = ['media'];
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
                
                <div class="flex items-center justify-end space-x-3">
                    <a href="<?php echo e(isset($isGroupPost) && $isGroupPost ? route('forums.groups.show', $group->slug) : url()->previous()); ?>" 
                       class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Update Post
                    </button>
                    
                    <?php $__env->startPush('scripts'); ?>
                    <script>
                        function handleFormSubmit(form) {
                            // Store the form data
                            const formData = new FormData(form);
                            
                            // Submit the form via AJAX
                            fetch(form.action, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                // Show success message
                                if (data.success) {
                                    // Create and show toast notification
                                    const toast = document.createElement('div');
                                    toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center';
                                    toast.innerHTML = `
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        ${data.message}
                                    `;
                                    document.body.appendChild(toast);
                                    
                                    // Remove toast after 5 seconds
                                    setTimeout(() => {
                                        toast.remove();
                                    }, 5000);
                                    
                                    // Redirect back after a short delay
                                    setTimeout(() => {
                                        window.location.href = form.redirect_to.value || '<?php echo e(url('/')); ?>';
                                    }, 1000);
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('An error occurred while updating the post.');
                            });
                            
                            return false; // Prevent default form submission
                        }
                    </script>
                    <?php $__env->stopPush(); ?>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form.update-post-form');
    if (!form) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        
        try {
            // Show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
            
            const response = await fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Failed to update post');
            }
            
            // Show success message
            alert(data.message || 'Post updated successfully!');
            
            // Redirect to the previous page or reload
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                window.location.reload();
            }
            
        } catch (error) {
            console.error('Error:', error);
            alert('Error: ' + error.message);
        } finally {
            // Reset button state
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        }
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\eduflow\resources\views/forums/posts/edit.blade.php ENDPATH**/ ?>