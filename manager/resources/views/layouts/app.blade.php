<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>bOOT</title>
    <link rel="stylesheet" href="{{ asset('css/manager.css') }}">
</head>
<body>
    <aside>
        <div class="brand">🚀 bOOT</div>
        <nav>
            <a href="{{ route('sites.index') }}" class="{{ request()->routeIs('sites.*') ? 'active' : '' }}">
                Projects
            </a>
            <a href="{{ route('databases.index') }}" class="{{ request()->routeIs('databases.*') ? 'active' : '' }}">
                Databases
            </a>
            <a href="{{ route('software.index') }}" class="{{ request()->routeIs('software.*') ? 'active' : '' }}">
                Software
            </a>
            <a href="{{ route('services.index') }}" class="{{ request()->routeIs('services.*') ? 'active' : '' }}">
                Services & Logs
            </a>
        </nav>
    </aside>

    <main>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-error">
                <ul style="list-style: none;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
