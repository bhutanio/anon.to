<?php

namespace App\Livewire\Admin;

use App\Models\Link;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Links extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    public array $selectedLinks = [];

    public bool $selectAll = false;

    public ?int $viewingLinkId = null;

    public bool $showDeleteConfirm = false;

    public ?int $deletingLinkId = null;

    public bool $showBulkDeleteConfirm = false;

    /**
     * Update search and reset pagination.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Update status filter and reset pagination.
     */
    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Toggle select all links on current page.
     */
    public function updatedSelectAll(): void
    {
        if ($this->selectAll) {
            $this->selectedLinks = $this->getLinksQuery()
                ->pluck('id')
                ->toArray();
        } else {
            $this->selectedLinks = [];
        }
    }

    /**
     * Get the links query with filters and search.
     */
    protected function getLinksQuery(): Builder
    {
        return Link::query()
            ->with(['user:id,name,email'])
            ->when($this->search, function (Builder $query) {
                $query->where(function (Builder $q) {
                    $q->where('hash', 'like', "%{$this->search}%")
                        ->orWhere('full_url', 'like', "%{$this->search}%")
                        ->orWhereHas('user', function (Builder $userQuery) {
                            $userQuery->where('name', 'like', "%{$this->search}%")
                                ->orWhere('email', 'like', "%{$this->search}%");
                        });
                });
            })
            ->when($this->statusFilter !== 'all', function (Builder $query) {
                match ($this->statusFilter) {
                    'active' => $query->where('is_active', true),
                    'inactive' => $query->where('is_active', false),
                    'reported' => $query->where('is_reported', true),
                    default => null,
                };
            })
            ->orderBy('created_at', 'desc');
    }

    /**
     * View link details.
     */
    public function viewLink(int $linkId): void
    {
        $this->viewingLinkId = $linkId;
    }

    /**
     * Close view modal.
     */
    public function closeView(): void
    {
        $this->viewingLinkId = null;
    }

    /**
     * Toggle link active status.
     */
    public function toggleActive(int $linkId): void
    {
        $link = Link::findOrFail($linkId);
        $this->authorize('adminDelete', $link);

        $link->update([
            'is_active' => ! $link->is_active,
        ]);

        $this->dispatch('link-updated');
    }

    /**
     * Show delete confirmation modal.
     */
    public function confirmDelete(int $linkId): void
    {
        $this->deletingLinkId = $linkId;
        $this->showDeleteConfirm = true;
    }

    /**
     * Delete a link.
     */
    public function deleteLink(): void
    {
        if ($this->deletingLinkId === null) {
            return;
        }

        $link = Link::findOrFail($this->deletingLinkId);
        $this->authorize('adminDelete', $link);

        $link->delete();

        $this->showDeleteConfirm = false;
        $this->deletingLinkId = null;

        $this->dispatch('link-deleted');
    }

    /**
     * Cancel delete operation.
     */
    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
        $this->deletingLinkId = null;
    }

    /**
     * Show bulk delete confirmation.
     */
    public function confirmBulkDelete(): void
    {
        if (empty($this->selectedLinks)) {
            return;
        }

        $this->showBulkDeleteConfirm = true;
    }

    /**
     * Bulk delete selected links.
     */
    public function bulkDelete(): void
    {
        if (empty($this->selectedLinks)) {
            return;
        }

        DB::transaction(function () {
            Link::whereIn('id', $this->selectedLinks)->delete();
        });

        $this->selectedLinks = [];
        $this->selectAll = false;
        $this->showBulkDeleteConfirm = false;

        $this->dispatch('links-deleted');
    }

    /**
     * Cancel bulk delete.
     */
    public function cancelBulkDelete(): void
    {
        $this->showBulkDeleteConfirm = false;
    }

    /**
     * Bulk toggle active status.
     */
    public function bulkToggleActive(): void
    {
        if (empty($this->selectedLinks)) {
            return;
        }

        DB::transaction(function () {
            $links = Link::whereIn('id', $this->selectedLinks)->get();

            foreach ($links as $link) {
                $link->update([
                    'is_active' => ! $link->is_active,
                ]);
            }
        });

        $this->selectedLinks = [];
        $this->selectAll = false;

        $this->dispatch('links-updated');
    }

    /**
     * Get the viewing link details.
     */
    public function getViewingLinkProperty(): ?Link
    {
        if ($this->viewingLinkId === null) {
            return null;
        }

        return Link::with(['user:id,name,email', 'reports'])->find($this->viewingLinkId);
    }

    public function render()
    {
        return view('livewire.admin.links', [
            'links' => $this->getLinksQuery()->paginate(25),
        ])->layout('components.layouts.admin');
    }
}
