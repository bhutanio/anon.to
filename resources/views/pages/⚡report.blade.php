<?php

use App\Models\Link;
use App\Models\Report;
use App\Rules\Turnstile;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.public')] #[Title('Report a Link')] class extends Component {
    public string $linkUrl = '';
    public string $email = '';
    public string $comment = '';
    public string $captchaToken = '';
    public bool $submitted = false;

    public function mount(): void
    {
        if (! config('services.turnstile.site_key')) {
            $this->captchaToken = 'no-turnstile';
        }
    }

    /**
     * Submit an abuse report.
     */
    public function submitReport(): void
    {
        $key = 'report:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('linkUrl', __('Too many reports. Please try again later.'));

            return;
        }

        RateLimiter::hit($key, 3600);

        $this->validate([
            'linkUrl' => ['required', 'url:http,https'],
            'email' => ['required', 'email'],
            'comment' => ['required', 'string', 'max:1000'],
            'captchaToken' => ['required', new Turnstile],
        ], [
            'captchaToken.required' => __('Security verification failed. Please refresh the page and try again.'),
        ]);

        $link = $this->findLink();

        if (! $link) {
            $this->addError('linkUrl', __('We could not find a link matching this URL.'));
            return;
        }

        if (Report::where('link_id', $link->id)->exists()) {
            $this->addError('linkUrl', __('This link has already been reported.'));
            return;
        }

        Report::create([
            'link_id' => $link->id,
            'email' => $this->email,
            'comment' => $this->comment,
        ]);

        $this->submitted = true;
    }

    /**
     * Look up a link by short URL hash or destination URL fingerprint.
     */
    protected function findLink(): ?Link
    {
        $parsed = parse_url($this->linkUrl);

        if ($parsed === false) {
            return null;
        }

        $appHost = parse_url(config('app.url'), PHP_URL_HOST);

        if (isset($parsed['host']) && strtolower($parsed['host']) === strtolower($appHost ?? '')) {
            $path = trim($parsed['path'] ?? '', '/');
            if (preg_match('/^[A-Za-z0-9]{6}$/', $path)) {
                return Link::where('hash', $path)->first();
            }
        }

        try {
            $components = Link::decomposeUrl($this->linkUrl);
        } catch (\InvalidArgumentException) {
            return null;
        }

        $fingerprint = Link::computeFingerprint($components);

        return Link::where('url_fingerprint', $fingerprint)->first();
    }
}; ?>

<div class="mx-auto max-w-lg py-8 animate-fade-in">
    <h1 class="font-heading font-bold text-2xl text-zinc-900 dark:text-zinc-100 mb-2">{{ __('Report a Link') }}</h1>
    <p class="text-zinc-500 dark:text-zinc-400 text-sm mb-6">{{ __('Use this form to report a link for abuse. Provide the shortened or destination URL.') }}</p>

    @if ($submitted)
        <div class="rounded-xl border border-emerald-500/40 bg-emerald-50 dark:bg-emerald-500/10 backdrop-blur-sm p-6 glow-border">
            <div class="flex items-start gap-3">
                <svg class="size-6 text-emerald-400 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                </svg>
                <div>
                    <h3 class="font-heading font-bold text-zinc-900 dark:text-zinc-100 mb-1">{{ __('Report Submitted') }}</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Thank you for your report. An administrator will review it shortly.') }}</p>
                </div>
            </div>
        </div>
    @else
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700/50 bg-white dark:bg-zinc-900/50 backdrop-blur-sm p-6">
            <form wire:submit="submitReport" class="space-y-4">
                <flux:input
                    wire:model="linkUrl"
                    :label="__('Link URL')"
                    type="url"
                    placeholder="https://..."
                    required
                    :description="__('Enter the shortened URL or the destination URL.')"
                />
                @error('linkUrl')
                    <p class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</p>
                @enderror

                <flux:input
                    wire:model="email"
                    :label="__('Your Email')"
                    type="email"
                    placeholder="you@example.com"
                    required
                />

                <flux:textarea
                    wire:model="comment"
                    :label="__('Comment')"
                    placeholder="{{ __('Describe why this link should be reviewed...') }}"
                    rows="4"
                    required
                />

                <x-turnstile />

                @error('captchaToken')
                    <p class="text-red-600 dark:text-red-400 text-sm mb-2">{{ $message }}</p>
                @enderror

                <flux:button variant="primary" type="submit" class="w-full">
                    {{ __('Submit Report') }}
                </flux:button>
            </form>
        </div>
    @endif
</div>
