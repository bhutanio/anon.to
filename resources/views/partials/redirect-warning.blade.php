{{--
    Redirect Warning Partial

    Displays a warning page before redirecting to an external URL.
    Can be used for both saved links and direct URL anonymization.

    Required Props:
    - $destinationUrl (string): The full URL to redirect to
    - $parsed (array): Parsed URL components (scheme, host, port, path, query, fragment)

    Optional Props:
    - $link (Link|null): Link model instance (for saved links only)
--}}

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8"
     x-data="{
         domain: '{{ $parsed['host'] }}',
         destinationUrl: '{{ $destinationUrl }}',
         trustDomain: false,

         init() {
             // Check if domain is already trusted on page load
             if (this.isDomainTrusted()) {
                 // Auto-redirect immediately if domain is trusted
                 window.location.href = this.destinationUrl;
             }
         },

         isDomainTrusted() {
             try {
                 const trustedDomainsJson = localStorage.getItem('anon_trusted_domains');
                 if (!trustedDomainsJson) return false;

                 const trustedDomains = JSON.parse(trustedDomainsJson);
                 if (!Array.isArray(trustedDomains)) return false;

                 // Exact domain matching only
                 return trustedDomains.includes(this.domain);
             } catch (e) {
                 // Handle localStorage disabled or JSON parse errors
                 console.error('Error checking trusted domains:', e);
                 return false;
             }
         },

         saveTrustedDomain() {
             if (!this.trustDomain) return;

             try {
                 // Read existing trusted domains
                 const trustedDomainsJson = localStorage.getItem('anon_trusted_domains');
                 let trustedDomains = [];

                 if (trustedDomainsJson) {
                     trustedDomains = JSON.parse(trustedDomainsJson);
                     if (!Array.isArray(trustedDomains)) {
                         trustedDomains = [];
                     }
                 }

                 // Add domain if not already present (exact match only)
                 if (!trustedDomains.includes(this.domain)) {
                     trustedDomains.push(this.domain);
                     localStorage.setItem('anon_trusted_domains', JSON.stringify(trustedDomains));
                 }
             } catch (e) {
                 // Handle localStorage disabled or JSON errors gracefully
                 console.error('Error saving trusted domain:', e);
             }
         }
     }"
     x-init="init()">

    {{-- Warning Icon --}}
    <div class="flex justify-center mb-6">
        <div class="rounded-full bg-yellow-100 dark:bg-yellow-900/20 p-4">
            <svg class="w-12 h-12 text-yellow-600 dark:text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
    </div>

    {{-- Heading --}}
    <h1 class="text-3xl font-bold text-center text-gray-900 dark:text-gray-100 mb-2">You are leaving anon.to</h1>
    <p class="text-center text-gray-600 dark:text-gray-400 mb-8">
        This link will take you to an external website. Please review the destination before continuing.
    </p>

    {{-- Destination URL Display --}}
    <div class="mb-8">
        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Destination</h2>

        <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-4 break-all font-mono text-sm">
            <div class="flex items-start gap-2">
                @if($parsed['scheme'] === 'https')
                    <svg class="w-5 h-5 text-green-600 dark:text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                @else
                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                @endif
                <span class="text-gray-900 dark:text-gray-100">{{ $destinationUrl }}</span>
            </div>
        </div>

        {{-- URL Components --}}
        <div class="mt-4 space-y-2 text-sm">
            <div class="flex gap-2">
                <span class="text-gray-500 dark:text-gray-400 w-20">Protocol:</span>
                <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $parsed['scheme'] }}</span>
                @if($parsed['scheme'] === 'https')
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">Secure</span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400">Not Secure</span>
                @endif
            </div>

            <div class="flex gap-2">
                <span class="text-gray-500 dark:text-gray-400 w-20">Domain:</span>
                <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $parsed['host'] }}</span>
            </div>

            @if($parsed['port'])
                <div class="flex gap-2">
                    <span class="text-gray-500 dark:text-gray-400 w-20">Port:</span>
                    <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $parsed['port'] }}</span>
                </div>
            @endif

            @if($parsed['path'] && $parsed['path'] !== '/')
                <div class="flex gap-2">
                    <span class="text-gray-500 dark:text-gray-400 w-20">Path:</span>
                    <span class="text-gray-900 dark:text-gray-100 font-medium break-all">{{ $parsed['path'] }}</span>
                </div>
            @endif

            @if($parsed['query'])
                <div class="flex gap-2">
                    <span class="text-gray-500 dark:text-gray-400 w-20">Query:</span>
                    <span class="text-gray-900 dark:text-gray-100 font-medium break-all">{{ $parsed['query'] }}</span>
                </div>
            @endif

            @if($parsed['fragment'])
                <div class="flex gap-2">
                    <span class="text-gray-500 dark:text-gray-400 w-20">Fragment:</span>
                    <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $parsed['fragment'] }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Security Warning --}}
    @if($parsed['scheme'] !== 'https')
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="text-sm text-yellow-800 dark:text-yellow-300">
                    <strong>Warning:</strong> This link uses an insecure connection (HTTP). Your data may be visible to others.
                </div>
            </div>
        </div>
    @endif

    {{-- Domain Trust Checkbox --}}
    <div class="mb-6">
        <label class="flex items-start gap-3 cursor-pointer group">
            <input
                type="checkbox"
                x-model="trustDomain"
                class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 rounded cursor-pointer"
            >
            <span class="text-sm text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-300 transition">
                Don't warn me about <span class="font-medium text-gray-700 dark:text-gray-300">{{ $parsed['host'] }}</span> in the future
            </span>
        </label>
    </div>

    {{-- Link Stats (Only for saved links) --}}
    @if(isset($link) && $link)
        <div class="flex items-center justify-center gap-6 mb-8 text-sm text-gray-600 dark:text-gray-400">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span>{{ number_format($link->visits) }} visit{{ $link->visits === 1 ? '' : 's' }}</span>
            </div>

            @if($link->created_at)
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Created {{ $link->created_at->diffForHumans() }}</span>
                </div>
            @endif
        </div>
    @endif

    {{-- Action Buttons --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <flux:button
            href="{{ $destinationUrl }}"
            @click="saveTrustedDomain()"
            variant="primary"
            icon-trailing="arrow-right"
            class="flex-1">
            Continue to Site
        </flux:button>

        <flux:button href="/" variant="ghost">
            Go Back
        </flux:button>
    </div>

    {{-- Footer Info --}}
    <div class="mt-6 text-center">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            External links are provided for convenience. anon.to is not responsible for the content of external sites.
        </p>
    </div>
</div>
