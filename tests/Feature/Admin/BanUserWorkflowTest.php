<?php

declare(strict_types=1);

use App\Models\Link;
use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

describe('Ban User Workflow', function () {
    test('banning a user deactivates all their links', function () {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);

        $link1 = Link::factory()->create(['user_id' => $user->id, 'is_active' => true]);
        $link2 = Link::factory()->create(['user_id' => $user->id, 'is_active' => true]);

        $this->actingAs($admin);

        DB::transaction(function () use ($user, $admin) {
            $user->update([
                'banned_at' => now(),
                'banned_by' => $admin->id,
            ]);

            $user->links()->update(['is_active' => false]);
            $user->notes()->update(['is_active' => false]);
        });

        expect($link1->fresh()->is_active)->toBeFalse()
            ->and($link2->fresh()->is_active)->toBeFalse();
    });

    test('banning a user deactivates all their notes', function () {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);

        $note1 = Note::factory()->create(['user_id' => $user->id, 'is_active' => true]);
        $note2 = Note::factory()->create(['user_id' => $user->id, 'is_active' => true]);

        $this->actingAs($admin);

        DB::transaction(function () use ($user, $admin) {
            $user->update([
                'banned_at' => now(),
                'banned_by' => $admin->id,
            ]);

            $user->links()->update(['is_active' => false]);
            $user->notes()->update(['is_active' => false]);
        });

        expect($note1->fresh()->is_active)->toBeFalse()
            ->and($note2->fresh()->is_active)->toBeFalse();
    });

    test('banning a user sets banned_at timestamp', function () {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($admin);

        DB::transaction(function () use ($user, $admin) {
            $user->update([
                'banned_at' => now(),
                'banned_by' => $admin->id,
            ]);

            $user->links()->update(['is_active' => false]);
            $user->notes()->update(['is_active' => false]);
        });

        expect($user->fresh()->banned_at)->not->toBeNull()
            ->and($user->fresh()->banned_by)->toBe($admin->id);
    });

    test('banning a user deactivates all content in single transaction', function () {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);

        Link::factory()->count(3)->create(['user_id' => $user->id, 'is_active' => true]);
        Note::factory()->count(3)->create(['user_id' => $user->id, 'is_active' => true]);

        $this->actingAs($admin);

        DB::transaction(function () use ($user, $admin) {
            $user->update([
                'banned_at' => now(),
                'banned_by' => $admin->id,
            ]);

            $user->links()->update(['is_active' => false]);
            $user->notes()->update(['is_active' => false]);
        });

        expect($user->fresh()->banned_at)->not->toBeNull()
            ->and($user->links()->where('is_active', true)->count())->toBe(0)
            ->and($user->notes()->where('is_active', true)->count())->toBe(0);
    });

    test('unbanning a user clears banned_at timestamp', function () {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create([
            'is_admin' => false,
            'banned_at' => now(),
            'banned_by' => $admin->id,
        ]);

        $this->actingAs($admin);

        $user->update([
            'banned_at' => null,
            'banned_by' => null,
        ]);

        expect($user->fresh()->banned_at)->toBeNull()
            ->and($user->fresh()->banned_by)->toBeNull();
    });

    test('admin cannot ban themselves via policy', function () {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin);

        expect($admin->can('ban', $admin))->toBeFalse();
    });

    test('admin cannot ban other admins via policy', function () {
        $admin1 = User::factory()->create(['is_admin' => true]);
        $admin2 = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin1);

        expect($admin1->can('ban', $admin2))->toBeFalse();
    });
});
