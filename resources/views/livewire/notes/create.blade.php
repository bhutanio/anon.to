<div class="min-h-full flex flex-col">
    {{-- Navigation --}}
    <x-navigation />

    {{-- Main Content --}}
    <main class="flex-1 px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-4xl mx-auto w-full">
            {{-- Hero Section --}}
            <div class="text-center mb-12">
                <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 dark:text-white mb-4">
                    Share Code
                    <span class="text-indigo-600 dark:text-indigo-400">Securely</span>
                </h2>
                <p class="text-lg text-gray-600 dark:text-gray-400 max-w-xl mx-auto">
                    Create password-protected notes. Burn-after-reading and expiration options available.
                </p>
            </div>

            {{-- Note Creation Form --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 mb-8">
                <form wire:submit.prevent="createNote" class="space-y-6">
                    {{-- Content Input --}}
                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Content <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            wire:model.defer="content"
                            id="content"
                            rows="12"
                            placeholder="Paste your code or text here..."
                            class="block w-full px-4 py-3 text-base border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition font-mono"
                            style="font-family: 'Fira Code', 'Monaco', 'Courier New', monospace;"
                        ></textarea>
                        <div class="mt-1 flex items-center justify-between">
                            <p class="char-count">{{ mb_strlen($content) }} characters</p>
                            @error('content')
                                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Title Input --}}
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Title (Optional)
                        </label>
                        <input
                            wire:model.defer="title"
                            type="text"
                            id="title"
                            placeholder="Optional title for your note"
                            class="block w-full px-4 py-3 text-base border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition"
                        >
                        @error('title')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Expiration and View Limit Row --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Expiration Select --}}
                        <div>
                            <label for="expires_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Expires in
                            </label>
                            <select
                                wire:model.defer="expires_at"
                                id="expires_at"
                                class="block w-full px-4 py-3 text-base border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition"
                            >
                                <option value="10-minutes">10 minutes</option>
                                <option value="1-hour">1 hour</option>
                                <option value="1-day">1 day</option>
                                <option value="1-week">1 week</option>
                                <option value="1-month" selected>1 month</option>
                                @auth
                                    <option value="never">Never</option>
                                @endauth
                            </select>
                        </div>

                        {{-- Burn After Reading --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Burn after reading
                            </label>
                            <div class="flex items-center gap-3">
                                <input
                                    wire:model.live="enable_burn_after_reading"
                                    type="checkbox"
                                    id="enable_burn_after_reading"
                                    class="w-5 h-5 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700"
                                >
                                <input
                                    wire:model.defer="view_limit"
                                    type="number"
                                    id="view_limit"
                                    placeholder="Views"
                                    min="1"
                                    max="100"
                                    :disabled="!$enable_burn_after_reading"
                                    class="flex-1 px-4 py-3 text-base border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                            </div>
                            @error('view_limit')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Password Protection --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Password protection (Optional)
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <input
                                    wire:model.defer="password"
                                    type="password"
                                    id="password"
                                    placeholder="Password"
                                    class="block w-full px-4 py-3 text-base border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition"
                                >
                                @error('password')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <input
                                    wire:model.defer="password_confirmation"
                                    type="password"
                                    id="password_confirmation"
                                    placeholder="Confirm password"
                                    class="block w-full px-4 py-3 text-base border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition"
                                >
                                @error('password_confirmation')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Error Messages --}}
                    @if($errorMessage)
                        <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                            <p class="text-sm text-red-600 dark:text-red-400 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $errorMessage }}
                            </p>
                        </div>
                    @endif

                    {{-- Submit Button --}}
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="w-full flex items-center justify-center px-6 py-4 border border-transparent text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition shadow-lg hover:shadow-xl"
                    >
                        <span wire:loading.remove wire:target="createNote">Create Note</span>
                        <span wire:loading wire:target="createNote" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Creating...
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
                            <h3 class="text-sm font-semibold text-green-900 dark:text-green-100">Your note is ready!</h3>
                        </div>

                        <div class="flex items-center gap-2">
                            <input
                                type="text"
                                readonly
                                value="{{ $shortUrl }}"
                                id="note-url"
                                class="flex-1 px-4 py-3 text-base font-mono bg-white dark:bg-gray-800 border border-green-300 dark:border-green-700 rounded-lg text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-green-500"
                            >
                            <button
                                type="button"
                                @click="navigator.clipboard.writeText('{{ $shortUrl }}').then(() => { copied = true; $wire.markAsCopied(); setTimeout(() => copied = false, 2000); })"
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
                            <a href="/n/{{ $hash }}" target="_blank" class="flex items-center gap-1 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                View note
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Features --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg mb-3">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Password Protected</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Secure your notes with password protection</p>
                </div>

                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg mb-3">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Self-Destructing</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Burn-after-reading and expiration options</p>
                </div>
            </div>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="py-6 px-4 sm:px-6 lg:px-8 border-t border-gray-200 dark:border-gray-800">
        <div class="max-w-4xl mx-auto text-center text-sm text-gray-600 dark:text-gray-400">
            <p>&copy; {{ date('Y') }} anon.to. Secure code sharing with privacy-first features.</p>
        </div>
    </footer>
</div>
