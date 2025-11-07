<?php

namespace Database\Factories;

use App\Models\Link;
use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $reportable = fake()->randomElement([Link::factory(), Note::factory()]);

        return [
            'reportable_type' => $reportable->modelName(),
            'reportable_id' => $reportable,
            'category' => fake()->randomElement(['spam', 'malware', 'illegal', 'copyright', 'harassment', 'other']),
            'url' => fake()->optional()->url(),
            'email' => fake()->optional()->safeEmail(),
            'comment' => fake()->paragraph(),
            'ip_address' => fake()->ipv4(),
            'user_id' => null,
            'status' => 'pending',
            'admin_notes' => null,
            'dealt_by' => null,
            'dealt_at' => null,
        ];
    }

    /**
     * Indicate that the report has been dealt with.
     */
    public function dealt(?User $admin = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'dealt',
            'admin_notes' => fake()->optional()->sentence(),
            'dealt_by' => $admin?->id ?? User::factory(),
            'dealt_at' => now(),
        ]);
    }

    /**
     * Indicate that the report was dismissed.
     */
    public function dismissed(?User $admin = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'dismissed',
            'admin_notes' => fake()->optional()->sentence(),
            'dealt_by' => $admin?->id ?? User::factory(),
            'dealt_at' => now(),
        ]);
    }

    /**
     * Indicate that the report was submitted by a logged-in user.
     */
    public function byUser(?User $user = null): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user?->id ?? User::factory(),
        ]);
    }
}
