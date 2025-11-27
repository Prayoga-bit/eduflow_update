@extends('layouts.main')

@section('title', $group->name . ' - EduFlow')

@push('styles')
<style>
    .group-avatar {
        @apply w-24 h-24 rounded-full border-4 border-white -mt-12 ml-8 bg-white shadow-lg;
    }
    
    .tab-active {
        @apply border-indigo-500 text-indigo-600 border-b-2 font-medium;
    }
    
    .tab-inactive {
        @apply border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300;
    }
    
    .member-avatar {
        @apply w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-medium text-sm;
    }
</style>
@endpush

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Forum Banner -->
        @include('forums.components.forum-banner')
        
        <!-- Group Navigation Tabs -->
        @include('forums.groups.partials.nav-tabs')
        
        <div class="mt-6 flex flex-col lg:flex-row gap-6">
            <!-- Main Content -->
            <main class="flex-1 min-w-0 py-2">
                @if(request()->routeIs('forums.groups.show') || request()->routeIs('forums.groups.discussion'))
                    <!-- Posts List -->
                    @include('forums.groups.partials.posts', ['posts' => $posts])
                @else
                    @hasSection('group-content')
                        @yield('group-content')
                    @endif
                @endif
            </main>
            
            <!-- Sidebar -->
            <aside class="lg:w-80 flex-shrink-0 space-y-6">
                @include('forums.components.sidebar')
            </aside>
        </div>
    </div>
</div>

@stack('modals')
@endsection

@push('scripts')
<script>
    // Group specific JavaScript can go here
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize any group page specific JavaScript
    });
</script>
@endpush
