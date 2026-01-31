@extends('layouts.app')

@section('content')
<header class="mb-8 flex items-center justify-between">
    <div>
        <h1 class="text-4xl font-semibold tracking-tight text-[#1d1d1f]">Projects</h1>
        <p class="text-apple-grey mt-2">Manage your local development projects.</p>
    </div>
    <button onclick="document.getElementById('createForm').classList.toggle('hidden')" class="btn">
        <span class="mr-1">+</span> New Project
    </button>
</header>

<!-- Create New Project (Hidden by default) -->
<div id="createForm" class="card mb-12 hidden" x-data="{ 
    verified: false,
    setVerified(val) {
        this.verified = val;
    }
}">
    <h2 class="text-lg font-semibold mb-6">New Project</h2>
    <form action="{{ route('sites.store') }}" method="POST" class="space-y-5">
        @csrf
        <div>
            <label class="block text-sm text-apple-grey mb-2">GitHub Repository</label>
            <input type="text" name="repo" id="repo" placeholder="git@github.com:user/repo.git" class="input-field" autocomplete="off">
            <div id="gitMessage" class="mt-2 text-xs hidden"></div>
        </div>
        
        <!-- Show these fields only after verification -->
        <div x-show="verified" x-transition class="space-y-5">
            <div>
                <label class="block text-sm text-apple-grey mb-2">Project Name</label>
                <input type="text" name="name" id="name" placeholder="my-project" class="input-field" required>
            </div>
            <div class="flex items-center gap-3">
                <input type="checkbox" name="horizon" id="horizon" value="1" class="w-4 h-4 rounded">
                <label for="horizon" class="text-sm">Install Laravel Horizon</label>
            </div>
            <div class="flex items-center gap-3">
                <input type="checkbox" name="deployment" id="deployment" value="1" class="w-4 h-4 rounded" checked>
                <label for="deployment" class="text-sm">Run Laravel Deployment (migrate, storage:link, npm install)</label>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="btn">Create Project</button>
            </div>
        </div>
    </form>
</div>

<!-- Project List -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    @foreach($sites as $site)
    <div class="card p-0 flex flex-col h-full overflow-hidden hover:shadow-md transition-shadow duration-300">
        <!-- Card Header -->
        <div class="p-5 pb-0">
            <div class="flex items-start justify-between mb-2">
                <h3 class="text-xl font-bold text-[#1d1d1f] tracking-tight truncate">{{ $site['name'] }}</h3>
            </div>
            <a href="{{ $site['url'] }}" target="_blank" class="text-sm text-apple-blue hover:underline font-medium mb-4 block truncate">
                {{ $site['url'] }}
            </a>
            
            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-4 py-4 border-t border-dashed border-gray-200">
                <div>
                    <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">DATABASE</span>
                    <p class="text-sm font-medium text-gray-700 truncate" title="{{ $site['db_name'] }}">
                        {{ $site['db_name'] }}
                    </p>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">VERSION</span>
                    <p class="text-sm font-medium text-gray-700">
                        {{ $site['version'] ?? 'N/A' }}
                    </p>
                </div>
                @if($site['type'] === 'Laravel')
                <div class="col-span-2">
                     <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">HORIZON</span>
                     <div class="mt-1">
                        @if($site['horizon'] === 'running')
                            <a href="{{ $site['url'] }}/horizon" target="_blank" class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-green-50 text-green-700 text-xs font-semibold hover:bg-green-100 transition-colors">
                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span> Active
                            </a>
                        @else
                            <span class="inline-block px-2 py-1 rounded bg-gray-50 text-gray-500 text-xs font-semibold">
                                Inactive
                            </span>
                        @endif
                     </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Action Footer -->
        <div class="mt-auto border-t border-gray-100 bg-gray-50/50 p-2 flex items-center justify-between">
            <div class="flex gap-1">
                <a href="file://{{ $site['path'] }}" 
                   class="flex items-center justify-center w-8 h-8 rounded hover:bg-white hover:shadow-sm transition-all text-gray-500 hover:text-gray-800" 
                   title="Open Project Folder">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                </a>
                <a href="{{ route('services.logs', ['type' => 'project', 'project' => $site['name']]) }}" 
                   class="flex items-center justify-center w-8 h-8 rounded hover:bg-white hover:shadow-sm transition-all text-gray-500 hover:text-gray-800 relative" 
                   title="View Logs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    @if($site['log_count'] > 0)
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[9px] font-bold rounded-full w-4 h-4 flex items-center justify-center">
                        {{ $site['log_count'] > 99 ? '99+' : $site['log_count'] }}
                    </span>
                    @endif
                </a>
            </div>

            <!-- Dropdown Container -->
            <div class="relative">
                <button onclick="toggleDropdown(event, 'dropdown-{{ $loop->index }}')" class="flex items-center justify-center w-8 h-8 rounded hover:bg-white hover:shadow-sm transition-all text-gray-500 hover:text-gray-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                </button>
                <!-- Dropdown Menu -->
                <div id="dropdown-{{ $loop->index }}" class="dropdown-menu absolute right-0 bottom-full mb-2 w-48 bg-white rounded-lg shadow-xl border border-gray-100 hidden z-20">
                    @if($site['env_exists'])
                    <a href="{{ route('sites.env', $site['name']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 first:rounded-t-lg">
                        Edit .env
                    </a>
                    @endif
                    <form action="{{ route('services.logs-clear') }}" method="POST" onsubmit="return confirm('Clear all logs for this project?')">
                        @csrf
                        <input type="hidden" name="type" value="project">
                        <input type="hidden" name="project" value="{{ $site['name'] }}">
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            Clear Logs
                        </button>
                    </form>
                    <form action="{{ route('sites.destroy', $site['name']) }}" method="POST" onsubmit="return confirm('Delete this project? Database will be preserved.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 last:rounded-b-lg">
                            Delete Project
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- ... services section ... -->

<script>
// ... git check code ...

// Dropdown Logic
function toggleDropdown(e, id) {
    e.stopPropagation();
    const menu = document.getElementById(id);
    const isHidden = menu.classList.contains('hidden');
    
    // Close all others
    document.querySelectorAll('.dropdown-menu').forEach(el => el.classList.add('hidden'));
    
    if (isHidden) {
        menu.classList.remove('hidden');
    }
}

// Close on outside click
document.addEventListener('click', (e) => {
    // Don't close if click is inside a dropdown menu
    if (e.target.closest('.dropdown-menu')) {
        return;
    }
    document.querySelectorAll('.dropdown-menu').forEach(el => el.classList.add('hidden'));
});
</script>

<!-- Services Section -->
<div class="mt-16 pt-8 border-t border-apple-border">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-semibold tracking-tight text-[#1d1d1f]">System Services</h2>
            <p class="text-apple-grey mt-1 text-sm">Monitor and manage core infrastructure.</p>
        </div>
        <a href="{{ route('services.php') }}" class="btn-secondary text-xs px-4 py-2">Edit php.ini</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        @foreach($servicesStatus as $key => $s)
        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold">{{ $s['label'] }}</h3>
                @if($s['active'])
                    <span class="text-xs text-green-600 font-medium flex items-center gap-1.5">
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span> Active
                    </span>
                @else
                    <span class="text-xs text-red-500 font-medium">Stopped</span>
                @endif
            </div>
            <p class="text-xs text-apple-grey mb-4 font-mono">{{ $key }}</p>
            <div class="flex gap-2">
                <form action="{{ route('sites.restart-service') }}" method="POST" class="flex-1">
                    @csrf
                    <input type="hidden" name="service" value="{{ $key }}">
                    <button type="submit" class="btn-secondary text-xs w-full py-2">Restart</button>
                </form>
                @php 
                    $logKey = match($key) {
                        'nginx' => 'nginx',
                        'php8.4-fpm' => 'php',
                        'redis-server' => 'redis',
                        'postgresql' => 'postgres',
                        default => null
                    };
                @endphp
                @if($logKey)
                <a href="{{ route('services.logs', ['type' => $logKey]) }}" class="btn-secondary text-xs px-4 py-2" title="View Logs">Logs</a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

<script>
let timeout;
const repoInput = document.getElementById('repo');
const msg = document.getElementById('gitMessage');

repoInput.addEventListener('input', () => {
    clearTimeout(timeout);
    msg.textContent = '';
    msg.className = 'mt-2 text-xs hidden';
    
    // Reset verification state on change (Alpine v3)
    const formElement = document.getElementById('createForm');
    if (formElement && formElement._x_dataStack) {
        formElement._x_dataStack[0].verified = false;
    }
    
    timeout = setTimeout(checkGit, 600);
});

async function checkGit() {
    const repo = repoInput.value;
    if (!repo) return;

    msg.textContent = 'Checking...';
    msg.className = 'mt-2 text-xs text-apple-grey block';

    try {
        const res = await fetch(`{{ route('sites.check-git') }}?repo=${encodeURIComponent(repo)}`);
        const data = await res.json();

        if (data.status === 'ok') {
            msg.textContent = '✓ Access verified';
            msg.className = 'mt-2 text-xs text-green-600 block';
            
            // Set Alpine.js verified state to true (Alpine v3)
            const formElement = document.getElementById('createForm');
            if (formElement && formElement._x_dataStack) {
                formElement._x_dataStack[0].verified = true;
            }
            
            if (!document.getElementById('name').value) {
                const name = repo.split('/').pop().replace('.git', '');
                document.getElementById('name').value = name;
            }
        } else {
            msg.innerHTML = `✕ ${data.message}<br><textarea readonly class="w-full h-16 mt-2 p-2 text-[10px] bg-gray-100 rounded">${data.public_key || ''}</textarea>`;
            msg.className = 'mt-2 text-xs text-red-500 block';
        }
    } catch (e) {
        msg.textContent = '✕ Check failed';
        msg.className = 'mt-2 text-xs text-red-500 block';
        
        // Reset verification state on error (Alpine v3)
        const formElement = document.getElementById('createForm');
        if (formElement && formElement._x_dataStack) {
            formElement._x_dataStack[0].verified = false;
        }
    }
}
</script>
@endsection
