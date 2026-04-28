<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - PT Djavva Network Solution</title>
    
    <!-- Tailwind CDN with Class Dark Mode -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
    
    <!-- Global Styles -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    
    <!-- jQuery & DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    @vite(['resources/js/app.js'])

    @stack('styles')
</head>
<body class="bg-[var(--bg-page)] text-[var(--text-main)] antialiased h-screen flex flex-col overflow-hidden transition-colors" style="font-family:system-ui,sans-serif">

    <!-- Header Partial -->
    @include('admin.partials.header')

    <div class="flex flex-1 overflow-hidden">
        <!-- Sidebar Partial -->
        @include('admin.partials.sidebar')

        <!-- Main Content Section -->
        <main class="flex-1 overflow-auto p-2 flex flex-col gap-2">
            <!-- Global Alerts -->
            @if(session('success'))
                <div class="bg-green-100 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-400 px-3 py-2 rounded-sm text-xs font-bold flex items-center justify-between mb-1 animate-pulse-once">
                    <span>{{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-green-600 dark:text-green-400 hover:text-green-800">&times;</button>
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-400 px-3 py-2 rounded-sm text-xs font-bold flex items-center justify-between mb-1">
                    <span>{{ session('error') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-red-600 dark:text-red-400 hover:text-red-800">&times;</button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Footer Partial -->
    @include('admin.partials.footer')

    @stack('scripts')
</body>
</html>
