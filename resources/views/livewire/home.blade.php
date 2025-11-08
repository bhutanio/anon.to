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
                            <flux:button href="/" variant="primary">
                                Go to Homepage
                            </flux:button>
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
                        <flux:field>
                            <flux:label>Paste your long URL</flux:label>
                            <flux:input
                                wire:model="url"
                                type="url"
                                name="url"
                                placeholder="https://example.com/very/long/url/that/needs/shortening"
                            />
                            @if($errorMessage)
                                <flux:error>{{ $errorMessage }}</flux:error>
                            @endif
                        </flux:field>

                        {{-- Submit Button --}}
                        <flux:button
                            type="submit"
                            variant="primary"
                            class="w-full"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="createLink">Shorten URL</span>
                            <span wire:loading wire:target="createLink" class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Shortening...
                            </span>
                        </flux:button>
                    </form>

                    {{-- Success Result --}}
                    @if($shortUrl)
                        <div class="mt-6 p-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg" x-data="{ copied: @entangle('copied') }">
                            <div class="flex items-center gap-2 mb-3">
                                <flux:icon.check-circle class="w-5 h-5 text-green-600 dark:text-green-400" variant="solid" />
                                <h3 class="text-sm font-semibold text-green-900 dark:text-green-100">Your short link is ready!</h3>
                            </div>

                            <div class="flex items-center gap-2">
                                <flux:input
                                    type="text"
                                    readonly
                                    value="{{ $shortUrl }}"
                                    id="short-url"
                                    class="flex-1 font-mono"
                                />
                                <flux:button
                                    type="button"
                                    variant="primary"
                                    @click="$clipboard.copy('{{ $shortUrl }}').then((success) => { if (success) { copied = true; $wire.markAsCopied(); setTimeout(() => copied = false, 2000); } })"
                                >
                                    <flux:icon.clipboard x-show="!copied" variant="mini" />
                                    <flux:icon.check x-show="copied" x-cloak variant="mini" />
                                    <span x-show="!copied">Copy</span>
                                    <span x-show="copied" x-cloak>Copied!</span>
                                </flux:button>
                            </div>

                            <div class="mt-4 flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                                <flux:link href="/{{ $hash }}" target="_blank" icon="arrow-top-right-on-square">
                                    Preview redirect
                                </flux:link>
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
