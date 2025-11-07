<?php

namespace App\Livewire;

use App\Actions\Links\CreateLink;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class Home extends Component
{
    public string $url = '';

    public ?string $shortUrl = null;

    public ?string $hash = null;

    public bool $copied = false;

    public ?string $errorMessage = null;

    /**
     * Create a shortened link with rate limiting.
     */
    public function createLink(CreateLink $createLink): void
    {
        // Reset state
        $this->shortUrl = null;
        $this->hash = null;
        $this->errorMessage = null;
        $this->copied = false;

        // Check rate limit
        $key = auth()->check()
            ? 'create-link:user:'.auth()->id()
            : 'create-link:ip:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, auth()->check() ? 100 : 20)) {
            $seconds = RateLimiter::availableIn($key);
            $this->errorMessage = "Too many requests. Please try again in {$seconds} seconds.";

            return;
        }

        // Validate URL
        $validated = $this->validate([
            'url' => [
                'required',
                'string',
                'url',
                'max:2048',
                'regex:/^https?:\/\//',
            ],
        ], [
            'url.required' => 'Please enter a URL to shorten.',
            'url.url' => 'Please enter a valid URL.',
            'url.max' => 'The URL is too long. Maximum length is 2048 characters.',
            'url.regex' => 'Only HTTP and HTTPS URLs are supported.',
        ]);

        try {
            // Create the link
            $link = $createLink->execute([
                'url' => $validated['url'],
                'user_id' => auth()->id(),
                'custom_slug' => null,
                'expires_at' => null,
            ]);

            // Hit the rate limiter
            RateLimiter::hit($key, 3600); // 1 hour

            // Set the short URL
            $this->hash = $link->hash ?? $link->slug;
            $this->shortUrl = url($this->hash);

            // Clear the input
            $this->url = '';
        } catch (\InvalidArgumentException $e) {
            $this->errorMessage = $e->getMessage();
        } catch (\Exception $e) {
            $this->errorMessage = 'An error occurred while creating the link. Please try again.';
        }
    }

    /**
     * Copy the short URL to clipboard (handled by JavaScript).
     */
    public function markAsCopied(): void
    {
        $this->copied = true;

        // Reset copied state after 2 seconds
        $this->dispatch('reset-copied');
    }

    public function render()
    {
        return view('livewire.home')->layout('components.layouts.guest');
    }
}
