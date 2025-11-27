<?php

namespace App\Http\Controllers;

use App\Models\ForumPost;
use App\Models\ForumGroup;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Get global posts (where group_id is null)
        $globalPosts = ForumPost::select('postid', 'user_id', 'title', 'content', 'media_path', 'media_name', 'like_count', 'created_at', 'updated_at')
            ->whereNull('group_id')
            ->with([
                'user' => function($query) {
                    $query->select('id', 'username as name');
                },
                'replies.user' => function($query) {
                    $query->select('id', 'username as name');
                }
            ])
            ->latest()
            ->take(10)
            ->get();
            
        // Get group posts from groups the user is a member of
        $groupPosts = collect([]);
        
        if (auth()->check()) {
            $groupPosts = ForumPost::select('postid', 'user_id', 'group_id', 'title', 'content', 'media_path', 'media_name', 'like_count', 'created_at', 'updated_at')
                ->whereHas('group.members', function($query) {
                    $query->where('user_id', auth()->id());
                })
                ->with([
                    'user' => function($query) {
                        $query->select('id', 'username as name');
                    },
                    'replies.user' => function($query) {
                        $query->select('id', 'username as name');
                    },
                    'group' => function($query) {
                        $query->select('id', 'name', 'slug');
                    }
                ])
                ->latest()
                ->take(10)
                ->get();
        }
        
        // Merge and sort all posts by creation date
        $posts = $globalPosts->merge($groupPosts)
            ->sortByDesc('created_at')
            ->take(10);
            
        // Get recent tasks for the sidebar
        $recentTasks = [];
        if (Auth::check()) {
            $recentTasks = Task::where('user_id', Auth::id())
                ->with('taskBoard')
                ->orderBy('due_date', 'asc')
                ->take(5) // Get 5 most recent tasks
                ->get();
        }
            
        // Get public groups for recommended rooms (where is_private is false)
        $publicGroups = ForumGroup::where('is_private', false)
            ->withCount('members')
            ->orderBy('members_count', 'desc')
            ->take(8) // Limit to 8 most popular public groups
            ->get();

        return view('home', [
            'posts' => $posts,
            'recentTasks' => $recentTasks,
            'publicGroups' => $publicGroups
        ]);
    }
}
