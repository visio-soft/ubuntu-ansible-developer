@extends('layouts.app')

@section('content')
<header class="flex justify-between items-center mb-10">
    <h1 class="text-3xl font-bold tracking-tight">Edit .env: {{ $site }}</h1>
    <div class="flex gap-4">
        <a href="{{ route('sites.index') }}" class="btn-secondary !px-4 !py-2 !text-sm flex items-center">Back</a>
    </div>
</header>

<div class="card p-8">
    <div class="mb-4 flex items-center justify-between">
        <div class="text-[10px] font-bold text-apple-grey uppercase tracking-widest flex items-center gap-2">
            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
            Path: {{ $path }}
        </div>
    </div>
    
    <form action="{{ route('sites.save-env', $site) }}" method="POST" class="space-y-6">
        @csrf
        <textarea name="content" 
                  class="w-full h-[60vh] font-mono p-6 bg-gray-50 border border-apple-border rounded-apple-sm text-[13px] leading-relaxed outline-none focus:ring-1 focus:ring-apple-blue/20 transition-all resize-none shadow-inner" 
                  spellcheck="false">{{ $content }}</textarea>
        
        <div class="flex justify-end pt-4">
            <button type="submit" class="btn !px-8">
                Save & Update Environment
            </button>
        </div>
    </form>
</div>
@endsection
