@extends('layouts.app')

@section('content')
<header class="mb-12">
    <h1 class="text-4xl font-semibold tracking-tight text-[#1d1d1f]">Software</h1>
    <p class="text-apple-grey mt-2">Manage installed development tools.</p>
</header>

<div class="card p-0 overflow-hidden">
    <div class="divide-y divide-apple-border">
    @foreach($software as $tool)
        <div class="p-6 flex items-center justify-between hover:bg-[#fafafa] transition-colors">
            <div class="flex items-center gap-5">
                <span class="text-3xl filter drop-shadow-sm">{{ $tool['icon'] }}</span>
                <div>
                    <h3 class="text-lg font-semibold text-[#1d1d1f] flex items-center gap-2">
                        {{ $tool['name'] }}
                        @if($tool['installed'])
                            <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-[10px] font-bold uppercase tracking-wide">Installed</span>
                        @endif
                    </h3>
                    <p class="text-sm text-apple-grey mt-0.5">{{ $tool['description'] }}</p>
                    @if($tool['installed'] && $tool['version'])
                        <p class="text-xs text-apple-grey mt-1 font-mono">v{{ $tool['version'] }}</p>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-3">
                @if($tool['installed'])
                    @if($tool['url'])
                        <a href="{{ $tool['url'] }}" target="_blank" class="btn-secondary text-xs px-4 py-2 font-medium">Open</a>
                    @else
                        <button disabled class="btn-secondary opacity-50 cursor-default text-xs px-4 py-2 font-medium">Installed</button>
                    @endif
                @else
                    <form action="{{ route('software.install') }}" method="POST">
                        @csrf
                        <input type="hidden" name="software" value="{{ $tool['key'] }}">
                        <button type="submit" class="btn text-xs px-6 py-2">Install</button>
                    </form>
                @endif
            </div>
        </div>
    @endforeach
    </div>
</div>
@endsection
