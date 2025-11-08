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
                    <flux:field>
                        <flux:label>Content <span class="text-red-500">*</span></flux:label>
                        <flux:textarea
                            wire:model.defer="content"
                            name="content"
                            rows="12"
                            placeholder="Paste your code or text here..."
                            class="font-mono"
                            style="font-family: 'Fira Code', 'Monaco', 'Courier New', monospace;"
                        />
                        <div class="mt-1 flex items-center justify-between">
                            <flux:description>{{ mb_strlen($content) }} characters</flux:description>
                            @error('content')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </div>
                    </flux:field>

                    {{-- Title Input --}}
                    <flux:field>
                        <flux:label>Title (Optional)</flux:label>
                        <flux:input
                            wire:model.defer="title"
                            type="text"
                            name="title"
                            placeholder="Optional title for your note"
                        />
                        @error('title')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>

                    {{-- Expiration and View Limit Row --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Expiration Select --}}
                        <flux:field>
                            <flux:label>Expires in</flux:label>
                            <flux:select
                                wire:model.defer="expires_at"
                                name="expires_at"
                            >
                                <option value="10-minutes">10 minutes</option>
                                <option value="1-hour">1 hour</option>
                                <option value="1-day">1 day</option>
                                <option value="1-week">1 week</option>
                                <option value="1-month" selected>1 month</option>
                                @auth
                                    <option value="never">Never</option>
                                @endauth
                            </flux:select>
                        </flux:field>

                        {{-- Burn After Reading --}}
                        <flux:field>
                            <flux:label>Burn after reading</flux:label>
                            <div class="flex items-center gap-3">
                                <flux:checkbox
                                    wire:model.live="enable_burn_after_reading"
                                    name="enable_burn_after_reading"
                                />
                                <flux:input
                                    wire:model.defer="view_limit"
                                    type="number"
                                    name="view_limit"
                                    placeholder="Views"
                                    min="1"
                                    max="100"
                                    :disabled="!$enable_burn_after_reading"
                                    class="flex-1"
                                />
                            </div>
                            @error('view_limit')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>
                    </div>

                    {{-- Password Protection --}}
                    <flux:fieldset>
                        <flux:legend>Password protection (Optional)</flux:legend>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <flux:field>
                                <flux:input
                                    wire:model.defer="password"
                                    type="password"
                                    name="password"
                                    placeholder="Password"
                                />
                                @error('password')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>
                            <flux:field>
                                <flux:input
                                    wire:model.defer="password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    placeholder="Confirm password"
                                />
                                @error('password_confirmation')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>
                        </div>
                    </flux:fieldset>

                    {{-- Error Messages --}}
                    @if($errorMessage)
                        <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                            <p class="text-sm text-red-600 dark:text-red-400 flex items-center gap-2">
                                <flux:icon.exclamation-circle variant="mini" />
                                {{ $errorMessage }}
                            </p>
                        </div>
                    @endif

                    {{-- Submit Button --}}
                    <flux:button
                        type="submit"
                        variant="primary"
                        class="w-full"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove wire:target="createNote">Create Note</span>
                        <span wire:loading wire:target="createNote" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Creating...
                        </span>
                    </flux:button>
                </form>

                {{-- Success Result --}}
                @if($shortUrl)
                    <div class="mt-6 p-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg" x-data="{ copied: @entangle('copied') }">
                        <div class="flex items-center gap-2 mb-3">
                            <flux:icon.check-circle class="w-5 h-5 text-green-600 dark:text-green-400" variant="solid" />
                            <h3 class="text-sm font-semibold text-green-900 dark:text-green-100">Your note is ready!</h3>
                        </div>

                        <div class="flex items-center gap-2">
                            <flux:input
                                type="text"
                                readonly
                                value="{{ $shortUrl }}"
                                id="note-url"
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
                            <flux:link href="/n/{{ $hash }}" target="_blank" icon="arrow-top-right-on-square">
                                View note
                            </flux:link>
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
