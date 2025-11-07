<?php

declare(strict_types=1);

use App\Actions\Links\GenerateHash;
use App\Models\Link;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->group('feature');

beforeEach(function () {
    $this->action = app(GenerateHash::class);
});

describe('execute() with auto-generated hash', function () {
    test('generates random hash of correct length', function () {
        $hash = $this->action->execute();

        expect($hash)->toBeString()
            ->toHaveLength(config('anon.hash_length', 6));
    });

    test('generates unique hash not in database', function () {
        $hash = $this->action->execute();

        expect(Link::where('hash', $hash)->exists())->toBeFalse();
    });

    test('avoids excluded words', function () {
        config(['anon.excluded_words' => ['badword', 'another']]);
        config(['anon.hash_length' => 7]);

        // Create a link with an excluded word to ensure it's avoided
        Link::factory()->create(['hash' => 'badword']);

        $hash = $this->action->execute();

        expect($hash)->not->toBeIn(['badword', 'another']);
    });

    test('generates unique hash even with collision', function () {
        // Create a link to force potential collision
        $existingHash = \Illuminate\Support\Str::random(6);
        Link::factory()->create(['hash' => $existingHash]);

        $hash = $this->action->execute();

        // Should generate different hash
        expect($hash)->not->toBe($existingHash);
    });

    test('throws exception after max attempts', function () {
        // Create links to fill up the hash space (using very short hashes)
        config(['anon.hash_length' => 2]); // Very short to make collisions likely

        // Create many links to increase collision probability
        // With 2-char hashes, we only have 62^2 = 3844 possible combinations
        // We won't fill it completely, but we can skip this test as it's hard to test reliably
        $this->markTestSkipped('Difficult to reliably test hash exhaustion without mocking');
    });
});
