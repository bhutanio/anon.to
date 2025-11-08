<?php

declare(strict_types=1);

namespace App\Actions\Links;

use App\Models\Link;
use App\Services\UrlService;

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
     * @param  array{url: string, user_id: int|null}  $data
     *
     * @throws \InvalidArgumentException If validation fails
     * @throws \RuntimeException If hash generation fails
     */
    public function execute(array $data): Link
    {
        $url = $data['url'];
        $userId = $data['user_id'] ?? null;

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

        // Step 4: Generate unique hash
        $hash = $this->generateHash->execute();

        // Step 5: Hash IP address for privacy
        $ipAddress = request()->ip();
        $hashedIp = $ipAddress ? hash('sha256', $ipAddress) : null;

        // Step 6: Create link record (caching handled by observer)
        return Link::create([
            'hash' => $hash,
            'url_scheme' => $parsed['scheme'],
            'url_host' => $parsed['host'],
            'url_port' => $parsed['port'],
            'url_path' => $parsed['path'],
            'url_query' => $parsed['query'],
            'url_fragment' => $parsed['fragment'],
            'full_url' => $url,
            'full_url_hash' => hash('sha256', $url),
            'visits' => 0,
            'is_active' => true,
            'is_reported' => false,
            'user_id' => $userId,
            'ip_address' => $hashedIp,
        ]);
    }
}
