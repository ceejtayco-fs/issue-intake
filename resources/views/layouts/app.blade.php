<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Issue Intake')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased text-slate-900">
    <div class="min-h-full flex flex-col">
        <header class="bg-white border-b border-slate-200">
            <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
                <a href="{{ route('issues.index') }}" class="flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-indigo-600 text-white text-sm font-bold">II</span>
                    <span class="font-semibold">Issue Intake</span>
                </a>
                <nav class="flex items-center gap-4 text-sm">
                    <a href="{{ route('issues.index') }}" class="text-slate-600 hover:text-slate-900">All issues</a>
                    <a href="{{ route('issues.index', ['trashed' => 'only']) }}" class="text-slate-600 hover:text-slate-900">Trash</a>
                    <a href="{{ route('issues.create') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-white hover:bg-indigo-500">
                        New issue
                    </a>
                </nav>
            </div>
        </header>

        @if (session('flash'))
            @php $flash = session('flash'); @endphp
            <div class="bg-{{ $flash['type'] === 'success' ? 'emerald' : 'red' }}-50 border-b border-{{ $flash['type'] === 'success' ? 'emerald' : 'red' }}-200">
                <div class="max-w-6xl mx-auto px-6 py-2 text-sm text-{{ $flash['type'] === 'success' ? 'emerald' : 'red' }}-800">
                    {{ $flash['message'] }}
                </div>
            </div>
        @endif

        <main class="flex-1">
            <div class="max-w-6xl mx-auto px-6 py-8">
                @yield('content')
            </div>
        </main>

        <footer class="border-t border-slate-200 py-4">
            <div class="max-w-6xl mx-auto px-6 text-xs text-slate-500">
                Issue Intake &mdash; Laravel {{ app()->version() }}
            </div>
        </footer>
    </div>
</body>
</html>
