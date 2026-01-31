@extends('layouts.app')

@section('content')
<div class="h-[calc(100vh-4rem)] flex flex-col" x-data="{ 
    selected: null, 
    search: '', 
    logs: [],
    loading: true,
    init() {
        const url = new URL(window.location.href);
        url.searchParams.set('json', '1');
        fetch(url.toString())
            .then(res => res.json())
            .then(data => {
                this.logs = data;
                this.loading = false;
            })
            .catch(err => {
                console.error(err);
                this.logs = [{raw: 'Error loading logs: ' + err}];
                this.loading = false;
            });
    },
    filteredLogs() {
        if (!this.search) return this.logs;
        return this.logs.filter(log => 
            (log.message && log.message.toLowerCase().includes(this.search.toLowerCase())) || 
            (log.level && log.level.toLowerCase().includes(this.search.toLowerCase()))
        );
    }
}">
    <header class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-[#1d1d1f]">{{ $title }} Logs</h1>
        <p class="text-xs text-apple-grey mt-1 font-mono">{{ $logPath }}</p>
    </header>

    <div x-show="loading" class="flex-1 flex items-center justify-center text-gray-500">
        <svg class="animate-spin h-5 w-5 mr-3" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        Loading logs...
    </div>

    <!-- Main Content -->
    <div x-show="!loading" class="flex-1 flex flex-col h-full overflow-hidden">
        @if($type === 'project')
        <!-- Herd Style Split View -->
        <template x-if="logs.length > 0 && !logs[0].raw">
            <div class="flex-1 flex flex-col border border-gray-200 rounded-lg overflow-hidden bg-white shadow-sm h-full">
                
                <!-- Table (Top) -->
                <div class="flex-1 overflow-auto bg-white border-b border-gray-200 min-h-0">
                    <table class="w-full text-left text-sm whitespace-nowrap min-w-full">
                        <thead class="bg-gray-50 sticky top-0 z-10 box-shadow-sm">
                            <tr>
                                <th class="px-4 py-2 font-medium text-gray-500 w-24 bg-gray-50">Level</th>
                                <th class="px-4 py-2 font-medium text-gray-500 w-48 bg-gray-50">Date</th>
                                <th class="px-4 py-2 font-medium text-gray-500 bg-gray-50">Message</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="(log, index) in filteredLogs()" :key="index">
                                <tr @click="selected = log" 
                                    class="cursor-pointer transition-colors"
                                    :class="selected === log ? 'bg-blue-50' : 'hover:bg-gray-50'">
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                                              :class="{
                                                  'bg-red-100 text-red-700': log.level === 'ERROR' || log.level === 'CRITICAL',
                                                  'bg-orange-100 text-orange-700': log.level === 'WARNING',
                                                  'bg-blue-100 text-blue-700': log.level === 'INFO',
                                                  'bg-gray-100 text-gray-700': log.level === 'DEBUG'
                                              }"
                                              x-text="log.level"></span>
                                    </td>
                                    <td class="px-4 py-2 text-gray-500 font-mono text-xs" x-text="log.date"></td>
                                    <td class="px-4 py-2 text-gray-800 truncate max-w-lg" x-text="log.message.substring(0, 100) + (log.message.length > 100 ? '...' : '')"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Detail Panel (Bottom) -->
                <div class="h-1/2 bg-gray-50 overflow-auto border-t border-gray-200 flex flex-col shrink-0" x-show="selected">
                    <div class="px-4 py-2 bg-white border-b border-gray-200 sticky top-0 shadow-sm">
                        <span class="font-semibold text-sm" x-text="selected ? selected.message : ''"></span>
                    </div>
                    <div class="p-4 font-mono text-xs text-gray-700 whitespace-pre-wrap selection:bg-blue-100" x-text="selected ? selected.stack_trace : ''"></div>
                </div>
                <div class="hidden items-center justify-center text-gray-400 text-sm flex-1" x-show="!selected">
                    Select a log entry to view details
                </div>
            </div>
        </template>
        @endif

        <!-- Raw/Fallback View -->
        <template x-if="logs.length > 0 && logs[0].raw">
             <div class="card p-0 overflow-hidden flex-1 flex flex-col bg-[#1e1e1e] text-gray-300 font-mono text-xs h-full">
                <div class="overflow-auto p-4 flex-1 whitespace-pre-wrap" x-text="logs[0].raw"></div>
            </div>
        </template>
        
        <template x-if="logs.length === 0">
             <div class="flex items-center justify-center h-full text-gray-500">No logs found.</div>
        </template>
    </div>
</div>
@endsection
