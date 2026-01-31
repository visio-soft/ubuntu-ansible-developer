@extends('layouts.app')

@section('content')
<header class="flex justify-between items-center mb-10">
    <h1 class="text-3xl font-bold tracking-tight">Software Center</h1>
    <div>
        <span class="badge badge-other">Available Tools</span>
    </div>
</header>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($software as $tool)
        <div class="card flex flex-col justify-between">
            <div>
                <div class="flex gap-4 items-center mb-4">
                    <div class="text-4xl">{{ $tool['icon'] }}</div>
                    <div>
                        <div class="text-lg font-bold">{{ $tool['name'] }}</div>
                        <div class="text-sm text-apple-grey">{{ $tool['description'] }}</div>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 flex justify-between items-center">
                @if($tool['installed'])
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-green-500/10 text-green-600 uppercase tracking-tight">
                         Installed
                    </span>
                    @if($tool['url'])
                    <a href="{{ $tool['url'] }}" class="btn-secondary !px-4 !py-1.5 !text-xs">Open</a>
                    @endif
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-gray-500/10 text-gray-500 uppercase tracking-tight">
                         Not Installed
                    </span>
                    <form action="{{ route('software.install') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="software" value="{{ $tool['key'] }}">
                        <button type="submit" class="btn !px-4 !py-1.5 !text-xs font-bold">Install</button>
                    </form>
                @endif
            </div>
        </div>
    @endforeach
</div>
@endsection
