<?php

declare(strict_types=1);

namespace App\Livewire\Notes;

use App\Actions\Notes\CreateNote;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class Create extends Component
{
    public string $content = '';

    public ?string $title = null;

    public ?string $password = null;

    public ?string $password_confirmation = null;

    public string $expires_at = '1-month';

    public ?int $view_limit = null;

    public bool $enable_burn_after_reading = false;

    public ?string $shortUrl = null;

    public ?string $hash = null;

    public bool $copied = false;

    public ?string $errorMessage = null;

    /**
     * Create a note with rate limiting.
     */
    public function createNote(CreateNote $createNote): void
    {
        // Reset state
        $this->shortUrl = null;
        $this->hash = null;
        $this->errorMessage = null;
        $this->copied = false;

        // Check rate limit
        $key = auth()->check()
            ? 'create-note:user:'.auth()->id()
            : 'create-note:ip:'.request()->ip();

        $limit = auth()->check() ? 50 : 10;

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = ceil($seconds / 60);
            $this->errorMessage = "Too many notes created. Please try again in {$minutes} minutes.";

            return;
        }

        // Validate inputs
        $validated = $this->validate([
            'content' => [
                'required',
                'string',
                'max:1048576', // 1MB
            ],
            'title' => [
                'nullable',
                'string',
                'max:255',
            ],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'max:255',
                'same:password_confirmation',
            ],
            'password_confirmation' => [
                'nullable',
                'required_with:password',
            ],
            'view_limit' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ], [
            'content.required' => 'Please enter some content for your note.',
            'content.max' => 'Content is too large. Maximum size is 1MB.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.same' => 'Password confirmation does not match.',
            'password_confirmation.required_with' => 'Please confirm your password.',
            'view_limit.min' => 'View limit must be at least 1.',
            'view_limit.max' => 'View limit cannot exceed 100.',
        ]);

        try {
            // Parse expires_at
            $expiresAt = $this->parseExpiresAt($this->expires_at);

            // Create the note
            $note = $createNote->execute([
                'content' => $validated['content'],
                'title' => $validated['title'],
                'password' => $validated['password'],
                'expires_at' => $expiresAt,
                'view_limit' => $this->enable_burn_after_reading ? $validated['view_limit'] : null,
                'user_id' => auth()->id(),
            ]);

            // Hit the rate limiter
            RateLimiter::hit($key, 3600); // 1 hour

            // Set the short URL
            $this->hash = $note->hash;
            $this->shortUrl = url('/n/'.$this->hash);

            // Clear the input
            $this->reset(['content', 'title', 'password', 'password_confirmation', 'view_limit', 'enable_burn_after_reading']);
            $this->expires_at = '1-month';
        } catch (\InvalidArgumentException $e) {
            $this->errorMessage = $e->getMessage();
        } catch (\Exception $e) {
            $this->errorMessage = 'An error occurred while creating the note. Please try again.';
        }
    }

    /**
     * Parse the expires_at value into a datetime.
     */
    protected function parseExpiresAt(string $value): ?string
    {
        if ($value === 'never') {
            if (! auth()->check()) {
                throw new \InvalidArgumentException('The "Never" expiration option is only available to authenticated users.');
            }

            return null;
        }

        $now = now();

        return match ($value) {
            '10-minutes' => $now->addMinutes(10)->toDateTimeString(),
            '1-hour' => $now->addHour()->toDateTimeString(),
            '1-day' => $now->addDay()->toDateTimeString(),
            '1-week' => $now->addWeek()->toDateTimeString(),
            '1-month' => $now->addMonth()->toDateTimeString(),
            default => $now->addMonth()->toDateTimeString(),
        };
    }

    /**
     * Copy the note URL to clipboard (handled by JavaScript).
     */
    public function markAsCopied(): void
    {
        $this->copied = true;

        // Reset copied state after 2 seconds
        $this->dispatch('reset-copied');
    }

    public function render()
    {
        return view('livewire.notes.create')
            ->layout('components.layouts.guest');
    }
}
