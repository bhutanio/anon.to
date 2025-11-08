<?php

namespace App\Livewire;

use App\Actions\Links\CreateLink;
use App\Actions\Links\ValidateUrl;
use App\Livewire\Concerns\HasRateLimiting;
use App\Services\UrlService;
use Livewire\Component;

class Home extends Component
{
    use HasRateLimiting;

    public string $url = '';

    public ?string $shortUrl = null;

    public ?string $hash = null;

    public bool $copied = false;

    public ?string $errorMessage = null;

    public ?string $urlParam = null;

    public ?array $parsedUrl = null;

    /**
     * Mount the component and handle URL parameter if provided.
     */
    public function mount(
        ValidateUrl $validateUrl,
        UrlService $urlService
    ): void {
        // Check for URL parameter in query string
        // Support both formats: ?url=https://... and /?https://...
        $urlToAnonymize = request()->query('url');

        if (! $urlToAnonymize) {
            // Check if the raw query string is a URL (format: /?https://...)
            // We use the raw query string because Laravel converts dots to underscores in query keys
            $queryString = request()->getQueryString();
            if ($queryString) {
                // URL decode first, then check if it starts with http:// or https://
                $decodedQueryString = urldecode($queryString);
                // Remove trailing '=' if present (Laravel adds it during parsing)
                $decodedQueryString = rtrim($decodedQueryString, '=');

                if (str_starts_with($decodedQueryString, 'http://') || str_starts_with($decodedQueryString, 'https://')) {
                    $urlToAnonymize = $decodedQueryString;
                }
            }
        }

        if ($urlToAnonymize) {
            try {
                // Validate the URL
                $validateUrl->execute($urlToAnonymize);

                // Parse URL into components
                $this->parsedUrl = $urlService->parse($urlToAnonymize);

                // Set the URL parameter for display
                $this->urlParam = $urlToAnonymize;
            } catch (\InvalidArgumentException $e) {
                $this->errorMessage = $e->getMessage();
                $this->urlParam = $urlToAnonymize; // Still set it to show error state
            }
        }
    }

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

        // Check rate limit (with IP hashing for privacy)
        if ($this->checkRateLimit('create-link', 100, 20, true)) {
            $seconds = $this->getRateLimitSeconds('create-link', true);
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
            ]);

            // Hit the rate limiter (with IP hashing for privacy)
            $this->hitRateLimit('create-link', 3600, true);

            // Set the short URL
            $this->hash = $link->hash;
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
