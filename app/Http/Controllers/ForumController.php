<?php

namespace App\Http\Controllers;

use App\Models\ForumPost;
use App\Models\ForumGroup;
use App\Models\ForumReply;
use App\Traits\HandlesMediaUploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForumController extends Controller
{
    use HandlesMediaUploads;

    /**
     * Display the forum index page with groups.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get paginated forum groups with member count and creator
        $forumGroups = ForumGroup::withCount(['members', 'posts'])
            ->with(['creator', 'members' => function($query) {
                $query->limit(3); // Only load first 3 members for the preview
            }])
            ->latest()
            ->paginate(9);

        return view('forums.index', [
            'forumGroups' => $forumGroups,
        ]);
    }

    /**
     * Show the posts within a specific forum group.
     *
     * @param  string  $slug
     * @return \Illuminate\View\View
     */
    public function showGroup($slug)
    {
        $group = ForumGroup::withCount('members')
            ->where('slug', $slug)
            ->with(['creator', 'members' => function($query) {
                $query->limit(5); // Load first 5 members for the sidebar
            }])
            ->firstOrFail();

        // Check if the group is private and user is not a member
        if ($group->is_private && !$group->isMember(auth()->user())) {
            return redirect()->route('forums.index')
                ->with('error', 'This is a private group. You must be a member to view its content.');
        }

        // Debug: Check if users exist
        $usersCount = \App\Models\User::count();
        \Log::info('Total users in database:', ['count' => $usersCount]);
        
        // Get posts with user and replies count
        $posts = $group->posts()
            ->with([
                'user' => function($q) {
                    $q->withTrashed(); // Include soft-deleted users if any
                },
                'replies' => function($query) {
                    $query->with(['user' => function($q) {
                        $q->withTrashed(); // Include soft-deleted users if any
                    }])->orderBy('created_at', 'asc');
                }
            ])
            ->withCount('replies')
            ->latest()
            ->paginate(10);
            
        // Debug: Log the first post's user relationship
        if ($posts->count() > 0) {
            $firstPost = $posts->first();
            $firstPost->load(['user']); // Explicitly load user relationship
            \Log::info('First post user relationship:', [
                'post_id' => $firstPost->id,
                'user_id' => $firstPost->user_id,
                'user_loaded' => $firstPost->relationLoaded('user'),
                'user' => $firstPost->user ? $firstPost->user->toArray() : null
            ]);
        }
            
        // Debug: Log the posts and their relationships
        \Log::info('Posts loaded:', [
            'count' => $posts->count(),
            'first_post' => $posts->first() ? [
                'id' => $posts->first()->id,
                'content' => $posts->first()->content,
                'user' => $posts->first()->user ? $posts->first()->user->name : null,
                'replies_count' => $posts->first()->replies_count,
                'replies' => $posts->first()->replies->map(function($reply) {
                    return [
                        'id' => $reply->id,
                        'content' => $reply->content,
                        'user' => $reply->user ? $reply->user->name : null
                    ];
                })
            ] : null
        ]);

        $isMember = auth()->check() && $group->isMember(auth()->user());

        // If not a member, show limited information
        if (!$isMember) {
            return view('forums.groups.show', [
                'group' => $group,
                'posts' => $posts,
                'isMember' => false,
                'activeTab' => 'discussion'
            ]);
        }

        // Check if the request is an AJAX request
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('forums.groups.partials.posts', ['posts' => $posts])->render(),
                'next_page_url' => $posts->nextPageUrl()
            ]);
        }

        // Eager load the user relationship for all posts
        $posts->load(['user' => function($q) {
            $q->withTrashed();
        }]);
        
        return view('forums.groups.show', [
            'group' => $group,
            'posts' => $posts,
            'isMember' => true,
            'activeTab' => 'discussion'
        ]);
    }

    /**
     * Store a new forum post.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storePost(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'media' => 'nullable|file|max:10240', // 10MB max
        ]);

        $post = new ForumPost();
        $post->user_id = auth()->id();
        $post->title = $validated['title'];
        $post->content = $validated['content'];

        // Handle file upload
        if ($request->hasFile('media') && $request->file('media')->isValid()) {
            $file = $request->file('media');
            $path = $file->store('forum_media', 'public');
            
            $post->media_path = $path;
            $post->media_name = $file->getClientOriginalName();
            $post->media_type = $this->getMediaType($file);
        }

        $post->save();

        return redirect()->back()->with('success', 'Post created successfully!');
    }

    /**
     * Get the media type based on file mime type
     */
    private function getMediaType($file)
    {
        $mimeType = $file->getMimeType();
        
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }
        
        if (in_array($file->getClientOriginalExtension(), ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'])) {
            return 'document';
        }
        
        return 'file';
    }

    /**
     * Store a new reply to a forum post.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $postId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeReply(Request $request, $postId)
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $reply = new ForumReply();
        $reply->postid = $postId;
        $reply->user_id = Auth::id();
        $reply->content = $validated['content'];
        $reply->save();
        
        return redirect()->back()->with('success', 'Reply added successfully!');
    }

    /**
     * Display a listing of the user's forum groups.
     *
     * @return \Illuminate\View\View
     */
    public function myGroups()
    {
        $user = auth()->user();
        
        $forumGroups = $user->forumGroups()
            ->withCount('members')
            ->with('creator')
            ->latest()
            ->paginate(9);

        return view('forums.index', [
            'forumGroups' => $forumGroups,
            'showMyGroups' => true, // Flag to highlight the 'My Groups' tab
        ]);
    }

    /**
     * Display the media page.
     *
     * @return \Illuminate\View\View
     */
    public function media(ForumGroup $group = null)
    {
        $query = ForumPost::whereNotNull('media_path');
        
        if ($group) {
            $query->where('group_id', $group->id);
            
            // Check if user can view private group content
            if ($group->is_private && !$group->isMember(auth()->user())) {
                return redirect()->route('forums.groups.show', $group);
            }
        } else {
            // For global media, only show from public groups or groups the user is a member of
            $query->whereHas('group', function($q) {
                $q->where('is_private', false)
                  ->orWhereHas('members', function($q) {
                      $q->where('user_id', auth()->id());
                  });
            });
        }
        
        $mediaPosts = $query->with(['user', 'replies', 'group'])
            ->withCount('replies')
            ->latest()
            ->paginate(12);

        return view('forums.media', [
            'mediaPosts' => $mediaPosts,
            'activeTab' => 'media',
            'group' => $group,
        ]);
    }

    /**
     * Show the discussion page for a specific forum group.
     *
     * @param  string  $slug
     * @return \Illuminate\View\View
     */
    public function groupDiscussion($slug)
    {
        $group = ForumGroup::where('slug', $slug)
            ->with(['creator', 'members' => function($query) {
                $query->limit(5); // Load first 5 members for the sidebar
            }])
            ->firstOrFail();

        // Get paginated posts for the discussion
        $posts = $group->posts()
            ->with(['user', 'replies.user'])
            ->withCount('replies')
            ->latest()
            ->paginate(10);

        // Get recent discussions for the sidebar
        $recentDiscussions = $group->posts()
            ->with('user')
            ->withCount('replies')
            ->latest()
            ->take(5)
            ->get();

        return view('forums.groups.discussion', [
            'group' => $group,
            'posts' => $posts,
            'recentDiscussions' => $recentDiscussions,
            'isMember' => $group->isMember(auth()->user()),
            'activeTab' => 'discussion',
        ]);
    }
}
