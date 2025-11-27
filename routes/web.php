<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\DebugLogController;

// Debug logging routes - only accessible in local environment
if (app()->environment('local')) {
    Route::post('/debug/log', [DebugLogController::class, 'log']);
    Route::get('/debug/logs', [DebugLogController::class, 'getLogs']);
}

// Test route to check database schema
Route::get('/test/db-schema', function () {
    $schema = \Illuminate\Support\Facades\DB::select("SHOW CREATE TABLE post_group");
    $schema = array_shift($schema);
    
    return response()->json([
        'schema' => $schema->{'Create Table'}
    ]);
});

// Test route to create a sample post
Route::get('/test/create-post', function () {
    if (!auth()->check()) {
        return response()->json(['error' => 'Not authenticated']);
    }
    
    // Get the first group
    $group = \App\Models\ForumGroup::first();
    
    if (!$group) {
        return response()->json(['error' => 'No groups found']);
    }
    
    // Create a test post
    $post = new \App\Models\PostGroup([
        'group_id' => $group->id,
        'user_id' => auth()->id(),
        'content' => 'This is a test post created at ' . now(),
    ]);
    
    $post->save();
    
    return response()->json([
        'success' => true,
        'post' => $post->load('user')
    ]);
});

// Test route to check database state
Route::get('/test/db-state', function () {
    $tables = ['users', 'post_group', 'reply_post_group'];
    $result = [];
    
    foreach ($tables as $table) {
        $result[$table] = [
            'count' => DB::table($table)->count(),
            'columns' => Schema::getColumnListing($table),
        ];
        
        // Get sample data for each table
        $result[$table]['sample'] = DB::table($table)->first();
    }
    
    // Check for posts with non-existent users
    $postsWithInvalidUsers = DB::table('post_group')
        ->whereNotIn('user_id', function($query) {
            $query->select('id')->from('users');
        })
        ->orWhereNull('user_id')
        ->get();
        
    $result['posts_with_invalid_users'] = $postsWithInvalidUsers;
    
    return response()->json($result);
});

// Test route for checking posts and their users
Route::get('/test/check-posts', function () {
    // Get all posts with their user and replies
    $posts = \App\Models\PostGroup::with(['user' => function($q) {
        $q->withTrashed();
    }])->get();
    
    return response()->json([
        'total_posts' => $posts->count(),
        'posts' => $posts->map(function($post) {
            return [
                'id' => $post->id,
                'content' => $post->content,
                'user_id' => $post->user_id,
                'user' => $post->user ? [
                    'id' => $post->user->id,
                    'name' => $post->user->name,
                    'email' => $post->user->email,
                    'deleted_at' => $post->user->deleted_at,
                ] : null,
                'replies_count' => $post->replies->count(),
                'created_at' => $post->created_at,
            ];
        })
    ]);
});

// Test route for debugging user associations with posts
Route::get('/test/user-posts', function () {
    // Get all users with their posts and replies
    $users = \App\Models\User::with(['posts', 'replies'])->get();
    
    return response()->json([
        'total_users' => $users->count(),
        'users' => $users->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'posts_count' => $user->posts->count(),
                'replies_count' => $user->replies->count(),
                'created_at' => $user->created_at,
                'deleted_at' => $user->deleted_at,
            ];
        })
    ]);
});

// Test route for debugging posts
Route::get('/test/posts', function () {
    // Get the first group
    $group = \App\Models\ForumGroup::first();
    if (!$group) {
        return response()->json([
            'error' => 'No groups found',
            'groups_count' => \App\Models\ForumGroup::count()
        ]);
    }
    
    // Debug group info
    $groupInfo = [
        'id' => $group->id,
        'name' => $group->name,
        'slug' => $group->slug,
        'is_private' => $group->is_private,
        'created_at' => $group->created_at,
        'updated_at' => $group->updated_at,
        'posts_count' => $group->posts()->count(),
        'members_count' => $group->members()->count(),
    ];
    
    // Get posts with relationships
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
        ->get();
        
    // Debug database tables
    $tables = [
        'post_group' => DB::table('post_group')->count(),
        'forum_groups' => DB::table('forum_groups')->count(),
        'reply_post_group' => DB::table('reply_post_group')->count(),
    ];
    
    return response()->json([
        'debug' => [
            'database' => [
                'connection' => config('database.default'),
                'database' => config('database.connections.' . config('database.default') . '.database'),
                'tables' => $tables,
            ],
            'group' => $groupInfo,
        ],
        'posts_count' => $posts->count(),
        'posts' => $posts->map(function($post) {
            return [
                'id' => $post->id,
                'content' => $post->content,
                'user' => $post->user ? $post->user->name : null,
                'created_at' => $post->created_at,
                'updated_at' => $post->updated_at,
                'replies_count' => $post->replies_count,
                'replies' => $post->replies->map(function($reply) {
                    return [
                        'id' => $reply->id,
                        'content' => $reply->content,
                        'user' => $reply->user ? $reply->user->name : null,
                        'created_at' => $reply->created_at,
                    ];
                })
            ];
        })
    ]);
});

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Authentication Routes
Auth::routes();

// Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Home Route
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Task Routes
Route::prefix('tasks')->name('tasks.')->middleware(['web', 'auth'])->group(function () {
    Route::get('/', [App\Http\Controllers\TasksController::class, 'index'])->name('index');
    Route::post('/', [App\Http\Controllers\TasksController::class, 'store'])->name('store');
    Route::get('/{task}', [App\Http\Controllers\TasksController::class, 'show'])->name('show');  // Moved up
    Route::put('/{task}', [App\Http\Controllers\TasksController::class, 'update'])->name('update');
    Route::delete('/{task}', [App\Http\Controllers\TasksController::class, 'destroy'])->name('destroy');
    
    Route::get('/task-boards', function() {
        return \App\Models\TaskBoard::where('user_id', auth()->id())
            ->select('id', 'name')
            ->get();
    })->middleware('auth');
    
    // Task status update
    Route::post('/{task}/status', [App\Http\Controllers\TasksController::class, 'updateStatus'])->name('status.update');

    // Notes routes
    Route::post('/notes', [App\Http\Controllers\TasksController::class, 'storeNote'])->name('notes.store');
    Route::put('/notes/{note}', [App\Http\Controllers\TasksController::class, 'updateNote'])->name('notes.update');
    Route::delete('/notes/{note}', [App\Http\Controllers\TasksController::class, 'destroyNote'])->name('notes.destroy');
});

Route::middleware(['auth'])->group(function () {
    // Toggle task status
    Route::post('/tasks/{task}/toggle', [\App\Http\Controllers\TasksController::class, 'toggleStatus'])
        ->name('tasks.toggle');
});

// Forum Group Routes
Route::prefix('forum/groups')->name('forum.groups.')->group(function () {
    // Public routes
    Route::get('/', [App\Http\Controllers\ForumGroupController::class, 'index'])->name('index');
    Route::get('/{forumGroup:slug}', [App\Http\Controllers\ForumGroupController::class, 'show'])->name('show');
    
    // Protected routes (require authentication)
    Route::middleware(['auth'])->group(function () {
        // Group creation
        Route::get('/create', [App\Http\Controllers\ForumGroupController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\ForumGroupController::class, 'store'])->name('store');
        
        // Group membership
        Route::post('/{forumGroup:slug}/join', [App\Http\Controllers\ForumGroupController::class, 'join'])
            ->name('join');
            
        Route::post('/{forumGroup:slug}/leave', [App\Http\Controllers\ForumGroupController::class, 'leave'])
            ->name('leave');
        
        // Group management (only for group admins/creators)
        Route::middleware(['can:update,forumGroup'])->group(function () {
            Route::get('/{forumGroup:slug}/edit', [App\Http\Controllers\ForumGroupController::class, 'edit'])->name('edit');
            Route::put('/{forumGroup:slug}', [App\Http\Controllers\ForumGroupController::class, 'update'])->name('update');
            Route::delete('/{forumGroup:slug}', [App\Http\Controllers\ForumGroupController::class, 'destroy'])->name('destroy');
        });
    });
});

// Forum Post Routes
Route::middleware(['auth'])->group(function () {
    // Edit post form
    Route::get('/posts/{post}/edit', [App\Http\Controllers\ForumPostController::class, 'edit'])
        ->name('posts.edit');
        
    // Update post
    Route::put('/posts/{post}', [App\Http\Controllers\ForumPostController::class, 'update'])
        ->name('posts.update');
        
    // Delete post
    Route::delete('/posts/{post}', [App\Http\Controllers\ForumPostController::class, 'destroy'])
        ->name('posts.destroy');
});

// Forum Routes
Route::prefix('forums')->name('forums.')->group(function () {
    // Main forums page with groups
    Route::get('/', [App\Http\Controllers\ForumController::class, 'index'])->name('index');
    
    // Global posts
    Route::post('/posts', [App\Http\Controllers\ForumPostController::class, 'store'])->name('posts.store');
    Route::put('/posts/{post}', [App\Http\Controllers\ForumPostController::class, 'update'])->name('posts.update');
    Route::post('/posts/{post}/like', [App\Http\Controllers\ForumPostController::class, 'toggleLike'])->name('posts.like');
    
    // Group posts and replies
    Route::prefix('groups/{group:slug}')->group(function () {
        // Store a new post in the group
        Route::post('/posts', [App\Http\Controllers\GroupPostController::class, 'storePost'])
            ->middleware('auth')
            ->name('groups.posts.store');
            
        // Get all posts for the group (paginated)
        Route::get('/posts', [App\Http\Controllers\GroupPostController::class, 'getGroupPosts'])
            ->name('groups.posts.index');
            
        // Group post routes that require authentication
        Route::middleware(['auth'])->group(function () {
            // Store a reply to a post
            Route::post('/posts/{post}/replies', [App\Http\Controllers\GroupPostController::class, 'storeReply'])
                ->name('groups.posts.replies.store')
                ->middleware('auth');
                
            // Edit post form
            Route::get('/posts/{post}/edit', [App\Http\Controllers\GroupPostController::class, 'edit'])
                ->name('groups.posts.edit');
                
            // Update a post
            Route::put('/posts/{post}', [App\Http\Controllers\GroupPostController::class, 'update'])
                ->name('groups.posts.update');
                
            // Delete a post
            Route::delete('/posts/{post}', [App\Http\Controllers\GroupPostController::class, 'deletePost'])
                ->name('groups.posts.destroy');
        });
        
        // Get a single post with its replies (public)
        Route::get('/posts/{post}', [App\Http\Controllers\GroupPostController::class, 'getPostWithReplies'])
            ->name('groups.posts.show');
    });
    
    // My Groups page
    Route::get('/my-groups', [App\Http\Controllers\ForumController::class, 'myGroups'])
        ->name('my-groups')
        ->middleware('auth');
    
    // Media page
    Route::get('/media', [App\Http\Controllers\ForumController::class, 'media'])->name('media');
    
    

    // Group routes
    Route::prefix('groups')->name('groups.')->group(function () {
        // Show group
        Route::get('/{group:slug}', [App\Http\Controllers\ForumController::class, 'showGroup'])
            ->name('show')
            ->scopeBindings();
        
        // Group discussion
        Route::get('/{group:slug}/discussion', [App\Http\Controllers\ForumController::class, 'groupDiscussion'])
            ->name('discussion');
            
        // Group media
        Route::get('/{group:slug}/media', [App\Http\Controllers\ForumController::class, 'media'])
            ->name('media')
            ->scopeBindings();
            
        // Group posts
        Route::post('/{group}/posts', [App\Http\Controllers\GroupPostController::class, 'storePost'])
            ->middleware('auth')
            ->name('posts.store');
            
        // Group post replies
        Route::post('/{group}/posts/{post}/replies', [App\Http\Controllers\GroupPostController::class, 'storeReply'])
            ->middleware('auth')
            ->name('posts.replies.store');
    });
});

// Add a route for replying to forum posts from the home page
Route::post('/forum/{post}/reply', [App\Http\Controllers\ForumController::class, 'storeReply'])
    ->name('forum.reply')
    ->middleware('auth');
