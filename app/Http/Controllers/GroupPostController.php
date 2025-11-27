<?php

namespace App\Http\Controllers;

use App\Models\ForumGroup;
use App\Models\PostGroup;
use App\Models\ReplyPostGroup;
use App\Traits\HandlesMediaUploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class GroupPostController extends Controller
{
    use HandlesMediaUploads;
    
    /**
     * Delete a group post.
     *
     * @param  \App\Models\ForumGroup  $group
     * @param  \App\Models\PostGroup  $post
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function deletePost(ForumGroup $group, PostGroup $post)
    {
        // Check if the post belongs to the group
        if ($post->group_id !== $group->id) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid post for this group.'
                ], 404);
            }
            return redirect()->back()->with('error', 'Invalid post for this group.');
        }
        
        // Check if the authenticated user is the owner of the post or an admin
        if ($post->user_id !== auth()->id() && !$group->isAdmin(auth()->user())) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to delete this post.'
                ], 403);
            }
            return redirect()->back()->with('error', 'You are not authorized to delete this post.');
        }

        try {
            // Delete any associated media files
            if ($post->media_path) {
                Storage::disk('public')->delete($post->media_path);
            }
            
            $post->delete();

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Post deleted successfully.'
                ]);
            }

            return redirect()->back()->with('success', 'Post deleted successfully!');
            
        } catch (\Exception $e) {
            \Log::error('Error deleting post: ' . $e->getMessage());
            
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete post: ' . $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to delete post: ' . $e->getMessage());
        }
    }
    /**
     * Store a newly created post in the group.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function storePost(Request $request, ForumGroup $group)
    {
        // Enable query logging
        \DB::enableQueryLog();
        \Log::info('=== Starting post creation ===');
        \Log::info('Request data:', $request->except(['_token']));
        \Log::info('Files in request:', $request->allFiles());

        // Check if user is a member of the group
        if (!$group->isMember(auth()->user())) {
            $message = 'You must be a member of this group to post.';
            \Log::warning($message, ['user_id' => auth()->id(), 'group_id' => $group->id]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 403);
            }
            return redirect()->back()->with('error', $message);
        }

        // Log file in the request
        $mediaPath = null;
        $mediaType = null;
        $mediaName = null;
        $mediaSize = null;

        try {
            // Handle file upload if present
            if ($request->hasFile('media')) {
                $file = $request->file('media');
                
                if ($file && $file->isValid()) {
                    // Log file details
                    $fileDetails = [
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime' => $file->getMimeType(),
                        'extension' => $file->getClientOriginalExtension()
                    ];
                    
                    \Log::info('Processing file upload', $fileDetails);
                    
                    // Validate file size and type
                    $maxSize = 10 * 1024 * 1024; // 10MB
                    if ($file->getSize() > $maxSize) {
                        throw new \Exception('File size exceeds maximum allowed size of 10MB');
                    }
                    
                    // Set file properties
                    $mediaName = $file->getClientOriginalName();
                    $mediaType = $this->getMediaType($file);
                    $mediaSize = (string) $file->getSize();
                    
                    // Generate a unique filename
                    $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    
                    // Ensure the directory exists
                    $directory = 'uploads/group_posts';
                    if (!Storage::disk('public')->exists($directory)) {
                        Storage::disk('public')->makeDirectory($directory, 0755, true);
                    }
                    
                    // Store the file in the public disk
                    $mediaPath = $file->storeAs($directory, $filename, 'public');
                    
                    if (!$mediaPath) {
                        throw new \Exception('Failed to store the uploaded file: ' . $mediaName);
                    }
                    
                    \Log::info('File stored successfully', [
                        'original_name' => $mediaName,
                        'stored_path' => $mediaPath,
                        'type' => $mediaType,
                        'size' => $mediaSize
                    ]);
                } else {
                    \Log::warning('Invalid file in request', [
                        'file' => $file ? get_class($file) : 'null',
                        'isValid' => $file ? ($file->isValid() ? 'true' : 'false') : 'null'
                    ]);
                }
            } else {
                \Log::info('No media file in request');
            }
        } catch (\Exception $e) {
            \Log::error('Error processing file upload: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            
            // If we're in an API context, return a JSON response
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error processing file: ' . $e->getMessage()
                ], 422);
            }
            
            // Otherwise, redirect back with error
            return redirect()->back()->with('error', 'Error processing file: ' . $e->getMessage());
        }

        // Validate the request
        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            // Media validation is handled separately in the file upload section
        ], [
            'content.required' => 'Post content is required.',
            'content.max' => 'Post content may not be greater than 5000 characters.'
        ]);

        // Start database transaction
        \DB::beginTransaction();

        try {
            // Create the post
            $post = new PostGroup([
                'group_id' => $group->id,
                'user_id' => auth()->id(),
                'content' => $validated['content'],
                'media_path' => $mediaPath,
                'media_type' => $mediaType,
                'media_name' => $mediaName,
                'media_size' => $mediaSize,
            ]);
            
            $post->save();
            
            // Log the created post
            \Log::info('Post created successfully', [
                'post_id' => $post->id,
                'has_media' => !is_null($mediaPath)
            ]);

            // Commit the transaction
            \DB::commit();
            
            // Log queries for debugging
            \Log::info('Database queries:', \DB::getQueryLog());

            // Load the user relationship for the response
            $post->load('user');

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Post created successfully',
                    'post' => $post,
                    'redirect' => route('forums.groups.show', ['group' => $group->slug])
                ]);
            }

            return redirect()->route('forums.groups.show', ['group' => $group->slug])
                ->with('success', 'Post created successfully');
            
        } catch (\Exception $e) {
            // Rollback the transaction on error
            \DB::rollBack();
            
            // Log the error with stack trace
            \Log::error('Error in storePost: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
                'files' => $request->allFiles()
            ]);
            
            $errorMessage = 'Failed to create post. Please try again.';
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ], 500);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', $errorMessage);
        }
    }

    /**
     * Store a reply to a group post.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $postId
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeReply(Request $request, ForumGroup $group, PostGroup $post)
    {
        // Check if the post belongs to the group
        if ($post->group_id !== $group->id) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid post for this group.'
                ], 404);
            }
            return redirect()->back()->with('error', 'Invalid post for this group.');
        }
        
        // Check if user is a member of the group
        if (!$group->isMember(auth()->user())) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be a member of this group to reply.'
                ], 403);
            }
            return redirect()->back()->with('error', 'You must be a member of this group to reply.');
        }

        $validated = $request->validate([
            'content' => 'required|string|max:2000',
            'media' => 'nullable|file|max:10240', // 10MB max
        ]);

        try {
            $mediaPath = null;
            $mediaType = null;
            $mediaName = null;
            $mediaSize = null;

            if ($request->hasFile('media') && $request->file('media')->isValid()) {
                $file = $request->file('media');
                $mediaName = $file->getClientOriginalName();
                $mediaPath = $file->store('uploads/group_replies', 'public');
                $mediaType = $this->getMediaType($file);
                $mediaSize = $file->getSize();
            }

            $reply = new ReplyPostGroup([
                'post_group_id' => $post->id,
                'user_id' => auth()->id(),
                'content' => $validated['content'],
                'media_path' => $mediaPath,
                'media_type' => $mediaType,
                'media_name' => $mediaName,
                'media_size' => $mediaSize,
            ]);

            $reply->save();

            // Load the user relationship for the response
            $reply->load('user');

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Reply added successfully',
                    'reply' => $reply,
                ]);
            }

            return redirect()->back()->with('success', 'Reply added successfully');
            
        } catch (\Exception $e) {
            \Log::error('Error adding reply: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add reply: ' . $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to add reply: ' . $e->getMessage());
        }
    }

    /**
     * Get all posts for a group.
     *
     * @param  string  $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGroupPosts($slug)
    {
        $group = ForumGroup::where('slug', $slug)->firstOrFail();
        
        // Check if user is a member of the group if it's private
        if ($group->is_private && !$group->isMember(auth()->user())) {
            return response()->json([
                'success' => false,
                'message' => 'You must be a member of this group to view posts.'
            ], 403);
        }

        $posts = $group->postGroups()
            ->with(['user', 'replies.user'])
            ->withCount('replies')
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'posts' => $posts,
        ]);
    }

    /**
     * Get a single post with its replies.
     *
     * @param  int  $postId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPostWithReplies($postId)
    {
        $post = PostGroup::with(['user', 'replies.user'])
            ->withCount('replies')
            ->findOrFail($postId);
            
        // Check if user is a member of the group
        if (!$post->group->isMember(auth()->user())) {
            return response()->json([
                'success' => false,
                'message' => 'You must be a member of this group to view this post.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'post' => $post,
        ]);
    }

    /**
     * Show the form for editing the specified post.
     *
     * @param  \App\Models\ForumGroup  $group
     * @param  \App\Models\PostGroup  $post
     * @return \Illuminate\Http\Response
     */
    public function edit(ForumGroup $group, PostGroup $post)
    {
        // Check if the post belongs to the group
        if ($post->group_id !== $group->id) {
            return redirect()->back()->with('error', 'Invalid post for this group.');
        }
        
        // Check if the authenticated user is the owner of the post or an admin
        if ($post->user_id !== auth()->id() && !$group->isAdmin(auth()->user())) {
            return redirect()->back()->with('error', 'You are not authorized to edit this post.');
        }
        
        // Use the existing edit view for posts, but pass the group context
        return view('forums.posts.edit', [
            'post' => $post,
            'group' => $group,
            'isGroupPost' => true
        ]);
    }

    /**
     * Update the specified post in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ForumGroup  $group
     * @param  \App\Models\PostGroup  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ForumGroup $group, PostGroup $post)
    {
         // Verify the post belongs to the group
        if ($post->group_id != $group->id) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found in this group'
            ], 404);
        }

        // Check if user is authorized to update
        if (auth()->id() !== $post->user_id && !$group->isAdmin(auth()->user())) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this post'
            ], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string',
            'media' => 'nullable|file|max:10240',
            'remove_media' => 'nullable|boolean',
        ]);

        try {
            \DB::beginTransaction();

            $post->content = $validated['content'];

            // Handle media removal if requested
            if ($request->has('remove_media') && $post->media_path) {
                Storage::disk('public')->delete($post->media_path);
                $post->media_path = null;
                $post->media_type = null;
            }

            // Handle new media upload
            if ($request->hasFile('media')) {
                // Delete old media if exists
                if ($post->media_path) {
                    Storage::disk('public')->delete($post->media_path);
                }

                $file = $request->file('media');
                $path = $file->store('posts/media', 'public');
                
                $post->media_path = $path;
                $post->media_type = $file->getClientMimeType();
                $post->media_name = $file->getClientOriginalName();
            }

            $post->save();
            \DB::commit();

            // For AJAX requests
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Post updated successfully',
                    'redirect' => url()->previous() // Return the previous URL
                ]);
            }

            // For regular form submission
            return redirect()
                ->back()
                ->with('success', 'Post updated successfully');

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error updating post: ' . $e->getMessage());
            
            // For AJAX requests
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update post: ' . $e->getMessage()
                ], 500);
            }

            // For regular form submission
            return redirect()
                ->back()
                ->with('error', 'Failed to update post: ' . $e->getMessage())
                ->withInput();
        }
    }
    // Note: This controller uses custom methods instead of the default resource methods
    // to handle the specific requirements of group posts.
}
