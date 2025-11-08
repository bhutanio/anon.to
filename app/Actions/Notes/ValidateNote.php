<?php

declare(strict_types=1);

namespace App\Actions\Notes;

class ValidateNote
{
    /**
     * Validate note data for creation.
     *
     * Checks content, syntax, and title fields.
     *
     * @param  array{content: string, syntax: string|null, title: string|null}  $data  The note data to validate
     *
     * @throws \InvalidArgumentException If validation fails
     */
    public function execute(array $data): void
    {
        $content = $data['content'] ?? null;
        $syntax = $data['syntax'] ?? null;
        $title = $data['title'] ?? null;

        // Validate content is not empty
        if (empty(trim($content))) {
            throw new \InvalidArgumentException('Content cannot be empty');
        }

        // Validate content is a string
        if (! is_string($content)) {
            throw new \InvalidArgumentException('Content must be a string');
        }

        // Validate content length (max 1MB = 1048576 bytes)
        if (strlen($content) > 1048576) {
            throw new \InvalidArgumentException('Content cannot exceed 1MB (1048576 bytes)');
        }

        // Validate syntax if provided
        if ($syntax !== null) {
            if (! is_string($syntax)) {
                throw new \InvalidArgumentException('Syntax must be a string');
            }

            $validLanguages = config('anon.syntax_languages', []);
            if (! in_array($syntax, $validLanguages)) {
                throw new \InvalidArgumentException('Invalid syntax language. Must be one of the supported languages.');
            }
        }

        // Validate title if provided
        if ($title !== null) {
            if (! is_string($title)) {
                throw new \InvalidArgumentException('Title must be a string');
            }

            if (strlen($title) > 255) {
                throw new \InvalidArgumentException('Title cannot exceed 255 characters');
            }
        }
    }
}
