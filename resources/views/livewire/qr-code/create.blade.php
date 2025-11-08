<div class="min-h-full flex flex-col">
    {{-- Navigation --}}
    <x-navigation />

    {{-- Main Content --}}
    <main class="flex-1 px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-4xl mx-auto w-full">
            {{-- Hero Section --}}
            <div class="text-center mb-12">
                <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 dark:text-white mb-4">
                    Generate QR Codes
                    <span class="text-indigo-600 dark:text-indigo-400">Securely</span>
                </h2>
                <p class="text-lg text-gray-600 dark:text-gray-400 max-w-xl mx-auto">
                    Create QR codes from any text or URL. Download in PNG, SVG, or PDF format. Privacy-first, no data stored.
                </p>
            </div>

            {{-- QR Code Generation Form --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 mb-8">
                <form wire:submit.prevent="generateQrCode" class="space-y-6">
                    {{-- Content Input --}}
                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Content <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            wire:model.defer="content"
                            id="content"
                            rows="10"
                            placeholder="Enter text, URL, or any data to generate QR code..."
                            class="block w-full px-4 py-3 text-base border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition font-mono"
                            style="font-family: 'Fira Code', 'Monaco', 'Courier New', monospace;"
                        ></textarea>
                        <div class="mt-1 flex items-center justify-between">
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ mb_strlen($content) }} / 2,900 characters</p>
                            @error('content')
                                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
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

                    {{-- Generate Button --}}
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="w-full flex items-center justify-center px-6 py-4 border border-transparent text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition shadow-lg hover:shadow-xl"
                    >
                        <span wire:loading.remove wire:target="generateQrCode">Generate QR Code</span>
                        <span wire:loading wire:target="generateQrCode" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Generating...
                        </span>
                    </button>
                </form>

                {{-- QR Code Preview --}}
                @if($qrCodeDataUrl)
                    <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 text-center">Your QR Code</h3>

                        {{-- QR Code Image --}}
                        <div class="flex justify-center mb-6">
                            <img
                                src="{{ $qrCodeDataUrl }}"
                                alt="Generated QR Code"
                                class="rounded-lg shadow-lg"
                                style="max-width: 512px; width: 100%; height: auto;"
                            >
                        </div>

                        {{-- Download Buttons --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <button
                                wire:click="downloadPng"
                                wire:loading.attr="disabled"
                                class="flex items-center justify-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Download PNG
                            </button>

                            <button
                                wire:click="downloadSvg"
                                wire:loading.attr="disabled"
                                class="flex items-center justify-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Download SVG
                            </button>

                            <button
                                wire:click="downloadPdf"
                                wire:loading.attr="disabled"
                                class="flex items-center justify-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Download PDF
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Features --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg mb-3">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">No Storage</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Your data is never saved on our servers</p>
                </div>

                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg mb-3">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Multiple Formats</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Download in PNG, SVG, or PDF format</p>
                </div>

                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg mb-3">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Privacy First</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Generate QR codes completely private</p>
                </div>
            </div>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="py-6 px-4 sm:px-6 lg:px-8 border-t border-gray-200 dark:border-gray-800">
        <div class="max-w-4xl mx-auto text-center text-sm text-gray-600 dark:text-gray-400">
            <p>&copy; {{ date('Y') }} anon.to. Privacy-first QR code generation.</p>
        </div>
    </footer>
</div>
