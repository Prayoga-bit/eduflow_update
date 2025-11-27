@extends('forums.groups.show')

@push('styles')
    @parent
    <style>
        /* Additional styles specific to the discussion view */
        .post-actions {
            @apply flex items-center space-x-4 text-sm text-gray-500;
        }
        
        .post-actions button {
            @apply flex items-center space-x-1 hover:text-indigo-600 transition-colors;
        }
        
        .post-actions i {
            @apply text-base;
        }
    </style>
@endpush

@section('content')
    @parent
    
    @section('group-content')
        @if($posts->count() > 0 || (auth()->check() && $group->isMember(auth()->user())))
            <!-- Posts -->
            <div class="space-y-6">
                @if($posts->count() > 0)
                    @foreach($posts as $post)
                        @include('forums.components.post', ['post' => $post])
                    @endforeach
                    
                    @if($posts->hasPages())
                        <div class="mt-6">
                            {{ $posts->links() }}
                        </div>
                    @endif
                @else
                    <div class="bg-white rounded-xl p-8 text-center border border-gray-100">
                        <i class="fas fa-comment-slash text-4xl text-gray-300 mb-3"></i>
                        <h3 class="text-lg font-medium text-gray-900">No posts yet</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Be the first to start a discussion in this group!
                        </p>
                    </div>
                @endif
            </div>
        @else
            <div class="bg-white rounded-xl p-8 text-center border border-gray-100">
                <i class="fas fa-lock text-4xl text-gray-300 mb-3"></i>
                <h3 class="text-lg font-medium text-gray-900">This is a private group</h3>
                <p class="mt-1 text-sm text-gray-500">
                    You need to join this group to view and participate in discussions.
                </p>
                @if(auth()->check())
                    <div class="mt-4">
                        <form action="{{ route('forum.groups.join', $group->slug) }}" method="POST" class="inline-block">
                            @csrf
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-user-plus mr-2"></i> Join Group
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        @endif
    @endsection
@endsection

@push('scripts')
    <script>
        // Initialize any discussion-specific JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Handle post likes
            document.querySelectorAll('.like-button').forEach(button => {
                button.addEventListener('click', function() {
                    const postId = this.dataset.postId;
                    // Implement like functionality
                    console.log('Liked post:', postId);
                });
            });
            
            // Handle post comments
            document.querySelectorAll('.comment-button').forEach(button => {
                button.addEventListener('click', function() {
                    const postId = this.dataset.postId;
                    // Toggle comment section
                    const comments = document.getElementById(`comments-${postId}`);
                    comments.classList.toggle('hidden');
                });
            });
        });
    </script>
@endpush
