<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\RateLimiter;

trait HasRateLimiting
{
    /**
     * Check if the rate limit has been exceeded.
     *
     * @param  string  $prefix  The rate limit key prefix (e.g., 'create-link')
     * @param  int  $authLimit  Limit for authenticated users
     * @param  int  $anonLimit  Limit for anonymous users
     * @param  bool  $hashIp  Whether to hash the IP address for privacy
     * @return bool Returns true if rate limit exceeded
     */
    protected function checkRateLimit(string $prefix, int $authLimit, int $anonLimit, bool $hashIp = false): bool
    {
        $key = $this->getRateLimitKey($prefix, $hashIp);
        $limit = auth()->check() ? $authLimit : $anonLimit;

        return RateLimiter::tooManyAttempts($key, $limit);
    }

    /**
     * Get the time in seconds until the rate limit is available again.
     *
     * @param  string  $prefix  The rate limit key prefix
     * @param  bool  $hashIp  Whether to hash the IP address for privacy
     */
    protected function getRateLimitSeconds(string $prefix, bool $hashIp = false): int
    {
        $key = $this->getRateLimitKey($prefix, $hashIp);

        return RateLimiter::availableIn($key);
    }

    /**
     * Hit the rate limiter.
     *
     * @param  string  $prefix  The rate limit key prefix
     * @param  int  $decaySeconds  Number of seconds before the rate limit resets (default: 3600 = 1 hour)
     * @param  bool  $hashIp  Whether to hash the IP address for privacy
     */
    protected function hitRateLimit(string $prefix, int $decaySeconds = 3600, bool $hashIp = false): void
    {
        $key = $this->getRateLimitKey($prefix, $hashIp);

        RateLimiter::hit($key, $decaySeconds);
    }

    /**
     * Get the rate limit key for the current user/IP.
     *
     * @param  string  $prefix  The rate limit key prefix
     * @param  bool  $hashIp  Whether to hash the IP address for privacy
     */
    protected function getRateLimitKey(string $prefix, bool $hashIp = false): string
    {
        if (auth()->check()) {
            return "{$prefix}:user:".auth()->id();
        }

        $ip = request()->ip();

        if ($hashIp) {
            $ip = hash('sha256', $ip);
        }

        return "{$prefix}:ip:{$ip}";
    }
}
