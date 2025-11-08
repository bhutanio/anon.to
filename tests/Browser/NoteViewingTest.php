<?php

declare(strict_types=1);

use App\Models\Note;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    // Clean up any existing test notes
    Note::query()->delete();
});

test('user can view a public note', function () {
    $note = Note::factory()->create([
        'content' => 'Test note content',
        'title' => 'Test Note',
    ]);

    $page = visit("/n/{$note->hash}");

    $page->assertSee('Test Note')
        ->assertSee('Test note content');
});

test('password-protected note shows password prompt', function () {
    $note = Note::factory()->withPassword('test1234')->create([
        'content' => 'Secret content',
    ]);

    $page = visit("/n/{$note->hash}");

    $page->assertSee('Password Protected')
        ->assertSee('This note requires a password to view')
        ->assertDontSee('Secret content');
});

test('correct password unlocks note', function () {
    $note = Note::factory()->withPassword('test1234')->create([
        'content' => 'Secret content',
    ]);

    $page = visit("/n/{$note->hash}");

    $page->fill('passwordInput', 'test1234')
        ->click('Unlock Note')
        ->waitForText('Secret content');
});

test('incorrect password shows error', function () {
    $note = Note::factory()->withPassword('test1234')->create([
        'content' => 'Secret content',
    ]);

    $page = visit("/n/{$note->hash}");

    $page->fill('passwordInput', 'wrong')
        ->click('Unlock Note')
        ->waitForText('Incorrect password');
});

test('note owner bypasses password protection', function () {
    $user = User::factory()->create();
    $note = Note::factory()->withPassword('test1234')->create([
        'content' => 'Secret content',
        'user_id' => $user->id,
    ]);

    actingAs($user);

    $page = visit("/n/{$note->hash}");

    // Should see content immediately without password prompt
    $page->assertSee('Secret content')
        ->assertSee('You own this')
        ->assertDontSee('Password Protected');
});

test('burn-after-reading displays warning when views are low', function () {
    $note = Note::factory()->create([
        'content' => 'Burn after reading',
        'view_limit' => 3,
        'views' => 2, // Only 1 view remaining
    ]);

    $page = visit("/n/{$note->hash}");

    $page->assertSee('Warning')
        ->assertSee('1 more view');
});

test('expired note shows 410 Gone page', function () {
    $note = Note::factory()->expired()->create([
        'content' => 'Expired content',
    ]);

    $page = visit("/n/{$note->hash}");

    $page->assertSee('Note Expired')
        ->assertDontSee('Expired content');
});

test('copy to clipboard button works', function () {
    $note = Note::factory()->create([
        'content' => 'Copy this text',
    ]);

    $page = visit("/n/{$note->hash}");

    $page->click('Copy to Clipboard')
        ->waitForText('Copied!');
});

test('note content displays as plain text', function () {
    $note = Note::factory()->create([
        'content' => '<?php echo "Hello";',
    ]);

    $page = visit("/n/{$note->hash}");

    $page->assertSee('<?php echo "Hello";');
});
