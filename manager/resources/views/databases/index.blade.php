@extends('layouts.app')

@section('content')
<header class="mb-8 flex items-center justify-between">
    <div>
        <h1 class="text-4xl font-semibold tracking-tight text-[#1d1d1f]">Databases</h1>
        <p class="text-apple-grey mt-2">Manage your PostgreSQL databases.</p>
    </div>
    <button onclick="document.getElementById('createDbForm').classList.toggle('hidden')" class="btn">
        New Database +
    </button>
</header>

<!-- Create Database Form (Hidden by default) -->
<div id="createDbForm" class="card mb-12 hidden">
    <h2 class="text-lg font-semibold mb-6">Create PostgreSQL Database</h2>
    <form action="{{ route('databases.create') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm text-apple-grey mb-2">Database Name</label>
            <input type="text" name="name" placeholder="my_database" class="input-field" required>
            <p class="text-xs text-apple-grey mt-1">A PostgreSQL user with the same name will be created</p>
        </div>
        <div>
            <label class="block text-sm text-apple-grey mb-2">Password</label>
            <input type="password" name="password" placeholder="Minimum 6 characters" class="input-field" required>
        </div>
        <div class="flex justify-end gap-3">
            <button type="button" onclick="document.getElementById('createDbForm').classList.add('hidden')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn">Create Database</button>
        </div>
    </form>
</div>

<!-- Database List -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    @foreach($databases as $db)
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold">{{ $db->datname }}</h3>
                <p class="text-sm text-apple-grey">{{ number_format($db->size / 1024 / 1024, 2) }} MB</p>
            </div>
            <a href="tableplus:///?driver=PostgreSQL&host=127.0.0.1&port=5432&database={{ $db->datname }}&user={{ $db->datname }}&password=secret" 
               class="btn-secondary text-xs px-4 py-2">
                Open in TablePlus
            </a>
        </div>
    </div>
    @endforeach
</div>
@endsection
