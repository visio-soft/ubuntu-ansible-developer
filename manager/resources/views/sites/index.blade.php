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
                <input type="text" name="repo" id="repo" placeholder="git@github.com:username/repo.git" class="input-field grow">
                <button type="button" class="btn btn-secondary !px-4" onclick="checkGit()">Check Access</button>
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

        <button type="submit" class="btn w-full">Create Project</button>
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
            
            <div class="text-[0.8rem] text-apple-grey space-y-1">
                <span class="block">Path: {{ $site['path'] }}</span>
            </div>

            @if($site['type'] === 'Laravel')
            <div class="mt-4 pt-4 border-t border-apple-border flex items-center justify-between">
                <span class="text-[0.8rem] font-medium">Horizon Status</span>
                @if($site['horizon'] === 'running')
                    <span class="badge bg-green-500/10 text-green-600 flex items-center normal-case">
                         <span class="w-2 h-2 bg-green-500 rounded-full mr-1.5 animate-pulse"></span> Active
                    </span>
                @else
                     <span class="badge bg-gray-500/10 text-gray-500 normal-case">
                         Inactive
                    </span>
                @endif
            </div>
            @endif
        </div>
    @endforeach
</div>

<script>
async function checkGit() {
    const repo = document.getElementById('repo').value;
    const msg = document.getElementById('gitMessage');
    
    if (!repo) {
        msg.textContent = 'Please enter a repository URL.';
        msg.className = 'mt-2 text-xs text-red-500 block';
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
