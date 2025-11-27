<?php

namespace App\Http\Controllers;

use App\Models\ForumPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Traits\HandlesMediaUploads;

class ForumPostController extends Controller
{
    use HandlesMediaUploads;
    
    /**
     * Store a newly created post in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
            'media' => 'nullable|file|max:10240', // 10MB max
        ]);

        // Handle file upload if present
        $mediaPath = null;
        $mediaType = null;
        $mediaName = null;
        
        if ($request->hasFile('media')) {
            $media = $request->file('media');
            $mediaName = $media->getClientOriginalName();
            $mediaPath = $media->store('forum_media', 'public');
            
            // Get MIME type from the file itself, not just the extension
            $mimeType = $media->getMimeType();
            
            // Fallback to uploaded file's MIME type if our detection fails
            $mediaType = $mimeType ?: $this->getMediaType($media);
            
            // Ensure media_type is set to something meaningful
            if (empty($mediaType)) {
                $extension = strtolower($media->getClientOriginalExtension());
                $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
                $mediaType = in_array($extension, $imageExtensions) ? 'image/' . $extension : 'application/' . $extension;
            }
        }

        $post = ForumPost::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'content' => $validated['content'],
            'media_path' => $mediaPath,
            'media_type' => $mediaType,
            'media_name' => $mediaName,
        ]);

        return redirect()->back()->with('success', 'Post created successfully!');
    }

    /**
     * Show the form for editing the specified post.
     *
     * @param  \App\Models\ForumPost  $post
     * @return \Illuminate\View\View
     */
    public function edit(ForumPost $post)
    {
        // Authorize that the user can edit this post
        $this->authorize('update', $post);
        
        // Load any additional relationships if needed
        $post->load('user');
        
        return view('forums.posts.edit', compact('post'));
    }

    /**
     * Update the specified post in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ForumPost  $post
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, ForumPost $post)
    {
        // Authorize that the user can update this post
        $this->authorize('update', $post);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
            'media' => 'nullable|file|max:10240', // 10MB max
            'remove_media' => 'nullable|boolean',
        ]);

        // Handle media removal if requested
        if ($request->has('remove_media') && $post->media_path) {
            // Delete the old media file
            Storage::disk('public')->delete($post->media_path);
            
            // Clear media fields
            $validated['media_path'] = null;
            $validated['media_type'] = null;
            $validated['media_name'] = null;
        }

        // Handle new media upload if present
        if ($request->hasFile('media')) {
            // Delete old media if exists
            if ($post->media_path) {
                Storage::disk('public')->delete($post->media_path);
            }
            
            $media = $request->file('media');
            $mediaName = $media->getClientOriginalName();
            $mediaPath = $media->store('forum_media', 'public');
            
            // Get MIME type from the file itself, not just the extension
            $mimeType = $media->getMimeType();
            
            // Fallback to uploaded file's MIME type if our detection fails
            $mediaType = $mimeType ?: $this->getMediaType($media);
            
            // Ensure media_type is set to something meaningful
            if (empty($mediaType)) {
                $extension = strtolower($media->getClientOriginalExtension());
                $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
                $mediaType = in_array($extension, $imageExtensions) ? 'image/' . $extension : 'application/' . $extension;
            }
            
            $validated['media_path'] = $mediaPath;
            $validated['media_type'] = $mediaType;
            $validated['media_name'] = $mediaName;
        }

        // Update the post
        $post->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'media_path' => $validated['media_path'] ?? $post->media_path,
            'media_type' => $validated['media_type'] ?? $post->media_type,
            'media_name' => $validated['media_name'] ?? $post->media_name,
        ]);

        // Return JSON response for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Post updated successfully!',
                'redirect' => $request->input('redirect_to', url()->previous())
            ]);
        }

        // For regular form submissions
        return redirect()
            ->back()
            ->with('success', 'Post updated successfully!');
    }

    /**
     * Remove the specified post from storage.
     *
     * @param  \App\Models\ForumPost  $post
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(ForumPost $post)
    {
        // Authorize that the user can delete this post
        $this->authorize('delete', $post);

        // Delete any associated media
        if ($post->media_path) {
            Storage::disk('public')->delete($post->media_path);
        }

        $post->delete();

        return response()->json(['success' => true, 'message' => 'Post deleted successfully']);
    }
    
    /**
     * Toggle like on a post
     *
     * @param  \App\Models\ForumPost  $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleLike(ForumPost $post)
    {
        $user = auth()->user();
        
        // For simplicity, we'll just increment/decrement the like_count
        // In a real app, you'd want to track which users liked which posts
        $post->increment('like_count');
        
        return response()->json([
            'success' => true,
            'like_count' => $post->fresh()->like_count
        ]);
    }
}
