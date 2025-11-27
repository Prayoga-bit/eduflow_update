<div class="post-card relative">
    <style>
        [x-cloak] { display: none !important; }
        .dropdown-menu {
            z-index: 50;
        }
    </style>
    <div class="flex items-start space-x-3">
        <!-- User Avatar -->
        <div class="flex-shrink-0">
            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-medium">
                {{ strtoupper(substr($post->user->name, 0, 1)) }}
            </div>
        </div>
        
        <!-- Post Content -->
        <div class="flex-1 min-w-0">
            <!-- Post Header -->
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-900">
                        {{ $post->user->name }}
                        @if($post->user_id === $post->group->user_id)
                            <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                <i class="fas fa-crown mr-1 text-yellow-500"></i> Admin
                            </span>
                        @endif
                    </p>
                    <div class="flex items-center text-xs text-gray-500 mt-0.5">
                        <time datetime="{{ $post->created_at->toIso8601String() }}" title="{{ $post->created_at->format('F j, Y g:i a') }}">
                            {{ $post->created_at->diffForHumans() }}
                        </time>
                        @if($post->group)
                            <span class="mx-1">•</span>
                            <a href="{{ route('forums.groups.show', $post->group->slug) }}" class="text-indigo-600 hover:text-indigo-800">
                                {{ $post->group->name }}
                            </a>
                        @endif
                    </div>
                </div>
                
                @if(auth()->check() && (auth()->id() === $post->user_id || ($post->group && $post->group->isAdmin(auth()->user()))))
                    <div x-data="{ open: false }" class="relative" @keydown.escape="open = false">
                        <button 
                            @click="open = !open" 
                            @keydown.space.prevent="open = !open"
                            @keydown.enter.prevent="open = !open"
                            class="text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            :aria-expanded="open.toString()"
                            :aria-haspopup="true"
                            type="button"
                        >
                            <span class="sr-only">More options</span>
                            <i class="fas fa-ellipsis-h text-sm"></i>
                        </button>
                        
                        <!-- Dropdown menu -->
                        <div 
                            x-show="open" 
                            @click.away="open = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="dropdown-menu absolute right-0 z-50 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none" 
                            role="menu" 
                            aria-orientation="vertical" 
                            aria-labelledby="options-menu"
                            x-cloak
                            x-ref="dropdown"
                            @keydown.escape.window="open = false"
                            @click.away="open = false"
                        >
                            <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                                @if(auth()->id() === $post->user_id)
                                    <!-- Edit option for post owner -->
                                    <a 
                                        href="{{ route('forums.posts.edit', $post) }}" 
                                        class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100"
                                        role="menuitem"
                                        tabindex="-1"
                                        @click="open = false"
                                    >
                                        <i class="fas fa-edit mr-2"></i> Edit Post
                                    </a>
                                    
                                    <!-- Delete option for post owner -->
                                    <form action="{{ route('forums.posts.destroy', $post) }}" method="POST" class="w-full">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="button" 
                                            class="text-red-600 w-full text-left block px-4 py-2 text-sm hover:bg-gray-100"
                                            role="menuitem"
                                            tabindex="-1"
                                            @click="
                                                if(confirm('Are you sure you want to delete this post?')) { 
                                                    this.form.submit(); 
                                                }
                                            "
                                        >
                                            <i class="fas fa-trash mr-2"></i> Delete Post
                                        </button>
                                    </form>
                                @elseif($post->group && $post->group->isAdmin(auth()->user()))
                                    <!-- Remove post option for group admin -->
                                    <form action="{{ route('forums.posts.destroy', $post) }}" method="POST" class="w-full">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="button" 
                                            class="text-red-600 w-full text-left block px-4 py-2 text-sm hover:bg-gray-100"
                                            role="menuitem"
                                            tabindex="-1"
                                            @click="
                                                if(confirm('Are you sure you want to remove this post?')) { 
                                                    this.form.submit(); 
                                                }
                                            "
                                        >
                                            <i class="fas fa-trash mr-2"></i> Remove Post
                                        </button>
                                    </form>
                                @endif
                                
                                <!-- Copy Link option (always visible) -->
                                <button 
                                    type="button" 
                                    class="text-gray-700 w-full text-left block px-4 py-2 text-sm hover:bg-gray-100"
                                    role="menuitem"
                                    tabindex="-1"
                                    @click="
                                        navigator.clipboard.writeText('{{ url()->current() }}');
                                        alert('Link copied to clipboard!');
                                        open = false;
                                    "
                                >
                                    <i class="fas fa-link mr-2"></i> Copy Link
                                </button>
                                
                                @if(auth()->check() && auth()->id() !== $post->user_id)
                                    <!-- Report option for non-owners -->
                                    <button 
                                        type="button"
                                        class="text-gray-700 w-full text-left block px-4 py-2 text-sm hover:bg-gray-100"
                                        @click="
                                            alert('Report functionality will be implemented here');
                                            open = false;
                                        "
                                    >
                                        <i class="fas fa-flag mr-2"></i> Report Post
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            
            <!-- Post Body -->
            <div class="mt-2 text-sm text-gray-700 prose max-w-none">
                {{ $post->content }}
            </div>
            
            <!-- Post Media -->
            @if($post->media->isNotEmpty())
                <div class="mt-3 grid grid-cols-2 gap-2">
                    @foreach($post->media as $media)
                        <div class="rounded-lg overflow-hidden bg-gray-100">
                            <img src="{{ $media->getUrl() }}" alt="" class="w-full h-32 object-cover">
                        </div>
                    @endforeach
                </div>
            @endif
            
            <!-- Post Actions -->
            <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-3">
                <div class="flex items-center space-x-4">
                    <button type="button" class="flex items-center text-xs font-medium text-gray-500 hover:text-indigo-600">
                        <i class="far fa-thumbs-up mr-1.5"></i>
                        <span>Like</span>
                        @if($post->likes_count > 0)
                            <span class="ml-1 text-gray-700">{{ $post->likes_count }}</span>
                        @endif
                    </button>
                    <button type="button" class="flex items-center text-xs font-medium text-gray-500 hover:text-indigo-600">
                        <i class="far fa-comment-alt mr-1.5"></i>
                        <span>Comment</span>
                        @if($post->comments_count > 0)
                            <span class="ml-1 text-gray-700">{{ $post->comments_count }}</span>
                        @endif
                    </button>
                    <button type="button" class="flex items-center text-xs font-medium text-gray-500 hover:text-indigo-600">
                        <i class="far fa-share-square mr-1.5"></i>
                        <span>Share</span>
                    </button>
                </div>
                
                <div class="text-xs text-gray-500">
                    {{ $post->views }} {{ Str::plural('view', $post->views) }}
                </div>
            </div>
            
            <!-- Comments Section -->
            <div class="mt-3 border-t border-gray-100 pt-3">
                <!-- Comment Form -->
                @auth
                    <form action="{{ route('forums.comments.store', $post) }}" method="POST" class="mt-2">
                        @csrf
                        <div class="flex items-start space-x-2">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-xs font-medium">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                            </div>
                            <div class="flex-1">
                                <input type="text" name="content" placeholder="Write a comment..." 
                                       class="block w-full border-0 border-b border-gray-200 focus:ring-0 focus:border-indigo-500 text-sm px-0 py-1"
                                       required>
                            </div>
                        </div>
                    </form>
                @endauth
                
                <!-- Comments List -->
                @if($post->comments->isNotEmpty())
                    <div class="mt-3 space-y-3">
                        @foreach($post->comments->take(3) as $comment)
                            <div class="flex items-start space-x-2">
                                <div class="flex-shrink-0">
                                    <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-xs font-medium">
                                        {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="bg-gray-50 rounded-lg p-2">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-medium text-gray-900">{{ $comment->user->name }}</span>
                                            <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="mt-0.5 text-sm text-gray-700">{{ $comment->content }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        
                        @if($post->comments_count > 3)
                            <a href="{{ route('forums.posts.show', $post) }}" class="block text-xs font-medium text-indigo-600 hover:text-indigo-800 text-center mt-2">
                                View all {{ $post->comments_count }} comments
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
