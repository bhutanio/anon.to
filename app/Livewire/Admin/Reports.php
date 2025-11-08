<?php

namespace App\Livewire\Admin;

use App\Models\Report;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Reports extends Component
{
    use WithPagination;

    public string $statusFilter = 'pending';

    public ?int $viewingReportId = null;

    public bool $showDeleteContentConfirm = false;

    public ?int $deletingReportId = null;

    public bool $showBanUserConfirm = false;

    public ?int $banningReportId = null;

    public string $adminNotes = '';

    public int $maxAdminNotesLength = 500;

    /**
     * Update status filter and reset pagination.
     */
    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Get the reports query with filters.
     */
    protected function getReportsQuery(): Builder
    {
        return Report::query()
            ->with(['reportable', 'user:id,name,email', 'dealtBy:id,name'])
            ->when($this->statusFilter !== 'all', function (Builder $query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy('created_at', 'desc');
    }

    /**
     * View report details.
     */
    public function viewReport(int $reportId): void
    {
        $this->viewingReportId = $reportId;
        $report = Report::find($reportId);

        if ($report && $report->admin_notes) {
            $this->adminNotes = $report->admin_notes;
        } else {
            $this->adminNotes = '';
        }
    }

    /**
     * Close view modal.
     */
    public function closeView(): void
    {
        $this->viewingReportId = null;
        $this->adminNotes = '';
    }

    /**
     * Show delete content confirmation modal.
     */
    public function confirmDeleteContent(int $reportId): void
    {
        $this->deletingReportId = $reportId;
        $this->showDeleteContentConfirm = true;
    }

    /**
     * Delete content and mark report as resolved.
     */
    public function deleteContent(): void
    {
        if ($this->deletingReportId === null) {
            return;
        }

        $report = Report::with('reportable')->findOrFail($this->deletingReportId);
        $this->authorize('resolve', $report);

        DB::transaction(function () use ($report) {
            if ($report->reportable) {
                $report->reportable->delete();
            }

            $report->update([
                'status' => 'resolved',
                'dealt_by' => auth()->id(),
                'dealt_at' => now(),
            ]);
        });

        $this->showDeleteContentConfirm = false;
        $this->deletingReportId = null;

        $this->dispatch('content-deleted');
    }

    /**
     * Cancel delete content.
     */
    public function cancelDeleteContent(): void
    {
        $this->showDeleteContentConfirm = false;
        $this->deletingReportId = null;
    }

    /**
     * Show ban user confirmation modal.
     */
    public function confirmBanUser(int $reportId): void
    {
        $this->banningReportId = $reportId;
        $this->showBanUserConfirm = true;
    }

    /**
     * Ban user and mark report as resolved.
     */
    public function banContentCreator(): void
    {
        if ($this->banningReportId === null) {
            return;
        }

        $report = Report::with(['reportable.user'])->findOrFail($this->banningReportId);
        $this->authorize('resolve', $report);

        if (! $report->reportable || ! $report->reportable->user) {
            $this->showBanUserConfirm = false;
            $this->banningReportId = null;

            return;
        }

        $user = $report->reportable->user;

        DB::transaction(function () use ($report, $user) {
            $user->update([
                'banned_at' => now(),
                'banned_by' => auth()->id(),
            ]);

            $user->links()->update(['is_active' => false]);
            $user->notes()->update(['is_active' => false]);

            $report->update([
                'status' => 'resolved',
                'dealt_by' => auth()->id(),
                'dealt_at' => now(),
            ]);
        });

        $this->showBanUserConfirm = false;
        $this->banningReportId = null;

        $this->dispatch('user-banned');
    }

    /**
     * Cancel ban user.
     */
    public function cancelBanUser(): void
    {
        $this->showBanUserConfirm = false;
        $this->banningReportId = null;
    }

    /**
     * Dismiss a report.
     */
    public function dismissReport(int $reportId): void
    {
        $report = Report::findOrFail($reportId);
        $this->authorize('dismiss', $report);

        $report->update([
            'status' => 'dismissed',
            'dealt_by' => auth()->id(),
            'dealt_at' => now(),
        ]);

        $this->dispatch('report-dismissed');
    }

    /**
     * Save admin notes.
     */
    public function saveAdminNotes(): void
    {
        if ($this->viewingReportId === null) {
            return;
        }

        $this->validate([
            'adminNotes' => 'nullable|string|max:500',
        ]);

        $report = Report::findOrFail($this->viewingReportId);
        $this->authorize('addNotes', $report);

        $report->update([
            'admin_notes' => $this->adminNotes,
        ]);

        $this->dispatch('notes-saved');
    }

    /**
     * Get the viewing report details.
     */
    public function getViewingReportProperty(): ?Report
    {
        if ($this->viewingReportId === null) {
            return null;
        }

        return Report::with(['reportable', 'user:id,name,email', 'dealtBy:id,name'])
            ->find($this->viewingReportId);
    }

    /**
     * Get character count for admin notes.
     */
    public function getAdminNotesCharCountProperty(): int
    {
        return strlen($this->adminNotes);
    }

    public function render()
    {
        return view('livewire.admin.reports', [
            'reports' => $this->getReportsQuery()->paginate(25),
        ])->layout('components.layouts.admin');
    }
}
