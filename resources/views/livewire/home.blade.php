<div class="min-h-full flex flex-col">
    {{-- Navigation --}}
    <x-navigation />

    {{-- Check if URL parameter exists --}}
    @if($urlParam)
        {{-- URL Parameter Mode: Display redirect warning --}}
        <div class="flex-1 flex items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
            <div class="max-w-2xl w-full">
                @if($errorMessage)
                    {{-- Error state for URL parameter --}}
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
                        <div class="flex justify-center mb-6">
                            <div class="rounded-full bg-red-100 dark:bg-red-900/20 p-4">
                                <svg class="w-12 h-12 text-red-600 dark:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <h1 class="text-3xl font-bold text-center text-gray-900 dark:text-gray-100 mb-2">Invalid URL</h1>
                        <p class="text-center text-gray-600 dark:text-gray-400 mb-6">
                            {{ $errorMessage }}
                        </p>
                        <div class="text-center">
                            <a href="/" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                                Go to Homepage
                            </a>
                        </div>
                    </div>
                @else
                    {{-- Valid URL: Display redirect warning --}}
                    @include('partials.redirect-warning', [
                        'destinationUrl' => $urlParam,
                        'parsed' => $parsedUrl,
                        'link' => null
                    ])
                @endif
            </div>
        </div>
    @else
        {{-- Normal Mode: Display link creation form --}}
        <main class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8 py-12">
            <div class="max-w-2xl w-full">
                {{-- Hero Section --}}
                <div class="text-center mb-12">
                    <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 dark:text-white mb-4">
                        Shorten URLs
                        <span class="text-indigo-600 dark:text-indigo-400">Anonymously</span>
                    </h2>
                    <p class="text-lg text-gray-600 dark:text-gray-400 max-w-xl mx-auto">
                        Create short links with privacy-focused redirect warnings. No tracking, no ads, just safe URL shortening.
                    </p>
                </div>

                {{-- Link Creation Form --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 mb-8">
                    <form wire:submit.prevent="createLink" class="space-y-6">
                        {{-- URL Input --}}
                        <div>
                            <label for="url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Paste your long URL
                            </label>
                            <div class="relative">
                                <input
                                    wire:model="url"
                                    type="url"
                                    id="url"
                                    placeholder="https://example.com/very/long/url/that/needs/shortening"
                                    class="block w-full px-4 py-4 text-base border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition"
                                >
                                <div wire:loading wire:target="createLink" class="absolute right-4 top-1/2 -translate-y-1/2">
                                    <svg class="animate-spin h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>

                            {{-- Error Messages --}}
                            @if($errorMessage)
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $errorMessage }}
                                </p>
                            @endif
                        </div>

                        {{-- Submit Button --}}
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="w-full flex items-center justify-center px-6 py-4 border border-transparent text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition shadow-lg hover:shadow-xl"
                        >
                            <span wire:loading.remove wire:target="createLink">Shorten URL</span>
                            <span wire:loading wire:target="createLink" class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Shortening...
                            </span>
                        </button>
                    </form>

                    {{-- Success Result --}}
                    @if($shortUrl)
                        <div class="mt-6 p-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg" x-data="{ copied: @entangle('copied') }">
                            <div class="flex items-center gap-2 mb-3">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3 class="text-sm font-semibold text-green-900 dark:text-green-100">Your short link is ready!</h3>
                            </div>

                            <div class="flex items-center gap-2">
                                <input
                                    type="text"
                                    readonly
                                    value="{{ $shortUrl }}"
                                    id="short-url"
                                    class="flex-1 px-4 py-3 text-base font-mono bg-white dark:bg-gray-800 border border-green-300 dark:border-green-700 rounded-lg text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-green-500"
                                >
                                <button
                                    type="button"
                                    @click="$clipboard.copy('{{ $shortUrl }}').then((success) => { if (success) { copied = true; $wire.markAsCopied(); setTimeout(() => copied = false, 2000); } })"
                                    class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition flex items-center gap-2 whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                >
                                    <svg x-show="!copied" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    <svg x-show="copied" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span x-show="!copied">Copy</span>
                                    <span x-show="copied" x-cloak>Copied!</span>
                                </button>
                            </div>

                            <div class="mt-4 flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                                <a href="/{{ $hash }}" target="_blank" class="flex items-center gap-1 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                    Preview redirect
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Features --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg mb-3">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Privacy First</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">No tracking or analytics on anonymous links</p>
                    </div>

                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg mb-3">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Safe Redirects</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Warning page shows destination before redirect</p>
                    </div>

                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg mb-3">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Lightning Fast</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Cached links load instantly</p>
                    </div>
                </div>
            </div>
        </main>

        {{-- Footer --}}
        <footer class="py-6 px-4 sm:px-6 lg:px-8 border-t border-gray-200 dark:border-gray-800">
            <div class="max-w-4xl mx-auto text-center text-sm text-gray-600 dark:text-gray-400">
                <p>&copy; {{ date('Y') }} anon.to. Anonymous URL shortening with privacy-focused redirects.</p>
            </div>
        </footer>
    @endif
</div>
