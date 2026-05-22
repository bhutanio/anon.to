<?php

use App\Models\Domain;
use App\Models\Link;
use App\Rules\Turnstile;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.public')] #[Title('Anonymous URL Shortener and Redirect')] class extends Component {
    public string $url = '';
    public string $shortUrl = '';
    public string $captchaToken = '';

    public function mount(): void
    {
        if (! config('services.turnstile.site_key')) {
            $this->captchaToken = 'no-turnstile';
        }
    }

    /**
     * Shorten a URL.
     */
    public function shorten(): void
    {
        $key = 'shorten:' . (auth()->id() ?: request()->ip());

        if (RateLimiter::tooManyAttempts($key, 20)) {
            $this->addError('url', __('Too many requests. Please try again in a moment.'));
            return;
        }

        RateLimiter::hit($key, 60);

        $this->validate([
            'url' => ['required', 'url:http,https', 'max:2048'],
            'captchaToken' => ['required', new Turnstile],
        ], [
            'captchaToken.required' => __('Security verification failed. Please refresh the page and try again.'),
        ]);

        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $inputHost = parse_url($this->url, PHP_URL_HOST);

        if ($appHost && strtolower($appHost) === strtolower($inputHost ?? '')) {
            $this->addError('url', __('You cannot shorten URLs from this domain.'));
            return;
        }

        if ($inputHost && Domain::isAllowed($inputHost) === false) {
            $this->addError('url', __('This domain has been blocked.'));
            return;
        }

        $link = Link::findOrCreateByUrl($this->url, auth()->id());

        $this->shortUrl = url('/' . $link->hash);
        $this->captchaToken = config('services.turnstile.site_key') ? '' : 'no-turnstile';
        $this->dispatch('turnstile-reset');
    }
}; ?>

<div class="mx-auto max-w-2xl py-12 text-center stagger-children">
    {{-- Heading --}}
    <h1 class="font-heading font-bold text-4xl sm:text-5xl tracking-tight text-zinc-900 dark:text-zinc-100 mb-3">{{ __('Anonymous URL Shortener and Redirect') }}</h1>
    <p class="text-zinc-500 dark:text-zinc-400 text-lg mb-10">{{ __('Shorten or redirect any URL anonymously.') }}
        <br>{{ __('Your referrer is hidden and nothing is logged.') }}</p>

    {{-- URL Input --}}
    <form wire:submit="shorten" class="space-y-4">
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700/50 bg-white dark:bg-zinc-900/50 backdrop-blur-sm p-3 transition-all duration-150 focus-within:border-emerald-500/50 focus-within:glow-border">
            <div class="flex gap-2">
                <div class="flex-1">
                    <flux:input
                        wire:model="url"
                        type="url"
                        placeholder="https://example.com/long-url"
                        required
                    />
                </div>
                <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="shorten">{{ __('Shorten') }}</span>
                    <span wire:loading wire:target="shorten">{{ __('Shortening...') }}</span>
                </flux:button>
            </div>
        </div>

        @error('url')
            <p class="text-red-600 dark:text-red-400 text-sm text-start">{{ $message }}</p>
        @enderror

        @error('captchaToken')
            <p class="text-red-600 dark:text-red-400 text-sm text-start">{{ $message }}</p>
        @enderror

        <x-turnstile reset-event="turnstile-reset" />
    </form>

    {{-- Result --}}
    @if ($shortUrl)
        <div class="mt-6 rounded-xl border border-emerald-500/40 bg-emerald-50 dark:bg-zinc-900/50 backdrop-blur-sm p-4 glow-border animate-fade-in-up">
            <p class="mb-2 text-sm font-medium text-zinc-600 dark:text-zinc-300">{{ __('Your shortened URL:') }}</p>
            <div class="flex gap-2">
                <flux:input
                    type="text"
                    :value="$shortUrl"
                    readonly
                    class="!font-mono-accent !text-emerald-600 dark:!text-emerald-400"
                />
                <flux:button
                    variant="primary"
                    x-on:click="
                        let btn = $el;
                        let url = $wire.shortUrl;
                        let done = () => { btn.textContent = '{{ __('Copied!') }}'; setTimeout(() => btn.textContent = '{{ __('Copy') }}', 2000) };
                        if (navigator.clipboard) {
                            navigator.clipboard.writeText(url).then(done).catch(() => done());
                        } else {
                            let t = document.createElement('textarea'); t.value = url; document.body.appendChild(t); t.select(); document.execCommand('copy'); document.body.removeChild(t); done();
                        }
                    "
                >
                    {{ __('Copy') }}
                </flux:button>
            </div>
        </div>
    @endif

    {{-- Direct Redirect --}}
    <div class="mt-8 rounded-xl border border-zinc-200 dark:border-zinc-700/50 bg-white dark:bg-zinc-900/50 backdrop-blur-sm text-start overflow-hidden"
         x-data="{
             targetUrl: '',
             copied: false,
             get redirectUrl() { return this.targetUrl ? '{{ url('/') }}/?' + this.targetUrl : '' },
             copy() {
                 if (!this.redirectUrl) return;
                 if (navigator.clipboard) {
                     navigator.clipboard.writeText(this.redirectUrl).then(() => { this.copied = true; setTimeout(() => this.copied = false, 2000) });
                 } else {
                     let t = document.createElement('textarea'); t.value = this.redirectUrl; document.body.appendChild(t); t.select(); document.execCommand('copy'); document.body.removeChild(t);
                     this.copied = true; setTimeout(() => this.copied = false, 2000);
                 }
             }
         }">
        <div class="flex items-start gap-3.5 p-5 sm:p-6">
            <div class="inline-flex items-center justify-center rounded-lg bg-emerald-500/10 p-2.5 mt-0.5 shrink-0">
                <svg class="size-5 text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
            </div>
            <div class="min-w-0">
                <h2 class="font-heading font-bold text-zinc-900 dark:text-zinc-100 mb-0.5">{{ __('Direct Redirect') }}</h2>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Redirect any URL anonymously — no short link created, nothing stored.') }}</p>
            </div>
        </div>

        <div class="px-5 sm:px-6 pb-5 sm:pb-6 space-y-3">
            {{-- Live URL preview --}}
            <div class="rounded-lg bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-100 dark:border-zinc-800/60 px-4 py-3">
                <p class="font-mono-accent text-sm leading-relaxed break-all">
                    <span class="text-zinc-200 dark:text-zinc-400 select-all">{{ url('/') }}/?</span><span class="text-emerald-600 dark:text-emerald-400 transition-opacity duration-150" x-text="targetUrl || 'https://example.com'" :class="targetUrl ? 'opacity-100' : 'opacity-60'"></span>
                </p>
            </div>

            {{-- Input + actions --}}
            <div class="flex gap-2">
                <input
                    x-model="targetUrl"
                    type="url"
                    placeholder="{{ __('Paste a URL to redirect anonymously...') }}"
                    class="flex-1 min-w-0 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/50 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-950 transition-shadow duration-150"
                />
                <button
                    x-on:click="copy()"
                    x-bind:disabled="!targetUrl"
                    class="shrink-0 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 px-3.5 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 transition-all duration-150 hover:bg-zinc-100 dark:hover:bg-zinc-700/50 disabled:opacity-30 disabled:pointer-events-none"
                >
                    <span x-show="!copied">{{ __('Copy') }}</span>
                    <span x-cloak x-show="copied" class="text-emerald-600 dark:text-emerald-400">{{ __('Copied!') }}</span>
                </button>
                <a
                    x-bind:href="redirectUrl"
                    x-show="targetUrl"
                    x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    rel="noreferrer nofollow"
                    referrerpolicy="no-referrer"
                    class="group shrink-0 inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-3.5 py-2 text-sm font-medium text-zinc-950 transition-all duration-150 hover:bg-emerald-400"
                >
                    {{ __('Go') }}
                    <svg class="size-3.5 transition-transform duration-150 group-hover:translate-x-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    {{-- Feature Cards --}}
    <div class="mt-12 grid gap-6 sm:grid-cols-3">
        <div class="group rounded-xl border border-zinc-200 dark:border-zinc-700/50 bg-white dark:bg-zinc-900/50 backdrop-blur-sm p-6 text-start transition-all duration-150 hover:border-emerald-500/40">
            <div class="mb-3 inline-flex items-center justify-center rounded-lg bg-emerald-500/10 p-2">
                <svg class="size-5 text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
            </div>
            <h3 class="font-heading font-bold text-zinc-900 dark:text-zinc-100 mb-1">{{ __('SSL Secured') }}</h3>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('All redirects happen over encrypted HTTPS connections.') }}</p>
        </div>
        <div class="group rounded-xl border border-zinc-200 dark:border-zinc-700/50 bg-white dark:bg-zinc-900/50 backdrop-blur-sm p-6 text-start transition-all duration-150 hover:border-emerald-500/40">
            <div class="mb-3 inline-flex items-center justify-center rounded-lg bg-emerald-500/10 p-2">
                <svg class="size-5 text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                </svg>
            </div>
            <h3 class="font-heading font-bold text-zinc-900 dark:text-zinc-100 mb-1">{{ __('No Logs') }}</h3>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('We don\'t track clicks, visitors, or any personal data.') }}</p>
        </div>
        <div class="group rounded-xl border border-zinc-200 dark:border-zinc-700/50 bg-white dark:bg-zinc-900/50 backdrop-blur-sm p-6 text-start transition-all duration-150 hover:border-emerald-500/40">
            <div class="mb-3 inline-flex items-center justify-center rounded-lg bg-emerald-500/10 p-2">
                <svg class="size-5 text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                </svg>
            </div>
            <h3 class="font-heading font-bold text-zinc-900 dark:text-zinc-100 mb-1">{{ __('Referrer Hiding') }}</h3>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Destination sites never see where you came from.') }}</p>
        </div>
    </div>
</div>
