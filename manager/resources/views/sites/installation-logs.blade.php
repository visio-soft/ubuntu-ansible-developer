@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <header class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-[#1d1d1f]">Installation Logs</h1>
        <p class="text-xs text-apple-grey mt-1">Real-time project setup progress</p>
    </header>

    <div class="card p-0 overflow-hidden">
        <div class="bg-[#1e1e1e] text-gray-300 font-mono text-xs h-[70vh] overflow-y-auto p-4" id="logContainer">
            <div class="text-gray-500">Loading logs...</div>
        </div>
    </div>

    <div class="mt-4 flex justify-end">
        <a href="{{ route('sites.index') }}" class="btn-secondary">Back to Projects</a>
    </div>
</div>

<script>
let lastSize = 0;
const logContainer = document.getElementById('logContainer');

async function fetchLogs() {
    try {
        const response = await fetch('/installation-logs');
        const data = await response.json();
        
        if (data.content && data.content.length > lastSize) {
            lastSize = data.content.length;
            logContainer.innerHTML = '<pre class="whitespace-pre-wrap">' + escapeHtml(data.content) + '</pre>';
            logContainer.scrollTop = logContainer.scrollHeight;
        }
    } catch (error) {
        console.error('Error fetching logs:', error);
    }
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Poll every 2 seconds
setInterval(fetchLogs, 2000);
fetchLogs(); // Initial load
</script>
@endsection
