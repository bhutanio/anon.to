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
     */
    public function isInternalUrl(string $url): bool
    {
        $parsed = parse_url($url);

        if (! isset($parsed['host'])) {
            return false;
        }

        $host = $parsed['host'];

        // Check for localhost variations
        if (in_array($host, ['localhost', '127.0.0.1', '::1', '0.0.0.0'])) {
            return true;
        }

        // Resolve hostname to IP if needed
        $ip = filter_var($host, FILTER_VALIDATE_IP) ? $host : gethostbyname($host);

        // If gethostbyname fails to resolve, it returns the original hostname
        if ($ip === $host && ! filter_var($ip, FILTER_VALIDATE_IP)) {
            // Hostname didn't resolve, could be suspicious
            return false;
        }

        // Check private IP ranges
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return true;
        }

        // Additional checks for private ranges
        $privateRanges = [
            '10.', // 10.0.0.0/8
            '192.168.', // 192.168.0.0/16
            '172.16.', '172.17.', '172.18.', '172.19.', // 172.16.0.0/12
            '172.20.', '172.21.', '172.22.', '172.23.',
            '172.24.', '172.25.', '172.26.', '172.27.',
            '172.28.', '172.29.', '172.30.', '172.31.',
            '169.254.', // 169.254.0.0/16 (link-local)
        ];

        foreach ($privateRanges as $range) {
            if (str_starts_with($ip, $range)) {
                return true;
            }
        }

        return false;
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
