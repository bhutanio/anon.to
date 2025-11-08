<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Note Expired - anon.to</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-50 dark:bg-gray-900">
    <div class="min-h-full flex flex-col">
        {{-- Header --}}
        <header class="py-6 px-4 sm:px-6 lg:px-8 border-b border-gray-200 dark:border-gray-800">
            <div class="max-w-6xl mx-auto flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="/" class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">anon.to</h1>
                    </a>
                </div>

                <div class="flex items-center gap-4">
                    <a href="/notes/create" class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400">
                        Create Note
                    </a>
                    @auth
                        <a href="/dashboard" class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400">
                            Dashboard
                        </a>
                    @else
                        <a href="/login" class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400">
                            Sign In
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        {{-- Main Content --}}
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8">
            <div class="max-w-2xl mx-auto text-center py-16">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-yellow-100 dark:bg-yellow-900/30 rounded-full mb-6">
                    <svg class="w-10 h-10 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Note Expired</h2>
                <p class="text-lg text-gray-600 dark:text-gray-400 mb-2">
                    This note has expired or been deleted.
                </p>
                <p class="text-base text-gray-500 dark:text-gray-500 mb-8">
                    Expired notes are automatically deleted to protect your privacy.
                </p>
                <a href="/notes/create" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                    Create Your Own Note
                </a>
            </div>
        </main>

        {{-- Footer --}}
        <footer class="py-6 px-4 sm:px-6 lg:px-8 border-t border-gray-200 dark:border-gray-800">
            <div class="max-w-6xl mx-auto text-center text-sm text-gray-600 dark:text-gray-400">
                <p>&copy; {{ date('Y') }} anon.to. Secure code sharing with privacy-first features.</p>
            </div>
        </footer>
    </div>
</body>
</html>
