<?php

declare(strict_types=1);

namespace App\Livewire\Notes;

use App\Actions\Notes\IncrementViews;
use App\Models\Note;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class View extends Component
{
    public string $hash;

    public ?Note $note = null;

    public bool $requiresPassword = false;

    public bool $isOwner = false;

    public bool $isExpired = false;

    public bool $isDeleted = false;

    public string $passwordInput = '';

    public ?string $passwordError = null;

    public bool $showRaw = false;

    public int $attemptsRemaining = 5;

    public bool $copied = false;

    /**
     * Mount the component and load the note.
     */
    public function mount(string $hash, IncrementViews $incrementViews): void
    {
        $this->hash = $hash;

        // Try to load from cache first
        $this->note = Cache::remember("note:{$hash}", 86400, function () use ($hash) {
            return Note::where('hash', $hash)->where('is_active', true)->first();
        });

        // Check if note exists
        if (! $this->note) {
            abort(410, 'Note not found or has been deleted');
        }

        // Check if note is expired
        if ($this->note->expires_at && $this->note->expires_at->isPast()) {
            abort(410, 'Note has expired');
        }

        // Check if user is the owner
        $this->isOwner = auth()->check() && auth()->id() === $this->note->user_id;

        // Check if password is required
        if ($this->note->password_hash && ! $this->isOwner) {
            // Check if password was already verified in session
            $sessionKey = "note-password-verified:{$hash}";
            if (! session()->has($sessionKey)) {
                $this->requiresPassword = true;

                // Check remaining attempts
                $this->updateRemainingAttempts();

                return;
            }
        }

        // Increment views if not requiring password
        if (! $this->requiresPassword) {
            $wasDeleted = $incrementViews->execute($this->note, request()->ip());

            if ($wasDeleted) {
                abort(410, 'Note has been deleted after reaching view limit');
            }

            // Refresh the note to get updated view count
            $this->note->refresh();

            // Update cache
            Cache::put("note:{$hash}", $this->note, 86400);
        }
    }

    /**
     * Verify the password and unlock the note.
     */
    public function verifyPassword(IncrementViews $incrementViews): void
    {
        $this->passwordError = null;

        // Validate password input
        $this->validate([
            'passwordInput' => ['required', 'string'],
        ], [
            'passwordInput.required' => 'Please enter the password.',
        ]);

        // Check rate limit
        $key = "note-password:{$this->hash}:ip:".request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = ceil($seconds / 60);
            $this->passwordError = "Too many password attempts. Please try again in {$minutes} minutes.";

            return;
        }

        // Verify password
        if (! Hash::check($this->passwordInput, $this->note->password_hash)) {
            // Hit rate limiter
            RateLimiter::hit($key, 900); // 15 minutes

            // Update remaining attempts
            $this->updateRemainingAttempts();

            $this->passwordError = 'Incorrect password. Please try again.';
            $this->passwordInput = '';

            return;
        }

        // Password is correct, clear rate limit
        RateLimiter::clear($key);

        // Store in session for 15 minutes
        session()->put("note-password-verified:{$this->hash}", true);
        session()->save();

        // Increment views
        $wasDeleted = $incrementViews->execute($this->note, request()->ip());

        if ($wasDeleted) {
            $this->isDeleted = true;
            $this->note = null;

            return;
        }

        // Refresh the note
        $this->note->refresh();

        // Update cache
        Cache::put("note:{$this->hash}", $this->note, 86400);

        // Hide password prompt
        $this->requiresPassword = false;
        $this->passwordInput = '';
    }

    /**
     * Update the remaining password attempts.
     */
    protected function updateRemainingAttempts(): void
    {
        $key = "note-password:{$this->hash}:ip:".request()->ip();
        $attempts = RateLimiter::attempts($key);
        $this->attemptsRemaining = max(0, 5 - $attempts);
    }

    /**
     * Toggle raw view mode.
     */
    public function toggleRaw(): void
    {
        $this->showRaw = ! $this->showRaw;
    }

    /**
     * Copy the note content to clipboard (handled by JavaScript).
     */
    public function markAsCopied(): void
    {
        $this->copied = true;
    }

    public function render()
    {
        return view('livewire.notes.view')->layout('components.layouts.guest');
    }
}
