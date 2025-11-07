<?php

declare(strict_types=1);

namespace App\Actions\Links;

use App\Models\Link;
use App\Services\UrlService;
use Illuminate\Support\Facades\Cache;

class CreateLink
{
    public function __construct(
        protected UrlService $urlService,
        protected ValidateUrl $validateUrl,
        protected GenerateHash $generateHash,
        protected CheckDuplicate $checkDuplicate,
    ) {}

    /**
     * Create a new shortened link.
     *
     * @param  array{url: string, user_id: int|null, custom_slug: string|null, expires_at: string|null}  $data
     *
     * @throws \InvalidArgumentException If validation fails
     * @throws \RuntimeException If hash generation fails
     */
    public function execute(array $data): Link
    {
        $url = $data['url'];
        $userId = $data['user_id'] ?? null;
        $customSlug = $data['custom_slug'] ?? null;
        $expiresAt = $data['expires_at'] ?? null;

        // Step 1: Validate URL
        $this->validateUrl->execute($url);

        // Step 2: Check for duplicate
        $existingLink = $this->checkDuplicate->execute($url);
        if ($existingLink) {
            // Return existing link instead of creating duplicate
            return $existingLink;
        }

        // Step 3: Parse URL into components
        $parsed = $this->urlService->parse($url);

        // Step 4: Generate unique hash or validate custom slug
        $hash = $this->generateHash->execute($customSlug);

        // Step 5: Hash IP address for privacy
        $ipAddress = request()->ip();
        $hashedIp = $ipAddress ? hash('sha256', $ipAddress) : null;

        // Step 6: Create link record
        $link = Link::create([
            'hash' => $hash,
            'slug' => $customSlug,
            'url_scheme' => $parsed['scheme'],
            'url_host' => $parsed['host'],
            'url_port' => $parsed['port'],
            'url_path' => $parsed['path'],
            'url_query' => $parsed['query'],
            'url_fragment' => $parsed['fragment'],
            'full_url' => $url,
            'full_url_hash' => hash('sha256', $url),
            'expires_at' => $expiresAt,
            'user_id' => $userId,
            'ip_address' => $hashedIp,
            'user_agent' => request()->userAgent(),
        ]);

        // Step 7: Cache the link for fast retrieval
        $this->cacheLink($link);

        return $link;
    }

    /**
     * Cache a link for fast retrieval.
     */
    protected function cacheLink(Link $link): void
    {
        $ttl = config('anon.default_cache_ttl', 86400); // 24 hours

        Cache::put("link:{$link->hash}", $link, $ttl);

        if ($link->slug) {
            Cache::put("link:{$link->slug}", $link, $ttl);
        }
    }
}
