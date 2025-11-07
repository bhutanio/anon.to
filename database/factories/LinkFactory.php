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
            'expires_at' => fake()->optional(0.3)->dateTimeBetween('now', '+30 days'),
            'password_hash' => null,
            'visits' => fake()->numberBetween(0, 1000),
            'unique_visits' => fake()->numberBetween(0, 500),
            'last_visited_at' => fake()->optional()->dateTimeThisMonth(),
            'is_active' => true,
            'is_reported' => false,
            'user_id' => null,
            'ip_address' => hash('sha256', fake()->ipv4()),
            'user_agent' => fake()->userAgent(),
        ];
    }

    /**
     * Indicate that the link is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDays(rand(1, 30)),
        ]);
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
}
