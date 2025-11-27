<!-- Navigation Tabs -->
<div class="bg-white rounded-b-xl shadow-sm border border-gray-100 -mt-4 relative z-10">
    <div class="border-t border-gray-200">
        <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
            <a href="{{ route('forums.groups.show', $group->slug) }}" 
               class="border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ request()->routeIs('forums.groups.show') ? 'tab-active' : 'tab-inactive' }}">
                <i class="fas fa-stream mr-2"></i>Discussion
            </a>
            <a href="{{ route('forums.groups.media', $group) }}" 
               class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ request()->routeIs('forums.groups.media') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                <i class="fas fa-images mr-2"></i>Media
            </a>
            @if(auth()->check() && $group->isAdmin(auth()->user()))
                <a href="#" 
                   class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm tab-inactive">
                    <i class="fas fa-cog mr-2"></i>Settings
                </a>
            @endif
        </nav>
    </div>
</div>
