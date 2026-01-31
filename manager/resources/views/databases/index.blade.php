@extends('layouts.app')

@section('content')
<header class="flex justify-between items-center mb-10">
    <h1 class="text-3xl font-bold tracking-tight">Databases</h1>
    <div>
        <span class="badge badge-other">PostgreSQL 14</span>
    </div>
</header>

<div class="bg-white p-6 rounded-apple-lg shadow-sm border border-black/5 mb-10 max-w-2xl">
    <div class="mb-4 font-semibold">Create New Database</div>
    
    <form action="{{ route('databases.store') }}" method="POST" class="flex gap-2">
        @csrf
        <input type="text" name="name" placeholder="database_name" class="input-field" required>
        <button type="submit" class="btn shrink-0">Create</button>
    </form>
</div>

<div class="bg-white rounded-apple-lg shadow-sm border border-black/5 overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead class="bg-gray-50 border-b border-apple-border">
            <tr>
                <th class="px-6 py-4 text-xs font-bold text-apple-grey uppercase tracking-widest">Name</th>
                <th class="px-6 py-4 text-xs font-bold text-apple-grey uppercase tracking-widest">Size</th>
                <th class="px-6 py-4 text-xs font-bold text-apple-grey uppercase tracking-widest">Status</th>
                <th class="px-6 py-4 text-xs font-bold text-apple-grey uppercase tracking-widest">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($databases as $db)
                <tr class="hover:bg-gray-50/50 transition-colors border-b border-apple-border last:border-0">
                    <td class="px-6 py-4 text-sm font-semibold">{{ $db->datname }}</td>
                    <td class="px-6 py-4 text-sm text-apple-grey">{{ round($db->size / 1024 / 1024, 2) }} MB</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-500/10 text-green-600 uppercase tracking-tighter">
                            Active
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex gap-2">
                             <a href="postgres://{{ $db->datname }}:secret@127.0.0.1:5432/{{ $db->datname }}?name={{ $db->datname }}&statusColor=0071e3" 
                                class="btn-secondary px-3 py-1.5 rounded-apple-sm text-xs font-semibold transition-all hover:ring-1 hover:ring-apple-blue/20">
                                Open in TablePlus
                             </a>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if(count($databases) === 0)
    <div class="mt-10 text-center py-12 border-2 border-dashed border-apple-border rounded-apple-lg">
        <div class="text-apple-grey">No databases found.</div>
    </div>
@endif

@endsection
