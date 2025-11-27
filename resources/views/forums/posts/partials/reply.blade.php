<div class="flex space-x-3 {{ $depth > 0 ? 'ml-8 mt-4' : 'mt-6' }}">
    <div class="flex-shrink-0">
        <img class="h-10 w-10 rounded-full" src="{{ $reply->user->profile_photo_url }}" alt="{{ $reply->user->name }}">
    </div>
    <div class="flex-1 min-w-0">
        <div class="rounded-lg border border-gray-200 overflow-hidden">
            <div class="bg-white p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <p class="text-sm font-medium text-gray-900">{{ $reply->user->name }}</p>
                        <span class="text-xs text-gray-500">•</span>
                        <time datetime="{{ $reply->created_at->toDateTimeString() }}" class="text-xs text-gray-500">
                            {{ $reply->created_at->diffForHumans() }}
                        </time>
                    </div>
                    @if(auth()->id() === $reply->user_id || auth()->user()->is_admin)
                    <div class="relative">
                        <button type="button" id="reply-options-{{ $reply->id }}" class="text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Open options</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                            </svg>
                        </button>
                        <div id="reply-dropdown-{{ $reply->id }}" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Edit</a>
                            <form action="{{ route('forums.replies.destroy', $reply) }}" method="POST" class="block w-full text-left">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100" onclick="return confirm('Are you sure you want to delete this reply?')">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="mt-1 text-sm text-gray-700">
                    {!! \Illuminate\Support\Str::markdown($reply->content) !!}
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-2 border-t border-gray-100">
                <div class="flex items-center space-x-4">
                    <button type="button" class="inline-flex items-center text-xs text-gray-500 hover:text-gray-700"
                            onclick="document.getElementById('reply-form-{{ $reply->id }}').classList.toggle('hidden')">
                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        Reply
                    </button>
                    <button type="button" class="inline-flex items-center text-xs text-gray-500 hover:text-gray-700">
                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path>
                        </svg>
                        Like ({{ $reply->likes_count ?? 0 }})
                    </button>
                </div>

                <!-- Nested reply form (initially hidden) -->
                <div id="reply-form-{{ $reply->id }}" class="mt-3 hidden">
                    <form action="{{ route('forums.replies.store', $reply->post) }}" method="POST">
                        @csrf
                        <input type="hidden" name="parent_id" value="{{ $reply->id }}">
                        <div class="mb-2">
                            <label for="reply-content-{{ $reply->id }}" class="sr-only">Your reply</label>
                            <textarea id="reply-content-{{ $reply->id }}" name="content" rows="2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                placeholder="Write your reply..." required></textarea>
                            @error('content')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex justify-end space-x-2">
                            <button type="button" class="text-xs text-gray-500 hover:text-gray-700"
                                onclick="document.getElementById('reply-form-{{ $reply->id }}').classList.add('hidden')">
                                Cancel
                            </button>
                            <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Reply
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Nested replies -->
        @if($reply->replies->count() > 0)
            <div class="mt-4 space-y-4 border-l-2 border-gray-200 pl-4">
                @foreach($reply->replies as $nestedReply)
                    @include('forums.posts.partials.reply', ['reply' => $nestedReply, 'depth' => $depth + 1])
                @endforeach
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Toggle reply options dropdown
    document.addEventListener('DOMContentLoaded', function() {
        const replyOptions = document.getElementById('reply-options-{{ $reply->id }}');
        if (replyOptions) {
            replyOptions.addEventListener('click', function(e) {
                e.preventDefault();
                const dropdown = document.getElementById('reply-dropdown-{{ $reply->id }}');
                dropdown.classList.toggle('hidden');
            });
        }
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#reply-options-{{ $reply->id }}') && !e.target.closest('#reply-dropdown-{{ $reply->id }}')) {
            const dropdown = document.getElementById('reply-dropdown-{{ $reply->id }}');
            if (dropdown && !dropdown.classList.contains('hidden')) {
                dropdown.classList.add('hidden');
            }
        }
    });
</script>
@endpush
