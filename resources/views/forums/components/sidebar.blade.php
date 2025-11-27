@if(isset($group) && $group->isMember(auth()->user()))
<!-- Create Post Modal -->
<div id="createPostModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl w-full max-w-2xl mx-auto overflow-hidden transform transition-all">
        <div class="p-5 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold">Create Post in {{ $group->name }}</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <!-- Debug Panel -->
        <style>
            #debug-panel {
                position: fixed;
                bottom: 20px;
                right: 20px;
                width: 500px;
                max-height: 400px;
                background: white;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                border-radius: 8px;
                z-index: 10000;
                display: flex;
                flex-direction: column;
                overflow: hidden;
            }
            #debug-panel-header {
                background: #4f46e5;
                color: white;
                padding: 8px 16px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                cursor: move;
            }
            #debug-panel-content {
                padding: 12px;
                overflow-y: auto;
                flex-grow: 1;
                font-family: monospace;
                font-size: 12px;
                line-height: 1.4;
            }
            .debug-message {
                margin-bottom: 4px;
                padding: 4px 0;
                border-bottom: 1px solid #f0f0f0;
            }
            .debug-timestamp {
                color: #666;
                margin-right: 8px;
            }
            .debug-info { color: #2563eb; }
            .debug-error { color: #dc2626; }
            .debug-success { color: #059669; }
            .debug-warning { color: #d97706; }
        </style>

        <div id="debug-panel" style="display: block;">
            <div id="debug-panel-header">
                <strong>Debug Console</strong>
                <div>
                    <button id="clear-debug" class="text-white hover:text-gray-200 mr-2">
                        <i class="fas fa-trash"></i>
                    </button>
                    <button id="toggle-debug" class="text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div id="debug-panel-content">
                <div class="debug-message">
                    <span class="debug-timestamp">[System]</span>
                    <span class="debug-info">Debug console initialized. Ready to capture logs.</span>
                </div>
            </div>
        </div>
        
        <!-- Debug Info Container -->
        <div id="file-info" class="hidden"></div>
        
        <form id="create-post-form" action="{{ route('forums.groups.posts.store', ['group' => $group->slug]) }}" method="POST" enctype="multipart/form-data" onsubmit="event.preventDefault();">
            @csrf
            <div class="p-5">
                <div class="flex items-start space-x-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-medium flex-shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="flex-1">
                        <textarea name="content" 
                                id="post-content"
                                rows="4"
                                class="w-full border-0 focus:ring-0 resize-none text-gray-800 placeholder-gray-400 text-base" 
                                placeholder="What's on your mind?"
                                required></textarea>
                    </div>
                </div>
                
                <!-- Media Upload Section -->
                <div class="mb-4" id="media-upload-section">
                    <div id="drop-zone" class="border-2 border-dashed border-gray-200 rounded-xl p-4 text-center cursor-pointer hover:border-indigo-300 transition-colors">
                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-300 mb-2"></i>
                        <p class="text-sm text-gray-500 mb-1">Drag & drop files here or click to browse</p>
                        <p class="text-xs text-gray-400">Supports images, videos, and documents (max 10MB)</p>
                        <input type="file" 
                               class="hidden" 
                               id="media-upload" 
                               name="media[]" 
                               multiple 
                               accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx"
                               onchange="debugFileUpload(this)">
                    </div>
                    <div id="file-preview" class="mt-3 grid grid-cols-3 gap-2">
                        <!-- Preview items will be added here -->
                    </div>
                </div>
                
                <!-- Link Input (Hidden by default) -->
                <div id="link-input-container" class="mb-4 hidden">
                    <label for="media-link" class="block text-sm font-medium text-gray-700 mb-1">Paste media or website link</label>
                    <div class="flex">
                        <input type="url" id="media-link" name="media_link" 
                               class="flex-1 rounded-l-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                               placeholder="https://example.com">
                        <button type="button" id="insert-link" 
                                class="inline-flex items-center px-4 py-2 border border-l-0 border-gray-300 bg-gray-50 text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                            Insert
                        </button>
                    </div>
                </div>
                
                <!-- Post Actions -->
                <div class="flex items-center justify-between border-t border-gray-100 pt-4">
                    <div class="flex space-x-1">
                        <!-- Media Upload Button -->
                        <button type="button" id="media-upload-trigger" 
                                class="w-10 h-10 rounded-full text-gray-500 hover:bg-gray-100 flex items-center justify-center relative group"
                                title="Upload media (max 10MB)">
                            <i class="far fa-image"></i>
                            <input type="file" class="hidden" id="media-upload" name="media" accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx">
                        </button>
                        
                        <!-- Link Button -->
                        <button type="button" id="insert-link-trigger" 
                                class="w-10 h-10 rounded-full text-gray-500 hover:bg-gray-100 flex items-center justify-center"
                                title="Insert link">
                            <i class="fas fa-link"></i>
                        </button>
                        
                        <!-- Emoji Picker (Placeholder) -->
                        <button type="button" 
                                class="w-10 h-10 rounded-full text-gray-500 hover:bg-gray-100 flex items-center justify-center"
                                title="Insert emoji">
                            <i class="far fa-smile"></i>
                        </button>
                        
                        <!-- Media Preview Toggle -->
                        <button type="button" id="media-preview-toggle" 
                                class="w-10 h-10 rounded-full text-gray-500 hover:bg-gray-100 flex items-center justify-center ml-2 hidden"
                                title="Show/hide media preview">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <button type="submit" 
                            class="bg-indigo-600 text-white px-6 py-2 rounded-full font-medium hover:bg-indigo-700 transition">
                        Post
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

<!-- Forum Sidebar Component -->
<div class="space-y-4">
    @if(isset($group) && $group->isMember(auth()->user()))
        <!-- Create Post Card -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-medium">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <button 
                    class="flex-1 text-left text-gray-500 bg-gray-50 rounded-full px-4 py-2 hover:bg-gray-100 transition text-sm"
                    onclick="openModal()">
                    What's on your mind?
                </button>
            </div>
        </div>
    @endif

    <!-- About Group -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <h3 class="text-lg font-medium text-gray-900 mb-4">About this group</h3>
        <p class="text-sm text-gray-600 mb-4">{{ $group->description ?? 'No description provided.' }}</p>
        
        <div class="space-y-3 text-sm">
            <div class="flex items-center">
                <i class="fas fa-users text-gray-500 w-5"></i>
                <span class="ml-2 text-gray-600">
                    {{ $group->is_private ? 'Private' : 'Public' }} group
                </span>
            </div>
            <div class="flex items-center">
                <i class="fas fa-user-shield text-gray-500 w-5"></i>
                <span class="ml-2 text-gray-600">
                    Created by {{ $group->creator->name ?? 'Unknown' }}
                </span>
            </div>
            <div class="flex items-center">
                <i class="far fa-calendar-alt text-gray-500 w-5"></i>
                <span class="ml-2 text-gray-600">
                    Created {{ $group->created_at->diffForHumans() }}
                </span>
            </div>
            
            @if($group->rules)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">Group Rules</h4>
                    <ul class="text-sm text-gray-600 space-y-2">
                        @foreach(explode("\n", $group->rules) as $rule)
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2 text-xs"></i>
                                <span>{{ trim($rule) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Members -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium text-gray-900">Members</h3>
        @if($group->members_count > 5)
            <a href="#" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                See all
            </a>
        @endif
    </div>
    
    <div class="space-y-3">
        @forelse($group->members->take(5) as $member)
            <div class="flex items-center justify-between group">
                <div class="flex items-center flex-1 min-w-0">
                    <div class="member-avatar flex-shrink-0">
                        {{ strtoupper(substr($member->name, 0, 1)) }}
                    </div>
                    <div class="ml-3 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $member->name }}</p>
                        <p class="text-xs text-gray-500">
                            @if($member->id === $group->creator_id)
                                Group Creator
                            @elseif($member->pivot->role === 'admin')
                                Group Admin
                            @else
                                Member
                            @endif
                        </p>
                    </div>
                </div>
                @if($group->isAdmin(auth()->user()) && $member->id !== $group->creator_id)
                    <div class="relative group-hover:opacity-100 opacity-0 transition-opacity">
                        <button type="button" 
                                class="text-gray-400 hover:text-gray-500 focus:outline-none"
                                x-data="{ open: false }"
                                @click="open = !open"
                                @click.away="open = false">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div x-show="open" 
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-10">
                            <div class="py-1">
                                @if($member->pivot->role !== 'admin')
                                    <button type="button" 
                                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Make Admin
                                    </button>
                                @else
                                    <button type="button" 
                                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Remove Admin
                                    </button>
                                @endif
                                <button type="button" 
                                        class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    Remove from Group
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <p class="text-sm text-gray-500 text-center py-4">No members yet</p>
        @endforelse
    </div>
    
    @if($group->members_count > 5)
        <div class="mt-4 text-center">
            <a href="#" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                View all {{ $group->members_count }} members
            </a>
        </div>
    @endif
    
    @if(isset($group) && auth()->check() && $group->isMember(auth()->user()))
        <div class="mt-4 pt-4 border-t border-gray-100">
            <form action="{{ route('forum.groups.leave', $group) }}" method="POST">
                @csrf
                <button type="submit" 
                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-red-300 shadow-sm text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        onclick="return confirm('Are you sure you want to leave this group?')">
                    <i class="fas fa-sign-out-alt mr-2"></i>
                    Leave Group
                </button>
            </form>
        </div>
    @endif


    <!-- About Community
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <h3 class="font-semibold text-gray-800 mb-3">About Community</h3>
        <p class="text-gray-600 text-sm mb-4">
            A community for web developers to share knowledge, ask questions, and collaborate on projects. 
            All skill levels are welcome! Discuss frameworks, tools, best practices, and stay updated with 
            the latest in web development.
        </p>
        <div class="space-y-3 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-500">Created</span>
                <span class="font-medium">Jan 1, 2023</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Rules</span>
                <a href="#" class="text-indigo-600 hover:underline font-medium">View</a>
            </div>
        </div>
    </div> -->
    
    <!-- Online Members
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Online Members</h3>
            <span class="text-sm text-indigo-600 font-medium">1,234 online</span>
        </div>
        <div class="flex flex-wrap -mx-1">
            @for($i = 1; $i <= 10; $i++)
                <div class="w-8 h-8 m-1 rounded-full bg-gray-100 overflow-hidden transform transition-transform hover:scale-110" 
                     data-tooltip="User {{ $i }}">
                    <img src="https://i.pravatar.cc/100?img={{ $i }}" 
                         alt="User" 
                         class="w-full h-full object-cover">
                    <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></div>
                </div>
            @endfor
        </div>
    </div> -->
</div>

@push('scripts')
<script>
// Debug function to log messages to file
function debugLog(message, type = 'info', context = {}) {
    const timestamp = new Date().toLocaleTimeString();
    const logMessage = typeof message === 'object' ? JSON.stringify(message) : message;
    
    // Log to console for immediate feedback
    switch(type) {
        case 'error': console.error(`[${timestamp}] ${logMessage}`, context); break;
        case 'warn': console.warn(`[${timestamp}] ${logMessage}`, context); break;
        default: console.log(`[${timestamp}] [${type}] ${logMessage}`, context);
    }
    
    // Send to server to save to file
    fetch('/debug/log', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            message: logMessage,
            type: type,
            context: context
        })
    }).catch(error => {
        console.error('Failed to save debug log:', error);
    });
}

// Function to load logs from file
function loadLogs() {
    fetch('/debug/logs')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.logs) {
                const debugContent = document.getElementById('debug-panel-content');
                if (debugContent) {
                    debugContent.textContent = data.logs;
                    debugContent.scrollTop = debugContent.scrollHeight;
                }
            }
        })
        .catch(error => {
            console.error('Failed to load logs:', error);
        });
}

// Make debug panel draggable
function makeDraggable(panel, header) {
    let pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
    
    if (header) {
        header.onmousedown = dragMouseDown;
    }

    function dragMouseDown(e) {
        e = e || window.event;
        e.preventDefault();
        // Get the mouse cursor position at startup
        pos3 = e.clientX;
        pos4 = e.clientY;
        document.onmouseup = closeDragElement;
        // Call a function whenever the cursor moves
        document.onmousemove = elementDrag;
    }

    function elementDrag(e) {
        e = e || window.event;
        e.preventDefault();
        // Calculate the new cursor position
        pos1 = pos3 - e.clientX;
        pos2 = pos4 - e.clientY;
        pos3 = e.clientX;
        pos4 = e.clientY;
        // Set the element's new position
        panel.style.top = (panel.offsetTop - pos2) + "px";
        panel.style.left = (panel.offsetLeft - pos1) + "px";
        panel.style.right = 'auto';
        panel.style.bottom = 'auto';
    }

    function closeDragElement() {
        // Stop moving when mouse button is released
        document.onmouseup = null;
        document.onmousemove = null;
    }
}

// Initialize debug panel when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const debugPanel = document.getElementById('debug-panel');
    const debugHeader = document.getElementById('debug-panel-header');
    const toggleButton = document.getElementById('toggle-debug');
    const clearButton = document.getElementById('clear-debug');
    
    // Make panel draggable
    if (debugPanel && debugHeader) {
        makeDraggable(debugPanel, debugHeader);
    }
    
    // Toggle panel visibility
    if (toggleButton) {
        toggleButton.addEventListener('click', function() {
            debugPanel.style.display = debugPanel.style.display === 'none' ? 'flex' : 'none';
        });
    }
    
    // Clear debug messages
    if (clearButton) {
        clearButton.addEventListener('click', function() {
            const content = document.getElementById('debug-panel-content');
            if (content) {
                content.innerHTML = '';
                debugLog('Debug log cleared', 'info');
            }
        });
    }
    
    // Load existing logs and set up auto-refresh
    loadLogs();
    setInterval(loadLogs, 5000); // Refresh logs every 5 seconds
    
    // Log initial message
    debugLog('Debug console initialized. Ready to capture logs.', 'info');
});

// Debug function to show file info
function debugFileUpload(input) {
    const files = input.files;
    debugLog('File input changed');
    
    if (files && files.length > 0) {
        const fileList = Array.from(files).map(file => ({
            name: file.name,
            size: file.size,
            type: file.type
        }));
        debugLog({
            event: 'files_selected',
            count: files.length,
            files: fileList
        });
    } else {
        debugLog('No files selected', 'warning');
    }
}

// Isolate our code in an IIFE to prevent conflicts
(function(window, document) {
    'use strict';
    
    // Wait for DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize form submission
        const form = document.getElementById('create-post-form');
        if (form) {
            // Remove any existing submit event listeners
            const newForm = form.cloneNode(true);
            form.parentNode.replaceChild(newForm, form);
            
            // Track form submission state
            let isSubmitting = false;
            
            // Add new submit event listener
            newForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Prevent multiple submissions
                if (isSubmitting) {
                    debugLog('Form submission already in progress', 'warning');
                    return false;
                }
                
                // Set submitting state
                isSubmitting = true;
                
                const fileInput = document.getElementById('media-upload');
                const submitButton = newForm.querySelector('button[type="submit"]');
                const originalButtonText = submitButton ? submitButton.innerHTML : '';
                
                // Disable submit button to prevent multiple submissions
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting...';
                }
                
                // Log form submission
                debugLog('Form submission started');
                
                // Check if there are files to upload
                if (fileInput && fileInput.files.length > 0) {
                    // Check each file size
                    const maxSize = 10 * 1024 * 1024; // 10MB
                    let valid = true;
                    
                    for (let i = 0; i < fileInput.files.length; i++) {
                        if (fileInput.files[i].size > maxSize) {
                            alert('One or more files exceed the maximum file size of 10MB');
                            valid = false;
                            break;
                        }
                    }
                    
                    if (!valid) {
                        if (submitButton) {
                            submitButton.disabled = false;
                            submitButton.innerHTML = originalButtonText;
                        }
                        return false;
                    }
                }
                
                // Create FormData object
                const formData = new FormData();
                
                // Add all form fields except files
                const formElements = newForm.elements;
                for (let i = 0; i < formElements.length; i++) {
                    const element = formElements[i];
                    if (element.name && element.type !== 'file' && element.name !== '_token') {
                        formData.append(element.name, element.value);
                    }
                }
                
                // Add CSRF token
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                
                // Add file if exists
                if (fileInput && fileInput.files.length > 0) {
                    formData.append('media', fileInput.files[0]);
                }
                
                // Log form data to debug panel
                debugLog('=== Form Submission Started ===');
                debugLog('Endpoint: ' + newForm.action);
                debugLog('Form Data:');
                
                // Log form data entries
                const formDataObj = {};
                for (let [key, value] of formData.entries()) {
                    if (value instanceof File) {
                        debugLog(`- ${key}: ${value.name} (${value.size} bytes, ${value.type})`);
                        formDataObj[key] = {
                            name: value.name,
                            size: value.size,
                            type: value.type,
                            isFile: true
                        };
                    } else {
                        debugLog(`- ${key}: ${value}`);
                        formDataObj[key] = value;
                    }
                }
                
                // Log complete form data as object
                debugLog('Complete FormData:', 'info');
                debugLog(JSON.stringify(formDataObj, null, 2), 'info');
                
                // Submit the form
                fetch(newForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                        // Don't set Content-Type, let the browser set it with the boundary
                    },
                    cache: 'no-store' // Prevent caching of the request
                })
                .then(async response => {
                    const data = await response.json().catch(() => ({}));
                    debugLog('=== Server Response ===', 'info');
                    debugLog(`Status: ${response.status} ${response.statusText}`, 'info');
                    debugLog('Response Data:', 'info');
                    debugLog(JSON.stringify(data, null, 2), 'info');
                    
                    // Reset form state on success
                    isSubmitting = false;
                    
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalButtonText;
                    }
                    
                    if (response.redirected) {
                        debugLog(`Redirecting to: ${response.url}`, 'info');
                        window.location.href = response.url;
                    } else if (data.redirect) {
                        debugLog(`Redirecting to: ${data.redirect}`, 'info');
                        window.location.href = data.redirect;
                    } else if (data.success) {
                        debugLog('Post created successfully', 'success');
                        window.location.reload();
                    } else {
                        const errorMsg = data.message || 'An error occurred';
                        debugLog(`Error: ${errorMsg}`, 'error');
                        throw new Error(errorMsg);
                    }
                    return data;
                })
                .catch(error => {
                    debugLog(`Error: ${error.message}`, 'error');
                    console.error('Form submission error:', error);
                    
                    // Reset form state on error
                    isSubmitting = false;
                    
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalButtonText;
                    }
                    
                    // Show error message to user
                    alert('An error occurred while submitting the form. Please try again.');
                });
            });
        }
        // Initialize only if the required elements exist
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('media-upload');
        const filePreview = document.getElementById('file-preview');
        const mediaUploadTrigger = document.getElementById('media-upload-trigger');
        const insertLinkTrigger = document.getElementById('insert-link-trigger');
        const linkInputContainer = document.getElementById('link-input-container');
        const mediaLinkInput = document.getElementById('media-link');
        const insertLinkBtn = document.getElementById('insert-link');
        const mediaPreviewToggle = document.getElementById('media-preview-toggle');
        const mediaUploadSection = document.getElementById('media-upload-section');
        
        // If no file input or drop zone, exit
        if (!fileInput || !dropZone) return;
        
        let files = [];
    // Simple function to find the parent form
    function findParentForm(element) {
        try {
            return element && typeof element.closest === 'function' 
                ? element.closest('form') 
                : null;
        } catch (e) {
            // Fallback for older browsers
            while (element && element !== document) {
                if (element.tagName === 'FORM') {
                    return element;
                }
                element = element.parentNode;
            }
            return null;
        }
    }

    // Set up drag and drop
    try {
        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        // Highlight drop zone when item is dragged over it
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        // Handle dropped files
        dropZone.addEventListener('drop', handleDrop, false);
        
        // Click on drop zone to open file dialog
        dropZone.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (fileInput) fileInput.click();
        });
    } catch (e) {
        console.error('Error setting up drag and drop:', e);
    }
    
    // Handle file input change
    if (fileInput) {
        fileInput.addEventListener('change', handleFiles, false);
    }
    
    // Click on upload button to open file dialog
    if (mediaUploadTrigger) {
        mediaUploadTrigger.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (fileInput) fileInput.click();
        });
    }
    
    // Toggle link input
    if (insertLinkTrigger) {
        insertLinkTrigger.addEventListener('click', (e) => {
            e.preventDefault();
            linkInputContainer.classList.toggle('hidden');
            mediaUploadSection.classList.toggle('hidden', !linkInputContainer.classList.contains('hidden'));
        });
    }
    
    // Insert link
    if (insertLinkBtn) {
        insertLinkBtn.addEventListener('click', insertLink);
        mediaLinkInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                insertLink();
            }
        });
    }
    
    // Toggle media preview
    if (mediaPreviewToggle) {
        mediaPreviewToggle.addEventListener('click', () => {
            filePreview.classList.toggle('hidden');
            mediaPreviewToggle.innerHTML = filePreview.classList.contains('hidden') ? 
                '<i class="fas fa-eye"></i>' : 
                '<i class="fas fa-eye-slash"></i>';
        });
    }

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    function highlight() {
        dropZone.classList.add('border-indigo-500', 'bg-indigo-50');
    }

    function unhighlight() {
        dropZone.classList.remove('border-indigo-500', 'bg-indigo-50');
    }

    function handleDrop(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (!fileInput) return;
        
        const dt = e.dataTransfer;
        if (!dt || !dt.files) return;
        
        const droppedFiles = Array.from(dt.files);
        
        // Filter out files that are too large (max 10MB)
        const validFiles = droppedFiles.filter(file => file.size <= 10 * 1024 * 1024);
        
        if (validFiles.length !== droppedFiles.length) {
            alert('Some files exceed the maximum file size of 10MB and were not added.');
        }
        
        // Create a new DataTransfer object and add the files
        const dataTransfer = new DataTransfer();
        
        // Add existing files
        if (fileInput.files) {
            Array.from(fileInput.files).forEach(file => {
                dataTransfer.items.add(file);
            });
        }
        
        // Add new files
        validFiles.forEach(file => {
            dataTransfer.items.add(file);
        });
        
        // Update the file input with all files
        fileInput.files = dataTransfer.files;
        
        // Update the files array and preview
        files = Array.from(fileInput.files);
        updateFilePreviews();
    }

    function handleFiles(e) {
        if (!fileInput || !fileInput.files || fileInput.files.length === 0) return;
        
        // Only take the first file
        const newFiles = [fileInput.files[0]];
        
        // Clear the input to allow re-uploading the same file
        fileInput.value = '';
        
        // Log the file being processed
        debugLog(`Processing file: ${newFiles[0].name} (${newFiles[0].size} bytes, ${newFiles[0].type})`);
        
        // Filter out files that are too large (max 10MB)
        const validFiles = newFiles.filter(file => file.size <= 10 * 1024 * 1024);
        
        if (validFiles.length !== newFiles.length) {
            alert('Some files exceed the maximum file size of 10MB and were not added.');
        }
        
        // Update the files array
        files = validFiles;
        
        // Update the preview
        updateFilePreviews();
        
        // Show media preview toggle if we have files
        if (files.length > 0 && filePreview) {
            if (mediaPreviewToggle) mediaPreviewToggle.classList.remove('hidden');
            filePreview.classList.remove('hidden');
        } else {
            if (mediaPreviewToggle) mediaPreviewToggle.classList.add('hidden');
            if (filePreview) filePreview.classList.add('hidden');
        }
    }

    function updateFilePreviews() {
        if (!filePreview) return;
        
        filePreview.innerHTML = ''; // Clear existing previews
        
        files.forEach((file, index) => {
            const previewItem = document.createElement('div');
            previewItem.className = 'relative group';
            
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewItem.innerHTML = `
                        <img src="${e.target.result}" class="w-full h-20 object-cover rounded-md">
                        <button type="button" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity" data-index="${index}">
                            &times;
                        </button>
                    `;
                    
                    // Add remove button event listener
                    const removeBtn = previewItem.querySelector('button');
                    if (removeBtn) {
                        removeBtn.addEventListener('click', (e) => removeFile(e, index));
                    }
                };
                reader.readAsDataURL(file);
            } else if (file.type.startsWith('video/')) {
                previewItem.innerHTML = `
                    <div class="bg-gray-100 w-full h-20 rounded-md flex items-center justify-center">
                        <i class="fas fa-video text-2xl text-gray-400"></i>
                        <button type="button" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity" data-index="${index}">
                            &times;
                        </button>
                    </div>
                    <div class="text-xs text-gray-500 truncate mt-1">${file.name}</div>
                `;
                
                // Add remove button event listener
                const removeBtn = previewItem.querySelector('button');
                if (removeBtn) {
                    removeBtn.addEventListener('click', (e) => removeFile(e, index));
                }
            } else {
                previewItem.innerHTML = `
                    <div class="bg-gray-100 w-full h-20 rounded-md flex items-center justify-center">
                        <i class="fas fa-file text-2xl text-gray-400"></i>
                        <button type="button" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity" data-index="${index}">
                            &times;
                        </button>
                    </div>
                    <div class="text-xs text-gray-500 truncate mt-1">${file.name}</div>
                `;
                
                // Add remove button event listener
                const removeBtn = previewItem.querySelector('button');
                if (removeBtn) {
                    removeBtn.addEventListener('click', (e) => removeFile(e, index));
                }
            }
            
            filePreview.appendChild(previewItem);
        });
        
        // Show or hide the preview container based on files
        if (files.length > 0) {
            filePreview.classList.remove('hidden');
            if (mediaPreviewToggle) mediaPreviewToggle.classList.remove('hidden');
        } else {
            filePreview.classList.add('hidden');
            if (mediaPreviewToggle) mediaPreviewToggle.classList.add('hidden');
        }
    }
    
    function removeFile(e, index) {
        e.preventDefault();
        e.stopPropagation();
        
        if (!fileInput) return;
        
        // Remove the file from the files array
        files.splice(index, 1);
        
        // Update the file input
        const dataTransfer = new DataTransfer();
        files.forEach(file => dataTransfer.items.add(file));
        fileInput.files = dataTransfer.files;
        
        // Update the preview
        updateFilePreviews();
        
        // If no files left, hide the preview
        if (files.length === 0) {
            if (mediaPreviewToggle) mediaPreviewToggle.classList.add('hidden');
            if (filePreview) filePreview.classList.add('hidden');
        }
    }
    
    function insertLink() {
        const link = mediaLinkInput ? mediaLinkInput.value.trim() : '';
        if (!link) return;
        
        // Simple URL validation
        try {
            new URL(link);
            
            // Add the link to the content
            const contentField = document.querySelector('textarea[name="content"]');
            if (contentField) {
                const currentText = contentField.value;
                const linkText = `\n${link}\n`; // Add newlines for better formatting
                const newText = currentText + linkText;
                contentField.value = newText;
                
                // Clear and hide the link input
                if (mediaLinkInput) mediaLinkInput.value = '';
                if (linkInputContainer) linkInputContainer.classList.add('hidden');
                if (mediaUploadSection) mediaUploadSection.classList.remove('hidden');
                
                // Focus back on the content field
                contentField.focus();
                
                // Move cursor to the end
                contentField.selectionStart = contentField.selectionEnd = newText.length;
            }
        } catch (e) {
            alert('Please enter a valid URL (e.g., https://example.com)');
        }
    }
    
    // Initialize the component
    if (insertLinkBtn && mediaLinkInput) {
        insertLinkBtn.addEventListener('click', insertLink);
        mediaLinkInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                insertLink();
            }
        });
    }
    
    // Toggle media preview
    if (mediaPreviewToggle && filePreview) {
        mediaPreviewToggle.addEventListener('click', function() {
            filePreview.classList.toggle('hidden');
            mediaPreviewToggle.innerHTML = filePreview.classList.contains('hidden') ? 
                '<i class="fas fa-eye"></i>' : 
                '<i class="fas fa-eye-slash"></i>';
        });
    }
    }); // End of DOMContentLoaded
})(window, document); // End of IIFE
</script>
@endpush

@push('styles')
<style>
    [data-tooltip] {
        position: relative;
        cursor: pointer;
    }
    
    [data-tooltip]:hover::before {
        content: attr(data-tooltip);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%) translateY(-5px);
        background-color: #1F2937;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        white-space: nowrap;
        z-index: 10;
        opacity: 0;
        visibility: hidden;
        transition: all 0.2s ease;
    }
    
    [data-tooltip]:hover::after {
        content: '';
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%) translateY(-10px);
        border-width: 5px;
        border-style: solid;
        border-color: #1F2937 transparent transparent transparent;
        opacity: 0;
        visibility: hidden;
        transition: all 0.2s ease;
    }
    
    [data-tooltip]:hover::before,
    [data-tooltip]:hover::after {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) translateY(0);
    }
</style>
@endpush


@push('scripts')
<script>
    function openModal() {
        const modal = document.getElementById('createPostModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        document.addEventListener('keydown', handleEscape);
    }

    function closeModal() {
        const modal = document.getElementById('createPostModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        document.removeEventListener('keydown', handleEscape);
    }

    function handleEscape(event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    }

    // Close modal when clicking outside
    document.getElementById('createPostModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeModal();
        }
    });
</script>
@endpush
