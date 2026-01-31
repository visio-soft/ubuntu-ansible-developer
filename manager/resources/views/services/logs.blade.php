@extends('layouts.app')

@section('content')
<header class="flex justify-between items-center mb-10">
    <h1 class="text-3xl font-bold tracking-tight">Logs: {{ $title }}</h1>
    <div class="flex gap-4">
        <input type="text" id="searchInput" placeholder="Search logs..." 
               class="px-4 py-2 rounded-apple-sm border border-apple-border text-sm w-[300px] outline-none focus:ring-1 focus:ring-apple-blue/20 transition-all">
        <a href="{{ route('services.index') }}" class="btn-secondary !px-4 !py-2 !text-sm flex items-center">Back</a>
    </div>
</header>

<div class="card p-0 overflow-hidden flex flex-col h-[75vh]">
    <div class="px-4 py-2 border-b border-apple-border bg-gray-50/50 text-[10px] font-bold text-apple-grey uppercase tracking-widest flex justify-between items-center">
        <span>Source: {{ $logPath }}</span>
        <span id="statusIndicator" class="flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
            Live
        </span>
    </div>
    
    <div id="logContainer" class="flex-1 overflow-y-auto p-4 bg-[#1e1e1e] color-[#d4d4d4] font-mono text-[13px] leading-relaxed">
        <!-- Logs injected here -->
    </div>
</div>

<script>
    const logContainer = document.getElementById('logContainer');
    const searchInput = document.getElementById('searchInput');
    let allLines = [];
    let isAutoScroll = true;

    logContainer.addEventListener('scroll', () => {
        const threshold = 50;
        const position = logContainer.scrollTop + logContainer.offsetHeight;
        const height = logContainer.scrollHeight;
        isAutoScroll = position > height - threshold;
    });

    async function fetchLogs() {
        try {
            const url = new URL(window.location.href);
            url.searchParams.set('json', '1');
            
            const res = await fetch(url);
            const data = await res.json();
            
            if (data.content) {
                const lines = data.content.split('\n');
                if (JSON.stringify(lines) !== JSON.stringify(allLines)) {
                    allLines = lines;
                    renderLogs();
                }
            }
        } catch (e) { console.error("Fetch error", e); }
    }

    function renderLogs() {
        const filter = searchInput.value.toLowerCase();
        logContainer.innerHTML = '';
        
        allLines.forEach(line => {
            if (!line) return;
            if (filter && !line.toLowerCase().includes(filter)) return;

            const div = document.createElement('div');
            div.className = 'mb-0.5 break-all whitespace-pre-wrap text-gray-300';

            let html = escapeHtml(line);
            
            html = html.replace(/(\.ERROR)/g, '<span class="text-red-400 font-bold">$1</span>');
            html = html.replace(/(\.WARNING)/g, '<span class="text-yellow-400 font-bold">$1</span>');
            html = html.replace(/(\.INFO)/g, '<span class="text-green-400 font-bold">$1</span>');

            const fileRegex = /(\/var\/www\/projects\/[a-zA-Z0-9_\-\/]+\.php):(\d+)/g;
            html = html.replace(fileRegex, '<a href="vscode://file$1:$2" class="text-blue-400 underline hover:text-blue-300 cursor-pointer">$1:$2</a>');

            div.innerHTML = html;
            logContainer.appendChild(div);
        });

        if (isAutoScroll) {
            logContainer.scrollTop = logContainer.scrollHeight;
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    searchInput.addEventListener('input', renderLogs);
    fetchLogs();
    setInterval(fetchLogs, 2000);
</script>
@endsection
