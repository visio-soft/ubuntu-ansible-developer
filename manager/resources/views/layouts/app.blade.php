<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>bOOT</title>
    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'apple-blue': '#0071e3',
                        'apple-blue-hover': '#0077ed',
                        'apple-grey': '#86868b',
                        'apple-bg': '#f5f5f7',
                        'apple-border': 'rgba(0, 0, 0, 0.1)',
                    },
                    borderRadius: {
                        'apple-lg': '18px',
                        'apple-sm': '10px',
                    },
                    fontFamily: {
                        sans: ['-apple-system', 'BlinkMacSystemFont', 'Inter', 'Segoe UI', 'Roboto', 'Helvetica', 'Arial', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style type="text/tailwindcss">
        @layer components {
            .btn { @apply px-6 py-3 rounded-apple-sm font-semibold text-sm cursor-pointer transition-all bg-apple-blue text-white hover:bg-apple-blue-hover disabled:opacity-50; }
            .btn-secondary { @apply bg-[#e5e5ea] text-[#1d1d1f] hover:bg-[#d1d1d6]; }
            .card { @apply bg-white rounded-apple-lg p-6 shadow-[0_2px_8px_rgba(0,0,0,0.04)] border border-black/5 hover:shadow-[0_4px_16px_rgba(0,0,0,0.08)] hover:-translate-y-0.5 transition-all; }
            .badge { @apply px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider; }
            .badge-laravel { @apply bg-apple-blue text-white; }
            .badge-other { @apply bg-[#8e8e93] text-white; }
            .input-field { @apply bg-[#f5f5f7] border-none px-4 py-3 rounded-apple-sm text-sm w-full outline-none focus:bg-[#e5e5ea] transition-all; }
            .alert { @apply p-4 rounded-apple-sm text-sm font-medium mb-6; }
            .alert-success { @apply bg-green-100 text-green-700; }
            .alert-error { @apply bg-red-100 text-red-700; }
        }
        @layer base {
            body { @apply bg-apple-bg text-[#1d1d1f] antialiased min-h-screen flex; }
        }
    </style>
</head>
<body class="flex min-h-screen">
    <aside class="w-[250px] bg-white/80 backdrop-blur-xl p-8 flex flex-col border-r border-apple-border">
        <div class="text-xl font-semibold mb-10 flex items-center gap-2">🚀 bOOT</div>
        <nav class="space-y-1">
            @foreach([
                ['sites.index', 'Projects', 'sites.*'],
                ['databases.index', 'Databases', 'databases.*'],
                ['software.index', 'Software', 'software.*'],
                ['services.index', 'Services & Logs', 'services.*']
            ] as [$route, $label, $pattern])
            <a href="{{ route($route) }}" 
               class="block px-3 py-2.5 rounded-apple-sm font-medium text-[0.9rem] transition-all {{ request()->routeIs($pattern) ? 'bg-apple-blue text-white' : 'text-[#1d1d1f] hover:bg-black/5' }}">
                {{ $label }}
            </a>
            @endforeach
        </nav>
    </aside>

    <main class="flex-1 p-12 overflow-y-auto">
        <div class="max-w-6xl mx-auto">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-error">
                    <ul class="list-none">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</body>
</html>
