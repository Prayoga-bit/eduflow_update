@extends('forums.components.feed-layout', ['activeTab' => 'media'])

@push('styles')
<style>
    .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1rem;
        margin-top: 1.5rem;
    }
    
    .media-item {
        position: relative;
        aspect-ratio: 1/1;
        border-radius: 0.5rem;
        overflow: hidden;
        display: block;
        transition: transform 0.2s ease-in-out;
    }
    
    .media-item:hover {
        transform: translateY(-4px);
    }
    
    .media-item img,
    .media-item .video-placeholder {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .media-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(transparent, rgba(0,0,0,0.8));
        color: white;
        padding: 1rem;
        opacity: 0;
        transition: opacity 0.2s ease-in-out;
    }
    
    .media-item:hover .media-overlay {
        opacity: 1;
    }
    
    .media-placeholder {
        background: #f3f4f6;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        height: 100%;
        text-align: center;
        padding: 1rem;
    }
    
    .media-icon {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
    }
    
    .media-label {
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }
</style>
@endpush

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">
                @if(isset($group))
                    {{ $group->name }} Media
                @else
                    All Media Posts
                @endif
            </h2>
            @if(isset($group) && $group->is_private)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 mt-1">
                    <i class="fas fa-lock mr-1"></i> Private Group
                </span>
            @endif
        </div>
        
        @if(isset($group))
            <a href="{{ route('forums.groups.show', $group) }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-arrow-left mr-2"></i> Back to Group
            </a>
        @endif
    </div>
    
    @if($mediaPosts->count() > 0)
        <div class="media-grid">
            @foreach($mediaPosts as $post)
                <a href="{{ route('forums.posts.show', $post->postid) }}" class="media-item group">
                    @if($post->media_type === 'video')
                        <div class="media-placeholder">
                            <i class="fas fa-play-circle media-icon"></i>
                            <span class="media-label">Video</span>
                            <span class="sr-only">Video</span>
                        </div>
                    @elseif($post->media_type === 'document')
                        <div class="media-placeholder">
                            <i class="fas fa-file-alt media-icon"></i>
                            <span class="media-label">Document</span>
                            <span class="sr-only">Document</span>
                        </div>
                    @else
                        <img src="{{ asset('storage/' . $post->media_path) }}" 
                             alt="{{ $post->title }}" 
                             loading="lazy"
                             class="w-full h-full object-cover">
                    @endif
                    <div class="media-overlay">
                        <div class="font-medium">{{ Str::limit($post->title, 30) ?: 'Untitled' }}</div>
                        <div class="text-sm opacity-90">By {{ $post->user->name }}</div>
                        @if(!isset($group))
                            <div class="text-xs opacity-80 mt-1">
                                in {{ $post->group->name }}
                            </div>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
        
        <!-- Pagination -->
        <div class="mt-6">
            {{ $mediaPosts->links() }}
        </div>
    @else
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <i class="fas fa-images text-4xl text-gray-300 mb-3"></i>
            <h3 class="text-lg font-medium text-gray-900">No media found</h3>
            <p class="mt-1 text-gray-500">
                @if(isset($group))
                    This group doesn't have any media posts yet.
                @else
                    No media posts found in your groups.
                @endif
            </p>
        </div>
    @endif
@endsection
