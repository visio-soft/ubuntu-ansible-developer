@extends('layouts.app')

@section('content')
<header class="flex justify-between items-center mb-10">
    <h1 class="text-3xl font-bold tracking-tight">Projects</h1>
    <div>
        <span class="badge badge-other">PHP 8.4</span>
    </div>
</header>

<div class="bg-white p-6 rounded-apple-lg shadow-sm border border-black/5 mb-10 max-w-2xl">
    <div class="mb-2 font-semibold">Create New Project</div>
    
    <form action="{{ route('sites.store') }}" method="POST" id="createForm" class="space-y-4">
        @csrf
        
        <!-- Repo URL Input -->
        <div>
            <label class="block text-xs font-semibold text-apple-grey mb-1 uppercase tracking-wider">GitHub Repository (SSH Recommended)</label>
            <div class="flex gap-2">
                <input type="text" name="repo" id="repo" placeholder="git@github.com:username/repo.git" class="input-field grow" autocomplete="off">
            </div>
            <div id="gitMessage" class="mt-2 text-xs hidden"></div>
        </div>

        <!-- Project Name -->
        <div>
            <label class="block text-xs font-semibold text-apple-grey mb-1 uppercase tracking-wider">Project Name (Folder Name)</label>
            <input type="text" name="name" id="name" placeholder="my-app" class="input-field" required>
        </div>

        <!-- Options -->
        <div class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="horizon" id="horizon" value="1" class="cursor-pointer">
            <label for="horizon" class="cursor-pointer">Install & Configure Laravel Horizon</label>
        </div>

        <button type="submit" id="submitBtn" class="btn w-full">Create Project</button>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($sites as $site)
        <div class="card">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <div class="text-lg font-semibold">{{ $site['name'] }}</div>
                    <a href="{{ $site['url'] }}" target="_blank" class="text-sm text-apple-blue hover:underline">{{ $site['url'] }} ↗</a>
                </div>
                <div>
                   <span class="badge {{ $site['type'] === 'Laravel' ? 'badge-laravel' : 'badge-other' }}">
                       {{ $site['type'] }} {{ $site['version'] }}
                   </span>
                </div>
            </div>
            
            <div class="text-[0.8rem] text-apple-grey space-y-1.5">
                <span class="block">Path: {{ $site['path'] }}</span>
                <span class="block flex items-center gap-1.5 font-medium text-apple-grey">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                    Database: <span class="text-apple-dark">{{ $site['db_name'] }}</span>
                </span>
            </div>

            <div class="mt-6 pt-4 border-t border-apple-border flex flex-col gap-3">
                @if($site['type'] === 'Laravel')
                    <div class="flex items-center justify-between">
                        <span class="text-[0.8rem] font-medium">Horizon Status</span>
                        @if($site['horizon'] === 'running')
                            <div class="flex items-center gap-3">
                                <a href="{{ $site['url'] }}/horizon" target="_blank" class="text-[0.7rem] font-bold text-apple-blue hover:underline uppercase tracking-tight">Open Dashboard</a>
                                <span class="badge bg-green-500/10 text-green-600 flex items-center normal-case">
                                     <span class="w-2 h-2 bg-green-500 rounded-full mr-1.5 animate-pulse"></span> Active
                                </span>
                            </div>
                        @else
                             <span class="badge bg-gray-500/10 text-gray-500 normal-case">
                                 Inactive
                            </span>
                        @endif
                    </div>
                @endif
                
                <div class="flex gap-2">
                    @if($site['env_exists'])
                        <a href="{{ route('sites.env', $site['name']) }}" class="btn-secondary !py-1.5 !text-[11px] font-bold uppercase tracking-tight grow justify-center">Edit .env</a>
                    @endif
                    <form action="{{ route('sites.destroy', $site['name']) }}" method="POST" onsubmit="return confirm('Silmek istediğine emin misin? Bu işlem projeyi ve yapılandırmaları tamamen silecektir (Veritabanı korunur).')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-secondary !py-1.5 !px-3 hover:!bg-red-50 hover:!text-red-500 hover:!border-red-200 transition-colors" title="Delete Project">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>

<script>
let checkTimeout;
const repoInput = document.getElementById('repo');
const submitBtn = document.getElementById('submitBtn');
const msg = document.getElementById('gitMessage');

repoInput.addEventListener('input', () => {
    clearTimeout(checkTimeout);
    msg.textContent = 'Typing...';
    msg.className = 'mt-2 text-xs text-apple-grey block';
    
    checkTimeout = setTimeout(checkGit, 800);
});

async function checkGit() {
    const repo = repoInput.value;
    
    if (!repo) {
        msg.textContent = '';
        msg.className = 'mt-2 text-xs hidden';
        return;
    }

    msg.textContent = 'Checking access...';
    msg.className = 'mt-2 text-xs text-apple-grey block';

    try {
        const response = await fetch(`{{ route('sites.check-git') }}?repo=${encodeURIComponent(repo)}`);
        const data = await response.json();
        
        if (data.status === 'ok') {
            msg.textContent = '✅ Access Granted';
            msg.className = 'mt-2 text-xs text-green-500 block';
            
            const nameInput = document.getElementById('name');
            if (!nameInput.value) {
                const parts = repo.split('/');
                const last = parts[parts.length - 1];
                const clean = last.replace('.git', '');
                nameInput.value = clean;
            }
        } else {
            msg.innerHTML = `❌ ${data.message}<br>Suggested Action: ${data.key_guide}<br><strong>Public Key:</strong><br><textarea readonly class="w-full h-20 text-[10px] mt-1 p-2 bg-gray-50 rounded border border-gray-200 outline-none">${data.public_key}</textarea>`;
            msg.className = 'mt-2 text-xs text-red-500 block';
        }
    } catch (e) {
        msg.textContent = '❌ Error checking access.';
        msg.className = 'mt-2 text-xs text-red-500 block';
    }
}
</script>
@endsection
