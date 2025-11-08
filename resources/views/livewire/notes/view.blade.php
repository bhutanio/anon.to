<div class="min-h-full flex flex-col">
    {{-- Navigation --}}
    <x-navigation />

    {{-- Main Content --}}
    <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8">
        @if($isDeleted)
            {{-- 410 Gone - Note Deleted/Not Found --}}
            <div class="max-w-2xl mx-auto text-center py-16">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-red-100 dark:bg-red-900/30 rounded-full mb-6">
                    <svg class="w-10 h-10 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Note Not Found</h2>
                <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
                    This note has been deleted or does not exist. It may have reached its view limit or has been manually deleted.
                </p>
                <a href="/notes/create" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                    Create Your Own Note
                </a>
            </div>
        @elseif($isExpired)
            {{-- 410 Gone - Note Expired --}}
            <div class="max-w-2xl mx-auto text-center py-16">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-yellow-100 dark:bg-yellow-900/30 rounded-full mb-6">
                    <svg class="w-10 h-10 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Note Expired</h2>
                <p class="text-lg text-gray-600 dark:text-gray-400 mb-2">
                    This note expired on {{ $note->expires_at->format('M j, Y \a\t g:i A') }}.
                </p>
                <p class="text-base text-gray-500 dark:text-gray-500 mb-8">
                    Expired notes are automatically deleted to protect your privacy.
                </p>
                <a href="/notes/create" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                    Create Your Own Note
                </a>
            </div>
        @elseif($requiresPassword)
            {{-- Password Protection Overlay --}}
            <div class="max-w-md mx-auto py-16">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
                    <div class="text-center mb-6">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-100 dark:bg-indigo-900/30 rounded-full mb-4">
                            <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Password Protected</h2>
                        <p class="text-gray-600 dark:text-gray-400">This note requires a password to view</p>
                    </div>

                    <form wire:submit.prevent="verifyPassword" class="space-y-4">
                        <div>
                            <input
                                wire:model.defer="passwordInput"
                                type="password"
                                placeholder="Enter password"
                                autofocus
                                class="block w-full px-4 py-3 text-base border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition"
                            >
                            @if($passwordError)
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $passwordError }}</p>
                            @endif
                            @if($attemptsRemaining < 5)
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $attemptsRemaining }} {{ $attemptsRemaining === 1 ? 'attempt' : 'attempts' }} remaining
                                </p>
                            @endif
                        </div>

                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="w-full px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition"
                        >
                            <span wire:loading.remove wire:target="verifyPassword">Unlock Note</span>
                            <span wire:loading wire:target="verifyPassword">Verifying...</span>
                        </button>
                    </form>
                </div>
            </div>
        @else
            {{-- Note Content --}}
            <div class="max-w-6xl mx-auto">
                {{-- Metadata Header --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 sm:p-6 mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        {{-- Left Side: Title and Metadata --}}
                        <div class="flex-1">
                            @if($note->title)
                                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ $note->title }}</h2>
                            @endif
                            <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Created {{ $note->created_at->diffForHumans() }}
                                </span>

                                @if($note->expires_at)
                                    @php
                                        $hoursUntilExpiry = $note->expires_at->diffInHours(now());
                                        $isExpiringSoon = $hoursUntilExpiry < 24;
                                    @endphp
                                    <span class="flex items-center gap-1 {{ $isExpiringSoon ? 'text-yellow-600 dark:text-yellow-400' : '' }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Expires {{ $note->expires_at->diffForHumans() }}
                                    </span>
                                @else
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M infinity 8l4 4-4 4" />
                                        </svg>
                                        Never expires
                                    </span>
                                @endif

                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    {{ $note->views }} {{ $note->views === 1 ? 'view' : 'views' }}
                                </span>

                                @if($note->view_limit)
                                    @php
                                        $viewsRemaining = $note->view_limit - $note->views;
                                        $isLowViews = $viewsRemaining <= 5;
                                    @endphp
                                    <span class="flex items-center gap-1 {{ $isLowViews ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-yellow-600 dark:text-yellow-400' }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        {{ $viewsRemaining }} {{ $viewsRemaining === 1 ? 'view' : 'views' }} remaining
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Right Side: Badges --}}
                        <div class="flex flex-wrap items-center gap-2">
                            {{-- Password Protected Badge --}}
                            @if($note->password_hash && !$isOwner)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    Protected
                                </span>
                            @endif

                            {{-- Owner Badge --}}
                            @if($isOwner)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    You own this
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Burn After Reading Warning --}}
                @if($note->view_limit && ($note->view_limit - $note->views) <= 5)
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <p class="text-sm font-medium text-red-800 dark:text-red-300">
                                Warning: This note will be permanently deleted after {{ $note->view_limit - $note->views }} more {{ ($note->view_limit - $note->views) === 1 ? 'view' : 'views' }}.
                            </p>
                        </div>
                    </div>
                @endif

                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center gap-3 mb-6" x-data="{ copied: @entangle('copied') }">
                    <button
                        @click="navigator.clipboard.writeText(@js($note->content)).then(() => { copied = true; $wire.markAsCopied(); setTimeout(() => copied = false, 2000); })"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition"
                    >
                        <svg x-show="!copied" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <svg x-show="copied" x-cloak class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span x-show="!copied">Copy to Clipboard</span>
                        <span x-show="copied" x-cloak>Copied!</span>
                    </button>

                </div>

                {{-- Note Content --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                    <pre class="p-6 text-sm font-mono text-gray-900 dark:text-gray-100 whitespace-pre-wrap break-words overflow-x-auto">{{ $note->content }}</pre>
                </div>

                {{-- Note Info --}}
                <div class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
                    <p>{{ number_format($note->char_count) }} characters • {{ number_format($note->line_count) }} {{ $note->line_count === 1 ? 'line' : 'lines' }}</p>
                </div>
            </div>
        @endif
    </main>

    {{-- Footer --}}
    <footer class="py-6 px-4 sm:px-6 lg:px-8 border-t border-gray-200 dark:border-gray-800">
        <div class="max-w-6xl mx-auto text-center text-sm text-gray-600 dark:text-gray-400">
            <p>&copy; {{ date('Y') }} anon.to. Secure code sharing with privacy-first features.</p>
        </div>
    </footer>
</div>

{{-- Syntax highlighting disabled --}}
