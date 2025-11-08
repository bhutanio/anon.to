<?php

declare(strict_types=1);

use App\Models\Note;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    // Clean up any existing test notes
    Note::query()->delete();
});

test('user can create a note with basic content', function () {
    $page = visit('/notes/create');

    $page->assertSee('Share Code Securely')
        ->fill('content', 'This is a test note')
        ->select('syntax', 'plaintext')
        ->click('Create Note')
        ->waitForText('Your note is ready!')
        ->assertSee('/n/');

    // Verify note was created
    expect(Note::count())->toBe(1);
    $note = Note::first();
    expect($note->content)->toBe('This is a test note');
    expect($note->syntax)->toBe('plaintext');
});

test('user can create a password-protected note', function () {
    $page = visit('/notes/create');

    $page->fill('content', 'Secret content')
        ->fill('password', 'test1234')
        ->fill('password_confirmation', 'test1234')
        ->click('Create Note')
        ->waitForText('Your note is ready!');

    // Verify note has password
    $note = Note::first();
    expect($note->password_hash)->not->toBeNull();
});

test('user can create a note with burn-after-reading', function () {
    $page = visit('/notes/create');

    $page->fill('content', 'Burn after reading')
        ->check('enable_burn_after_reading')
        ->fill('view_limit', '5')
        ->click('Create Note')
        ->waitForText('Your note is ready!');

    // Verify note has view limit
    $note = Note::first();
    expect($note->view_limit)->toBe(5);
});

test('authenticated user can set never expiration', function () {
    $user = User::factory()->create();

    actingAs($user);

    $page = visit('/notes/create');

    $page->fill('content', 'Never expiring note')
        ->select('expires_at', 'never')
        ->click('Create Note')
        ->waitForText('Your note is ready!');

    // Verify note has no expiration
    $note = Note::first();
    expect($note->expires_at)->toBeNull();
});

test('validation errors are displayed for invalid input', function () {
    $page = visit('/notes/create');

    $page->click('Create Note')
        ->waitForText('Please enter some content for your note.');

    // Try with password mismatch
    $page->fill('content', 'Test')
        ->fill('password', 'test1234')
        ->fill('password_confirmation', 'different')
        ->click('Create Note')
        ->waitForText('Password confirmation does not match');
});

test('rate limiting prevents too many note creations', function () {
    // Create 11 notes quickly to trigger rate limit (anonymous limit is 10)
    for ($i = 0; $i < 11; $i++) {
        $page = visit('/notes/create');
        $page->fill('content', "Test note {$i}")
            ->click('Create Note');

        if ($i < 10) {
            $page->waitForText('Your note is ready!');
        } else {
            $page->waitForText('Too many notes created');
        }
    }
})->skip('Rate limiting test - enable if needed');
