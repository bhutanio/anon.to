<?php

namespace Database\Factories;

use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Note>
 */
class NoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $content = fake()->paragraphs(rand(3, 10), true);
        $lines = explode("\n", $content);

        return [
            'hash' => Str::random(8),
            'title' => fake()->optional()->sentence(),
            'content' => $content,
            'content_hash' => hash('sha256', $content),
            'char_count' => strlen($content),
            'line_count' => count($lines),
            'expires_at' => fake()->optional(0.4)->dateTimeBetween('now', '+30 days'),
            'password_hash' => null,
            'view_limit' => null,
            'views' => fake()->numberBetween(0, 100),
            'unique_views' => fake()->numberBetween(0, 75), // FIXED: Was unique_visits, should be unique_views
            'last_viewed_at' => fake()->optional()->dateTimeThisMonth(),
            'is_active' => true,
            'is_reported' => false,
            'is_public' => true,
            'user_id' => null,
            'forked_from_id' => null,
            'ip_address' => hash('sha256', fake()->ipv4()),
            'user_agent' => fake()->userAgent(),
        ];
    }

    /**
     * Indicate that the note has a password.
     */
    public function withPassword(string $password = 'password'): static
    {
        return $this->state(fn (array $attributes) => [
            'password_hash' => bcrypt($password),
        ]);
    }

    /**
     * Indicate that the note has an expiration date.
     */
    public function withExpiration(?int $days = null): static
    {
        $days = $days ?? fake()->randomElement([1, 7, 14, 30]);

        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->addDays($days),
        ]);
    }

    /**
     * Indicate that the note has a view limit.
     */
    public function withViewLimit(?int $limit = null): static
    {
        $limit = $limit ?? fake()->numberBetween(1, 10);

        return $this->state(fn (array $attributes) => [
            'view_limit' => $limit,
        ]);
    }

    /**
     * Indicate that the note has a burn-after-reading limit.
     */
    public function burnAfterReading(int $limit = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'view_limit' => $limit,
        ]);
    }

    /**
     * Indicate that the note is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDays(rand(1, 30)),
        ]);
    }

    /**
     * Indicate that the note belongs to a user.
     */
    public function forUser(?User $user = null): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user?->id ?? User::factory(),
        ]);
    }

    /**
     * Indicate that the note is forked from another note.
     */
    public function forkedFrom(?Note $note = null): static
    {
        return $this->state(fn (array $attributes) => [
            'forked_from_id' => $note?->id ?? Note::factory(),
        ]);
    }
}
