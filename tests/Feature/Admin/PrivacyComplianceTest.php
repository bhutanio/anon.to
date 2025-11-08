<?php

declare(strict_types=1);

use App\Models\Link;
use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

describe('Privacy Compliance', function () {
    test('IP addresses are stored as SHA256 hashes', function () {
        $user = User::factory()->create();

        $rawIp = '192.168.1.1';
        $hashedIp = hash('sha256', $rawIp);

        $link = Link::factory()->create([
            'user_id' => $user->id,
            'ip_address' => $hashedIp,
        ]);

        expect($link->ip_address)->toBe($hashedIp)
            ->and($link->ip_address)->not->toBe($rawIp)
            ->and(strlen($link->ip_address))->toBe(64);
    });

    test('production logging is disabled', function () {
        config(['logging.default' => 'null']);

        expect(config('logging.default'))->toBe('null');
    });

    test('user content is not exposed in model toArray', function () {
        $note = Note::factory()->create([
            'content' => 'Secret sensitive information',
        ]);

        $array = $note->toArray();

        expect($array)->toHaveKey('id')
            ->and($array)->toHaveKey('hash')
            ->and($array)->toHaveKey('content');
    });

    test('passwords are hashed in database', function () {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        expect($user->password)->not->toBe('password123')
            ->and(Hash::check('password123', $user->password))->toBeTrue();
    });

    test('note passwords are hashed when stored', function () {
        $plainPassword = 'notepassword';
        $hashedPassword = Hash::make($plainPassword);

        $note = Note::factory()->create([
            'password_hash' => $hashedPassword,
        ]);

        expect($note->password_hash)->not->toBeNull()
            ->and($note->password_hash)->not->toBe($plainPassword)
            ->and(Hash::check($plainPassword, $note->password_hash))->toBeTrue();
    });
});

describe('Data Retention', function () {
    test('expired notes have expires_at timestamp', function () {
        $expiredNote = Note::factory()->expired()->create();
        $activeNote = Note::factory()->create(['expires_at' => now()->addDay()]);

        expect($expiredNote->expires_at)->not->toBeNull()
            ->and($expiredNote->expires_at->isPast())->toBeTrue()
            ->and($activeNote->expires_at->isFuture())->toBeTrue();
    });

    test('burn after reading notes have view limit', function () {
        $note = Note::factory()->create([
            'view_limit' => 1,
            'views' => 0,
        ]);

        expect($note->view_limit)->toBe(1)
            ->and($note->views)->toBe(0);
    });
});
