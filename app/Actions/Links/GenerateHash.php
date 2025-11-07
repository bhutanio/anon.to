<?php

declare(strict_types=1);

namespace App\Actions\Links;

use App\Models\Link;
use Illuminate\Support\Str;

class GenerateHash
{
    protected array $excludedWords;

    protected int $hashLength;

    protected int $maxAttempts = 10;

    public function __construct()
    {
        $this->excludedWords = array_map('strtolower', config('anon.excluded_words', []));
        $this->hashLength = config('anon.hash_length', 6);
    }

    /**
     * Generate a unique hash for a link.
     *
     * @param  string|null  $customSlug  Custom slug for registered users (optional)
     *
     * @throws \RuntimeException If unable to generate unique hash after max attempts
     */
    public function execute(?string $customSlug = null): string
    {
        // If custom slug provided, validate and return it
        if ($customSlug !== null) {
            return $this->validateCustomSlug($customSlug);
        }

        // Generate random hash with collision detection
        return $this->generateUniqueHash();
    }

    /**
     * Generate a collision-free random hash.
     */
    protected function generateUniqueHash(): string
    {
        for ($attempt = 1; $attempt <= $this->maxAttempts; $attempt++) {
            $hash = Str::random($this->hashLength);

            // Check if hash is in excluded words list
            if (in_array(strtolower($hash), $this->excludedWords)) {
                continue;
            }

            // Check uniqueness in database
            if (! Link::where('hash', $hash)->exists()) {
                return $hash;
            }
        }

        throw new \RuntimeException(
            "Failed to generate unique hash after {$this->maxAttempts} attempts. ".
            'This may indicate hash space exhaustion or database issues.'
        );
    }

    /**
     * Validate and return custom slug for registered users.
     *
     * @throws \InvalidArgumentException
     */
    protected function validateCustomSlug(string $slug): string
    {
        // Validate format: alphanumeric and dashes only
        if (! preg_match('/^[a-zA-Z0-9\-]+$/', $slug)) {
            throw new \InvalidArgumentException(
                'Custom slug can only contain letters, numbers, and dashes'
            );
        }

        // Check length constraints
        if (strlen($slug) < 3) {
            throw new \InvalidArgumentException('Custom slug must be at least 3 characters');
        }

        if (strlen($slug) > 50) {
            throw new \InvalidArgumentException('Custom slug cannot exceed 50 characters');
        }

        // Check if slug is in excluded words
        if (in_array(strtolower($slug), $this->excludedWords)) {
            throw new \InvalidArgumentException('This custom slug is not allowed');
        }

        // Check uniqueness
        if (Link::where('slug', $slug)->exists()) {
            throw new \InvalidArgumentException('This custom slug is already taken');
        }

        return $slug;
    }
}
