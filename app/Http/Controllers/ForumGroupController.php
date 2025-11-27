<?php

namespace App\Http\Controllers;

use App\Models\ForumGroup;
use App\Models\ForumPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ForumGroupController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * Display a listing of forum groups.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->authorize('viewAny', ForumGroup::class);
        
        $forumGroups = ForumGroup::withCount('members')
            ->with('creator')
            ->latest()
            ->paginate(9);

        return view('forums.groups.index', [
            'forumGroups' => $forumGroups,
        ]);
    }

    /**
     * Show the form for creating a new forum group.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $this->authorize('create', ForumGroup::class);
        return view('forums.groups.create');
    }

    /**
     * Store a newly created forum group in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->authorize('create', ForumGroup::class);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:forum_groups',
            'description' => 'required|string',
            'banner_image' => 'nullable|image|max:2048', // 2MB max
            'is_public' => 'boolean',
        ]);

        $group = new ForumGroup([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'is_public' => $validated['is_public'] ?? true,
            'user_id' => auth()->id(),
        ]);

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            $path = $request->file('banner_image')->store('group_banners', 'public');
            $group->banner_image = $path;
        }

        $group->save();

        // Add the creator as the first member and admin
        $group->members()->attach(auth()->id(), ['role' => 'admin']);

        return redirect()->route('forums.groups.show', $group->slug)
            ->with('success', 'Group created successfully!');
    }

    /**
     * Display the specified forum group.
     *
     * @param  \App\Models\ForumGroup  $forumGroup
     * @return \Illuminate\View\View
     */
    public function show(ForumGroup $forumGroup, Request $request)
    {
        $this->authorize('view', $forumGroup);
        
        $forumGroup->load(['creator', 'members']);
        
        $posts = $forumGroup->posts()
            ->with(['user', 'replies'])
            ->withCount('replies')
            ->latest()
            ->paginate(5); // Reduced to 5 posts per page for better user experience

        // Get recent discussions for the sidebar
        $recentDiscussions = ForumPost::with(['user', 'replies', 'group'])
            ->withCount('replies')
            ->latest()
            ->take(5)
            ->get();

        // If it's an AJAX request, return JSON response
        if ($request->ajax()) {
            return response()->json([
                'html' => view('forums.partials.posts', ['posts' => $posts])->render(),
                'next_page_url' => $posts->nextPageUrl()
            ]);
        }

        return view('forums.groups.show', [
            'group' => $forumGroup,
            'posts' => $posts,
            'recentDiscussions' => $recentDiscussions,
            'isMember' => $forumGroup->isMember(auth()->user()),
            'activeTab' => 'group',
        ]);
    }

    /**
     * Add a user to a forum group.
     *
     * @param  \App\Models\ForumGroup  $forumGroup
     * @return \Illuminate\Http\RedirectResponse
     */
    public function join(ForumGroup $forumGroup)
    {
        $this->authorize('join', $forumGroup);
        
        if (!$forumGroup->isMember(auth()->user())) {
            $forumGroup->members()->attach(auth()->id(), ['role' => 'member']);
            return back()->with('success', 'You have joined the group successfully.');
        }
        
        return back()->with('error', 'You are already a member of this group.');
    }
    
    /**
     * Remove a user from a forum group.
     *
     * @param  \App\Models\ForumGroup  $forumGroup
     * @return \Illuminate\Http\RedirectResponse
     */
    public function leave(ForumGroup $forumGroup)
    {
        $this->authorize('leave', $forumGroup);
        
        if ($forumGroup->isMember(auth()->user())) {
            $forumGroup->members()->detach(auth()->id());
            return back()->with('success', 'You have left the group.');
        }
        
        return back()->with('error', 'You are not a member of this group.');
    }
    
    /**
     * Show the form for editing the specified forum group.
     *
     * @param  \App\Models\ForumGroup  $forumGroup
     * @return \Illuminate\View\View
     */
    public function edit(ForumGroup $forumGroup)
    {
        $this->authorize('update', $forumGroup);
        
        return view('forums.groups.edit', [
            'group' => $forumGroup,
        ]);
    }
    
    /**
     * Update the specified forum group in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ForumGroup  $forumGroup
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, ForumGroup $forumGroup)
    {
        $this->authorize('update', $forumGroup);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_private' => 'boolean',
            'banner_image' => 'nullable|image|max:2048',
        ]);
        
        if ($request->hasFile('banner_image')) {
            // Delete old banner if exists
            if ($forumGroup->banner_image) {
                Storage::delete('public/' . $forumGroup->banner_image);
            }
            
            $path = $request->file('banner_image')->store('forum-banners', 'public');
            $validated['banner_image'] = $path;
        }
        
        $forumGroup->update($validated);
        
        return redirect()->route('forum.groups.show', $forumGroup->slug)
            ->with('success', 'Group updated successfully.');
    }
    
    /**
     * Remove the specified forum group from storage.
     *
     * @param  \App\Models\ForumGroup  $forumGroup
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(ForumGroup $forumGroup)
    {
        $this->authorize('delete', $forumGroup);
        
        // Delete banner image if exists
        if ($forumGroup->banner_image) {
            Storage::delete('public/' . $forumGroup->banner_image);
        }
        
        $forumGroup->delete();
        
        return redirect()->route('forum.groups.index')
            ->with('success', 'Group deleted successfully.');
    }
}
