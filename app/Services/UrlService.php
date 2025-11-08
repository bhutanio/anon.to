<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Link;

class UrlService
{
    /**
     * Parse a URL into its components.
     *
     * @return array{scheme: string, host: string, port: int|null, path: string|null, query: string|null, fragment: string|null}
     */
    public function parse(string $url): array
    {
        $parsed = parse_url($url);

        if ($parsed === false || ! isset($parsed['scheme'], $parsed['host'])) {
            throw new \InvalidArgumentException('Invalid URL format');
        }

        return [
            'scheme' => $parsed['scheme'],
            'host' => $parsed['host'],
            'port' => $parsed['port'] ?? null,
            'path' => $parsed['path'] ?? null,
            'query' => $parsed['query'] ?? null,
            'fragment' => $parsed['fragment'] ?? null,
        ];
    }

    /**
     * Reconstruct a full URL from a Link model or components array.
     */
    public function reconstruct(Link|array $data): string
    {
        if ($data instanceof Link) {
            $data = [
                'scheme' => $data->url_scheme,
                'host' => $data->url_host,
                'port' => $data->url_port,
                'path' => $data->url_path,
                'query' => $data->url_query,
                'fragment' => $data->url_fragment,
            ];
        }

        $url = $data['scheme'].'://'.$data['host'];

        // Add port if not standard
        if ($data['port'] && ! $this->isStandardPort($data['scheme'], $data['port'])) {
            $url .= ':'.$data['port'];
        }

        // Add path
        if ($data['path']) {
            $url .= $data['path'];
        }

        // Add query string
        if ($data['query']) {
            $url .= '?'.$data['query'];
        }

        // Add fragment
        if ($data['fragment']) {
            $url .= '#'.$data['fragment'];
        }

        return $url;
    }

    /**
     * Check if URL points to an internal/private IP address (SSRF prevention).
     *
     * Note: This method does NOT perform DNS resolution to avoid:
     * - Performance issues (DNS timeouts can hang for 5-30 seconds)
     * - DNS rebinding attacks (attacker changes DNS from public to private IP)
     * - Security vulnerabilities (no timeout control in gethostbyname)
     *
     * If the host is already an IP address, we validate it.
     * If it's a hostname, we trust firewall rules to block internal domains.
     */
    public function isInternalUrl(string $url): bool
    {
        $parsed = parse_url($url);

        if (! isset($parsed['host'])) {
            return false;
        }

        $host = $parsed['host'];

        // Check for localhost variations (no DNS needed)
        if (in_array(strtolower($host), ['localhost', '127.0.0.1', '::1', '0.0.0.0'])) {
            return true;
        }

        // If host is already an IP address, validate it directly
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            // Returns false if IP is in private/reserved range, true otherwise
            $isPublicIp = filter_var(
                $host,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            );

            // If it's NOT a public IP, it's internal
            return ! $isPublicIp;
        }

        // For hostnames, do NOT resolve DNS
        // Trust that external hostnames are safe, and rely on:
        // 1. Firewall rules to block requests to internal domains
        // 2. Network-level restrictions
        // 3. Server configuration to prevent SSRF
        return false;
    }

    /**
     * Check if the URL is from this application (already shortened).
     *
     * This checks if the URL's host matches the APP_URL configuration.
     */
    public function isAnonUrl(string $url): bool
    {
        $parsed = parse_url($url);

        if (! isset($parsed['host'])) {
            return false;
        }

        $host = strtolower($parsed['host']);

        // Exclude localhost and internal hosts (these should be caught by isInternalUrl instead)
        $excludedHosts = ['localhost', '127.0.0.1', '::1', '0.0.0.0'];
        if (in_array($host, $excludedHosts)) {
            return false;
        }

        $appUrl = parse_url(config('app.url'));
        $appHost = isset($appUrl['host']) ? strtolower($appUrl['host']) : null;

        // Check if the host matches the app URL host
        return $appHost && $host === $appHost;
    }

    /**
     * Check if port is standard for the given scheme.
     */
    protected function isStandardPort(string $scheme, int $port): bool
    {
        $standardPorts = [
            'http' => 80,
            'https' => 443,
            'ftp' => 21,
        ];

        return isset($standardPorts[$scheme]) && $standardPorts[$scheme] === $port;
    }
}
