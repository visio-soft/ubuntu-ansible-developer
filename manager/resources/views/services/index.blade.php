@extends('layouts.app')

@section('content')
<header class="flex justify-between items-center mb-10">
    <h1 class="text-3xl font-bold tracking-tight">Services & Logs</h1>
    <div>
        <a href="{{ route('services.php') }}" class="btn btn-secondary">Edit php.ini</a>
    </div>
</header>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    @foreach($status as $key => $s)
    <div class="card">
        <div class="flex justify-between items-start mb-6">
            <div>
                <div class="text-lg font-semibold">{{ $s['label'] }}</div>
                <div class="text-xs text-apple-grey font-mono">{{ $key }}</div>
            </div>
            <div>
                @if($s['active'])
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-green-500/10 text-green-600 uppercase tracking-tighter">Active</span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-red-500/10 text-red-600 uppercase tracking-tighter">Stopped</span>
                @endif
            </div>
        </div>
        
        <div class="flex justify-between gap-2">
            <form action="{{ route('services.restart') }}" method="POST" class="grow">
                @csrf
                <input type="hidden" name="service" value="{{ $key }}">
                <button type="submit" class="btn-secondary w-full !py-2 !text-xs">Restart</button>
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
            <a href="{{ route('services.logs', ['type' => $logKey]) }}" class="btn-secondary shrink-0 !py-2 !px-4 !text-xs">Logs</a>
            @endif
        </div>
    </div>
    @endforeach
</div>

<h2 class="mt-12 mb-6 text-xl font-bold tracking-tight">Project Logs</h2>
<div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
    @foreach($projects as $proj)
    <div class="card hover:ring-1 hover:ring-apple-blue/10">
        <div class="flex justify-between items-center">
            <div class="text-sm font-semibold truncate">{{ $proj }}</div>
            <a href="{{ route('services.logs', ['type' => 'project', 'project' => $proj]) }}" class="btn-secondary !py-1.5 !px-3 !text-[10px] font-bold uppercase tracking-tight">Log</a>
        </div>
    </div>
    @endforeach
</div>
@endsection
