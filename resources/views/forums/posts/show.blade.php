@extends('layouts.app')

@section('title', $post->title . ' - ' . $group->name . ' - EduFlow')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Back to group link -->
    <div class="mb-4">
        <a href="{{ route('forums.groups.show', $group->slug) }}" class="text-indigo-600 hover:text-indigo-800 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to {{ $group->name }}
        </a>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Main Content -->
        <div class="flex-1">
            <!-- Main Post -->
            @include('forums.components.post', ['post' => $post, 'showFull' => true])

            <!-- Replies -->
            <div class="mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Replies</h3>
                @if($post->replies->count() > 0)
                    @foreach($post->replies as $reply)
                        @include('forums.components.post', ['post' => $reply])
                    @endforeach
                @else
                    <div class="text-center py-8 text-gray-500">
                        <p>No replies yet. Be the first to reply!</p>
                    </div>
                @endif
            </div>

            <!-- Reply Form -->
            <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Leave a reply</h3>
                <form action="{{ route('forums.replies.store', $post) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="content" class="sr-only">Your reply</label>
                        <textarea id="content" name="content" rows="4" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Write your reply here..." required></textarea>
                        @error('content')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Post Reply
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:w-80 flex-shrink-0">
            @include('forums.components.sidebar', ['group' => $group])
        </div>
    </div>
</div>
@endsection
