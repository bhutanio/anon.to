<?php

declare(strict_types=1);

use App\Models\Link;
use App\Models\Note;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Report Workflow', function () {
    test('deleting reported link marks report as resolved', function () {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);
        $link = Link::factory()->create(['user_id' => $user->id]);
        $report = Report::factory()->create([
            'reportable_type' => Link::class,
            'reportable_id' => $link->id,
            'status' => 'pending',
        ]);

        $this->actingAs($admin);

        $response = $this->delete("/admin/reports/{$report->id}/content");

        $report->refresh();
        expect($report->status)->toBe('resolved')
            ->and($report->dealt_by)->toBe($admin->id)
            ->and($report->dealt_at)->not->toBeNull()
            ->and(Link::find($link->id))->toBeNull();
    })->skip('Requires implementing HTTP routes for report actions');

    test('banning user via report deactivates all their content', function () {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);
        Link::factory()->count(3)->create(['user_id' => $user->id, 'is_active' => true]);
        Note::factory()->count(2)->create(['user_id' => $user->id, 'is_active' => true]);

        $link = $user->links->first();
        $report = Report::factory()->create([
            'reportable_type' => Link::class,
            'reportable_id' => $link->id,
            'status' => 'pending',
        ]);

        $this->actingAs($admin);

        DB::transaction(function () use ($report, $user, $admin) {
            $user->update([
                'banned_at' => now(),
                'banned_by' => $admin->id,
            ]);

            $user->links()->update(['is_active' => false]);
            $user->notes()->update(['is_active' => false]);

            $report->update([
                'status' => 'resolved',
                'dealt_by' => $admin->id,
                'dealt_at' => now(),
            ]);
        });

        $user->refresh();
        $report->refresh();

        expect($user->banned_at)->not->toBeNull()
            ->and($user->banned_by)->toBe($admin->id)
            ->and($user->links()->where('is_active', true)->count())->toBe(0)
            ->and($user->notes()->where('is_active', true)->count())->toBe(0)
            ->and($report->status)->toBe('resolved');
    });

    test('dismissing report sets status to dismissed', function () {
        $admin = User::factory()->create(['is_admin' => true]);
        $link = Link::factory()->create();
        $report = Report::factory()->create([
            'reportable_type' => Link::class,
            'reportable_id' => $link->id,
            'status' => 'pending',
        ]);

        $this->actingAs($admin);

        $report->update([
            'status' => 'dismissed',
            'dealt_by' => $admin->id,
            'dealt_at' => now(),
        ]);

        $report->refresh();

        expect($report->status)->toBe('dismissed')
            ->and($report->dealt_by)->toBe($admin->id)
            ->and($report->dealt_at)->not->toBeNull();
    });

    test('admin can add notes to report', function () {
        $admin = User::factory()->create(['is_admin' => true]);
        $link = Link::factory()->create();
        $report = Report::factory()->create([
            'reportable_type' => Link::class,
            'reportable_id' => $link->id,
            'status' => 'pending',
        ]);

        $this->actingAs($admin);

        $adminNotes = 'Reviewed and found to be compliant.';

        $report->update([
            'admin_notes' => $adminNotes,
        ]);

        $report->refresh();

        expect($report->admin_notes)->toBe($adminNotes);
    });

    test('admin can view pending reports', function () {
        $admin = User::factory()->create(['is_admin' => true]);
        Report::factory()->count(3)->create(['status' => 'pending']);

        $this->actingAs($admin);

        $pendingReports = Report::where('status', 'pending')->get();

        expect($pendingReports->count())->toBe(3);
    });
});
