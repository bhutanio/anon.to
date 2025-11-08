<?php

declare(strict_types=1);

namespace App\Actions\Notes;

use App\Models\Note;
use Illuminate\Support\Str;

class GenerateNoteHash
{
    protected array $excludedWords;

    protected int $hashLength;

    protected int $maxAttempts = 10;

    public function __construct()
    {
        $this->excludedWords = array_map('strtolower', config('anon.excluded_words', []));
        $this->hashLength = config('anon.note_hash_length', 8);
    }

    /**
     * Generate a unique hash for a note.
     *
     * @throws \RuntimeException If unable to generate unique hash after max attempts
     */
    public function execute(): string
    {
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
            if (! Note::where('hash', $hash)->exists()) {
                return $hash;
            }
        }

        throw new \RuntimeException(
            "Failed to generate unique hash after {$this->maxAttempts} attempts. ".
            'This may indicate hash space exhaustion or database issues.'
        );
    }
}
