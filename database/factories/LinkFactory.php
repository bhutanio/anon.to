<?php

namespace Database\Factories;

use App\Models\Link;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Link>
 */
class LinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $url = fake()->url();
        $parsed = parse_url($url);

        return [
            'hash' => Str::random(6),
            'url_scheme' => $parsed['scheme'] ?? 'https',
            'url_host' => $parsed['host'] ?? fake()->domainName(),
            'url_port' => $parsed['port'] ?? null,
            'url_path' => $parsed['path'] ?? '/',
            'url_query' => $parsed['query'] ?? null,
            'url_fragment' => $parsed['fragment'] ?? null,
            'full_url' => $url,
            'full_url_hash' => hash('sha256', $url),
            'title' => fake()->optional()->sentence(),
            'description' => fake()->optional()->paragraph(),
            'visits' => fake()->numberBetween(0, 1000),
            'last_visited_at' => fake()->optional()->dateTimeThisMonth(),
            'is_active' => true,
            'is_reported' => false,
            'user_id' => null,
            'ip_address' => hash('sha256', fake()->ipv4()),
        ];
    }

    /**
     * Indicate that the link has been reported.
     */
    public function reported(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_reported' => true,
        ]);
    }

    /**
     * Indicate that the link belongs to a user.
     */
    public function forUser(?User $user = null): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user?->id ?? User::factory(),
        ]);
    }

    /**
     * Create a link with a specific destination URL.
     */
    public function withUrl(string $url): static
    {
        $parsed = parse_url($url);

        return $this->state(fn (array $attributes) => [
            'url_scheme' => $parsed['scheme'] ?? 'https',
            'url_host' => $parsed['host'] ?? '',
            'url_port' => $parsed['port'] ?? null,
            'url_path' => $parsed['path'] ?? '/',
            'url_query' => $parsed['query'] ?? null,
            'url_fragment' => $parsed['fragment'] ?? null,
            'full_url' => $url,
            'full_url_hash' => hash('sha256', $url),
        ]);
    }
}
