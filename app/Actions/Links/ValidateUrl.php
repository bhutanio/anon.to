<?php

declare(strict_types=1);

namespace App\Actions\Links;

use App\Services\UrlService;

class ValidateUrl
{
    public function __construct(
        protected UrlService $urlService
    ) {}

    /**
     * Validate a URL for link creation.
     *
     * Checks URL format, length, scheme (http/https only), and prevents SSRF attacks.
     *
     * @param  string  $url  The URL to validate
     *
     * @throws \InvalidArgumentException If URL is invalid, too long, uses wrong scheme, or points to internal IP
     */
    public function execute(string $url): void
    {
        // Check URL is not empty
        if (empty(trim($url))) {
            throw new \InvalidArgumentException('URL cannot be empty');
        }

        // Check URL length
        $maxLength = config('anon.max_url_length', 2048);
        if (strlen($url) > $maxLength) {
            throw new \InvalidArgumentException("URL cannot exceed {$maxLength} characters");
        }

        // Validate URL format
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid URL format');
        }

        // Parse URL to check components
        try {
            $parsed = $this->urlService->parse($url);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException('Invalid URL: '.$e->getMessage());
        }

        // Check scheme is http or https only
        if (! in_array($parsed['scheme'], ['http', 'https'])) {
            throw new \InvalidArgumentException('Only HTTP and HTTPS URLs are allowed');
        }

        // Prevent shortening already shortened URLs from this application (check this BEFORE internal URL check)
        if ($this->urlService->isAnonUrl($url)) {
            throw new \InvalidArgumentException('This URL is already shortened. You cannot shorten a URL from this application.');
        }

        // SSRF Prevention: Check for internal IPs
        if ($this->urlService->isInternalUrl($url)) {
            throw new \InvalidArgumentException('Internal or private IP addresses are not allowed');
        }
    }
}
